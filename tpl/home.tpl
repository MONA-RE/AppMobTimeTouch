<ons-page id="ONSHome">
  <?php include "tpl/parts/topbar-home.tpl"; ?>
  
  <ons-pull-hook id="pull-hook">
    <?php echo $langs->trans("PullToRefresh"); ?>
  </ons-pull-hook>
  
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
            <ons-icon icon="md-time" size="48px" style="color: #4CAF50;"></ons-icon>
            <h3 style="color: #4CAF50; margin: 10px 0;">
              <?php echo $langs->trans("ClockedIn"); ?>
            </h3>
            <p style="margin: 5px 0;">
              <strong><?php echo $langs->trans("Since"); ?>:</strong> 
              <?php echo dol_print_date($clock_in_time, 'dayhour'); ?>
            </p>
            <p style="margin: 5px 0;">
              <strong><?php echo $langs->trans("Duration"); ?>:</strong>
              <span id="current-duration"><?php echo convertSecondsToReadableTime($current_duration); ?></span>
            </p>
            <?php if ($active_record && !empty($active_record->fk_timeclock_type)): ?>
              <?php
              $type = new TimeclockType($db);
              $type->fetch($active_record->fk_timeclock_type);
              ?>
              <p style="margin: 5px 0;">
                <strong><?php echo $langs->trans("Type"); ?>:</strong>
                <span style="color: <?php echo $type->color; ?>; font-weight: bold;">
                  <?php echo $type->label; ?>
                </span>
              </p>
            <?php endif; ?>
          </div>
          
          <!-- Clock Out Button -->
          <div style="margin-top: 20px;">
            <ons-button modifier="large" onclick="clockOut()" style="background-color: #f44336; color: white; width: 100%; border-radius: 25px;">
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
            <ons-button modifier="large" onclick="clockIn()" style="background-color: #4CAF50; color: white; width: 100%; border-radius: 25px;">
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
              <ons-icon icon="md-access-time" style="color: #2196F3;"></ons-icon>
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
              <ons-icon icon="md-pause" style="color: #FF9800;"></ons-icon>
              <p style="margin: 5px 0; font-size: 14px; color: #666;">
                <?php echo $langs->trans("BreakTime"); ?>
              </p>
              <p style="margin: 0; font-size: 18px; font-weight: bold; color: #FF9800;">
                <?php echo convertSecondsToReadableTime($today_total_breaks * 60); ?>
              </p>
            </div>
          </ons-col>
        </ons-row>
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
              <?php echo $type->label; ?>
            </div>
            <div style="font-size: 12px; color: #999;">
              <?php echo $clock_in; ?>
              <?php if ($clock_out): ?>
                - <?php echo $clock_out; ?>
              <?php else: ?>
                - <?php echo $langs->trans("InProgress"); ?>
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
    </ons-card>
  </div>
  <?php endif; ?>
  
  <script type="text/javascript">
    var updateDurationTimer = null;
    
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
      var clockInTime = <?php echo $is_clocked_in ? $clock_in_time : 0; ?>;
      if (clockInTime > 0) {
        var now = Math.floor(Date.now() / 1000);
        var duration = now - clockInTime;
        var hours = Math.floor(duration / 3600);
        var minutes = Math.floor((duration % 3600) / 60);
        
        var durationText = hours + 'h' + (minutes < 10 ? '0' : '') + minutes;
        
        var durationElement = document.getElementById('current-duration');
        if (durationElement) {
          durationElement.textContent = durationText;
        }
      }
    }
    
    // Clock In function (placeholder)
    function clockIn() {
      console.log('Clock In clicked');
      // TODO: Implement clock in functionality
      ons.notification.alert('<?php echo $langs->trans("ClockInFunctionality"); ?>');
    }
    
    // Clock Out function (placeholder)
    function clockOut() {
      console.log('Clock Out clicked');
      // TODO: Implement clock out functionality
      ons.notification.alert('<?php echo $langs->trans("ClockOutFunctionality"); ?>');
    }
    
    // View record function (placeholder)
    function viewRecord(recordId) {
      console.log('View record:', recordId);
      // TODO: Implement view record functionality
      ons.notification.alert('<?php echo $langs->trans("ViewRecordFunctionality"); ?>: ' + recordId);
    }
  </script>
</ons-page>