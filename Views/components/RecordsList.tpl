<?php
/**
 * Composant RecordsList - Responsabilité unique : Affichage liste des enregistrements récents
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage de la liste des records récents
 * Respecte le principe ISP : Interface spécialisée pour l'affichage des enregistrements
 */
?>

<!-- Recent Records -->
<?php if (!empty($recent_records)): ?>
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 10px;">
      <h3><?php echo $langs->trans("RecentRecords"); ?></h3>
    </div>
    <ons-list style="margin: 0;">
      <?php 
      $count = 0;
      foreach ($recent_records as $record): 
        if ($count >= 5) break; // Limit to 5 records
        $count++;
        
        // Get type info
        $type = new TimeclockType($db);
        $type->fetch($record->fk_timeclock_type);
        
        $record_date = dol_print_date($db->jdate($record->clock_in_time), 'day');
        $clock_in = dol_print_date($db->jdate($record->clock_in_time), 'hour');
        $clock_out = !empty($record->clock_out_time) ? dol_print_date($db->jdate($record->clock_out_time), 'hour') : '';
        $duration = !empty($record->work_duration) ? TimeHelper::convertSecondsToReadableTime($record->work_duration * 60) : '';
        $status_class = '';
        
        switch ($record->status) {
          case 2: // In progress
            $status_class = 'status-active';
            break;
          case 3: // Completed
            $status_class = 'status-completed';
            break;
          default:
            $status_class = 'status-draft';
        }
      ?>
      <ons-list-item tappable onclick="viewRecord(<?php echo $record->id; ?>)">
        <div class="left">
          <div style="width: 8px; height: 40px; background-color: <?php echo $type->color; ?>; border-radius: 4px;"></div>
        </div>
        <div class="center">
          <div style="font-weight: bold; margin-bottom: 2px;">
            <?php echo $record_date; ?>
          </div>
          <div style="font-size: 14px; color: #666; margin-bottom: 2px;">
            <span style="background-color: <?php echo $type->color; ?>; color: white; padding: 2px 6px; border-radius: 8px; font-size: 11px;">
            <?php echo $type->label; ?>
            </span>
          </div>
          <div style="font-size: 12px; color: #999;">
            <?php echo $clock_in; ?>
            <?php if ($clock_out): ?>
              - <?php echo $clock_out; ?>
            <?php endif; ?>
            <div style="margin-top: 2px;">
              <?php echo $record->getLibStatut(5); ?>
            </div>
          </div>
        </div>
        <div class="right">
          <?php if ($duration): ?>
            <div style="font-weight: bold; color: #4CAF50;">
              <?php echo $duration; ?>
            </div>
          <?php else: ?>
            <ons-icon icon="md-more-vert" style="color: #999;"></ons-icon>
          <?php endif; ?>
        </div>
      </ons-list-item>
      <?php endforeach; ?>
    </ons-list>
    
    <?php if (count($recent_records) > 5): ?>
    <div style="text-align: center; padding: 10px;">
      <ons-button modifier="quiet" onclick="gotoPage('myTimeclockRecords');">
        <?php echo $langs->trans("ViewAllRecords"); ?>
      </ons-button>
    </div>
    <?php endif; ?>
  </ons-card>
</div>
<?php endif; ?>