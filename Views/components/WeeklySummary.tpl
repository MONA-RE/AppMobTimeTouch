<?php
/**
 * Composant WeeklySummary - Responsabilité unique : Affichage résumé hebdomadaire
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du résumé hebdomadaire
 * Respecte le principe OCP : Extensible pour différents affichages de résumés
 */
?>

<!-- Weekly Summary -->
<?php if ($weekly_summary): ?>
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 10px;">
      <h3><?php echo $langs->trans("WeekSummary"); ?> - <?php echo $langs->trans("Week"); ?> <?php echo $weekly_summary->week_number; ?></h3>
    </div>
    <div class="content" style="padding: 0 15px 15px 15px;">
      <ons-row>
        <ons-col width="33%">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("TotalHours"); ?>
            </p>
            <p style="margin: 0; font-size: 16px; font-weight: bold;">
              <?php echo TimeHelper::convertSecondsToReadableTime($weekly_summary->total_hours * 3600); ?>
            </p>
          </div>
        </ons-col>
        <ons-col width="33%">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("DaysWorked"); ?>
            </p>
            <p style="margin: 0; font-size: 16px; font-weight: bold;">
              <?php echo $weekly_summary->days_worked; ?>
            </p>
          </div>
        </ons-col>
        <ons-col width="33%">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("Status"); ?>
            </p>
            <div style="margin: 0;">
              <?php echo $weekly_summary->getLibStatut(3); ?>
            </div>
          </div>
        </ons-col>
      </ons-row>
      
      <?php if ($weekly_summary->overtime_hours > 0): ?>
      <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 10px; margin-top: 10px;">
        <p style="margin: 0; color: #856404; font-size: 14px;">
          <ons-icon icon="md-warning" style="color: #ffc107;"></ons-icon>
          <strong><?php echo $langs->trans("OvertimeHours"); ?>:</strong>
          <?php echo TimeHelper::convertSecondsToReadableTime($weekly_summary->overtime_hours * 3600); ?>
        </p>
      </div>
      <?php endif; ?>
      
      <!-- Weekly progress -->
      <?php 
      $weekly_progress = min(100, ($weekly_summary->total_hours / $weekly_summary->expected_hours) * 100);
      $weekly_color = $weekly_progress > 100 ? '#f44336' : ($weekly_progress > 80 ? '#FF9800' : '#4CAF50');
      ?>
      <div style="margin-top: 15px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
          <span style="font-size: 12px; color: #666;"><?php echo $langs->trans("WeeklyTarget"); ?></span>
          <span style="font-size: 12px; color: #666;"><?php echo round($weekly_progress, 1); ?>%</span>
        </div>
        <div style="background-color: #e0e0e0; height: 6px; border-radius: 3px; overflow: hidden;">
          <div style="background-color: <?php echo $weekly_color; ?>; height: 100%; width: <?php echo min(100, $weekly_progress); ?>%; transition: width 0.3s ease;"></div>
        </div>
      </div>
    </div>
  </ons-card>
</div>
<?php endif; ?>