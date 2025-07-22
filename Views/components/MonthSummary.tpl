<?php
/**
 * Composant MonthSummary - Responsabilité unique : Affichage résumé mensuel
 * 
 * TK2507-0344 MVP 3: Template MonthSummary avec calculs théoriques
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du résumé mensuel
 * Respecte le principe OCP : Extensible pour différents affichages de résumés
 * 
 * Compatible avec array et objet $monthly_summary
 * Utilise nb_heure_theorique_mensuel pour les calculs de progression
 */

// Debug: Log monthly summary data
dol_syslog("MonthSummary.tpl: monthly_summary = " . json_encode($monthly_summary), LOG_DEBUG);

// Récupération des heures théoriques mensuelles depuis la configuration
global $conf;
$month_theoretical_hours = !empty($conf->global->APPMOBTIMETOUCH_NB_HEURE_THEORIQUE_MENSUEL) ? 
    (int)$conf->global->APPMOBTIMETOUCH_NB_HEURE_THEORIQUE_MENSUEL : 140;

// Normaliser les données (compatibilité array/objet)
$month_total_hours = 0;
$month_days_worked = 0;
$month_number = date('n'); // Numéro du mois (1-12)
$month_name = date('F'); // Nom du mois en anglais
$month_overtime_hours = 0;

if ($monthly_summary) {
    if (is_array($monthly_summary)) {
        // Format array
        $month_total_hours = $monthly_summary['total_hours'] ?? 0;
        $month_days_worked = $monthly_summary['days_worked'] ?? 0;
        $month_number = $monthly_summary['month_number'] ?? date('n');
        $month_name = $monthly_summary['month_name'] ?? date('F');
        $month_overtime_hours = $monthly_summary['overtime_hours'] ?? 0;
    } else {
        // Format objet
        $month_total_hours = $monthly_summary->total_hours ?? 0;
        $month_days_worked = $monthly_summary->days_worked ?? 0;
        $month_number = $monthly_summary->month_number ?? date('n');
        $month_name = $monthly_summary->month_name ?? date('F');
        $month_overtime_hours = $monthly_summary->overtime_hours ?? 0;
    }
}

// Calcul des heures supplémentaires basé sur les heures théoriques
if ($month_total_hours > $month_theoretical_hours) {
    $month_overtime_hours = $month_total_hours - $month_theoretical_hours;
}

// Debug: Log extracted values
dol_syslog("MonthSummary.tpl: month_total_hours = $month_total_hours, month_theoretical_hours = $month_theoretical_hours", LOG_DEBUG);
?>

<!-- Monthly Summary -->
<?php 
// Pour MVP 3 : Créer des données de démonstration si pas de données réelles
if (!isset($monthly_summary) || !$monthly_summary) {
    // Simuler des données basiques pour démonstration MVP 3
    $monthly_summary = array(
        'total_hours' => 0.02, // Simulation avec quelques minutes pour test
        'days_worked' => 1,
        'month_number' => date('n'),
        'month_name' => date('F'),
        'overtime_hours' => 0
    );
}
?>
<?php if ($monthly_summary): ?>
<div style="padding: 0 15px 15px 15px;">
  <ons-card class="card">
    <div class="title card__title" style="padding: 10px;">
      <h3><?php echo $langs->trans("MonthSummary"); ?> - <?php echo $langs->trans("Month"); ?> <?php echo $month_number; ?></h3>
    </div>
    <div class="content card__content" style="padding: 0 15px 15px 15px;">
      <ons-row>
        <ons-col width="33%" style="flex: 0 0 33%; max-width: 33%;">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("TotalHours"); ?>
            </p>
            <p style="margin: 0; font-size: 16px; font-weight: bold;">
              <?php echo TimeHelper::convertSecondsToReadableTime($month_total_hours * 3600); ?>
            </p>
          </div>
        </ons-col>
        <ons-col width="33%" style="flex: 0 0 33%; max-width: 33%;">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("DaysWorked"); ?>
            </p>
            <p style="margin: 0; font-size: 16px; font-weight: bold;">
              <?php echo $month_days_worked; ?>
            </p>
          </div>
        </ons-col>
        <ons-col width="33%" style="flex: 0 0 33%; max-width: 33%;">
          <div style="text-align: center; padding: 10px;">
            <p style="margin: 5px 0; font-size: 12px; color: #666;">
              <?php echo $langs->trans("Status"); ?>
            </p>
            <div style="margin: 0;">
              <?php 
              // Calcul du statut basé sur les heures théoriques mensuelles
              if ($month_total_hours >= $month_theoretical_hours) {
                  echo '<span style="color: #4CAF50; font-weight: bold;">' . $langs->trans("Complete") . '</span>';
              } elseif ($month_total_hours >= $month_theoretical_hours * 0.8) {
                  echo '<span style="color: #FF9800; font-weight: bold;">' . $langs->trans("InProgress") . '</span>';
              } else {
                  echo '<span style="color: #f44336; font-weight: bold;">' . $langs->trans("Incomplete") . '</span>';
              }
              ?>
            </div>
          </div>
        </ons-col>
      </ons-row>
      
      <?php if ($month_overtime_hours > 0): ?>
      <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 10px; margin-top: 10px;">
        <p style="margin: 0; color: #856404; font-size: 14px;">
          <ons-icon icon="md-warning" style="color: #ffc107;"></ons-icon>
          <strong><?php echo $langs->trans("OvertimeHours"); ?>:</strong>
          <?php echo TimeHelper::convertSecondsToReadableTime($month_overtime_hours * 3600); ?>
        </p>
      </div>
      <?php endif; ?>
      
      <!-- Monthly progress basé sur nb_heure_theorique_mensuel -->
      <?php 
      $monthly_progress = $month_theoretical_hours > 0 ? min(100, ($month_total_hours / $month_theoretical_hours) * 100) : 0;
      $monthly_color = $monthly_progress >= 100 ? '#4CAF50' : ($monthly_progress >= 80 ? '#FF9800' : '#f44336');
      ?>
      <div style="margin-top: 15px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
          <span style="font-size: 12px; color: #666;"><?php echo $langs->trans("MonthlyTarget"); ?> (<?php echo $month_theoretical_hours; ?>h)</span>
          <span style="font-size: 12px; color: #666;"><?php echo round($monthly_progress, 1); ?>%</span>
        </div>
        <div style="background-color: #e0e0e0; height: 6px; border-radius: 3px; overflow: hidden;">
          <div style="background-color: <?php echo $monthly_color; ?>; height: 100%; width: <?php echo min(100, $monthly_progress); ?>%; transition: width 0.3s ease;"></div>
        </div>
      </div>
    </div>
  </ons-card>
</div>
<?php else: ?>
<!-- Fallback: Show basic monthly summary -->
<div style="padding: 0 15px 15px 15px;">
  <ons-card class="status-card">
    <div class="title" style="padding: 15px;">
      <h3><?php echo $langs->trans("MonthlySummary"); ?></h3>
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
      <!-- Monthly target info -->
      <div style="text-align: center; margin-top: 10px; font-size: 12px; color: #666;">
        <?php echo $langs->trans("MonthlyTarget"); ?>: <?php echo $month_theoretical_hours; ?>h
      </div>
    </div>
  </ons-card>
</div>
<?php endif; ?>