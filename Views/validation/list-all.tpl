<?php
/**
 * Liste complète avec filtres - MVP 3.3
 * 
 * Responsabilité unique : Affichage liste filtrée des enregistrements (SRP)
 * MVP 3.3 : Interface de gestion complète avec filtres avancés
 */
?>

<ons-page id="ValidationListAll">
  <!-- TopBar avec retour (adapté selon vue) -->
  <ons-toolbar>
    <div class="left">
      <?php if (isset($is_personal_view) && $is_personal_view): ?>
      <ons-toolbar-button onclick="goBackToHome()">
        <ons-icon icon="md-arrow-back"></ons-icon>
      </ons-toolbar-button>
      <?php else: ?>
      <ons-toolbar-button onclick="goBackToDashboard()">
        <ons-icon icon="md-arrow-back"></ons-icon>
      </ons-toolbar-button>
      <?php endif; ?>
    </div>
    <div class="center"><?php echo $page_title; ?></div>
    <div class="right">
      <ons-toolbar-button onclick="toggleFilters()">
        <ons-icon icon="md-filter-list"></ons-icon>
      </ons-toolbar-button>
    </div>
  </ons-toolbar>

  <!-- Messages Component -->
  <?php include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/components/Messages.tpl'; ?>

  <!-- Statistiques rapides -->
  <div style="padding: 15px;">
    <ons-card>
      <div class="content" style="padding: 15px; <?php if (isset($is_personal_view) && $is_personal_view): ?>background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;<?php endif; ?>">
        <?php if (isset($is_personal_view) && $is_personal_view): ?>
        <!-- Vue personnelle : statistiques simplifiées -->
        <ons-row>
          <ons-col width="25%">
            <div style="text-align: center;">
              <div style="font-size: 22px; font-weight: bold; color: #ffffff;">
                <?php echo $stats['today']; ?>
              </div>
              <div style="font-size: 12px; color: rgba(255,255,255,0.9);">
                <?php echo $langs->trans('Today'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="25%">
            <div style="text-align: center;">
              <div style="font-size: 22px; font-weight: bold; color: #ffffff;">
                <?php echo $stats['approved']; ?>
              </div>
              <div style="font-size: 12px; color: rgba(255,255,255,0.9);">
                <?php echo $langs->trans('Approved'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="25%">
            <div style="text-align: center;">
              <div style="font-size: 22px; font-weight: bold; color: <?php echo (isset($stats['with_anomalies']) && $stats['with_anomalies'] > 0) ? '#ffeb3b' : '#ffffff'; ?>;">
                <?php echo $stats['with_anomalies'] ?? 0; ?>
              </div>
              <div style="font-size: 12px; color: rgba(255,255,255,0.9);">
                <?php echo $langs->trans('Anomalies'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="25%">
            <div style="text-align: center;">
              <div style="font-size: 22px; font-weight: bold; color: #ffffff;">
                <?php echo ($stats['today'] + $stats['approved'] + $stats['pending'] + $stats['rejected']); ?>
              </div>
              <div style="font-size: 12px; color: rgba(255,255,255,0.9);">
                <?php echo $langs->trans('Total'); ?>
              </div>
            </div>
          </ons-col>
        </ons-row>
        <?php else: ?>
        <!-- Vue manager : statistiques complètes -->
        <ons-row>
          <ons-col width="20%">
            <div style="text-align: center;">
              <div style="font-size: 20px; font-weight: bold; color: #007bff;">
                <?php echo $stats['today']; ?>
              </div>
              <div style="font-size: 12px; color: #6c757d;">
                <?php echo $langs->trans('Today'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="20%">
            <div style="text-align: center;">
              <div style="font-size: 20px; font-weight: bold; color: #6c757d;">
                <?php echo $stats['pending']; ?>
              </div>
              <div style="font-size: 12px; color: #6c757d;">
                <?php echo $langs->trans('Pending'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="20%">
            <div style="text-align: center;">
              <div style="font-size: 20px; font-weight: bold; color: #28a745;">
                <?php echo $stats['approved']; ?>
              </div>
              <div style="font-size: 12px; color: #6c757d;">
                <?php echo $langs->trans('Approved'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="20%">
            <div style="text-align: center;">
              <div style="font-size: 20px; font-weight: bold; color: #dc3545;">
                <?php echo $stats['rejected']; ?>
              </div>
              <div style="font-size: 12px; color: #6c757d;">
                <?php echo $langs->trans('Rejected'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="20%">
            <div style="text-align: center;">
              <div style="font-size: 20px; font-weight: bold; color: #ffc107;">
                <?php echo $stats['with_anomalies']; ?>
              </div>
              <div style="font-size: 12px; color: #6c757d;">
                <?php echo $langs->trans('Anomalies'); ?>
              </div>
            </div>
          </ons-col>
        </ons-row>
        <?php endif; ?>
      </div>
    </ons-card>
  </div>

  <!-- Panneau de filtres (caché par défaut) -->
  <div id="filters-panel" style="display: none; padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <h4 style="margin: 0; color: #495057;">
          <ons-icon icon="md-filter-list" style="color: #007bff; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('Filters'); ?>
        </h4>
      </div>
      <div class="content" style="padding: 15px;">
        <form id="filters-form">
          <ons-row>
            <!-- Filtre par statut -->
            <ons-col width="50%">
              <label for="filter_status" style="font-weight: 500; margin-bottom: 5px; display: block;">
                <?php echo $langs->trans('Status'); ?>
              </label>
              <select id="filter_status" name="filter_status" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
                <option value="all" <?php echo ($filters['status'] === 'all') ? 'selected' : ''; ?>><?php echo $langs->trans('All'); ?></option>
                <option value="pending" <?php echo ($filters['status'] === 'pending') ? 'selected' : ''; ?>><?php echo $langs->trans('Pending'); ?></option>
                <option value="approved" <?php echo ($filters['status'] === 'approved') ? 'selected' : ''; ?>><?php echo $langs->trans('Approved'); ?></option>
                <option value="rejected" <?php echo ($filters['status'] === 'rejected') ? 'selected' : ''; ?>><?php echo $langs->trans('Rejected'); ?></option>
                <option value="partial" <?php echo ($filters['status'] === 'partial') ? 'selected' : ''; ?>><?php echo $langs->trans('Partial'); ?></option>
              </select>
            </ons-col>
            
            <!-- Filtre par utilisateur -->
            <ons-col width="50%">
              <label for="filter_user" style="font-weight: 500; margin-bottom: 5px; display: block;">
                <?php echo $langs->trans('User'); ?>
              </label>
              <select id="filter_user" name="filter_user" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
                <option value=""><?php echo $langs->trans('AllUsers'); ?></option>
                <?php foreach ($team_members as $member): ?>
                <option value="<?php echo $member['id']; ?>" <?php echo ($filters['user_id'] == $member['id']) ? 'selected' : ''; ?>>
                  <?php echo dol_escape_htmltag($member['name']); ?>
                </option>
                <?php endforeach; ?>
              </select>
            </ons-col>
          </ons-row>
          
          <ons-row style="margin-top: 15px;">
            <!-- Date de début -->
            <ons-col width="50%">
              <label for="filter_date_from" style="font-weight: 500; margin-bottom: 5px; display: block;">
                <?php echo $langs->trans('DateFrom'); ?>
              </label>
              <input type="date" id="filter_date_from" name="filter_date_from" 
                     value="<?php echo $filters['date_from']; ?>"
                     style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
            </ons-col>
            
            <!-- Date de fin -->
            <ons-col width="50%">
              <label for="filter_date_to" style="font-weight: 500; margin-bottom: 5px; display: block;">
                <?php echo $langs->trans('DateTo'); ?>
              </label>
              <input type="date" id="filter_date_to" name="filter_date_to" 
                     value="<?php echo $filters['date_to']; ?>"
                     style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
            </ons-col>
          </ons-row>
          
          <ons-row style="margin-top: 15px;">
            <!-- Filtre anomalies -->
            <ons-col width="50%">
              <label for="filter_anomalies" style="font-weight: 500; margin-bottom: 5px; display: block;">
                <?php echo $langs->trans('Anomalies'); ?>
              </label>
              <select id="filter_anomalies" name="filter_anomalies" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
                <option value="all" <?php echo ($filters['has_anomalies'] === 'all') ? 'selected' : ''; ?>><?php echo $langs->trans('All'); ?></option>
                <option value="yes" <?php echo ($filters['has_anomalies'] === 'yes') ? 'selected' : ''; ?>><?php echo $langs->trans('WithAnomalies'); ?></option>
                <option value="no" <?php echo ($filters['has_anomalies'] === 'no') ? 'selected' : ''; ?>><?php echo $langs->trans('WithoutAnomalies'); ?></option>
              </select>
            </ons-col>
            
            <!-- Tri -->
            <ons-col width="50%">
              <label for="sort_by" style="font-weight: 500; margin-bottom: 5px; display: block;">
                <?php echo $langs->trans('SortBy'); ?>
              </label>
              <select id="sort_by" name="sort_by" style="width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px;">
                <option value="date_desc" <?php echo ($filters['sort_by'] === 'date_desc') ? 'selected' : ''; ?>><?php echo $langs->trans('DateDesc'); ?></option>
                <option value="date_asc" <?php echo ($filters['sort_by'] === 'date_asc') ? 'selected' : ''; ?>><?php echo $langs->trans('DateAsc'); ?></option>
                <option value="user_asc" <?php echo ($filters['sort_by'] === 'user_asc') ? 'selected' : ''; ?>><?php echo $langs->trans('UserAsc'); ?></option>
                <option value="status" <?php echo ($filters['sort_by'] === 'status') ? 'selected' : ''; ?>><?php echo $langs->trans('Status'); ?></option>
              </select>
            </ons-col>
          </ons-row>
          
          <!-- Boutons d'action -->
          <div style="margin-top: 20px; text-align: center;">
            <ons-button onclick="applyFilters()" style="background-color: #007bff; color: white; margin-right: 10px;">
              <ons-icon icon="md-search" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('ApplyFilters'); ?>
            </ons-button>
            <ons-button onclick="clearFilters()" modifier="quiet">
              <ons-icon icon="md-clear" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('ClearFilters'); ?>
            </ons-button>
          </div>
        </form>
      </div>
    </ons-card>
  </div>

  <!-- Batch Actions Bar (MVP 3.3) - Masquée en vue personnelle -->
  <?php if (!isset($is_personal_view) || !$is_personal_view): ?>
  <div id="batch-actions-bar" style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <span id="selected-count" style="font-weight: 500; color: #495057; font-size: 14px;">0 sélectionné(s)</span>
            
            <ons-button 
              onclick="batchValidateRecords('approve')"
              title="<?php echo $langs->trans('ApproveAll'); ?>"
              style="background-color: #28a745; color: white; border-radius: 50%; width: 40px; height: 40px; padding: 0; min-width: 40px; display: flex; align-items: center; justify-content: center;">
              <ons-icon icon="md-check" style="font-size: 18px;"></ons-icon>
            </ons-button>
            
            <ons-button 
              onclick="batchValidateRecords('reject')"
              title="<?php echo $langs->trans('RejectAll'); ?>"
              style="background-color: #dc3545; color: white; border-radius: 50%; width: 40px; height: 40px; padding: 0; min-width: 40px; display: flex; align-items: center; justify-content: center;">
              <ons-icon icon="md-cancel" style="font-size: 18px;"></ons-icon>
            </ons-button>
            
            <ons-button 
              onclick="showBatchCommentModal()"
              title="<?php echo $langs->trans('WithComment'); ?>"
              style="background-color: #007bff; color: white; border-radius: 50%; width: 40px; height: 40px; padding: 0; min-width: 40px; display: flex; align-items: center; justify-content: center;">
              <ons-icon icon="md-comment" style="font-size: 18px;"></ons-icon>
            </ons-button>
          </div>
          
          <ons-button 
            onclick="clearSelection()"
            modifier="quiet"
            title="<?php echo $langs->trans('Clear'); ?>"
            style="color: #6c757d; width: 36px; height: 36px; padding: 0; min-width: 36px; display: flex; align-items: center; justify-content: center;">
            <ons-icon icon="md-close" style="font-size: 16px;"></ons-icon>
          </ons-button>
        </div>
      </div>
    </ons-card>
  </div>
  <?php endif; ?>

  <!-- Liste des enregistrements -->
  <?php if (!empty($records)): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #e3f2fd; border-bottom: 1px solid #90caf9;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <h4 style="margin: 0; color: #1565c0;">
            <ons-icon icon="md-list" style="color: #2196f3; margin-right: 8px;"></ons-icon>
            <?php if (isset($is_personal_view) && $is_personal_view): ?>
            <?php echo $langs->trans('MyTimeclockRecords'); ?> (<?php echo count($records); ?>)
            <?php else: ?>
            <?php echo $langs->trans('ValidationRecords'); ?> (<?php echo count($records); ?>)
            <?php endif; ?>
          </h4>
          <?php if (!isset($is_personal_view) || !$is_personal_view): ?>
          <div style="display: flex; align-items: center; gap: 10px;">
            <ons-checkbox 
              id="select-all-checkbox" 
              style="margin-right: 5px;">
            </ons-checkbox>
            <label for="select-all-checkbox" style="font-size: 14px; color: #1565c0; cursor: pointer;">
              <?php echo $langs->trans('SelectAll'); ?>
            </label>
          </div>
          <?php endif; ?>
        </div>
      </div>
      
      <ons-list style="margin: 0;">
        <?php foreach ($records as $record): 
          $hasAnomalies = !empty($record['anomalies']);
          $priorityColor = $hasAnomalies ? '#ffc107' : '#28a745';
          
          // Utilisateur déjà enrichi dans le service
          $userName = isset($record['user']['fullname']) ? $record['user']['fullname'] : 'Utilisateur inconnu';
        ?>
        <ons-list-item>
          <div class="left">
            <div style="display: flex; align-items: center; gap: 8px;">
              <?php if (!isset($is_personal_view) || !$is_personal_view): ?>
              <ons-checkbox 
                class="record-checkbox" 
                data-record-id="<?php echo $record['rowid']; ?>"
                onclick="event.stopPropagation();">
              </ons-checkbox>
              <?php endif; ?>
              <div style="width: 6px; height: 40px; background-color: <?php echo $priorityColor; ?>; border-radius: 3px;"></div>
            </div>
          </div>
          <div class="center" onclick="<?php if (isset($is_personal_view) && $is_personal_view): ?>showEmployeeRecordDetails(<?php echo $record['rowid']; ?>)<?php else: ?>showRecordDetails(<?php echo $record['rowid']; ?>)<?php endif; ?>" style="cursor: pointer;">
            <?php if (!isset($is_personal_view) || !$is_personal_view): ?>
            <div style="font-weight: 500; margin-bottom: 8px; padding: 2px 6px;">
              <?php echo dol_escape_htmltag($userName); ?>
            </div>
            <?php endif; ?>
            <div style="font-size: 14px; color: #6c757d; margin-bottom: 8px; padding: 2px 6px;">
              <?php echo dol_print_date($record['clock_in_time'], 'day', 'tzuser'); ?>
              <?php if ($hasAnomalies): ?>
              <span style="background-color: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 8px;">
                <ons-icon icon="md-warning" style="font-size: 12px;"></ons-icon>
                <?php echo count($record['anomalies']); ?> anomalie(s)
              </span>
              <?php endif; ?>
            </div>
            <div style="font-size: 12px; color: #007bff; margin-bottom: 6px;">
              <?php echo dol_print_date($record['clock_in_time'], 'hour', 'tzuser'); ?>
              <?php if (!empty($record['clock_out_time'])): ?>
              - <?php echo dol_print_date($record['clock_out_time'], 'hour', 'tzuser'); ?>
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
    </ons-card>
  </div>
  <?php else: ?>
  <!-- État vide -->
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="content" style="text-align: center; padding: 40px;">
        <ons-icon icon="md-search" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></ons-icon>
        <h4 style="color: #6c757d; margin-bottom: 10px;">
          <?php echo $langs->trans('NoRecordsFound'); ?>
        </h4>
        <p style="color: #6c757d; margin: 0;">
          <?php echo $langs->trans('TryDifferentFilters'); ?>
        </p>
      </div>
    </ons-card>
  </div>
  <?php endif; ?>

  <!-- JavaScript pour la gestion des filtres -->
  <script>
  /**
   * Toggle l'affichage du panneau de filtres
   */
  function toggleFilters() {
    const panel = document.getElementById('filters-panel');
    if (panel.style.display === 'none') {
      panel.style.display = 'block';
      panel.scrollIntoView({ behavior: 'smooth' });
    } else {
      panel.style.display = 'none';
    }
  }
  
  /**
   * Appliquer les filtres sélectionnés
   */
  function applyFilters() {
    const form = document.getElementById('filters-form');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Ajouter l'action
    params.append('action', 'list_all');
    
    // Ajouter tous les filtres
    for (const [key, value] of formData.entries()) {
      if (value) {
        params.append(key, value);
      }
    }
    
    // Afficher un indicateur de chargement
    ons.notification.toast('<?php echo $langs->trans("ApplyingFilters"); ?>...', {timeout: 1000});
    
    // Rediriger avec les nouveaux filtres vers la page actuelle
    setTimeout(() => {
      // Détecter la page actuelle pour rediriger correctement
      const currentPath = window.location.pathname;
      let targetPage = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php';
      
      if (currentPath.includes('myrecords.php')) {
        targetPage = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/myrecords.php';
      }
      
      window.location.href = targetPage + '?' + params.toString();
    }, 500);
  }
  
  /**
   * Effacer tous les filtres
   */
  function clearFilters() {
    document.getElementById('filter_status').value = 'all';
    document.getElementById('filter_user').value = '';
    document.getElementById('filter_date_from').value = '';
    document.getElementById('filter_date_to').value = '';
    document.getElementById('filter_anomalies').value = 'all';
    document.getElementById('sort_by').value = 'date_desc';
    
    // Afficher un indicateur de chargement
    ons.notification.toast('<?php echo $langs->trans("ClearFilters"); ?>...', {timeout: 1000});
    
    // Rediriger avec les filtres vides vers la page actuelle
    setTimeout(() => {
      // Détecter la page actuelle pour rediriger correctement
      const currentPath = window.location.pathname;
      let targetPage = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php';
      
      if (currentPath.includes('myrecords.php')) {
        targetPage = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/myrecords.php';
      }
      
      window.location.href = targetPage + '?action=list_all&filter_status=all&filter_anomalies=all&sort_by=date_desc';
    }, 500);
  }
  
  /**
   * Retourner au dashboard
   */
  function goBackToDashboard() {
    window.location.href = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php';
  }
  
  /**
   * Afficher les détails d'un enregistrement
   */
  function showRecordDetails(recordId) {
    if (!recordId) {
      ons.notification.alert('ID d\'enregistrement invalide');
      return;
    }
    
    console.log('Navigating to record details for ID:', recordId);
    window.location.href = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php?action=viewRecord&id=' + recordId;
  }
  
  
  // === BATCH VALIDATION FUNCTIONS (MVP 3.3) ===
  
  /**
   * Toggle select all checkboxes
   */
  function toggleSelectAll() {
    console.log('toggleSelectAll() called'); // Debug
    
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const recordCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
    
    console.log('Select all checked:', selectAllCheckbox.checked); // Debug
    console.log('Found checkboxes:', recordCheckboxes.length); // Debug
    
    recordCheckboxes.forEach(checkbox => {
      checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateSelection();
  }
  
  /**
   * Update selection count and enable/disable batch actions
   */
  function updateSelection() {
    console.log('updateSelection() called'); // Debug
    
    const recordCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
    let count = 0;
    
    // Count checked checkboxes using OnsenUI API
    recordCheckboxes.forEach(checkbox => {
      if (checkbox.checked) {
        count++;
      }
    });
    
    console.log('Checked count:', count); // Debug
    
    const selectedCountSpan = document.getElementById('selected-count');
    const batchButtons = document.querySelectorAll('#batch-actions-bar ons-button');
    
    // Update count display
    if (selectedCountSpan) {
      selectedCountSpan.textContent = count + ' sélectionné(s)';
    }
    
    // Enable/disable batch action buttons based on selection
    batchButtons.forEach(button => {
      // Skip the clear button
      if (button.getAttribute('onclick') === 'clearSelection()') {
        return;
      }
      
      button.disabled = (count === 0);
      if (count === 0) {
        button.style.opacity = '0.5';
        button.style.cursor = 'not-allowed';
      } else {
        button.style.opacity = '1';
        button.style.cursor = 'pointer';
      }
    });
    
    // Update select all checkbox state
    const totalCheckboxes = recordCheckboxes.length;
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    if (selectAllCheckbox) {
      if (count === totalCheckboxes && count > 0) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
      } else if (count === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
      } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
      }
    }
  }
  
  /**
   * Clear all selections
   */
  function clearSelection() {
    const recordCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
    recordCheckboxes.forEach(checkbox => {
      checkbox.checked = false;
    });
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    if (selectAllCheckbox) {
      selectAllCheckbox.checked = false;
    }
    updateSelection();
  }
  
  /**
   * Batch validate selected records
   */
  function batchValidateRecords(action) {
    const selectedCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
    const recordIds = [];
    
    selectedCheckboxes.forEach(checkbox => {
      if (checkbox.checked) {
        recordIds.push(checkbox.getAttribute('data-record-id'));
      }
    });
    
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
    const selectedCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
    const recordIds = [];
    
    selectedCheckboxes.forEach(checkbox => {
      if (checkbox.checked) {
        recordIds.push(checkbox.getAttribute('data-record-id'));
      }
    });
    
    if (recordIds.length === 0) {
      ons.notification.alert('<?php echo $langs->trans("NoRecordsSelected"); ?>');
      return;
    }
    
    document.getElementById('batch-comment-modal').show();
  }
  
  // Initialize checkbox event handlers when DOM and OnsenUI are ready
  function initializeCheckboxHandlers() {
    console.log('Initializing checkbox handlers'); // Debug
    
    // Setup select all checkbox handler
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    if (selectAllCheckbox) {
      selectAllCheckbox.addEventListener('change', function() {
        console.log('Select all checkbox changed:', this.checked); // Debug
        const recordCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
        recordCheckboxes.forEach(checkbox => {
          checkbox.checked = this.checked;
        });
        updateSelection();
      });
    }
    
    // Setup individual checkbox handlers
    const recordCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
    recordCheckboxes.forEach(checkbox => {
      checkbox.addEventListener('change', function() {
        console.log('Record checkbox changed:', this.checked, 'ID:', this.getAttribute('data-record-id')); // Debug
        updateSelection();
      });
    });
    
    // Initial state setup
    updateSelection();
  }
  
  // Try to initialize when OnsenUI is ready, fallback to DOM ready
  if (typeof ons !== 'undefined') {
    ons.ready(initializeCheckboxHandlers);
  } else {
    // Fallback: wait for OnsenUI to be loaded
    document.addEventListener('DOMContentLoaded', function() {
      function waitForOns() {
        if (typeof ons !== 'undefined') {
          ons.ready(initializeCheckboxHandlers);
        } else {
          setTimeout(waitForOns, 100);
        }
      }
      waitForOns();
    });
  }
  
  // Debug MVP 3.3
  console.log('ValidationListAll MVP 3.3 loaded - Filter interface enabled');
  console.log('Total records:', <?php echo count($records); ?>);
  console.log('Applied filters:', <?php echo json_encode($filters); ?>);
  console.log('Statistics:', <?php echo json_encode($stats); ?>);
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
    const selectedCheckboxes = document.querySelectorAll('ons-checkbox.record-checkbox');
    const recordIds = [];
    
    selectedCheckboxes.forEach(checkbox => {
      if (checkbox.checked) {
        recordIds.push(checkbox.getAttribute('data-record-id'));
      }
    });
    
    if (!comment) {
      ons.notification.alert('<?php echo $langs->trans("CommentRequired"); ?>');
      return;
    }
    
    closeBatchCommentModal();
    performBatchValidation(recordIds, action, comment);
  }
  </script>
</ons-page>