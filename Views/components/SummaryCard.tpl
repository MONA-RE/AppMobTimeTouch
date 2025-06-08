<?php
/**
 * Composant SummaryCard - Responsabilité unique : Affichage résumé journalier
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du résumé du travail d'aujourd'hui
 * Respecte le principe OCP : Extensible pour différents types de résumés (daily, weekly)
 */
?>

<!-- Today's Summary -->
<div style="padding: 0 15px 15px 15px;">
  <ons-card>
    <div class="title" style="padding: 10px;">
      <h3><?php echo $langs->trans("TodaySummary"); ?></h3>
    </div>
    <div class="content" style="padding: 0 15px 15px 15px;">
      <ons-row>
        <ons-col width="50%">
          <div style="text-align: center; padding: 10px;">
            <ons-icon icon="md-access-time" style="color: #2196F3; font-size: 24px;"></ons-icon>
            <p style="margin: 5px 0; font-size: 14px; color: #666;">
              <?php echo $langs->trans("WorkedHours"); ?>
            </p>
            <p style="margin: 0; font-size: 18px; font-weight: bold; color: #2196F3;">
              <?php echo TimeHelper::convertSecondsToReadableTime($today_total_hours * 3600); ?>
            </p>
          </div>
        </ons-col>
        <ons-col width="50%">
          <!-- Placeholder for future break time display -->
          <div style="text-align: center; padding: 10px;">
            <ons-icon icon="md-trending-up" style="color: #4CAF50; font-size: 24px;"></ons-icon>
            <p style="margin: 5px 0; font-size: 14px; color: #666;">
              <?php echo $langs->trans("Progress"); ?>
            </p>
            <p style="margin: 0; font-size: 18px; font-weight: bold; color: #4CAF50;">
              <?php echo round(min(100, ($today_total_hours / $overtime_threshold) * 100), 1); ?>%
            </p>
          </div>
        </ons-col>
      </ons-row>
      
      <!-- Progress bar for daily target -->
      <div style="margin-top: 15px;">
        <?php 
        $daily_progress = min(100, ($today_total_hours / $overtime_threshold) * 100);
        $progress_color = $daily_progress > 100 ? '#f44336' : ($daily_progress > 80 ? '#FF9800' : '#4CAF50');
        ?>
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
          <span style="font-size: 12px; color: #666;"><?php echo $langs->trans("DailyTarget"); ?></span>
          <span style="font-size: 12px; color: #666;"><?php echo round($daily_progress, 1); ?>%</span>
        </div>
        <div style="background-color: #e0e0e0; height: 6px; border-radius: 3px; overflow: hidden;">
          <div style="background-color: <?php echo $progress_color; ?>; height: 100%; width: <?php echo min(100, $daily_progress); ?>%; transition: width 0.3s ease;"></div>
        </div>
      </div>
    </div>
  </ons-card>
</div>