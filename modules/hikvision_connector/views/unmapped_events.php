<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <h4><?php echo html_entity_decode($title); ?></h4>
            <p class="text-muted"><?php echo _l('hikvision_unmapped_events_help'); ?></p>
            <hr>
            <table class="table">
              <thead>
                <tr>
                  <th><?php echo _l('hikvision_card_no'); ?></th>
                  <th><?php echo _l('hikvision_person_name_reference'); ?></th>
                  <th><?php echo _l('hikvision_occurrences'); ?></th>
                  <th><?php echo _l('hikvision_last_seen'); ?></th>
                  <th><?php echo _l('options'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($unmapped_events)) { ?>
                  <tr><td colspan="5" class="text-center"><?php echo _l('hikvision_no_unmapped_events'); ?></td></tr>
                <?php } ?>
                <?php foreach ($unmapped_events as $row) { ?>
                  <tr>
                    <td><?php echo html_escape($row['card_no']); ?></td>
                    <td><?php echo html_escape($row['person_name']); ?></td>
                    <td><?php echo (int) $row['occurrences']; ?></td>
                    <td><?php echo _dt($row['last_seen']); ?></td>
                    <td>
                      <a href="<?php echo admin_url('hikvision_connector/mapping?card_no=' . urlencode($row['card_no'])); ?>" class="btn btn-info btn-icon">
                        <?php echo _l('hikvision_create_mapping'); ?>
                      </a>
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
<?php init_tail(); ?>
