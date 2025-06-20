<?php
/**
 * Composant WeeklySummary - Responsabilité unique : Affichage résumé hebdomadaire
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du résumé hebdomadaire
 * Respecte le principe OCP : Extensible pour différents affichages de résumés
 * 
 * Compatible avec array et objet $weekly_summary
 */

// Debug: Log weekly summary data
dol_syslog("WeeklySummary.tpl: weekly_summary = " . json_encode($weekly_summary), LOG_DEBUG);

// Normaliser les données (compatibilité array/objet)
$week_total_hours = 0;
$week_days_worked = 0;
$week_number = date('W');
$week_overtime_hours = 0;
$week_expected_hours = 40; // Par défaut 40h/semaine

if ($weekly_summary) {
    if (is_array($weekly_summary)) {
        // Format array
        $week_total_hours = $weekly_summary['total_hours'] ?? 0;
        $week_days_worked = $weekly_summary['days_worked'] ?? 0;
        $week_number = $weekly_summary['week_number'] ?? date('W');
        $week_overtime_hours = $weekly_summary['overtime_hours'] ?? 0;
        $week_expected_hours = $weekly_summary['expected_hours'] ?? 40;
    } else {
        // Format objet
        $week_total_hours = $weekly_summary->total_hours ?? 0;
        $week_days_worked = $weekly_summary->days_worked ?? 0;
        $week_number = $weekly_summary->week_number ?? date('W');
        $week_overtime_hours = $weekly_summary->overtime_hours ?? 0;
        $week_expected_hours = $weekly_summary->expected_hours ?? 40;
    }
}

// Debug: Log extracted values
dol_syslog("WeeklySummary.tpl: week_total_hours = $week_total_hours, week_days_worked = $week_days_worked", LOG_DEBUG);
?>

<!-- Weekly Summary -->
<?php if ($weekly_summary): ?>
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 10px;">
      <h3><?php echo $langs->trans("WeekSummary"); ?> - <?php echo $langs->trans("Week"); ?> <?php echo $week_number; ?></h3>
    </div>
    <div class="content" style="padding: 0 15px 15px 15px;">
      <ons-row>
        <ons-col width="33%">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("TotalHours"); ?>
            </p>
            <p style="margin: 0; font-size: 16px; font-weight: bold;">
              <?php echo TimeHelper::convertSecondsToReadableTime($week_total_hours * 3600); ?>
            </p>
          </div>
        </ons-col>
        <ons-col width="33%">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("DaysWorked"); ?>
            </p>
            <p style="margin: 0; font-size: 16px; font-weight: bold;">
              <?php echo $week_days_worked; ?>
            </p>
          </div>
        </ons-col>
        <ons-col width="33%">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("Status"); ?>
            </p>
            <div style="margin: 0;">
              <?php 
              // Calcul du statut basé sur les heures
              if ($week_total_hours >= $week_expected_hours) {
                  echo '<span style="color: #4CAF50; font-weight: bold;">' . $langs->trans("Complete") . '</span>';
              } elseif ($week_total_hours >= $week_expected_hours * 0.8) {
                  echo '<span style="color: #FF9800; font-weight: bold;">' . $langs->trans("InProgress") . '</span>';
              } else {
                  echo '<span style="color: #f44336; font-weight: bold;">' . $langs->trans("Incomplete") . '</span>';
              }
              ?>
            </div>
          </div>
        </ons-col>
      </ons-row>
      
      <?php if ($week_overtime_hours > 0): ?>
      <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 10px; margin-top: 10px;">
        <p style="margin: 0; color: #856404; font-size: 14px;">
          <ons-icon icon="md-warning" style="color: #ffc107;"></ons-icon>
          <strong><?php echo $langs->trans("OvertimeHours"); ?>:</strong>
          <?php echo TimeHelper::convertSecondsToReadableTime($week_overtime_hours * 3600); ?>
        </p>
      </div>
      <?php endif; ?>
      
      <!-- Weekly progress -->
      <?php 
      $weekly_progress = $week_expected_hours > 0 ? min(100, ($week_total_hours / $week_expected_hours) * 100) : 0;
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
<?php else: ?>
<!-- Fallback: Show basic weekly summary like original home.php -->
<div style="padding: 0 15px 15px 15px;">
  <ons-card class="status-card">
    <div class="title" style="padding: 15px;">
      <h3><?php echo $langs->trans("WeeklySummary"); ?></h3>
    </div>
    <div class="content" style="padding: 0 15px 15px 15px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 10px 0;">
        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
          <div style="font-size: 24px; font-weight: bold; color: #2196f3;">0h</div>
          <div style="font-size: 12px; color: #666; margin-top: 5px;"><?php echo $langs->trans("TotalHours"); ?></div>
        </div>
        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
          <div style="font-size: 24px; font-weight: bold; color: #2196f3;">0</div>
          <div style="font-size: 12px; color: #666; margin-top: 5px;"><?php echo $langs->trans("DaysWorked"); ?></div>
        </div>
      </div>
    </div>
  </ons-card>
</div>
<?php endif; ?>