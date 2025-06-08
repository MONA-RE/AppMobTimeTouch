<?php
/**
 * Composant Messages - Responsabilité unique : Affichage messages et erreurs
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage des messages
 * Respecte le principe ISP : Interface spécialisée pour les notifications
 */
?>

<!-- Messages d'erreur -->
<?php if (!empty($errors)): ?>
<div style="padding: 10px 15px;">
  <?php foreach ($errors as $error_msg): ?>
  <ons-card style="background-color: #ffebee; border-left: 4px solid #f44336; margin-bottom: 8px;">
    <div class="content" style="padding: 10px;">
      <ons-icon icon="md-warning" style="color: #f44336; margin-right: 8px;"></ons-icon>
      <span style="color: #c62828;"><?php echo dol_escape_htmltag($error_msg); ?></span>
    </div>
  </ons-card>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Messages de succès -->
<?php if (!empty($messages)): ?>
<div style="padding: 10px 15px;">
  <?php foreach ($messages as $msg): ?>
  <ons-card style="background-color: #e8f5e8; border-left: 4px solid #4CAF50; margin-bottom: 8px;">
    <div class="content" style="padding: 10px;">
      <ons-icon icon="md-check-circle" style="color: #4CAF50; margin-right: 8px;"></ons-icon>
      <span style="color: #2e7d32;"><?php echo dol_escape_htmltag($msg); ?></span>
    </div>
  </ons-card>
  <?php endforeach; ?>
</div>
<?php endif; ?>