<ons-page id="ONSHome">
  <?php include "tpl/parts/topbar-home.tpl"; ?>
  
  <ons-pull-hook id="pull-hook">
    <?php echo $langs->trans("PullToRefresh"); ?>
  </ons-pull-hook>
  
  <!-- Messages d'erreur/succès -->
  <?php if (!empty($errors)): ?>
  <div style="padding: 10px 15px;">
    <?php foreach ($errors as $error_msg): ?>
    <ons-card style="background-color: #ffebee; border-left: 4px solid #f44336;">
      <div class="content" style="padding: 10px;">
        <ons-icon icon="md-warning" style="color: #f44336; margin-right: 8px;"></ons-icon>
        <span style="color: #c62828;"><?php echo dol_escape_htmltag($error_msg); ?></span>
      </div>
    </ons-card>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($messages)): ?>
  <div style="padding: 10px 15px;">
    <?php foreach ($messages as $msg): ?>
    <ons-card style="background-color: #e8f5e8; border-left: 4px solid #4CAF50;">
      <div class="content" style="padding: 10px;">
        <ons-icon icon="md-check-circle" style="color: #4CAF50; margin-right: 8px;"></ons-icon>
        <span style="color: #2e7d32;"><?php echo dol_escape_htmltag($msg); ?></span>
      </div>
    </ons-card>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  
  <!-- Status Card -->
  <div style="padding: 15px;">
    <ons-card>
      <div class="title" style="text-align: center; padding: 10px 0;">
        <h2><?php echo $langs->trans("TimeclockStatus"); ?></h2>
      </div>
      
      <div class="content" style="text-align: center; padding: 20px;">
        <?php if ($is_clocked_in): ?>
          <!-- User is clocked in -->
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
                <?php echo convertSecondsToReadableTime($current_duration); ?>
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
          
        <?php else: ?>
          <!-- User is not clocked in -->
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
          
        <?php endif; ?>
      </div>
    </ons-card>
  </div>
  
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
                <?php echo convertSecondsToReadableTime($today_total_hours * 3600); ?>
              </p>
            </div>
          </ons-col>
          <ons-col width="50%">

            <!-- <div style="text-align: center; padding: 10px;">
              <ons-icon icon="md-pause" style="color: #FF9800; font-size: 24px;"></ons-icon>
              <p style="margin: 5px 0; font-size: 14px; color: #666;">
                <?php echo $langs->trans("BreakTime"); ?>
              </p>
              <p style="margin: 0; font-size: 18px; font-weight: bold; color: #FF9800;">
                <?php echo convertSecondsToReadableTime($today_total_breaks * 60); ?>
              </p>
            </div> -->

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
                <?php echo convertSecondsToReadableTime($weekly_summary->total_hours * 3600); ?>
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
            <?php echo convertSecondsToReadableTime($weekly_summary->overtime_hours * 3600); ?>
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
  
  <!-- Recent Records -->
  <?php if (!empty($recent_records)): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 10px;">
        <h3><?php echo $langs->trans("RecentRecords"); ?></h3>
      </div>
      <ons-list style="margin: 0;">
        <?php 
        $count = 0;
        foreach ($recent_records as $record): 
          if ($count >= 5) break; // Limit to 5 records
          $count++;
          
          // Get type info
          $type = new TimeclockType($db);
          $type->fetch($record->fk_timeclock_type);
          
          $record_date = dol_print_date($db->jdate($record->clock_in_time), 'day');
          $clock_in = dol_print_date($db->jdate($record->clock_in_time), 'hour');
          $clock_out = !empty($record->clock_out_time) ? dol_print_date($db->jdate($record->clock_out_time), 'hour') : '';
          $duration = !empty($record->work_duration) ? convertSecondsToReadableTime($record->work_duration * 60) : '';
          $status_class = '';
          
          switch ($record->status) {
            case 2: // In progress
              $status_class = 'status-active';
              break;
            case 3: // Completed
              $status_class = 'status-completed';
              break;
            default:
              $status_class = 'status-draft';
          }
        ?>
        <ons-list-item tappable onclick="viewRecord(<?php echo $record->id; ?>)">
          <div class="left">
            <div style="width: 8px; height: 40px; background-color: <?php echo $type->color; ?>; border-radius: 4px;"></div>
          </div>
          <div class="center">
            <div style="font-weight: bold; margin-bottom: 2px;">
              <?php echo $record_date; ?>
            </div>
            <div style="font-size: 14px; color: #666; margin-bottom: 2px;">
              <span style="background-color: <?php echo $type->color; ?>; color: white; padding: 2px 6px; border-radius: 8px; font-size: 11px;">
              <?php echo $type->label; ?>
              </span>
            </div>
            <div style="font-size: 12px; color: #999;">
              <?php echo $clock_in; ?>
              <?php if ($clock_out): ?>
                - <?php echo $clock_out; ?>
              <?php endif; ?>
              <div style="margin-top: 2px;">
                <?php echo $record->getLibStatut(5); ?>
              </div>
            </div>
          </div>
          <div class="right">
            <?php if ($duration): ?>
              <div style="font-weight: bold; color: #4CAF50;">
                <?php echo $duration; ?>
              </div>
            <?php else: ?>
              <ons-icon icon="md-more-vert" style="color: #999;"></ons-icon>
            <?php endif; ?>
          </div>
        </ons-list-item>
        <?php endforeach; ?>
      </ons-list>
      
      <?php if (count($recent_records) > 5): ?>
      <div style="text-align: center; padding: 10px;">
        <ons-button modifier="quiet" onclick="gotoPage('myTimeclockRecords');">
          <?php echo $langs->trans("ViewAllRecords"); ?>
        </ons-button>
      </div>
      <?php endif; ?>
    </ons-card>
  </div>
  <?php endif; ?>

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
          
          <!-- Localisation -->
          <div style="margin-bottom: 20px;">
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
        </form>
      </div>
    </div>
  </ons-modal>

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
            <div style="font-weight: 500; color: #333;"><?php echo dol_print_date($clock_in_time, 'hour'); ?></div>
          </div>
          
          <div style="flex: 1; min-width: 120px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 2px;"><?php echo $langs->trans("Duration"); ?></div>
            <div style="font-weight: bold; color: #4CAF50; font-size: 16px;" id="session-duration">
              <?php echo convertSecondsToReadableTime($current_duration); ?>
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
        
        <!-- Localisation de sortie -->
        <div style="margin-bottom: 20px;">
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
      </form>
    </div>
  </div>
</ons-modal>
  
  <script type="text/javascript">
    var updateDurationTimer = null;
    var appConfig = <?php echo json_encode($js_data); ?>;
    
    // Load TimeClock API module
    if (!window.TimeclockAPI) {
      var script = document.createElement('script');
      script.src = 'js/timeclock-api.js?v=' + (appConfig.version || Date.now());
      script.onload = function() {
        console.log('TimeClock API loaded successfully');
      };
      script.onerror = function() {
        console.error('Failed to load TimeClock API');
      };
      document.head.appendChild(script);
    }
    
    ons.ready(function () {
      // Initialize pull to refresh
      var pullHook = document.getElementById('pull-hook');

      pullHook.addEventListener('changestate', function (event) {
        var message = '';
        switch (event.state) {
          case 'initial':
            message = '<?php echo $langs->trans("PullToRefresh"); ?>';
            break;
          case 'preaction':
            message = '<?php echo $langs->trans("Release"); ?>';
            break;
          case 'action':
            message = '<?php echo $langs->trans("Loading"); ?>...';
            break;
        }
        pullHook.innerHTML = message;
      });

      pullHook.onAction = function (done) {
        // Refresh the page
        setTimeout(function() {
          location.reload();
          done();
        }, 1000);
      };
      
      <?php if ($is_clocked_in): ?>
      // Start duration update timer
      startDurationTimer();
      <?php endif; ?>
      
      // Auto-hide messages after 5 seconds
      setTimeout(function() {
        var messages = document.querySelectorAll('ons-card[style*="background-color: #e8f5e8"], ons-card[style*="background-color: #ffebee"]');
        messages.forEach(function(msg) {
          msg.style.opacity = '0';
          msg.style.transition = 'opacity 0.5s';
          setTimeout(function() {
            msg.style.display = 'none';
          }, 500);
        });
      }, 5000);
    });
    
    // Function to start updating duration
    function startDurationTimer() {
      updateDurationTimer = setInterval(function() {
        updateCurrentDuration();
      }, 60000); // Update every minute
    }
    
    // Function to stop duration timer
    function stopDurationTimer() {
      if (updateDurationTimer) {
        clearInterval(updateDurationTimer);
        updateDurationTimer = null;
      }
    }
    
    // Function to update current duration display
    function updateCurrentDuration() {
      if (appConfig.is_clocked_in && appConfig.clock_in_time) {
        var now = Math.floor(Date.now() / 1000);
        var duration = now - appConfig.clock_in_time;
        var hours = Math.floor(duration / 3600);
        var minutes = Math.floor((duration % 3600) / 60);
        
        var durationText = hours + 'h' + (minutes < 10 ? '0' : '') + minutes;
        
        var durationElement = document.getElementById('current-duration');
        if (durationElement) {
          durationElement.textContent = durationText;
        }
      }
    }
    
    function showClockInModal() {
        const modal = document.getElementById('clockInModal');
        modal.show();
        
        // Initialiser la sélection des types
        setTimeout(function() {
            initializeTimeclockTypeSelection();
        }, 100);
        
        // Get GPS location if required
        if (appConfig && appConfig.require_location) {
            getCurrentPosition('in');
        } else {
            // Mettre à jour le statut GPS comme "prêt"
            updateGPSStatus('ready', '<?php echo $langs->trans("ReadyToStart"); ?>');
        }
    }
    
// Show Clock Out Modal  
function showClockOutModal() {
        const modal = document.getElementById('clockOutModal');
        modal.show();
        
        // Démarrer le timer de session
        setTimeout(function() {
            startSessionTimer();
        }, 100);
        
        // Get GPS location if required
        if (appConfig && appConfig.require_location) {
            getCurrentPositionOut();
        } else {
            updateGPSStatusOut('ready', '<?php echo $langs->trans("ReadyToClockOut"); ?>');
        }
    }



    function getCurrentPosition(type) {
        const statusElement = document.getElementById('gps-status' + (type === 'out' ? '-out' : ''));
        
        updateGPSStatus('loading', '<?php echo $langs->trans("GettingLocation"); ?>...');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lon = position.coords.longitude;
                    var accuracy = Math.round(position.coords.accuracy);
                    
                    document.getElementById('clock' + type + '_latitude').value = lat;
                    document.getElementById('clock' + type + '_longitude').value = lon;
                    
                    updateGPSStatus('success', '<?php echo $langs->trans("LocationFound"); ?> (±' + accuracy + 'm)');
                },
                function(error) {
                    var errorMsg = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = '<?php echo $langs->trans("LocationPermissionDenied"); ?>';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = '<?php echo $langs->trans("LocationUnavailable"); ?>';
                            break;
                        case error.TIMEOUT:
                            errorMsg = '<?php echo $langs->trans("LocationTimeout"); ?>';
                            break;
                        default:
                            errorMsg = '<?php echo $langs->trans("LocationError"); ?>';
                            break;
                    }
                    updateGPSStatus('error', errorMsg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        } else {
            updateGPSStatus('error', '<?php echo $langs->trans("LocationNotSupported"); ?>');
        }
    }

    // Submit Clock In (uses API)
    function submitClockIn() {
      // Validate required location
      if (appConfig.require_location) {
        var lat = document.getElementById('clockin_latitude').value;
        var lon = document.getElementById('clockin_longitude').value;
        
        if (!lat || !lon) {
          ons.notification.alert('<?php echo $langs->trans("LocationRequiredForClockIn"); ?>');
          return;
        }
      }
      
      // Use API instead of form submission
      if (window.submitClockIn) {
        window.submitClockIn();
      } else {
        // Fallback to form submission
      document.getElementById('clockInForm').submit();
    }
    }
    
    // Submit Clock Out (uses API)
    function submitClockOut() {
      // Show confirmation
      ons.notification.confirm('<?php echo $langs->trans("ConfirmClockOut"); ?>').then(function(result) {
        if (result === 1) {
          // Use API instead of form submission
          if (window.submitClockOut) {
            window.submitClockOut();
          } else {
            // Fallback to form submission
          document.getElementById('clockOutForm').submit();
        }
        }
      });
    }
    
    // View record function
    function viewRecord(recordId) {
      console.log('View record:', recordId);
      // TODO: Navigate to record detail page
      ons.notification.toast('<?php echo $langs->trans("RecordDetails"); ?>: ' + recordId, {timeout: 1500});
    }
    
    // CSS Animations
    var style = document.createElement('style');
    style.textContent = `
      @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
      }
      
      .timeclock-status-active {
        animation: fadeIn 0.5s ease-in;
      }
      
      .timeclock-status-inactive {
        animation: fadeIn 0.5s ease-in;
      }
      
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
      }
      
      .select-input:focus,
      input[type="text"]:focus,
      textarea:focus {
        outline: none;
        border-color: #4CAF50 !important;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
      }
      
      ons-button[modifier="material"] {
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
      }
      
      ons-button[modifier="material"]:active {
        transform: translateY(1px);
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
      }
      
      .status-active {
        border-left: 3px solid #4CAF50;
      }
      
      .status-completed {
        border-left: 3px solid #2196F3;
      }
      
      .status-draft {
        border-left: 3px solid #999;
      }
      
      ons-list-item:active {
        background-color: #f5f5f5;
      }
      
      /* Progress bar animation */
      @keyframes progressFill {
        from { width: 0%; }
        to { width: var(--progress-width); }
      }
      
      /* Loading animation for modals */
      .modal-loading {
        opacity: 0.7;
        pointer-events: none;
      }
      
      /* Animation d'entrée pour le modal clock-out */
      #clockOutModal .modal__content {
        animation: slideInUp 0.3s ease-out;
      }
      
      @keyframes slideInUp {
        from {
          transform: translateY(100%);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }
      
      /* Styles pour les champs de saisie du clock-out */
      #clockout_location:focus, 
      #clockout_note:focus {
        outline: none;
        box-shadow: 0 0 10px rgba(244, 67, 54, 0.2);
        transform: translateY(-1px);
        transition: all 0.3s ease;
      }
      
      /* Style pour le statut GPS du clock-out */
      #gps-status-out.success {
        background: rgba(76, 175, 80, 0.1);
        border: 1px solid rgba(76, 175, 80, 0.3);
        color: #2e7d32;
      }
      
      #gps-status-out.error {
        background: rgba(244, 67, 54, 0.1);
        border: 1px solid rgba(244, 67, 54, 0.3);
        color: #c62828;
      }
      
      #gps-status-out.loading {
        background: rgba(255, 152, 0, 0.1);
        border: 1px solid rgba(255, 152, 0, 0.3);
        color: #ef6c00;
      }
      
      /* Animation pour le timer de session */
      #session-duration {
        animation: pulseGreen 2s infinite;
      }
      
      @keyframes pulseGreen {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
      }
      
      /* Responsive adjustments */
      @media (max-width: 480px) {
        ons-card {
          margin: 0 5px;
        }
        
        .content {
          padding: 10px !important;
        }
        
        h2, h3 {
          font-size: 18px;
        }
        
        #clockOutModal .modal__content {
          margin: 10px;
        }
      }
    `;
    document.head.appendChild(style);
    
    // Utility functions
    function formatDuration(seconds) {
      var hours = Math.floor(seconds / 3600);
      var minutes = Math.floor((seconds % 3600) / 60);
      return hours + 'h' + (minutes < 10 ? '0' : '') + minutes;
    }
    
    function showLoading(message) {
      ons.notification.toast(message || '<?php echo $langs->trans("Loading"); ?>...', {timeout: 1000});
    }
    
    function hideAllModals() {
      var modals = document.querySelectorAll('ons-modal');
      modals.forEach(function(modal) {
        if (modal.visible) {
          modal.hide();
        }
      });
    }
    
    // Handle page visibility changes (for timer updates)
    document.addEventListener('visibilitychange', function() {
      if (document.hidden) {
        // Page is hidden, stop timer to save battery
        if (updateDurationTimer) {
          stopDurationTimer();
        }
      } else {
        // Page is visible again
        if (appConfig.is_clocked_in) {
          startDurationTimer();
          updateCurrentDuration(); // Update immediately
        }
      }
    });
    
    // Keyboard shortcuts for testing (development only)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      document.addEventListener('keydown', function(e) {
        // Alt + I = Clock In
        if (e.altKey && e.key === 'i') {
          e.preventDefault();
          if (!appConfig.is_clocked_in) {
            showClockInModal();
          }
        }
        // Alt + O = Clock Out  
        if (e.altKey && e.key === 'o') {
          e.preventDefault();
          if (appConfig.is_clocked_in) {
            showClockOutModal();
          }
        }
        // Alt + R = Refresh
        if (e.altKey && e.key === 'r') {
          e.preventDefault();
          location.reload();
        }
      });
      
      console.log('Development shortcuts enabled:');
      console.log('Alt + I = Clock In Modal');
      console.log('Alt + O = Clock Out Modal');
      console.log('Alt + R = Refresh');
    }
    
    // Auto-save form data in case of connection issues
    function saveFormData(formId, data) {
      if (typeof(Storage) !== "undefined") {
        localStorage.setItem('timeclock_' + formId, JSON.stringify(data));
      }
    }
    
    function loadFormData(formId) {
      if (typeof(Storage) !== "undefined") {
        var data = localStorage.getItem('timeclock_' + formId);
        return data ? JSON.parse(data) : null;
      }
      return null;
    }
    
    function clearFormData(formId) {
      if (typeof(Storage) !== "undefined") {
        localStorage.removeItem('timeclock_' + formId);
      }
    }
    
    // Save form data when user types (for offline support)
    ['clockInForm', 'clockOutForm'].forEach(function(formId) {
      var form = document.getElementById(formId);
      if (form) {
        var inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(function(input) {
          input.addEventListener('input', function() {
            var formData = new FormData(form);
            var data = {};
            for (var pair of formData.entries()) {
              data[pair[0]] = pair[1];
            }
            saveFormData(formId, data);
          });
        });
      }
    });
    
    // Network status monitoring
    function updateNetworkStatus() {
      if (navigator.onLine) {
        // Online - clear any offline indicators
        var offlineIndicators = document.querySelectorAll('.offline-indicator');
        offlineIndicators.forEach(function(indicator) {
          indicator.remove();
        });
      } else {
        // Offline - show indicator
        if (!document.querySelector('.offline-indicator')) {
          var indicator = document.createElement('div');
          indicator.className = 'offline-indicator';
          indicator.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; background: #f44336; color: white; text-align: center; padding: 5px; font-size: 12px; z-index: 10000;';
          indicator.textContent = '<?php echo $langs->trans("OfflineMode"); ?>';
          document.body.insertBefore(indicator, document.body.firstChild);
        }
      }
    }
    
    // Check network status
    window.addEventListener('online', updateNetworkStatus);
    window.addEventListener('offline', updateNetworkStatus);
    updateNetworkStatus(); // Check initial status
    
    // Geolocation permissions check on load
    if (appConfig.require_location && 'permissions' in navigator) {
      navigator.permissions.query({name: 'geolocation'}).then(function(result) {
        if (result.state === 'denied') {
          ons.notification.alert('<?php echo $langs->trans("LocationPermissionRequired"); ?>');
        }
      });
    }
    
    // Performance monitoring (development only)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      console.log('Page load time:', (performance.now() / 1000).toFixed(2) + 's');
      
      // Monitor memory usage
      if ('memory' in performance) {
        setInterval(function() {
          var mem = performance.memory;
          if (mem.usedJSHeapSize > 50 * 1024 * 1024) { // 50MB threshold
            console.warn('High memory usage detected:', (mem.usedJSHeapSize / 1024 / 1024).toFixed(2) + 'MB');
          }
        }, 30000);
      }
    }

        /**
     * Sélectionner un type de pointage
     * @param {number} typeId - ID du type de pointage
     * @param {string} typeLabel - Libellé du type
     * @param {string} typeColor - Couleur du type
     */
     function selectTimeclockType(typeId, typeLabel, typeColor) {
        console.log('Selecting timeclock type:', typeId, typeLabel, typeColor);
        
        // Mettre à jour le champ caché
        const hiddenInput = document.getElementById('selected_timeclock_type');
        if (hiddenInput) {
            hiddenInput.value = typeId;
        }
        
        // Supprimer la sélection de tous les éléments
        const allItems = document.querySelectorAll('.timeclock-type-item');
        allItems.forEach(function(item) {
            item.classList.remove('selected');
            item.style.backgroundColor = '';
            item.style.borderLeft = '';
            
            // Masquer l'icône de validation
            const icon = item.querySelector('.type-selected-icon');
            if (icon) {
                icon.style.display = 'none';
            }
        });
        
        // Marquer l'élément sélectionné
        const selectedItem = document.querySelector(`[data-type-id="${typeId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
            selectedItem.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
            selectedItem.style.borderLeft = '4px solid ' + typeColor;
            
            // Afficher l'icône de validation
            const icon = selectedItem.querySelector('.type-selected-icon');
            if (icon) {
                icon.style.display = 'block';
                icon.style.color = typeColor;
            }
            
            // Animation de feedback
            selectedItem.style.transform = 'scale(0.98)';
            setTimeout(function() {
                selectedItem.style.transform = 'scale(1)';
            }, 150);
        }
        
        // Feedback visuel avec vibration (si supporté)
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
        
        // Feedback sonore léger (optionnel)
        playSelectionSound();
        
        console.log('Timeclock type selected:', typeId);
    }
    
    /**
     * Jouer un son de sélection discret
     */
     function playSelectionSound() {
        try {
            // Créer un son très court et discret
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            // Ignorer les erreurs de son (pas critique)
        }
    }
    
    /**
     * Initialiser la sélection des types de pointage
     */
     function initializeTimeclockTypeSelection() {
        console.log('Initializing timeclock type selection');
        
        // Ajouter les styles CSS pour les transitions
        if (!document.querySelector('#timeclock-type-styles')) {
            const style = document.createElement('style');
            style.id = 'timeclock-type-styles';
            style.textContent = `
                .timeclock-type-item {
                    transition: all 0.3s ease;
                    cursor: pointer;
                }
                
                .timeclock-type-item:hover {
                    background-color: rgba(76, 175, 80, 0.05) !important;
                }
                
                .timeclock-type-item.selected {
                    background-color: rgba(76, 175, 80, 0.1) !important;
                    transform: translateX(2px);
                }
                
                .timeclock-type-item:active {
                    transform: scale(0.98);
                }
                
                .type-selected-icon {
                    transition: all 0.3s ease;
                }
                
                /* Animation d'entrée pour le modal */
                #clockInModal .modal__content {
                    animation: slideInUp 0.3s ease-out;
                }
                
                @keyframes slideInUp {
                    from {
                        transform: translateY(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                /* Styles pour les champs de saisie */
                input:focus, textarea:focus {
                    outline: none;
                    box-shadow: 0 0 10px rgba(76, 175, 80, 0.2);
                    transform: translateY(-1px);
                    transition: all 0.3s ease;
                }
                
                /* Style pour le statut GPS */
                #gps-status.success {
                    background: rgba(76, 175, 80, 0.1);
                    border: 1px solid rgba(76, 175, 80, 0.3);
                    color: #2e7d32;
                }
                
                #gps-status.error {
                    background: rgba(244, 67, 54, 0.1);
                    border: 1px solid rgba(244, 67, 54, 0.3);
                    color: #c62828;
                }
                
                #gps-status.loading {
                    background: rgba(255, 152, 0, 0.1);
                    border: 1px solid rgba(255, 152, 0, 0.3);
                    color: #ef6c00;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Marquer le type par défaut comme sélectionné au chargement
        const defaultTypeId = document.getElementById('selected_timeclock_type').value;
        if (defaultTypeId) {
            const defaultItem = document.querySelector(`[data-type-id="${defaultTypeId}"]`);
            if (defaultItem) {
                // Récupérer la couleur depuis l'élément
                const colorElement = defaultItem.querySelector('[style*="background-color"]');
                const typeColor = colorElement ? colorElement.style.backgroundColor : '#4CAF50';
                const typeLabel = defaultItem.querySelector('.center div').textContent.trim();
                
                selectTimeclockType(parseInt(defaultTypeId), typeLabel, typeColor);
            }
        }
    }


    /**
     * Mettre à jour le statut GPS avec un style approprié
     */
     function updateGPSStatus(status, message) {
        const gpsStatus = document.getElementById('gps-status');
        const gpsStatusText = document.getElementById('gps-status-text');
        
        if (gpsStatus && gpsStatusText) {
            // Supprimer les classes existantes
            gpsStatus.classList.remove('success', 'error', 'loading');
            
            // Ajouter la classe appropriée
            gpsStatus.classList.add(status);
            
            // Mettre à jour le texte
            gpsStatusText.textContent = message;
            
            // Ajouter une icône appropriée
            let icon = 'md-gps-fixed';
            switch (status) {
                case 'success':
                    icon = 'md-gps-fixed';
                    break;
                case 'error':
                    icon = 'md-gps-off';
                    break;
                case 'loading':
                    icon = 'md-gps-not-fixed';
                    break;
                default:
                    icon = 'md-gps-fixed';
            }
            
            const iconElement = gpsStatus.querySelector('ons-icon');
            if (iconElement) {
                iconElement.setAttribute('icon', icon);
            }
        }
    }


        // Variable pour stocker l'option de session sélectionnée
        let selectedSessionOption = 'complete';
    




    /**
     * Démarrer le timer de session en temps réel
     */
    function startSessionTimer() {
        const durationElement = document.getElementById('session-duration');
        
        if (durationElement && appConfig && appConfig.is_clocked_in && appConfig.clock_in_time) {
            setInterval(function() {
                const now = Math.floor(Date.now() / 1000);
                const duration = now - appConfig.clock_in_time;
                const durationText = formatDuration(duration);
                durationElement.textContent = durationText;
            }, 1000); // Mise à jour chaque seconde
        }
    }


    /**
     * Confirmer le clock-out simplifié
     */
     function confirmClockOut() {
        console.log('Confirming simple clock-out');
        
        // Validation de localisation si requise
        if (appConfig && appConfig.require_location) {
            const lat = document.getElementById('clockout_latitude').value;
            const lon = document.getElementById('clockout_longitude').value;
            
            if (!lat || !lon) {
                ons.notification.alert('<?php echo $langs->trans("LocationRequiredForClockOut"); ?>');
                return;
            }
        }
        
        // Message de confirmation simple
        ons.notification.confirm({
            title: '<?php echo $langs->trans("ClockOut"); ?>',
            message: '<?php echo $langs->trans("ConfirmClockOut"); ?>',
            buttonLabels: ['<?php echo $langs->trans("Cancel"); ?>', '<?php echo $langs->trans("Confirm"); ?>']
        }).then(function(index) {
            if (index === 1) {
                // Procéder au clock-out
                submitClockOut();
            }
        });
    }

    /**
     * Mettre à jour le statut GPS pour le clock-out
     */
     function updateGPSStatusOut(status, message) {
        const gpsStatus = document.getElementById('gps-status-out');
        const gpsStatusText = document.getElementById('gps-status-out-text');
        
        if (gpsStatus && gpsStatusText) {
            // Supprimer les classes existantes
            gpsStatus.classList.remove('success', 'error', 'loading');
            
            // Ajouter la classe appropriée
            gpsStatus.classList.add(status);
            
            // Mettre à jour le texte
            gpsStatusText.textContent = message;
            
            // Ajouter une icône appropriée
            let icon = 'md-gps-fixed';
            switch (status) {
                case 'success':
                    icon = 'md-gps-fixed';
                    break;
                case 'error':
                    icon = 'md-gps-off';
                    break;
                case 'loading':
                    icon = 'md-gps-not-fixed';
                    break;
                default:
                    icon = 'md-gps-fixed';
            }
            
            const iconElement = gpsStatus.querySelector('ons-icon');
            if (iconElement) {
                iconElement.setAttribute('icon', icon);
            }
        }
    }


    /**
     * Obtenir la position GPS pour le clock-out
     */
     function getCurrentPositionOut() {
        updateGPSStatusOut('loading', '<?php echo $langs->trans("GettingLocation"); ?>...');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lon = position.coords.longitude;
                    var accuracy = Math.round(position.coords.accuracy);
                    
                    document.getElementById('clockout_latitude').value = lat;
                    document.getElementById('clockout_longitude').value = lon;
                    
                    updateGPSStatusOut('success', '<?php echo $langs->trans("LocationFound"); ?> (±' + accuracy + 'm)');
                },
                function(error) {
                    var errorMsg = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg = '<?php echo $langs->trans("LocationPermissionDenied"); ?>';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg = '<?php echo $langs->trans("LocationUnavailable"); ?>';
                            break;
                        case error.TIMEOUT:
                            errorMsg = '<?php echo $langs->trans("LocationTimeout"); ?>';
                            break;
                        default:
                            errorMsg = '<?php echo $langs->trans("LocationError"); ?>';
                            break;
                    }
                    updateGPSStatusOut('error', errorMsg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        } else {
            updateGPSStatusOut('error', '<?php echo $langs->trans("LocationNotSupported"); ?>');
        }
    }

        /**
     * Animation de shake pour les champs requis
     */
     function addShakeAnimation() {
        if (!document.querySelector('#shake-animation')) {
            const style = document.createElement('style');
            style.id = 'shake-animation';
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Initialiser l'animation shake au chargement
    addShakeAnimation();

  </script>
</ons-page>