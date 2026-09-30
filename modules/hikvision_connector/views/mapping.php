<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-5">
        <div class="panel_s">
          <div class="panel-body">
            <h4><?php echo _l('hikvision_add_mapping'); ?></h4>
            <hr>
            <?php echo form_open(admin_url('hikvision_connector/mapping')); ?>
            <?php echo render_input('card_no', 'hikvision_card_no', $prefill_card_no); ?>
            <div class="form-group">
              <label for="staffid"><?php echo _l('hikvision_staff'); ?></label>
              <select name="staffid" id="staffid" class="form-control selectpicker" data-live-search="true" required>
                <option value=""></option>
                <?php foreach ($staff as $member) { ?>
                  <option value="<?php echo $member['staffid']; ?>"><?php echo $member['firstname'] . ' ' . $member['lastname']; ?></option>
                <?php } ?>
              </select>
            </div>
            <?php echo render_input('person_name_hikvision', 'hikvision_person_name_reference'); ?>
            <?php echo render_input('device_serial_no', 'hikvision_device_serial_no'); ?>
            <button type="submit" class="btn btn-primary"><?php echo _l('add'); ?></button>
            <?php echo form_close(); ?>
          </div>
        </div>
      </div>
      <div class="col-md-7">
        <div class="panel_s">
          <div class="panel-body">
            <h4><?php echo html_entity_decode($title); ?></h4>
            <hr>
            <table class="table">
              <thead>
                <tr>
                  <th><?php echo _l('hikvision_card_no'); ?></th>
                  <th><?php echo _l('hikvision_staff'); ?></th>
                  <th><?php echo _l('hikvision_person_name_reference'); ?></th>
                  <th><?php echo _l('active'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($mappings as $row) { ?>
                  <tr>
                    <td><?php echo html_escape($row['card_no']); ?></td>
                    <td><?php echo html_escape($row['staff_name']); ?></td>
                    <td><?php echo html_escape($row['person_name_hikvision']); ?></td>
                    <td>
                      <?php echo $row['active'] == 1 ? '<span class="label bg-green">' . _l('active') . '</span>' : '<span class="label bg-default">' . _l('inactive') . '</span>'; ?>
                    </td>
                    <td>
                      <button class="btn btn-default btn-icon hikvision-toggle-mapping" data-id="<?php echo $row['id']; ?>">
                        <?php echo $row['active'] == 1 ? _l('deactivate') : _l('activate'); ?>
                      </button>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$(function() {
  $('.hikvision-toggle-mapping').on('click', function() {
    var id = $(this).data('id');
    $.post(admin_url + 'hikvision_connector/toggle_mapping/' + id, {}, function() {
      location.reload();
    });
  });
});
</script>
<?php init_tail(); ?>
