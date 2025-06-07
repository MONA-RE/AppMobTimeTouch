<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>TimeTracking - AppMobTimeTouch</title>
    
    <!-- OnsenUI CSS -->
    <link rel="stylesheet" href="css/onsenui.min.css">
    <link rel="stylesheet" href="css/onsen-css-components.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="Assets/css/timeclock-base.css">
    <link rel="stylesheet" href="Assets/css/timeclock-components.css">
    <link rel="stylesheet" href="Assets/css/timeclock-responsive.css">
</head>
<body>

<ons-page id="ONSHome">
  <?php include "tpl/parts/topbar-home.tpl"; ?>
  
  <ons-pull-hook id="pull-hook">
    <?php echo $langs->trans("PullToRefresh"); ?>
  </ons-pull-hook>
  
  <!-- Messages d'erreur/succès -->
  <?php if (!empty($errors)): ?>
  <div class="message-card">
    <?php foreach ($errors as $error_msg): ?>
    <ons-card class="error-card">
      <div class="content">
        <ons-icon icon="md-warning" style="color: #f44336; margin-right: 8px;"></ons-icon>
        <span><?php echo dol_escape_htmltag($error_msg); ?></span>
      </div>
    </ons-card>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($messages)): ?>
  <div class="message-card">
    <?php foreach ($messages as $msg): ?>
    <ons-card class="success-card">
      <div class="content">
        <ons-icon icon="md-check-circle" style="color: #4CAF50; margin-right: 8px;"></ons-icon>
        <span><?php echo dol_escape_htmltag($msg); ?></span>
      </div>
    </ons-card>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  
  <!-- Status Card -->
  <div class="status-card-container">
    <ons-card>
      <div class="card-title">
        <h2><?php echo $langs->trans("TimeclockStatus"); ?></h2>
      </div>
      
      <div class="card-content">
        <?php if ($is_clocked_in): ?>
          <!-- User is clocked in -->
          <div class="timeclock-status-active">
            <ons-icon icon="md-time" size="48px" style="color: #4CAF50; animation: pulse 2s infinite;"></ons-icon>
            <h3 style="color: #4CAF50; margin: 10px 0;">
              <?php echo $langs->trans("ClockedIn"); ?>
            </h3>
            <p class="status-since">
              <strong><?php echo $langs->trans("Since"); ?>:</strong> 
              <?php echo dol_print_date($clock_in_time, 'dayhour', 'tzuser'); ?>  
            </p>
            <p class="status-duration">
              <strong><?php echo $langs->trans("Duration"); ?>:</strong>
              <span id="current-duration">
                <?php echo TimeHelper::convertSecondsToReadableTime($current_duration); ?>
              </span>
            </p>
            <?php if ($active_record && !empty($active_record->fk_timeclock_type)): ?>
              <?php
              $type = new TimeclockType($db);
              $type->fetch($active_record->fk_timeclock_type);
              ?>
              <p class="status-type">
                <strong><?php echo $langs->trans("Type"); ?>:</strong>
                <span class="type-badge" style="background-color: <?php echo $type->color; ?>;">
                  <?php echo $type->label; ?>
                </span>
              </p>
            <?php endif; ?>
            
            <?php if ($active_record && !empty($active_record->location_in)): ?>
            <p class="status-location">
              <ons-icon icon="md-place" style="color: #666;"></ons-icon>
              <?php echo dol_escape_htmltag($active_record->location_in); ?>
            </p>
            <?php endif; ?>
            
            <!-- Alerte heures supplémentaires -->
            <?php if ($overtime_alert): ?>
            <div class="overtime-alert">
              <ons-icon icon="md-warning" style="color: #ffc107;"></ons-icon>
              <span>
                <strong><?php echo $langs->trans("OvertimeAlert"); ?></strong>
              </span>
            </div>
            <?php endif; ?>
          </div>
          
          <!-- Clock Out Button -->
          <div class="button-container">
            <ons-button modifier="large" onclick="showClockOutModal()" class="button-large button-clockout">
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
          <div class="button-container">
            <ons-button modifier="large" onclick="showClockInModal()" class="button-large button-clockin">
              <ons-icon icon="md-play-arrow" style="margin-right: 10px;"></ons-icon>
              <?php echo $langs->trans("ClockIn"); ?>
            </ons-button>
          </div>
          
        <?php endif; ?>
      </div>
    </ons-card>
  </div>
  
  <!-- Today's Summary -->
  <div class="summary-card-container">
    <ons-card>
      <div class="card-title">
        <h3><?php echo $langs->trans("TodaySummary"); ?></h3>
      </div>
      <div class="card-content-padded">
        <ons-row>
          <ons-col width="50%">
            <div class="summary-item">
              <ons-icon icon="md-access-time" style="color: #2196F3; font-size: 24px;"></ons-icon>
              <p class="label">
                <?php echo $langs->trans("WorkedHours"); ?>
              </p>
              <p class="value">
                <?php echo TimeHelper::convertSecondsToReadableTime($today_total_hours * 3600); ?>
              </p>
            </div>
          </ons-col>
          <ons-col width="50%">
            <!-- Break time placeholder for future use -->
          </ons-col>
        </ons-row>
        
        <!-- Progress bar for daily target -->
        <div class="progress-section">
          <?php 
          $daily_progress = min(100, ($today_total_hours / $overtime_threshold) * 100);
          $progress_color = $daily_progress > 100 ? '#f44336' : ($daily_progress > 80 ? '#FF9800' : '#4CAF50');
          ?>
          <div class="progress-header">
            <span><?php echo $langs->trans("DailyTarget"); ?></span>
            <span><?php echo round($daily_progress, 1); ?>%</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="background-color: <?php echo $progress_color; ?>; width: <?php echo min(100, $daily_progress); ?>%;"></div>
          </div>
        </div>
      </div>
    </ons-card>
  </div>
  
  <!-- Weekly Summary -->
  <?php if ($weekly_summary): ?>
  <div class="weekly-summary-container">
    <ons-card>
      <div class="card-title">
        <h3><?php echo $langs->trans("WeekSummary"); ?> - <?php echo $langs->trans("Week"); ?> <?php echo $weekly_summary->week_number; ?></h3>
      </div>
      <div class="weekly-summary-grid">
        <ons-row>
          <ons-col width="33%">
            <div class="weekly-summary-item">
              <p class="label">
                <?php echo $langs->trans("TotalHours"); ?>
              </p>
              <p class="value">
                <?php echo TimeHelper::convertSecondsToReadableTime($weekly_summary->total_hours * 3600); ?>
              </p>
            </div>
          </ons-col>
          <ons-col width="33%">
            <div class="weekly-summary-item">
              <p class="label">
                <?php echo $langs->trans("DaysWorked"); ?>
              </p>
              <p class="value">
                <?php echo $weekly_summary->days_worked; ?>
              </p>
            </div>
          </ons-col>
          <ons-col width="33%">
            <div class="weekly-summary-item">
              <p class="label">
                <?php echo $langs->trans("Status"); ?>
              </p>
              <div>
                <?php echo $weekly_summary->getLibStatut(3); ?>
              </div>
            </div>
          </ons-col>
        </ons-row>
        
        <?php if ($weekly_summary->overtime_hours > 0): ?>
        <div class="weekly-overtime-alert">
          <p>
            <ons-icon icon="md-warning" style="color: #ffc107;"></ons-icon>
            <strong><?php echo $langs->trans("OvertimeHours"); ?>:</strong>
            <?php echo TimeHelper::convertSecondsToReadableTime($weekly_summary->overtime_hours * 3600); ?>
          </p>
        </div>
        <?php endif; ?>
        
        <!-- Weekly progress -->
        <?php 
        $weekly_progress = min(100, ($weekly_summary->total_hours / $weekly_summary->expected_hours) * 100);
        $weekly_color = $weekly_progress > 100 ? '#f44336' : ($weekly_progress > 80 ? '#FF9800' : '#4CAF50');
        ?>
        <div class="progress-section">
          <div class="progress-header">
            <span><?php echo $langs->trans("WeeklyTarget"); ?></span>
            <span><?php echo round($weekly_progress, 1); ?>%</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" style="background-color: <?php echo $weekly_color; ?>; width: <?php echo min(100, $weekly_progress); ?>%;"></div>
          </div>
        </div>
      </div>
    </ons-card>
  </div>
  <?php endif; ?>
  
  <!-- Recent Records -->
  <?php if (!empty($recent_records)): ?>
  <div class="records-card-container">
    <ons-card>
      <div class="card-title">
        <h3><?php echo $langs->trans("RecentRecords"); ?></h3>
      </div>
      <ons-list class="records-list">
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
          $duration = !empty($record->work_duration) ? TimeHelper::convertSecondsToReadableTime($record->work_duration * 60) : '';
        ?>
        <ons-list-item tappable onclick="viewRecord(<?php echo $record->id; ?>)">
          <div class="left">
            <div class="record-item-left" style="background-color: <?php echo $type->color; ?>;"></div>
          </div>
          <div class="center">
            <div class="record-item-date">
              <?php echo $record_date; ?>
            </div>
            <div class="record-item-type">
              <span class="type-badge" style="background-color: <?php echo $type->color; ?>;">
              <?php echo $type->label; ?>
              </span>
            </div>
            <div class="record-item-time">
              <?php echo $clock_in; ?>
              <?php if ($clock_out): ?>
                - <?php echo $clock_out; ?>
              <?php endif; ?>
              <div class="record-item-status">
                <?php echo $record->getLibStatut(5); ?>
              </div>
            </div>
          </div>
          <div class="right">
            <?php if ($duration): ?>
              <div class="record-item-duration">
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
      <div class="view-all-container">
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
    <div class="modal-content">
      <!-- Header du modal -->
      <div class="modal-header-clockin">
        <ons-icon icon="md-play-arrow" size="24px" style="margin-right: 10px;"></ons-icon>
        <h3><?php echo $langs->trans("ClockIn"); ?></h3>
      </div>
      
      <div class="modal-body">
        <form id="clockInForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type="hidden" name="action" value="clockin">
          <input type="hidden" name="token" value="<?php echo newToken(); ?>">
          <input type="hidden" name="latitude" id="clockin_latitude">
          <input type="hidden" name="longitude" id="clockin_longitude">
          <input type="hidden" name="timeclock_type_id" id="selected_timeclock_type" value="<?php echo $default_type_id; ?>">
          
          <!-- Sélection du type de pointage avec OnsenUI -->
          <div class="form-section">
            <div class="form-label">
              <ons-icon icon="md-work" style="color: #4CAF50; margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans("TimeclockType"); ?>:
            </div>
            
            <ons-list id="timeclockTypeList" class="type-selection-list">
              <?php foreach ($timeclock_types as $type): ?>
              <ons-list-item 
                tappable 
                class="timeclock-type-item type-selection-item <?php echo ($type->id == $default_type_id) ? 'selected' : ''; ?>"
                data-type-id="<?php echo $type->id; ?>"
                onclick="selectTimeclockType(<?php echo $type->id; ?>, '<?php echo dol_escape_js($type->label); ?>', '<?php echo $type->color; ?>')">
                
                <div class="left type-item-left">
                  <div class="type-item-circle" style="background-color: <?php echo $type->color; ?>;"></div>
                </div>
                
                <div class="center type-item-center">
                  <div class="type-item-label">
                    <?php echo $type->label; ?>
                  </div>
                  <div class="type-item-code">
                    <?php echo $type->code; ?>
                  </div>
                </div>
                
                <div class="right type-item-right">
                  <ons-icon 
                    icon="md-check-circle" 
                    class="type-selected-icon" 
                    style="display: <?php echo ($type->id == $default_type_id) ? 'block' : 'none'; ?>;">
                  </ons-icon>
                </div>
              </ons-list-item>
              <?php endforeach; ?>
            </ons-list>
          </div>
          
          <!-- Localisation -->
          <div class="form-section">
            <div class="form-label">
              <ons-icon icon="md-place" style="color: #FF9800; margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans("Location"); ?>:
            </div>
            <input 
              type="text" 
              name="location" 
              id="clockin_location" 
              placeholder="<?php echo $langs->trans("EnterLocation"); ?>" 
              class="form-input">
          </div>
          
          <!-- Note -->
          <div class="form-section">
            <div class="form-label">
              <ons-icon icon="md-note" style="color: #2196F3; margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans("Note"); ?>:
            </div>
            <textarea 
              name="note" 
              placeholder="<?php echo $langs->trans("OptionalNote"); ?>" 
              class="form-textarea"></textarea>
          </div>
          
          <!-- Statut GPS -->
          <div id="gps-status" class="gps-status">
            <ons-icon icon="md-gps-fixed" style="color: #666; margin-right: 5px;"></ons-icon>
            <span id="gps-status-text"><?php echo $langs->trans("ReadyToStart"); ?></span>
          </div>
          
          <!-- Boutons d'action -->
          <div class="modal-buttons">
            <ons-button 
              onclick="clockInModal.hide()" 
              class="modal-button-cancel">
              <ons-icon icon="md-close" style="margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans("Cancel"); ?>
            </ons-button>
            
            <ons-button 
              onclick="submitClockIn()" 
              class="modal-button-submit modal-button-clockin">
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
  <div class="modal-content">
    <!-- Header du modal -->
    <div class="modal-header-clockout">
      <ons-icon icon="md-stop" size="24px" style="margin-right: 10px;"></ons-icon>
      <h3><?php echo $langs->trans("ClockOut"); ?></h3>
    </div>
    
    <div class="modal-body">
      <!-- Résumé de la session en cours -->
      <?php if ($is_clocked_in && $active_record): ?>
      <div class="session-summary-clockout">
        <div class="session-summary-header">
          <ons-icon icon="md-access-time" style="margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans("SessionSummary"); ?>
        </div>
        
        <div class="session-summary-grid">
          <div class="session-summary-item">
            <div class="label"><?php echo $langs->trans("Since"); ?></div>
            <div class="value"><?php echo dol_print_date($clock_in_time, 'hour', 'tzuser'); ?></div>
          </div>
          
          <div class="session-summary-item">
            <div class="label"><?php echo $langs->trans("Duration"); ?></div>
            <div class="duration" id="session-duration">
              <?php echo TimeHelper::convertSecondsToReadableTime($current_duration); ?>
            </div>
          </div>
          
          <?php if ($active_record && !empty($active_record->fk_timeclock_type)): ?>
            <?php
            $type = new TimeclockType($db);
            $type->fetch($active_record->fk_timeclock_type);
            ?>
          <div class="session-summary-item">
            <div class="label"><?php echo $langs->trans("Type"); ?></div>
            <div class="type-info">
              <div class="type-dot" style="background-color: <?php echo $type->color; ?>;"></div>
              <span class="type-label"><?php echo $type->label; ?></span>
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
        <div class="modal-buttons">
          <ons-button 
            onclick="clockOutModal.hide()" 
            class="modal-button-cancel">
            <ons-icon icon="md-close" style="margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Cancel"); ?>
          </ons-button>
          
          <ons-button 
            onclick="confirmClockOut()" 
            class="modal-button-submit modal-button-clockout">
            <ons-icon icon="md-stop" style="margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("ClockOut"); ?>
          </ons-button>
        </div>

        <!-- Localisation de sortie -->
        <div class="clockout-location-section">
          <div class="form-label">
            <ons-icon icon="md-place" style="color: #FF9800; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Location"); ?>:
          </div>
          <input 
            type="text" 
            name="location" 
            id="clockout_location" 
            placeholder="<?php echo $langs->trans("EnterLocation"); ?>" 
            class="form-input">
        </div>
        
        <!-- Note de fin de session -->
        <div class="clockout-note-section">
          <div class="form-label">
            <ons-icon icon="md-note" style="color: #2196F3; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans("Note"); ?>:
          </div>
          <textarea 
            name="note" 
            id="clockout_note"
            placeholder="<?php echo $langs->trans("OptionalNote"); ?>" 
            class="form-textarea form-textarea-clockout"></textarea>
        </div>
        
        <!-- Statut GPS -->
        <div id="gps-status-out" class="gps-status">
          <ons-icon icon="md-gps-fixed" style="color: #666; margin-right: 5px;"></ons-icon>
          <span id="gps-status-out-text"><?php echo $langs->trans("ReadyToClockOut"); ?></span>
        </div>
      </form>
    </div>
  </div>
</ons-modal>
  
  <!-- OnsenUI JS -->
  <script src="js/onsenui.min.js"></script>
  
  <!-- TimeClock API -->
  <script src="js/timeclock-api.js?v=<?php echo $js_data['version'] ?? '1.0'; ?>"></script>
  
  <!-- Custom JS Modules -->
  <script src="Assets/js/timeclock-app.js"></script>
  <script src="Assets/js/location-manager.js"></script>
  <script src="Assets/js/ui-components.js"></script>
  
  <script type="text/javascript">
    var appConfig = <?php echo json_encode($js_data); ?>;
  </script>
</ons-page>

</body>
</html>