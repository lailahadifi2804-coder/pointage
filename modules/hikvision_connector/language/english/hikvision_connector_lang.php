<?php

defined('BASEPATH') or exit('No direct script access allowed');

$lang['hikvision_connector']            = 'Hikvision Connector';
$lang['hikvision_connector_management'] = 'Hikvision Connector - Manage';
$lang['hikvision_settings']             = 'Hikvision Settings';
$lang['hikvision_mapping']              = 'Staff Mapping';
$lang['hikvision_unmapped_events']      = 'Unmapped Events';
$lang['hikvision_logs']                 = 'Event Log';

$lang['hikvision_enabled']              = 'Enable Hikvision synchronization';
$lang['hikvision_db_connection']        = 'Third-party Hikvision database connection';
$lang['hikvision_db_host']              = 'Host';
$lang['hikvision_db_port']              = 'Port';
$lang['hikvision_db_name']              = 'Database';
$lang['hikvision_db_user']              = 'Username';
$lang['hikvision_db_password']          = 'Password';
$lang['hikvision_password_hint']        = 'Leave empty (or "********") to keep the currently stored password.';
$lang['hikvision_source_table']         = 'Source table / view';
$lang['hikvision_test_connection']      = 'Test connection';
$lang['hikvision_connection_ok']        = 'Connection successful (%d rows found in table).';
$lang['hikvision_connection_failed']    = 'Unable to connect to the third-party database. Check the credentials.';
$lang['hikvision_table_not_found']      = 'Connected successfully but the configured table/view was not found.';

$lang['hikvision_column_mapping']       = 'Column mapping';
$lang['hikvision_column_id']            = 'ID column';
$lang['hikvision_column_datetime']      = 'Authentication Date and Time column';
$lang['hikvision_column_direction']     = 'Direction column';
$lang['hikvision_column_device_name']   = 'Device Name column';
$lang['hikvision_column_device_serial'] = 'Device Serial No. column';
$lang['hikvision_column_person_name']   = 'Person Name column';
$lang['hikvision_column_card_no']       = 'Card No. column';

$lang['hikvision_sync_settings']            = 'Synchronization settings';
$lang['hikvision_timezone_source']          = 'Source timezone';
$lang['hikvision_poll_interval_minutes']    = 'Sync frequency (minutes)';
$lang['hikvision_batch_size']               = 'Batch size';
$lang['hikvision_max_retry']                = 'Max retry count';

$lang['hikvision_add_mapping']              = 'Add mapping';
$lang['hikvision_card_no']                  = 'Card No.';
$lang['hikvision_staff']                    = 'Perfex staff member';
$lang['hikvision_person_name_reference']    = 'Hikvision name (reference only)';
$lang['hikvision_device_serial_no']         = 'Controller serial no. (optional)';
$lang['hikvision_create_mapping']           = 'Create mapping';

$lang['hikvision_unmapped_events_help']     = 'These cards were seen in Hikvision events but are not linked to any Perfex staff member. No attendance record is created for them until a mapping exists.';
$lang['hikvision_no_unmapped_events']       = 'No unmapped events.';
$lang['hikvision_occurrences']              = 'Occurrences';
$lang['hikvision_last_seen']                = 'Last seen';

$lang['hikvision_all_statuses']             = 'All statuses';
$lang['hikvision_auth_datetime']            = 'Date & time';
$lang['hikvision_direction']                = 'Direction';
$lang['hikvision_status']                   = 'Status';
$lang['hikvision_error']                    = 'Error';
$lang['hikvision_retry']                    = 'Retry';
$lang['hikvision_status_pending']           = 'Pending';
$lang['hikvision_status_imported']          = 'Imported';
$lang['hikvision_status_unmapped']          = 'Unmapped';
$lang['hikvision_status_error']             = 'Error';
$lang['hikvision_status_ignored']           = 'Ignored';
