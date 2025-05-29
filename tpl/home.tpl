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
              <?php echo dol_print_date($clock_in_time, 'dayhour'); ?>
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
            <div style="text-align: center; padding: 10px;">
              <ons-icon icon="md-pause" style="color: #FF9800; font-size: 24px;"></ons-icon>
              <p style="margin: 5px 0; font-size: 14px; color: #666;">
                <?php echo $langs->trans("BreakTime"); ?>
              </p>
              <p style="margin: 0; font-size: 18px; font-weight: bold; color: #FF9800;">
                <?php echo convertSecondsToReadableTime($today_total_breaks * 60); ?>
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
              <?php else: ?>
                - <span style="color: #4CAF50; font-weight: bold;"><?php echo $langs->trans("InProgress"); ?></span>
              <?php endif; ?>
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
    <div style="text-align: center; padding: 20px;">
      <h3><?php echo $langs->trans("ClockIn"); ?></h3>
      
      <form id="clockInForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="clockin">
        <input type="hidden" name="token" value="<?php echo newToken(); ?>">
        <input type="hidden" name="latitude" id="clockin_latitude">
        <input type="hidden" name="longitude" id="clockin_longitude">
        
        <!-- Type selection -->
        <div style="margin: 15px 0; text-align: left;">
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">
            <?php echo $langs->trans("TimeclockType"); ?>:
          </label>
          <select name="timeclock_type_id" class="select-input" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <?php foreach ($timeclock_types as $type): ?>
            <option value="<?php echo $type->id; ?>" <?php echo ($type->id == $default_type_id) ? 'selected' : ''; ?>>
              <?php echo $type->label; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <!-- Location -->
        <div style="margin: 15px 0; text-align: left;">
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">
            <?php echo $langs->trans("Location"); ?>:
          </label>
          <input type="text" name="location" id="clockin_location" placeholder="<?php echo $langs->trans("EnterLocation"); ?>" 
                 style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <!-- Note -->
        <div style="margin: 15px 0; text-align: left;">
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">
            <?php echo $langs->trans("Note"); ?>:
          </label>
          <textarea name="note" placeholder="<?php echo $langs->trans("OptionalNote"); ?>" 
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; height: 60px; resize: vertical;"></textarea>
        </div>
        
        <!-- GPS Status -->
        <div id="gps-status" style="margin: 10px 0; font-size: 12px; color: #666;"></div>
        
        <div style="margin-top: 20px;">
          <ons-button onclick="clockInModal.hide()" style="margin-right: 10px;">
            <?php echo $langs->trans("Cancel"); ?>
          </ons-button>
          <ons-button modifier="material" onclick="submitClockIn()" style="background-color: #4CAF50;">
            <?php echo $langs->trans("ClockIn"); ?>
          </ons-button>
        </div>
      </form>
    </div>
  </ons-modal>

  <!-- Clock Out Modal -->
  <ons-modal var="clockOutModal" id="clockOutModal">
    <div style="text-align: center; padding: 20px;">
      <h3><?php echo $langs->trans("ClockOut"); ?></h3>
      
      <form id="clockOutForm" method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input type="hidden" name="action" value="clockout">
        <input type="hidden" name="token" value="<?php echo newToken(); ?>">
        <input type="hidden" name="latitude" id="clockout_latitude">
        <input type="hidden" name="longitude" id="clockout_longitude">
        
        <!-- Location -->
        <div style="margin: 15px 0; text-align: left;">
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">
            <?php echo $langs->trans("Location"); ?>:
          </label>
          <input type="text" name="location" id="clockout_location" placeholder="<?php echo $langs->trans("EnterLocation"); ?>" 
                 style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>
        
        <!-- Note -->
        <div style="margin: 15px 0; text-align: left;">
          <label style="font-weight: bold; display: block; margin-bottom: 5px;">
            <?php echo $langs->trans("Note"); ?>:
          </label>
          <textarea name="note" placeholder="<?php echo $langs->trans("OptionalNote"); ?>" 
                    style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; height: 60px; resize: vertical;"></textarea>
        </div>
        
        <!-- GPS Status -->
        <div id="gps-status-out" style="margin: 10px 0; font-size: 12px; color: #666;"></div>
        
        <div style="margin-top: 20px;">
          <ons-button onclick="clockOutModal.hide()" style="margin-right: 10px;">
            <?php echo $langs->trans("Cancel"); ?>
          </ons-button>
          <ons-button modifier="material" onclick="submitClockOut()" style="background-color: #f44336;">
            <?php echo $langs->trans("ClockOut"); ?>
          </ons-button>
        </div>
      </form>
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
    
    // Show Clock In Modal
    function showClockInModal() {
      var modal = document.getElementById('clockInModal');
      modal.show();
      
      // Get GPS location if required
      if (appConfig.require_location) {
        getCurrentPosition('in');
      }
    }
    
    // Show Clock Out Modal  
    function showClockOutModal() {
      var modal = document.getElementById('clockOutModal');
      modal.show();
      
      // Get GPS location if required
      if (appConfig.require_location) {
        getCurrentPosition('out');
      }
    }
    
    // Get current GPS position
    function getCurrentPosition(type) {
      var statusElement = document.getElementById('gps-status' + (type === 'out' ? '-out' : ''));
      
      if (navigator.geolocation) {
        statusElement.innerHTML = '<ons-icon icon="md-gps-fixed" spin></ons-icon> <?php echo $langs->trans("GettingLocation"); ?>...';
        
        navigator.geolocation.getCurrentPosition(
          function(position) {
            var lat = position.coords.latitude;
            var lon = position.coords.longitude;
            var accuracy = Math.round(position.coords.accuracy);
            
            document.getElementById('clock' + type + '_latitude').value = lat;
            document.getElementById('clock' + type + '_longitude').value = lon;
            
            statusElement.innerHTML = '<ons-icon icon="md-gps-fixed" style="color: #4CAF50;"></ons-icon> <?php echo $langs->trans("LocationFound"); ?> (±' + accuracy + 'm)';
            statusElement.style.color = '#4CAF50';
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
            statusElement.innerHTML = '<ons-icon icon="md-gps-off" style="color: #f44336;"></ons-icon> ' + errorMsg;
            statusElement.style.color = '#f44336';
          },
          {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000
          }
        );
      } else {
        statusElement.innerHTML = '<ons-icon icon="md-gps-off" style="color: #f44336;"></ons-icon> <?php echo $langs->trans("LocationNotSupported"); ?>';
        statusElement.style.color = '#f44336';
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
  </script>
</ons-page>