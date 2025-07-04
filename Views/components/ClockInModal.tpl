<?php
/**
 * Composant ClockInModal - Responsabilité unique : Modal de pointage d'entrée
 * 
 * Respecte le principe SRP : Seule responsabilité la gestion du modal de clock-in
 * Respecte le principe ISP : Interface spécialisée pour le pointage d'entrée
 */
?>

<!-- Clock In Modal -->
<ons-modal var="clockInModal" id="clockInModal">
  <div style="background-color: white; border-radius: 8px; margin: 20px; max-height: 90vh; overflow-y: auto;">
    <!-- Header du modal -->
    <div style="background: linear-gradient(45deg, #4CAF50, #45a049); color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center;">
      <ons-icon icon="md-play-arrow" size="24px" style="margin-right: 10px;"></ons-icon>
      <h3 style="margin: 0; font-size: 18px;"><?php echo $langs->trans("ClockIn"); ?></h3>
    </div>
    
    <div style="padding: 20px;">
      <form id="clockInForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="clockin">
        <input type="hidden" name="token" value="<?php echo newToken(); ?>">
        <input type="hidden" name="latitude" id="clockin_latitude">
        <input type="hidden" name="longitude" id="clockin_longitude">
        <input type="hidden" name="timeclock_type_id" id="selected_timeclock_type" value="<?php echo $default_type_id; ?>">
        
        <!-- Sélection du type de pointage avec OnsenUI -->
        <div style="margin-bottom: 20px;">
          <div style="font-weight: bold; margin-bottom: 10px; color: #333;">
            <ons-icon icon="md-work" style="color: #4CAF50; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("TimeclockType"); ?>:
          </div>
          
          <ons-list id="timeclockTypeList" style="border: 1px solid #e0e0e0; border-radius: 6px; margin: 0;">
            <?php foreach ($timeclock_types as $type): ?>
            <ons-list-item 
              tappable 
              class="timeclock-type-item <?php echo ($type->id == $default_type_id) ? 'selected' : ''; ?>"
              data-type-id="<?php echo $type->id; ?>"
              onclick="selectTimeclockType(<?php echo $type->id; ?>, '<?php echo dol_escape_js($type->label); ?>', '<?php echo $type->color; ?>')"
              style="border-bottom: 1px solid #f0f0f0; transition: all 0.3s ease;">
              
              <div class="left" style="width: 50px;">
                <div style="width: 20px; height: 20px; background-color: <?php echo $type->color; ?>; border-radius: 50%; margin: auto; border: 2px solid rgba(255,255,255,0.8); box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
              </div>
              
              <div class="center">
                <div style="font-weight: 500; color: #333; font-size: 16px;">
                  <?php echo $type->label; ?>
                </div>
                <div style="font-size: 12px; color: #666; margin-top: 2px;">
                  <?php echo $type->code; ?>
                </div>
              </div>
              
              <div class="right">
                <ons-icon 
                  icon="md-check-circle" 
                  class="type-selected-icon" 
                  style="color: #4CAF50; font-size: 20px; display: <?php echo ($type->id == $default_type_id) ? 'block' : 'none'; ?>;">
                </ons-icon>
              </div>
            </ons-list-item>
            <?php endforeach; ?>
          </ons-list>
        </div>
        
        <!-- Boutons d'action -->
        <div style="display: flex; gap: 10px; margin-top: 25px;">
          <ons-button 
            onclick="clockInModal.hide()" 
            style="flex: 1; background: #f5f5f5; color: #666; border: 1px solid #ddd; border-radius: 25px; padding: 12px; font-size: 16px; font-weight: 500;">
            <ons-icon icon="md-close" style="margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Cancel"); ?>
          </ons-button>
          
          <ons-button 
            onclick="submitClockIn()" 
            style="flex: 2; background: linear-gradient(45deg, #4CAF50, #45a049); color: white; border: none; border-radius: 25px; padding: 12px; font-size: 16px; font-weight: 500; box-shadow: 0 3px 6px rgba(76, 175, 80, 0.3);">
            <ons-icon icon="md-play-arrow" style="margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("ClockIn"); ?>
          </ons-button>
        </div>

        <!-- Localisation -->
        <div style="margin-bottom: 20px; margin-top: 25px;">
          <div style="font-weight: bold; margin-bottom: 8px; color: #333;">
            <ons-icon icon="md-place" style="color: #FF9800; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Location"); ?>:
          </div>
          <input 
            type="text" 
            name="location" 
            id="clockin_location" 
            placeholder="<?php echo $langs->trans("EnterLocation"); ?>" 
            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; background: #f9f9f9;"
            onfocus="this.style.borderColor='#4CAF50'; this.style.background='white';"
            onblur="this.style.borderColor='#ddd'; this.style.background='#f9f9f9';">
        </div>
        
        <!-- Note -->
        <div style="margin-bottom: 20px;">
          <div style="font-weight: bold; margin-bottom: 8px; color: #333;">
            <ons-icon icon="md-note" style="color: #2196F3; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Note"); ?>:
          </div>
          <textarea 
            name="note" 
            placeholder="<?php echo $langs->trans("OptionalNote"); ?>" 
            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; height: 80px; resize: vertical; font-size: 16px; font-family: inherit; background: #f9f9f9;"
            onfocus="this.style.borderColor='#4CAF50'; this.style.background='white';"
            onblur="this.style.borderColor='#ddd'; this.style.background='#f9f9f9';"></textarea>
        </div>
        
        <!-- Statut GPS -->
        <div id="gps-status" style="margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 6px; font-size: 14px; color: #666; text-align: center; min-height: 20px;">
          <ons-icon icon="md-gps-fixed" style="color: #666; margin-right: 5px;"></ons-icon>
          <span id="gps-status-text"><?php echo $langs->trans("ReadyToStart"); ?></span>
        </div>
      </form>
    </div>
  </div>
</ons-modal>