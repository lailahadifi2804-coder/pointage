<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hikvision_connector extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('hikvision_connector/hikvision_connector_model');

        if (!has_permission('hikvision_connector_management', '', 'view') && !is_admin()) {
            access_denied('hikvision_connector');
        }
    }

    public function index()
    {
        redirect(admin_url('hikvision_connector/settings'));
    }

    public function settings()
    {
        if ($this->input->post()) {
            $this->hikvision_connector_model->save_settings($this->input->post());
            set_alert('success', _l('updated_successfully', _l('hikvision_settings')));
            redirect(admin_url('hikvision_connector/settings'));
        }

        $data['title']    = _l('hikvision_settings');
        $data['settings'] = $this->hikvision_connector_model->get_settings();
        $this->load->view('settings', $data);
    }

    public function test_connection()
    {
        if (!$this->input->is_ajax_request()) {
            die('no direct script access allowed');
        }

        $result = $this->hikvision_connector_model->test_connection($this->input->post());
        echo json_encode($result);
    }

    public function mapping()
    {
        if ($this->input->post() && $this->input->post('card_no')) {
            $this->hikvision_connector_model->add_mapping($this->input->post());
            set_alert('success', _l('added_successfully', _l('hikvision_mapping')));
            redirect(admin_url('hikvision_connector/mapping'));
        }

        $this->load->model('staff_model');
        $data['title']          = _l('hikvision_mapping');
        $data['mappings']       = $this->hikvision_connector_model->get_mappings();
        $data['staff']          = $this->staff_model->get('', ['active' => 1]);
        $data['prefill_card_no'] = $this->input->get('card_no');
        $this->load->view('mapping', $data);
    }

    public function toggle_mapping($id)
    {
        if (!$this->input->is_ajax_request()) {
            die('no direct script access allowed');
        }

        $success = $this->hikvision_connector_model->toggle_mapping($id);
        echo json_encode(['success' => (bool) $success]);
    }

    public function unmapped()
    {
        $this->load->model('staff_model');
        $data['title']           = _l('hikvision_unmapped_events');
        $data['unmapped_events'] = $this->hikvision_connector_model->get_unmapped_events();
        $data['staff']           = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('unmapped_events', $data);
    }

    public function logs()
    {
        $filters = [
            'status'  => $this->input->get('status'),
            'card_no' => $this->input->get('card_no'),
        ];

        $page   = (int) $this->input->get('page') ?: 1;
        $limit  = 50;
        $offset = ($page - 1) * $limit;
        $total  = $this->hikvision_connector_model->count_logs($filters);

        $this->load->library('pagination');
        $pagination_config = [
            'base_url'   => admin_url('hikvision_connector/logs?status=' . $filters['status']),
            'total_rows' => $total,
            'per_page'   => $limit,
            'page_query_string' => true,
            'query_string_segment' => 'page',
        ];
        $this->pagination->initialize($pagination_config);

        $data['title']    = _l('hikvision_logs');
        $data['logs']     = $this->hikvision_connector_model->get_logs($filters, $limit, $offset);
        $data['total']    = $total;
        $data['limit']    = $limit;
        $data['page']     = $page;
        $data['filters']  = $filters;
        $this->load->view('logs', $data);
    }

    public function retry_event($id)
    {
        if (!$this->input->is_ajax_request()) {
            die('no direct script access allowed');
        }

        $this->hikvision_connector_model->retry_event($id);
        echo json_encode(['success' => true]);
    }
}
