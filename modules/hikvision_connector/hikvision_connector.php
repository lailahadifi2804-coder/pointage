<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Hikvision Connector
Description: Synchronise les événements de badgeage Hikvision iVMS-4200 AC vers le module Timesheets (Présence / Quitter) sans dupliquer le système RH existant.
Requires at least: 2.3.*
*/

define('HIKVISION_CONNECTOR_MODULE_NAME', 'hikvision_connector');

hooks()->add_action('admin_init', 'hikvision_connector_permissions');
hooks()->add_action('admin_init', 'hikvision_connector_init_menu_items');
hooks()->add_action('before_cron_run', 'hikvision_connector_fetch_cron');
hooks()->add_action('before_cron_run', 'hikvision_connector_process_cron');

register_activation_hook(HIKVISION_CONNECTOR_MODULE_NAME, 'hikvision_connector_module_activation_hook');
register_language_files(HIKVISION_CONNECTOR_MODULE_NAME, [HIKVISION_CONNECTOR_MODULE_NAME]);

function hikvision_connector_module_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

/**
 * Permission de gestion du connecteur, suivant le même modèle que le module timesheets
 * (register_staff_capabilities avec view_own / view).
 */
function hikvision_connector_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
    ];
    register_staff_capabilities('hikvision_connector_management', $capabilities, _l('hikvision_connector_management'));
}

/**
 * Menu autonome (aucune modification du module timesheets requise).
 */
function hikvision_connector_init_menu_items()
{
    $CI = &get_instance();

    if (!has_permission('hikvision_connector_management', '', 'view') && !is_admin()) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('hikvision_connector', [
        'name'     => _l('hikvision_connector'),
        'icon'     => 'fa fa-id-card-o',
        'position' => 31,
    ]);

    $CI->app_menu->add_sidebar_children_item('hikvision_connector', [
        'slug'     => 'hikvision_connector_settings',
        'name'     => _l('hikvision_settings'),
        'href'     => admin_url('hikvision_connector/settings'),
        'icon'     => 'fa fa-gears',
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('hikvision_connector', [
        'slug'     => 'hikvision_connector_mapping',
        'name'     => _l('hikvision_mapping'),
        'href'     => admin_url('hikvision_connector/mapping'),
        'icon'     => 'fa fa-link',
        'position' => 2,
    ]);

    $CI->app_menu->add_sidebar_children_item('hikvision_connector', [
        'slug'     => 'hikvision_connector_unmapped',
        'name'     => _l('hikvision_unmapped_events'),
        'href'     => admin_url('hikvision_connector/unmapped'),
        'icon'     => 'fa fa-question-circle',
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_children_item('hikvision_connector', [
        'slug'     => 'hikvision_connector_logs',
        'name'     => _l('hikvision_logs'),
        'href'     => admin_url('hikvision_connector/logs'),
        'icon'     => 'fa fa-list-alt',
        'position' => 4,
    ]);
}

/**
 * Phase 1 (cron) : copie brute des nouveaux événements depuis la base tierce Hikvision
 * vers la table de staging locale tblhikvision_events. N'écrit jamais dans check_in_out.
 */
function hikvision_connector_fetch_cron()
{
    $CI = &get_instance();
    $CI->load->model('hikvision_connector/hikvision_connector_model');

    if (get_option('hikvision_enabled') != '1') {
        return;
    }

    $CI->hikvision_connector_model->fetch_events();
}

/**
 * Phase 2 (cron) : résolution du mapping Card No. -> staffid et écriture dans
 * check_in_out via les fonctions déjà existantes du module timesheets.
 */
function hikvision_connector_process_cron()
{
    $CI = &get_instance();
    $CI->load->model('hikvision_connector/hikvision_connector_model');

    if (get_option('hikvision_enabled') != '1') {
        return;
    }

    $CI->hikvision_connector_model->process_events();
}
