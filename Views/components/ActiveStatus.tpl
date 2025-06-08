<?php
/**
 * Composant ActiveStatus - Responsabilité unique : Affichage statut actif (pointé)
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du statut "pointé"
 */
?>

<div class="timeclock-status-active">
  <ons-icon icon="md-time" size="48px" style="color: #4CAF50; animation: pulse 2s infinite;"></ons-icon>
  <h3 style="color: #4CAF50; margin: 10px 0;">
    <?php echo $langs->trans("ClockedIn"); ?>
  </h3>
  <p style="margin: 5px 0;">
    <strong><?php echo $langs->trans("Since"); ?>:</strong> 
    <?php echo dol_print_date($clock_in_time, 'dayhour', 'tzuser'); ?>  
  </p>
  <p style="margin: 5px 0;">
    <strong><?php echo $langs->trans("Duration"); ?>:</strong>
    <span id="current-duration" style="font-weight: bold; color: #4CAF50;">
      <?php echo TimeHelper::convertSecondsToReadableTime($current_duration); ?>
    </span>
  </p>
  
  <?php if ($active_record && !empty($active_record->fk_timeclock_type)): ?>
    <?php
    $type = new TimeclockType($db);
    $type->fetch($active_record->fk_timeclock_type);
    ?>
    <p style="margin: 5px 0;">
      <strong><?php echo $langs->trans("Type"); ?>:</strong>
      <span style="background-color: <?php echo $type->color; ?>; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;">
        <?php echo $type->label; ?>
      </span>
    </p>
  <?php endif; ?>
  
  <?php if ($active_record && !empty($active_record->location_in)): ?>
  <p style="margin: 5px 0; font-size: 12px; color: #666;">
    <ons-icon icon="md-place" style="color: #666;"></ons-icon>
    <?php echo dol_escape_htmltag($active_record->location_in); ?>
  </p>
  <?php endif; ?>
  
  <!-- Alerte heures supplémentaires -->
  <?php if ($overtime_alert): ?>
  <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 10px; margin-top: 15px;">
    <ons-icon icon="md-warning" style="color: #ffc107;"></ons-icon>
    <span style="color: #856404; font-size: 14px; margin-left: 5px;">
      <strong><?php echo $langs->trans("OvertimeAlert"); ?></strong>
    </span>
  </div>
  <?php endif; ?>
</div>

<!-- Clock Out Button -->
<div style="margin-top: 20px;">
  <ons-button modifier="large" onclick="showClockOutModal()" style="background-color: #f44336; color: white; width: 100%; border-radius: 25px; font-size: 16px;">
    <ons-icon icon="md-stop" style="margin-right: 10px;"></ons-icon>
    <?php echo $langs->trans("ClockOut"); ?>
  </ons-button>
</div>