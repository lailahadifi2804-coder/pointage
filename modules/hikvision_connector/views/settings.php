<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4><?php echo html_entity_decode($title); ?></h4>
            <hr>
            <?php echo form_open(admin_url('hikvision_connector/settings'), ['id' => 'hikvision-settings-form']); ?>

            <div class="checkbox">
              <input type="checkbox" name="hikvision_enabled" value="1" <?php echo ($settings['hikvision_enabled'] == 1) ? 'checked' : ''; ?>>
              <label><?php echo _l('hikvision_enabled'); ?></label>
            </div>

            <hr>
            <h4><?php echo _l('hikvision_db_connection'); ?></h4>
            <div class="row">
              <div class="col-md-6">
                <?php echo render_input('hikvision_db_host', 'hikvision_db_host', $settings['hikvision_db_host']); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_input('hikvision_db_port', 'hikvision_db_port', $settings['hikvision_db_port']); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_input('hikvision_db_name', 'hikvision_db_name', $settings['hikvision_db_name']); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_input('hikvision_db_user', 'hikvision_db_user', $settings['hikvision_db_user']); ?>
              </div>
              <div class="col-md-6">
                <?php echo render_input('hikvision_db_password', 'hikvision_db_password', $settings['hikvision_db_password'], 'password'); ?>
                <p class="text-muted"><?php echo _l('hikvision_password_hint'); ?></p>
              </div>
              <div class="col-md-6">
                <?php echo render_input('hikvision_source_table', 'hikvision_source_table', $settings['hikvision_source_table']); ?>
              </div>
            </div>

            <div class="clearfix"></div>
            <button type="button" id="hikvision-test-connection" class="btn btn-default">
              <?php echo _l('hikvision_test_connection'); ?>
            </button>
            <span id="hikvision-test-connection-result"></span>

            <hr>
            <h4><?php echo _l('hikvision_column_mapping'); ?></h4>
            <div class="row">
              <div class="col-md-6"><?php echo render_input('hikvision_column_id', 'hikvision_column_id', $settings['hikvision_column_id']); ?></div>
              <div class="col-md-6"><?php echo render_input('hikvision_column_datetime', 'hikvision_column_datetime', $settings['hikvision_column_datetime']); ?></div>
              <div class="col-md-6"><?php echo render_input('hikvision_column_direction', 'hikvision_column_direction', $settings['hikvision_column_direction']); ?></div>
              <div class="col-md-6"><?php echo render_input('hikvision_column_device_name', 'hikvision_column_device_name', $settings['hikvision_column_device_name']); ?></div>
              <div class="col-md-6"><?php echo render_input('hikvision_column_device_serial', 'hikvision_column_device_serial', $settings['hikvision_column_device_serial']); ?></div>
              <div class="col-md-6"><?php echo render_input('hikvision_column_person_name', 'hikvision_column_person_name', $settings['hikvision_column_person_name']); ?></div>
              <div class="col-md-6"><?php echo render_input('hikvision_column_card_no', 'hikvision_column_card_no', $settings['hikvision_column_card_no']); ?></div>
            </div>

            <hr>
            <h4><?php echo _l('hikvision_sync_settings'); ?></h4>
            <div class="row">
              <div class="col-md-4"><?php echo render_input('hikvision_timezone_source', 'hikvision_timezone_source', $settings['hikvision_timezone_source']); ?></div>
              <div class="col-md-4"><?php echo render_input('hikvision_poll_interval_minutes', 'hikvision_poll_interval_minutes', $settings['hikvision_poll_interval_minutes'], 'number'); ?></div>
              <div class="col-md-4"><?php echo render_input('hikvision_batch_size', 'hikvision_batch_size', $settings['hikvision_batch_size'], 'number'); ?></div>
              <div class="col-md-4"><?php echo render_input('hikvision_max_retry', 'hikvision_max_retry', $settings['hikvision_max_retry'], 'number'); ?></div>
            </div>

            <hr>
            <button type="submit" class="btn btn-primary pull-right"><?php echo _l('save'); ?></button>
            <div class="clearfix"></div>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function() {
  $('#hikvision-test-connection').on('click', function() {
    var $btn = $(this);
    var $result = $('#hikvision-test-connection-result');
    $btn.prop('disabled', true);
    $result.html('');
    $.post(admin_url + 'hikvision_connector/test_connection', $('#hikvision-settings-form').serialize(), function(response) {
      var data = typeof response === 'string' ? JSON.parse(response) : response;
      $result.html('<span class="' + (data.success ? 'text-success' : 'text-danger') + '"> ' + data.message + '</span>');
    }).always(function() {
      $btn.prop('disabled', false);
    });
  });
});
</script>
<?php init_tail(); ?>
