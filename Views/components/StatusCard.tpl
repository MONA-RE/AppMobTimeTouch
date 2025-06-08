<?php
/**
 * Composant StatusCard - Responsabilité unique : Affichage statut timeclock
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du statut de pointage
 * Respecte le principe OCP : Extensible via sous-composants ActiveStatus/InactiveStatus
 */
?>

<div style="padding: 15px;">
  <ons-card>
    <div class="title" style="text-align: center; padding: 10px 0;">
      <h2><?php echo $langs->trans("TimeclockStatus"); ?></h2>
    </div>
    
    <div class="content" style="text-align: center; padding: 20px;">
      <?php if ($is_clocked_in): ?>
        <?php include 'Views/components/ActiveStatus.tpl'; ?>
      <?php else: ?>
        <?php include 'Views/components/InactiveStatus.tpl'; ?>
      <?php endif; ?>
    </div>
  </ons-card>
</div>