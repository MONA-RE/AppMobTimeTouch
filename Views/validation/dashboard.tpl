<?php
/**
 * Dashboard Manager MVP 3.1 - Interface graphique testable
 * 
 * Responsabilité unique : Affichage dashboard manager minimal (SRP)
 * MVP 3.1 : Dashboard avec statistiques de base et données réelles
 */
?>

<ons-page id="ValidationDashboard">
  <!-- TopBar Manager -->
  <ons-toolbar>
    <div class="left">
      <ons-toolbar-button onclick="history.back()">
        <ons-icon icon="md-arrow-back"></ons-icon>
      </ons-toolbar-button>
    </div>
    <div class="center"><?php echo $page_title; ?></div>
    <div class="right">
      <ons-toolbar-button onclick="refreshDashboard()">
        <ons-icon icon="md-refresh"></ons-icon>
      </ons-toolbar-button>
    </div>
  </ons-toolbar>

  <!-- Messages Component -->
  <?php include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/components/Messages.tpl'; ?>

  <!-- Statistiques MVP 3.1 - Basiques et essentielles -->
  <div style="padding: 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <h3 style="margin: 0; color: #495057;">
          <ons-icon icon="md-assessment" style="color: #007bff; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('ValidationStatistics'); ?>
        </h3>
      </div>
      <div class="content" style="padding: 20px;">
        <ons-row>
          <!-- Total en attente -->
          <ons-col width="50%">
            <div style="text-align: center; padding: 15px; border-right: 1px solid #dee2e6;">
              <div style="font-size: 32px; font-weight: bold; color: #007bff; margin-bottom: 5px;">
                <?php echo $stats['total_pending']; ?>
              </div>
              <div style="font-size: 14px; color: #6c757d;">
                <?php echo $langs->trans('PendingValidation'); ?>
              </div>
            </div>
          </ons-col>
          
          <!-- Avec anomalies -->
          <ons-col width="50%">
            <div style="text-align: center; padding: 15px;">
              <div style="font-size: 32px; font-weight: bold; color: #ffc107; margin-bottom: 5px;">
                <?php echo $stats['with_anomalies']; ?>
              </div>
              <div style="font-size: 14px; color: #6c757d;">
                <?php echo $langs->trans('WithAnomalies'); ?>
              </div>
            </div>
          </ons-col>
        </ons-row>
        
        <ons-row style="margin-top: 10px; border-top: 1px solid #dee2e6; padding-top: 10px;">
          <!-- Urgents -->
          <ons-col width="50%">
            <div style="text-align: center; padding: 15px; border-right: 1px solid #dee2e6;">
              <div style="font-size: 24px; font-weight: bold; color: #dc3545; margin-bottom: 5px;">
                <?php echo $stats['urgent_count']; ?>
              </div>
              <div style="font-size: 12px; color: #6c757d;">
                <?php echo $langs->trans('UrgentValidation'); ?>
              </div>
            </div>
          </ons-col>
          
          <!-- Aujourd'hui -->
          <ons-col width="50%">
            <div style="text-align: center; padding: 15px;">
              <div style="font-size: 24px; font-weight: bold; color: #28a745; margin-bottom: 5px;">
                <?php echo $stats['today_pending']; ?>
              </div>
              <div style="font-size: 12px; color: #6c757d;">
                <?php echo $langs->trans('TodayPending'); ?>
              </div>
            </div>
          </ons-col>
        </ons-row>
      </div>
    </ons-card>
  </div>

  <!-- Notifications Manager (limité pour MVP 3.1) -->
  <?php if (!empty($notifications)): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #fff3cd; border-bottom: 1px solid #ffeaa7;">
        <h4 style="margin: 0; color: #856404;">
          <ons-icon icon="md-notifications" style="color: #ffc107; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('RecentNotifications'); ?>
        </h4>
      </div>
      <ons-list style="margin: 0;">
        <?php foreach ($notifications as $notification): ?>
        <ons-list-item>
          <div class="center">
            <div style="font-weight: 500; margin-bottom: 3px;">
              <?php echo dol_escape_htmltag($notification['message']); ?>
            </div>
            <div style="font-size: 12px; color: #6c757d;">
              <?php echo dol_print_date($notification['created_date'], 'dayhour', 'tzuser'); ?>
            </div>
          </div>
          <div class="right">
            <ons-icon icon="md-chevron-right" style="color: #6c757d;"></ons-icon>
          </div>
        </ons-list-item>
        <?php endforeach; ?>
      </ons-list>
    </ons-card>
  </div>
  <?php endif; ?>

  <!-- Enregistrements avec Validation en Lot (MVP 3.3) -->
  <?php if (!empty($pending_records)): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #e3f2fd; border-bottom: 1px solid #90caf9;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <h4 style="margin: 0; color: #1565c0;">
            <ons-icon icon="md-schedule" style="color: #2196f3; margin-right: 8px;"></ons-icon>
            <?php echo $langs->trans('TodaysRecords'); ?>
          </h4>
          <div style="display: flex; align-items: center; gap: 10px;">
            <ons-checkbox 
              id="select-all-checkbox" 
              onchange="toggleSelectAll()"
              style="margin-right: 5px;">
            </ons-checkbox>
            <label for="select-all-checkbox" style="font-size: 14px; color: #1565c0; cursor: pointer;">
              <?php echo $langs->trans('SelectAll'); ?>
            </label>
          </div>
        </div>
      </div>
      
      <!-- Batch Actions Bar (MVP 3.3) -->
      <div id="batch-actions-bar" style="display: none; padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div style="display: flex; align-items: center; gap: 15px;">
            <span id="selected-count" style="font-weight: 500; color: #495057;">0 sélectionné(s)</span>
            
            <ons-button 
              onclick="batchValidateRecords('approve')"
              style="background-color: #28a745; color: white; border-radius: 6px; padding: 8px 12px;">
              <ons-icon icon="md-check" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('ApproveAll'); ?>
            </ons-button>
            
            <ons-button 
              onclick="batchValidateRecords('reject')"
              style="background-color: #dc3545; color: white; border-radius: 6px; padding: 8px 12px;">
              <ons-icon icon="md-cancel" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('RejectAll'); ?>
            </ons-button>
            
            <ons-button 
              onclick="showBatchCommentModal()"
              style="background-color: #007bff; color: white; border-radius: 6px; padding: 8px 12px;">
              <ons-icon icon="md-comment" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('WithComment'); ?>
            </ons-button>
          </div>
          
          <ons-button 
            onclick="clearSelection()"
            modifier="quiet"
            style="color: #6c757d;">
            <ons-icon icon="md-close" style="margin-right: 5px;"></ons-icon>
            <?php echo $langs->trans('Clear'); ?>
          </ons-button>
        </div>
      </div>
      
      <ons-list style="margin: 0;">
        <?php foreach ($pending_records as $record): 
          $hasAnomalies = !empty($record['anomalies']);
          $priorityColor = $hasAnomalies ? '#ffc107' : '#28a745';
          
          // Utilisateur déjà enrichi dans le service
          $userName = isset($record['user']['fullname']) ? $record['user']['fullname'] : 'Utilisateur inconnu';
        ?>
        <ons-list-item>
          <div class="left">
            <div style="display: flex; align-items: center; gap: 8px;">
              <ons-checkbox 
                class="record-checkbox" 
                value="<?php echo $record['rowid']; ?>"
                onchange="updateSelection()"
                onclick="event.stopPropagation();">
              </ons-checkbox>
              <div style="width: 6px; height: 40px; background-color: <?php echo $priorityColor; ?>; border-radius: 3px;"></div>
            </div>
          </div>
          <div class="center" onclick="showRecordDetails(<?php echo $record['rowid']; ?>)" style="cursor: pointer;">
            <div style="font-weight: 500; margin-bottom: 8px;padding: 2px 6px;">
              <?php echo dol_escape_htmltag($userName); ?>
            </div>
            <div style="font-size: 14px; color: #6c757d; margin-bottom: 8px;padding: 2px 6px;">
              <?php echo dol_print_date($record['clock_in_time'], 'day'); ?>
              <?php if ($hasAnomalies): ?>
              <span style="background-color: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 8px;">
                <ons-icon icon="md-warning" style="font-size: 12px;"></ons-icon>
                <?php echo count($record['anomalies']); ?> anomalie(s)
              </span>
              <?php endif; ?>
            </div>
            <div style="font-size: 12px; color: #007bff; margin-bottom: 6px;">
              <?php echo dol_print_date($record['clock_in_time'], 'hour'); ?>
              <?php if (!empty($record['clock_out_time'])): ?>
              - <?php echo dol_print_date($record['clock_out_time'], 'hour'); ?>
              <?php endif; ?>
            </div>
            <?php if (!empty($record['work_duration'])): ?>
            <div style="font-size: 11px; color: #28a745; font-weight: 500;">
              <ons-icon icon="md-schedule" style="font-size: 12px; margin-right: 3px;"></ons-icon>
              Durée: <?php echo TimeHelper::formatDuration((int)$record['work_duration']); ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="right">
            <div style="text-align: right;">
              <?php 
                // Déterminer le statut et la couleur selon validation_status
                $validationStatus = isset($record['validation_status']) ? (int)$record['validation_status'] : 0;
                $recordStatus = (int)$record['status'];
                
                if ($validationStatus == 1) { // VALIDATION_APPROVED
                  $statusLabel = $langs->trans('Approved');
                  $statusColor = '#28a745'; // Vert pour approuvé
                  $statusIcon = 'md-check-circle';
                } elseif ($validationStatus == 2) { // VALIDATION_REJECTED
                  $statusLabel = $langs->trans('Rejected');
                  $statusColor = '#dc3545'; // Rouge pour rejeté
                  $statusIcon = 'md-cancel';
                } elseif ($validationStatus == 3) { // VALIDATION_PARTIAL
                  $statusLabel = $langs->trans('Partial');
                  $statusColor = '#ffc107'; // Orange pour partiel
                  $statusIcon = 'md-remove-circle';
                } else { // VALIDATION_PENDING
                  if ($recordStatus == 2) { // STATUS_IN_PROGRESS
                    $statusLabel = $langs->trans('InProgress');
                    $statusColor = '#007bff'; // Bleu pour en cours
                    $statusIcon = 'md-play-circle';
                  } else { // STATUS_COMPLETED
                    $statusLabel = $langs->trans('PendingValidation');
                    $statusColor = '#6c757d'; // Gris pour en attente
                    $statusIcon = 'md-schedule';
                  }
                }
              ?>
              <div style="font-size: 12px; color: <?php echo $statusColor; ?>; font-weight: 500;">
                <ons-icon icon="<?php echo $statusIcon; ?>" style="font-size: 12px; margin-right: 2px;"></ons-icon>
                <?php echo $statusLabel; ?>
              </div>
              <ons-icon icon="md-chevron-right" style="color: #6c757d; margin-top: 5px;"></ons-icon>
            </div>
          </div>
        </ons-list-item>
        <?php endforeach; ?>
      </ons-list>
      
      <!-- Lien vers liste complète -->
      <div style="text-align: center; padding: 15px; border-top: 1px solid #dee2e6;">
        <ons-button modifier="quiet" onclick="gotoFullList()" style="color: #007bff;">
          <?php echo $langs->trans('ViewAllPendingRecords'); ?>
          <ons-icon icon="md-arrow-forward" style="margin-left: 5px;"></ons-icon>
        </ons-button>
      </div>
    </ons-card>
  </div>
  <?php else: ?>
  <!-- État vide pour MVP 3.1 -->
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="content" style="text-align: center; padding: 40px;">
        <ons-icon icon="md-check-circle" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></ons-icon>
        <h4 style="color: #28a745; margin-bottom: 10px;">
          <?php echo $langs->trans('AllValidated'); ?>
        </h4>
        <p style="color: #6c757d; margin: 0;">
          <?php echo $langs->trans('NoValidationPending'); ?>
        </p>
      </div>
    </ons-card>
  </div>
  <?php endif; ?>

  <!-- Actions rapides MVP 3.1 -->
  <div style="padding: 0 15px 80px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <h4 style="margin: 0; color: #495057;">
          <ons-icon icon="md-settings" style="color: #6c757d; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('QuickActions'); ?>
        </h4>
      </div>
      <div class="content" style="padding: 20px;">
        <ons-row>
          <ons-col width="50%">
            <ons-button 
              onclick="refreshDashboard()"
              style="width: 100%; background-color: #007bff; color: white; border-radius: 8px; padding: 12px;">
              <ons-icon icon="md-refresh" style="margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans('Refresh'); ?>
            </ons-button>
          </ons-col>
          <ons-col width="50%">
            <ons-button 
              onclick="gotoFullList()"
              style="width: 100%; background-color: #28a745; color: white; border-radius: 8px; padding: 12px;">
              <ons-icon icon="md-list" style="margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans('ViewAll'); ?>
            </ons-button>
          </ons-col>
        </ons-row>
      </div>
    </ons-card>
  </div>

  <!-- JavaScript pour MVP 3.1 -->
  <script>
  /**
   * Actualiser le dashboard
   */
  function refreshDashboard() {
    ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
    setTimeout(function() {
      location.reload();
    }, 500);
  }
  
  /**
   * Aller vers liste complète - Navigate to dedicated list page (MVP 3.3)
   */
  function gotoFullList() {
    // Show loading message
    ons.notification.toast('<?php echo $langs->trans("LoadingAllRecords"); ?>...', {timeout: 1000});
    
    // Navigate to dedicated list page
    setTimeout(() => {
      window.location.href = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php?action=list_all';
    }, 500);
  }
  
  
  // === BATCH VALIDATION FUNCTIONS (MVP 3.3) ===
  
  /**
   * Toggle select all checkboxes
   */
  function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const recordCheckboxes = document.querySelectorAll('.record-checkbox');
    
    recordCheckboxes.forEach(checkbox => {
      checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelection();
  }
  
  /**
   * Update selection count and show/hide batch actions
   */
  function updateSelection() {
    const checkedBoxes = document.querySelectorAll('.record-checkbox:checked');
    const count = checkedBoxes.length;
    const batchActionsBar = document.getElementById('batch-actions-bar');
    const selectedCountSpan = document.getElementById('selected-count');
    
    if (count > 0) {
      batchActionsBar.style.display = 'block';
      selectedCountSpan.textContent = count + ' sélectionné(s)';
    } else {
      batchActionsBar.style.display = 'none';
      // Uncheck select all if no items selected
      document.getElementById('select-all-checkbox').checked = false;
    }
    
    // Update select all checkbox state
    const totalCheckboxes = document.querySelectorAll('.record-checkbox').length;
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    if (count === totalCheckboxes && count > 0) {
      selectAllCheckbox.checked = true;
    } else if (count === 0) {
      selectAllCheckbox.checked = false;
    } else {
      selectAllCheckbox.indeterminate = true;
    }
  }
  
  /**
   * Clear all selections
   */
  function clearSelection() {
    document.querySelectorAll('.record-checkbox').forEach(checkbox => {
      checkbox.checked = false;
    });
    document.getElementById('select-all-checkbox').checked = false;
    updateSelection();
  }
  
  /**
   * Batch validate selected records
   */
  function batchValidateRecords(action) {
    const selectedCheckboxes = document.querySelectorAll('.record-checkbox:checked');
    const recordIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (recordIds.length === 0) {
      ons.notification.alert('<?php echo $langs->trans("NoRecordsSelected"); ?>');
      return;
    }
    
    // Confirmation dialog
    const actionLabels = {
      'approve': '<?php echo $langs->trans("Approve"); ?>',
      'reject': '<?php echo $langs->trans("Reject"); ?>',
      'partial': '<?php echo $langs->trans("Partial"); ?>'
    };
    
    const confirmMessage = `<?php echo $langs->trans("ConfirmBatchValidation"); ?> ${recordIds.length} enregistrement(s) - ${actionLabels[action]}?`;
    
    ons.notification.confirm({
      message: confirmMessage,
      callback: function(confirmed) {
        if (confirmed) {
          performBatchValidation(recordIds, action);
        }
      }
    });
  }
  
  /**
   * Perform the actual batch validation
   */
  function performBatchValidation(recordIds, action, comment = null) {
    // Show loading
    ons.notification.toast('<?php echo $langs->trans("ProcessingBatchValidation"); ?>...', {timeout: 1000});
    
    // Disable batch actions during processing
    const batchButtons = document.querySelectorAll('#batch-actions-bar ons-button');
    batchButtons.forEach(btn => btn.disabled = true);
    
    const formData = new FormData();
    formData.append('action', 'batch_validate');
    formData.append('batch_action', action);
    recordIds.forEach(id => formData.append('record_ids[]', id));
    if (comment) {
      formData.append('batch_comment', comment);
    }
    formData.append('token', '<?php echo newToken(); ?>');
    
    fetch('<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php', {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      batchButtons.forEach(btn => btn.disabled = false);
      
      if (data.error === 0) {
        // Success
        ons.notification.toast(data.messages[0], {timeout: 3000});
        // Refresh page after 2 seconds
        setTimeout(() => {
          location.reload();
        }, 2000);
      } else {
        // Error
        ons.notification.alert(data.errors[0]);
      }
    })
    .catch(error => {
      batchButtons.forEach(btn => btn.disabled = false);
      console.error('Batch validation error:', error);
      ons.notification.alert('<?php echo $langs->trans("BatchValidationError"); ?>');
    });
  }
  
  /**
   * Show batch comment modal
   */
  function showBatchCommentModal() {
    const selectedCheckboxes = document.querySelectorAll('.record-checkbox:checked');
    const recordIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (recordIds.length === 0) {
      ons.notification.alert('<?php echo $langs->trans("NoRecordsSelected"); ?>');
      return;
    }
    
    document.getElementById('batch-comment-modal').show();
  }
  
  // Debug MVP 3.3
  console.log('ValidationDashboard MVP 3.3 loaded - Batch validation enabled');
  console.log('Stats:', <?php echo json_encode($stats); ?>);
  console.log('Pending records:', <?php echo count($pending_records); ?>);
  console.log('Notifications:', <?php echo count($notifications); ?>);
  </script>

  <!-- Batch Comment Modal (MVP 3.3) -->
  <ons-modal id="batch-comment-modal" var="batchCommentModal">
    <div style="text-align: center; padding: 20px; background-color: white; border-radius: 10px; margin: 20px;">
      <h3 style="margin: 0 0 20px 0; color: #333;">
        <ons-icon icon="md-comment" style="color: #007bff; margin-right: 8px;"></ons-icon>
        <?php echo $langs->trans('BatchValidationComment'); ?>
      </h3>
      
      <textarea 
        id="batch-comment-textarea"
        placeholder="<?php echo $langs->trans('EnterValidationComment'); ?>"
        rows="4"
        style="width: 100%; border: 1px solid #ced4da; border-radius: 6px; padding: 10px; font-family: inherit; margin-bottom: 20px;">
      </textarea>
      
      <div style="display: flex; justify-content: space-between; gap: 10px;">
        <ons-button 
          onclick="closeBatchCommentModal()"
          modifier="quiet"
          style="flex: 1; border: 1px solid #6c757d; border-radius: 6px;">
          <?php echo $langs->trans('Cancel'); ?>
        </ons-button>
        
        <ons-button 
          onclick="submitBatchWithComment('approve')"
          style="flex: 1; background-color: #28a745; color: white; border-radius: 6px;">
          <ons-icon icon="md-check" style="margin-right: 5px;"></ons-icon>
          <?php echo $langs->trans('Approve'); ?>
        </ons-button>
        
        <ons-button 
          onclick="submitBatchWithComment('reject')"
          style="flex: 1; background-color: #dc3545; color: white; border-radius: 6px;">
          <ons-icon icon="md-cancel" style="margin-right: 5px;"></ons-icon>
          <?php echo $langs->trans('Reject'); ?>
        </ons-button>
      </div>
    </div>
  </ons-modal>

  <script>
  /**
   * Additional functions for batch comment modal
   */
  function closeBatchCommentModal() {
    document.getElementById('batch-comment-modal').hide();
    document.getElementById('batch-comment-textarea').value = '';
  }
  
  function submitBatchWithComment(action) {
    const comment = document.getElementById('batch-comment-textarea').value.trim();
    const selectedCheckboxes = document.querySelectorAll('.record-checkbox:checked');
    const recordIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (!comment) {
      ons.notification.alert('<?php echo $langs->trans("CommentRequired"); ?>');
      return;
    }
    
    closeBatchCommentModal();
    performBatchValidation(recordIds, action, comment);
  }
  </script>
</ons-page>