<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Connecteur Hikvision iVMS-4200 AC -> module Timesheets (check_in_out).
 *
 * Ce modèle ne modifie jamais Timesheets_model.php : il réutilise
 * add_check_in_out_value_to_timesheet() (déjà publique dans le module
 * timesheets) après avoir inséré directement dans check_in_out, exactement
 * comme le fait déjà le cron auto_checkout_cron() du module timesheets.
 */
class Hikvision_connector_model extends App_Model
{
    private $settings_fields = [
        'hikvision_enabled',
        'hikvision_db_host',
        'hikvision_db_port',
        'hikvision_db_name',
        'hikvision_db_user',
        'hikvision_db_password',
        'hikvision_source_table',
        'hikvision_column_id',
        'hikvision_column_datetime',
        'hikvision_column_direction',
        'hikvision_column_device_name',
        'hikvision_column_device_serial',
        'hikvision_column_person_name',
        'hikvision_column_card_no',
        'hikvision_timezone_source',
        'hikvision_poll_interval_minutes',
        'hikvision_batch_size',
        'hikvision_max_retry',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /* ==================== Paramètres ==================== */

    public function get_settings()
    {
        $settings = [];
        foreach ($this->settings_fields as $field) {
            $settings[$field] = get_option($field);
        }
        // Le mot de passe en clair ne doit jamais revenir vers la vue.
        $settings['hikvision_db_password'] = $settings['hikvision_db_password'] !== '' ? '********' : '';

        return $settings;
    }

    public function save_settings($data)
    {
        foreach ($this->settings_fields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            if ($field === 'hikvision_db_password') {
                if ($data[$field] === '' || $data[$field] === '********') {
                    continue; // conserve le mot de passe existant
                }
                update_option($field, $this->encrypt_value($data[$field]));
                continue;
            }

            update_option($field, $data[$field]);
        }
    }

    private function encrypt_value($value)
    {
        $this->load->library('encryption');

        return $this->encryption->encrypt($value);
    }

    private function decrypt_value($value)
    {
        if ($value === '' || $value === null) {
            return '';
        }
        $this->load->library('encryption');

        return $this->encryption->decrypt($value);
    }

    /* ==================== Connexion base tierce ==================== */

    private function qi($identifier)
    {
        $identifier = str_replace('`', '', $identifier);

        return '`' . $identifier . '`';
    }

    /**
     * Ouvre une connexion secondaire en lecture seule vers la base MySQL
     * tierce (iVMS-4200). N'affecte jamais $this->db (connexion Perfex).
     * @return object|false
     */
    public function get_source_db($config_override = null)
    {
        $config = $config_override ?: [
            'hostname' => get_option('hikvision_db_host'),
            'port'     => get_option('hikvision_db_port'),
            'username' => get_option('hikvision_db_user'),
            'password' => $this->decrypt_value(get_option('hikvision_db_password')),
            'database' => get_option('hikvision_db_name'),
        ];

        $db_config = [
            'hostname' => $config['hostname'],
            'port'     => $config['port'],
            'username' => $config['username'],
            'password' => $config['password'],
            'database' => $config['database'],
            'dbdriver' => 'mysqli',
            'dbprefix' => '',
            'pconnect' => false,
            'db_debug' => false,
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
        ];

        $source_db = $this->load->database($db_config, true);

        if (!$source_db || !$source_db->conn_id) {
            return false;
        }

        return $source_db;
    }

    public function test_connection($data)
    {
        $config = [
            'hostname' => $data['hikvision_db_host'],
            'port'     => $data['hikvision_db_port'],
            'username' => $data['hikvision_db_user'],
            'database' => $data['hikvision_db_name'],
            'password' => (!empty($data['hikvision_db_password']) && $data['hikvision_db_password'] !== '********')
                ? $data['hikvision_db_password']
                : $this->decrypt_value(get_option('hikvision_db_password')),
        ];

        $source_db = $this->get_source_db($config);

        if (!$source_db) {
            return ['success' => false, 'message' => _l('hikvision_connection_failed')];
        }

        $query = $source_db->query('SELECT COUNT(*) as total FROM ' . $this->qi($data['hikvision_source_table']));
        $source_db->close();

        if ($query === false) {
            return ['success' => false, 'message' => _l('hikvision_table_not_found')];
        }

        $row = $query->row();

        return ['success' => true, 'message' => sprintf(_l('hikvision_connection_ok'), $row->total)];
    }

    /* ==================== Timezone ==================== */

    public function normalize_datetime($raw, $source_tz)
    {
        try {
            $dt = DateTime::createFromFormat('Y-m-d\TH:i:s', $raw, new DateTimeZone($source_tz));
            if (!$dt) {
                $dt = new DateTime($raw, new DateTimeZone($source_tz));
            }
            $dt->setTimezone(new DateTimeZone(date_default_timezone_get()));

            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return date('Y-m-d H:i:s', strtotime($raw));
        }
    }

    /* ==================== Phase 1 : Fetch ==================== */

    public function fetch_events()
    {
        $batch_size = (int) get_option('hikvision_batch_size');
        $last_id    = (int) get_option('hikvision_last_fetched_id');
        $source_tz  = get_option('hikvision_timezone_source');

        $table  = get_option('hikvision_source_table');
        $col_id = get_option('hikvision_column_id');

        $source_db = $this->get_source_db();
        if (!$source_db) {
            log_activity('Hikvision Connector: impossible de se connecter à la base tierce');

            return false;
        }

        $sql = 'SELECT '
            . $this->qi($col_id) . ' AS event_id, '
            . $this->qi(get_option('hikvision_column_datetime')) . ' AS auth_datetime, '
            . $this->qi(get_option('hikvision_column_direction')) . ' AS direction, '
            . $this->qi(get_option('hikvision_column_device_name')) . ' AS device_name, '
            . $this->qi(get_option('hikvision_column_device_serial')) . ' AS device_serial, '
            . $this->qi(get_option('hikvision_column_person_name')) . ' AS person_name, '
            . $this->qi(get_option('hikvision_column_card_no')) . ' AS card_no '
            . 'FROM ' . $this->qi($table) . ' '
            . 'WHERE ' . $this->qi($col_id) . ' > ' . $last_id . ' '
            . 'ORDER BY ' . $this->qi($col_id) . ' ASC '
            . 'LIMIT ' . $batch_size;

        $query = $source_db->query($sql);

        if ($query === false) {
            log_activity('Hikvision Connector: échec de la requête de lecture sur la base tierce');
            $source_db->close();

            return false;
        }

        $rows = $query->result_array();
        $source_db->close();

        if (empty($rows)) {
            return true;
        }

        $max_id = $last_id;

        foreach ($rows as $row) {
            $event_id   = (string) $row['event_id'];
            $normalized = $this->normalize_datetime($row['auth_datetime'], $source_tz);

            $this->db->query(
                'INSERT IGNORE INTO ' . db_prefix() . 'hikvision_events '
                . '(hikvision_event_id, auth_datetime_raw, auth_datetime, direction_raw, device_name, device_serial_no, person_name, card_no, status) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")',
                [
                    $event_id,
                    $row['auth_datetime'],
                    $normalized,
                    $row['direction'],
                    $row['device_name'],
                    $row['device_serial'],
                    $row['person_name'],
                    $row['card_no'],
                ]
            );

            if ((int) $row['event_id'] > $max_id) {
                $max_id = (int) $row['event_id'];
            }
        }

        update_option('hikvision_last_fetched_id', (string) $max_id);

        return true;
    }

    /* ==================== Phase 2 : Process ==================== */

    public function process_events()
    {
        $max_retry = (int) get_option('hikvision_max_retry');

        $this->db->where("(status = 'pending' OR (status = 'error' AND retry_count < " . $max_retry . '))');
        $this->db->order_by('auth_datetime', 'asc');
        $this->db->limit(500);
        $events = $this->db->get(db_prefix() . 'hikvision_events')->result_array();

        if (empty($events)) {
            return;
        }

        $this->load->model('timesheets/timesheets_model');

        foreach ($events as $event) {
            $this->process_single_event($event);
        }
    }

    private function process_single_event($event)
    {
        $card_no = trim((string) $event['card_no']);

        $this->db->where('card_no', $card_no);
        $this->db->where('active', 1);
        $mapping = $this->db->get(db_prefix() . 'hikvision_staff_mapping')->row();

        if (!$mapping) {
            $this->db->where('id', $event['id']);
            $this->db->update(db_prefix() . 'hikvision_events', [
                'status'         => 'unmapped',
                'date_processed' => date('Y-m-d H:i:s'),
            ]);

            return;
        }

        $direction = trim((string) $event['direction_raw']);
        if (strcasecmp($direction, 'Enter') === 0) {
            $type_check = 1;
        } elseif (strcasecmp($direction, 'Exit') === 0) {
            $type_check = 2;
        } else {
            $this->db->where('id', $event['id']);
            $this->db->update(db_prefix() . 'hikvision_events', [
                'status'         => 'error',
                'retry_count'    => $event['retry_count'] + 1,
                'error_message'  => 'Valeur Direction non reconnue: ' . $direction,
                'date_processed' => date('Y-m-d H:i:s'),
            ]);

            return;
        }

        try {
            $data = [
                'staff_id'       => $mapping->staffid,
                'date'           => $event['auth_datetime'],
                'type_check'     => $type_check,
                'type'           => 'W',
                'route_point_id' => 0,
                'workplace_id'   => 0,
            ];

            $this->db->insert(db_prefix() . 'check_in_out', $data);
            $check_in_out_id = $this->db->insert_id();

            if (!$check_in_out_id) {
                throw new Exception('Échec insertion check_in_out');
            }

            $this->timesheets_model->add_check_in_out_value_to_timesheet(
                $mapping->staffid,
                date('Y-m-d', strtotime($event['auth_datetime']))
            );

            $this->db->where('id', $event['id']);
            $this->db->update(db_prefix() . 'hikvision_events', [
                'status'          => 'imported',
                'staffid'         => $mapping->staffid,
                'type_check'      => $type_check,
                'check_in_out_id' => $check_in_out_id,
                'date_processed'  => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
            $this->db->where('id', $event['id']);
            $this->db->update(db_prefix() . 'hikvision_events', [
                'status'         => 'error',
                'retry_count'    => $event['retry_count'] + 1,
                'error_message'  => $e->getMessage(),
                'date_processed' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /* ==================== Mapping employés ==================== */

    public function get_mappings()
    {
        $this->db->select(db_prefix() . 'hikvision_staff_mapping.*, CONCAT(firstname, " ", lastname) as staff_name');
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hikvision_staff_mapping.staffid', 'left');
        $this->db->order_by(db_prefix() . 'hikvision_staff_mapping.id', 'desc');

        return $this->db->get(db_prefix() . 'hikvision_staff_mapping')->result_array();
    }

    public function add_mapping($data)
    {
        $insert = [
            'staffid'               => (int) $data['staffid'],
            'card_no'               => trim($data['card_no']),
            'device_serial_no'      => !empty($data['device_serial_no']) ? trim($data['device_serial_no']) : null,
            'person_name_hikvision' => !empty($data['person_name_hikvision']) ? trim($data['person_name_hikvision']) : null,
            'active'                => 1,
            'date_created'          => date('Y-m-d H:i:s'),
            'created_by'            => get_staff_user_id(),
        ];

        $this->db->insert(db_prefix() . 'hikvision_staff_mapping', $insert);
        $id = $this->db->insert_id();

        if ($id) {
            $this->reprocess_card($insert['card_no']);
        }

        return $id;
    }

    public function toggle_mapping($id)
    {
        $mapping = $this->db->where('id', $id)->get(db_prefix() . 'hikvision_staff_mapping')->row();
        if (!$mapping) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'hikvision_staff_mapping', [
            'active' => $mapping->active == 1 ? 0 : 1,
        ]);

        return true;
    }

    /* ==================== Événements non mappés ==================== */

    public function get_unmapped_events()
    {
        $this->db->select('card_no, person_name, COUNT(*) as occurrences, MAX(auth_datetime) as last_seen');
        $this->db->where('status', 'unmapped');
        $this->db->group_by('card_no');
        $this->db->order_by('last_seen', 'desc');

        return $this->db->get(db_prefix() . 'hikvision_events')->result_array();
    }

    /**
     * Rejoue automatiquement les événements en attente pour une carte
     * dès qu'un mapping est créé (cf. conception §4.3).
     */
    public function reprocess_card($card_no)
    {
        $this->db->where('card_no', $card_no);
        $this->db->where('status', 'unmapped');
        $this->db->update(db_prefix() . 'hikvision_events', ['status' => 'pending']);
    }

    /* ==================== Journal ==================== */

    private function apply_log_filters($filters)
    {
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        if (!empty($filters['card_no'])) {
            $this->db->where('card_no', $filters['card_no']);
        }
    }

    public function get_logs($filters = [], $limit = 50, $offset = 0)
    {
        $this->apply_log_filters($filters);
        $this->db->order_by('auth_datetime', 'desc');
        $this->db->limit($limit, $offset);

        return $this->db->get(db_prefix() . 'hikvision_events')->result_array();
    }

    public function count_logs($filters = [])
    {
        $this->apply_log_filters($filters);

        return $this->db->count_all_results(db_prefix() . 'hikvision_events');
    }

    public function retry_event($id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'hikvision_events', [
            'status'        => 'pending',
            'retry_count'   => 0,
            'error_message' => null,
        ]);
    }
}
