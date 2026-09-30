<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'hikvision_staff_mapping')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "hikvision_staff_mapping` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `staffid` int(11) NOT NULL,
        `card_no` varchar(50) NOT NULL,
        `device_serial_no` varchar(100) NULL DEFAULT NULL,
        `person_name_hikvision` varchar(200) NULL DEFAULT NULL,
        `active` tinyint(1) NOT NULL DEFAULT 1,
        `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `created_by` int(11) NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_card_no` (`card_no`),
        KEY `idx_staffid` (`staffid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'hikvision_events')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "hikvision_events` (
        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `hikvision_event_id` varchar(100) NOT NULL,
        `auth_datetime_raw` varchar(30) NOT NULL,
        `auth_datetime` datetime NOT NULL,
        `direction_raw` varchar(10) NOT NULL,
        `device_name` varchar(150) NULL DEFAULT NULL,
        `device_serial_no` varchar(100) NULL DEFAULT NULL,
        `person_name` varchar(200) NULL DEFAULT NULL,
        `card_no` varchar(50) NULL DEFAULT NULL,
        `staffid` int(11) NULL DEFAULT NULL,
        `type_check` tinyint(1) NULL DEFAULT NULL,
        `status` enum('pending','imported','unmapped','error','ignored') NOT NULL DEFAULT 'pending',
        `check_in_out_id` int(11) NULL DEFAULT NULL,
        `retry_count` int(11) NOT NULL DEFAULT 0,
        `error_message` text NULL,
        `date_fetched` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_processed` datetime NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_hikvision_event_id` (`hikvision_event_id`),
        KEY `idx_status` (`status`),
        KEY `idx_card_no` (`card_no`),
        KEY `idx_staffid` (`staffid`),
        KEY `idx_auth_datetime` (`auth_datetime`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Valeurs par défaut des options (get_option/update_option, core Perfex — table tboptions).
$defaults = [
    'hikvision_enabled'               => '0',
    'hikvision_db_host'               => '',
    'hikvision_db_port'               => '3306',
    'hikvision_db_name'               => '',
    'hikvision_db_user'               => '',
    'hikvision_db_password'           => '',
    'hikvision_source_table'          => 'acs_event',
    'hikvision_column_id'             => 'ID',
    'hikvision_column_datetime'       => 'Authentication Date and Time',
    'hikvision_column_direction'      => 'Direction',
    'hikvision_column_device_name'    => 'Device Name',
    'hikvision_column_device_serial'  => 'Device Serial No.',
    'hikvision_column_person_name'    => 'Person Name',
    'hikvision_column_card_no'        => 'Card No.',
    'hikvision_timezone_source'       => date_default_timezone_get(),
    'hikvision_poll_interval_minutes' => '5',
    'hikvision_batch_size'            => '500',
    'hikvision_max_retry'             => '5',
    'hikvision_last_fetched_id'       => '0',
];

foreach ($defaults as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}
