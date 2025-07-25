<?php
/**
 * Composant ClockOutModal - Responsabilité unique : Modal de pointage de sortie
 * 
 * Respecte le principe SRP : Seule responsabilité la gestion du modal de clock-out
 * Respecte le principe ISP : Interface spécialisée pour le pointage de sortie
 */
?>

<!-- Clock Out Modal -->
<ons-modal var="clockOutModal" id="clockOutModal">
  <div style="background-color: white; border-radius: 8px; margin: 20px; max-height: 90vh; overflow-y: auto;">
    <!-- Header du modal -->
    <div style="background: linear-gradient(45deg, #f44336, #d32f2f); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
      <ons-icon icon="md-stop" size="24px" style="margin-right: 10px;"></ons-icon>
      <h3 style="margin: 0; font-size: 18px;"><?php echo $langs->trans("ClockOut"); ?></h3>
    </div>
    
    <div style="padding: 20px;">
      <!-- Résumé de la session en cours -->
      <?php if ($is_clocked_in && $active_record): ?>
      <div style="background: linear-gradient(135deg, #e3f2fd, #f3e5f5); border-radius: 8px; padding: 15px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
        <div style="font-weight: bold; color: #1976d2; margin-bottom: 8px; display: flex; align-items: center;">
          <ons-icon icon="md-access-time" style="margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans("SessionSummary"); ?>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
          <div style="flex: 1; min-width: 120px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 2px;"><?php echo $langs->trans("Since"); ?></div>
            <div style="font-weight: 500; color: #333;"><?php echo dol_print_date($clock_in_time, 'hour', 'tzuser'); ?></div>
          </div>
          
          <div style="flex: 1; min-width: 120px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 2px;"><?php echo $langs->trans("Duration"); ?></div>
            <div style="font-weight: bold; color: #4CAF50; font-size: 16px;" id="session-duration">
              <?php echo TimeHelper::convertSecondsToReadableTime($current_duration); ?>
            </div>
          </div>
          
          <?php if ($active_record && !empty($active_record->fk_timeclock_type)): ?>
            <?php
            $type = new TimeclockType($db);
            $type->fetch($active_record->fk_timeclock_type);
            ?>
          <div style="flex: 1; min-width: 120px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 2px;"><?php echo $langs->trans("Type"); ?></div>
            <div style="display: flex; align-items: center;">
              <div style="width: 12px; height: 12px; background-color: <?php echo $type->color; ?>; border-radius: 50%; margin-right: 6px;"></div>
              <span style="font-size: 14px; color: #333;"><?php echo $type->label; ?></span>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      
      <form id="clockOutForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="clockout">
        <input type="hidden" name="token" value="<?php echo newToken(); ?>">
        <input type="hidden" name="latitude" id="clockout_latitude">
        <input type="hidden" name="longitude" id="clockout_longitude">
        
        <!-- Boutons d'action -->
        <div style="display: flex; gap: 10px; margin-top: 25px;">
          <ons-button 
            onclick="clockOutModal.hide()" 
            style="flex: 1; background: #f5f5f5; color: #666; border: 1px solid #ddd; border-radius: 25px; padding: 12px; font-size: 16px; font-weight: 500;">
            <ons-icon icon="md-close" style="margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Cancel"); ?>
          </ons-button>
          
          <ons-button 
            onclick="confirmClockOut()" 
            style="flex: 2; background: linear-gradient(45deg, #f44336, #d32f2f); color: white; border: none; border-radius: 25px; padding: 12px; font-size: 16px; font-weight: 500; box-shadow: 0 3px 6px rgba(244, 67, 54, 0.3);">
            <ons-icon icon="md-stop" style="margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("ClockOut"); ?>
          </ons-button>
        </div>

        <!-- Localisation de sortie -->
        <div style="margin-bottom: 20px; margin-top: 25px;">
          <div style="font-weight: bold; margin-bottom: 8px; color: #333;">
            <ons-icon icon="md-place" style="color: #FF9800; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Location"); ?>:
          </div>
          <input 
            type="text" 
            name="location" 
            id="clockout_location" 
            placeholder="<?php echo $langs->trans("EnterLocation"); ?>" 
            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; background: #f9f9f9;"
            onfocus="this.style.borderColor='#f44336'; this.style.background='white';"
            onblur="this.style.borderColor='#ddd'; this.style.background='#f9f9f9';">
        </div>
        
        <!-- Note de fin de session -->
        <div style="margin-bottom: 20px;">
          <div style="font-weight: bold; margin-bottom: 8px; color: #333;">
            <ons-icon icon="md-note" style="color: #2196F3; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Note"); ?>:
          </div>
          <textarea 
            name="note" 
            id="clockout_note"
            placeholder="<?php echo $langs->trans("OptionalNote"); ?>" 
            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; height: 80px; resize: vertical; font-size: 16px; font-family: inherit; background: #f9f9f9;"
            onfocus="this.style.borderColor='#f44336'; this.style.background='white';"
            onblur="this.style.borderColor='#ddd'; this.style.background='#f9f9f9';"></textarea>
        </div>
        
        <!-- Statut GPS -->
        <div id="gps-status-out" style="margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 6px; font-size: 14px; color: #666; text-align: center; min-height: 20px;">
          <ons-icon icon="md-gps-fixed" style="color: #666; margin-right: 5px;"></ons-icon>
          <span id="gps-status-out-text"><?php echo $langs->trans("ReadyToClockOut"); ?></span>
        </div>
      </form>
    </div>
  </div>
</ons-modal>