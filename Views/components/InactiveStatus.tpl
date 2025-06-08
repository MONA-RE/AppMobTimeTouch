<?php
/**
 * Composant InactiveStatus - Responsabilité unique : Affichage statut inactif (non pointé)
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du statut "non pointé"
 */
?>

<div class="timeclock-status-inactive">
  <ons-icon icon="md-pause-circle-outline" size="48px" style="color: #999;"></ons-icon>
  <h3 style="color: #999; margin: 10px 0;">
    <?php echo $langs->trans("NotClockedIn"); ?>
  </h3>
  <p style="color: #666; margin: 10px 0;">
    <?php echo $langs->trans("ReadyToStart"); ?>
  </p>
</div>

<!-- Clock In Button -->
<div style="margin-top: 20px;">
  <ons-button modifier="large" onclick="showClockInModal()" style="background-color: #4CAF50; color: white; width: 100%; border-radius: 25px; font-size: 16px;">
    <ons-icon icon="md-play-arrow" style="margin-right: 10px;"></ons-icon>
    <?php echo $langs->trans("ClockIn"); ?>
  </ons-button>
</div>