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
            <?php echo form_open(admin_url('hikvision_connector/logs'), ['method' => 'get', 'class' => 'form-inline']); ?>
            <div class="form-group">
              <select name="status" class="form-control" onchange="this.form.submit()">
                <option value=""><?php echo _l('hikvision_all_statuses'); ?></option>
                <?php foreach (['pending', 'imported', 'unmapped', 'error', 'ignored'] as $status) { ?>
                  <option value="<?php echo $status; ?>" <?php echo ($filters['status'] == $status) ? 'selected' : ''; ?>>
                    <?php echo _l('hikvision_status_' . $status); ?>
                  </option>
                <?php } ?>
              </select>
            </div>
            <?php echo form_close(); ?>
            <hr>
            <table class="table">
              <thead>
                <tr>
                  <th><?php echo _l('hikvision_auth_datetime'); ?></th>
                  <th><?php echo _l('hikvision_direction'); ?></th>
                  <th><?php echo _l('hikvision_card_no'); ?></th>
                  <th><?php echo _l('hikvision_staff'); ?></th>
                  <th><?php echo _l('hikvision_status'); ?></th>
                  <th><?php echo _l('hikvision_error'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($logs as $row) { ?>
                  <tr>
                    <td><?php echo _dt($row['auth_datetime']); ?></td>
                    <td><?php echo html_escape($row['direction_raw']); ?></td>
                    <td><?php echo html_escape($row['card_no']); ?></td>
                    <td><?php echo $row['staffid'] ? html_escape(get_staff_full_name($row['staffid'])) : ''; ?></td>
                    <td><span class="label bg-<?php echo $row['status'] == 'imported' ? 'green' : ($row['status'] == 'error' ? 'danger' : 'warning'); ?>">
                      <?php echo _l('hikvision_status_' . $row['status']); ?>
                    </span></td>
                    <td><?php echo html_escape($row['error_message']); ?></td>
                    <td>
                      <?php if ($row['status'] == 'error') { ?>
                        <button class="btn btn-default btn-icon hikvision-retry-event" data-id="<?php echo $row['id']; ?>">
                          <?php echo _l('hikvision_retry'); ?>
                        </button>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
            <?php echo $this->pagination->create_links(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function() {
  $('.hikvision-retry-event').on('click', function() {
    var id = $(this).data('id');
    $.post(admin_url + 'hikvision_connector/retry_event/' + id, {}, function() {
      location.reload();
    });
  });
});
</script>
<?php init_tail(); ?>
