<?php
/**
 * Liste complète avec filtres - MVP 3.3
 * 
 * Responsabilité unique : Affichage liste filtrée des enregistrements (SRP)
 * MVP 3.3 : Interface de gestion complète avec filtres avancés
 */
?>

<ons-page id="ValidationListAll">
  <!-- TopBar avec retour -->
  <ons-toolbar>
    <div class="left">
      <ons-toolbar-button onclick="goBackToDashboard()">
        <ons-icon icon="md-arrow-back"></ons-icon>
      </ons-toolbar-button>
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
      <div class="content" style="padding: 15px;">
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

  <!-- Liste des enregistrements -->
  <?php if (!empty($records)): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #e3f2fd; border-bottom: 1px solid #90caf9;">
        <h4 style="margin: 0; color: #1565c0;">
          <ons-icon icon="md-list" style="color: #2196f3; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('ValidationRecords'); ?> (<?php echo count($records); ?>)
        </h4>
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
            <div style="width: 6px; height: 40px; background-color: <?php echo $priorityColor; ?>; border-radius: 3px;"></div>
          </div>
          <div class="center" onclick="showRecordDetails(<?php echo $record['rowid']; ?>)" style="cursor: pointer;">
            <div style="font-weight: 500; margin-bottom: 8px; padding: 2px 6px;">
              <?php echo dol_escape_htmltag($userName); ?>
            </div>
            <div style="font-size: 14px; color: #6c757d; margin-bottom: 8px; padding: 2px 6px;">
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
    
    // Rediriger avec les nouveaux filtres
    setTimeout(() => {
      window.location.href = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php?' + params.toString();
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
    
    // Appliquer les filtres vides
    applyFilters();
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
  
  // Debug MVP 3.3
  console.log('ValidationListAll MVP 3.3 loaded - Filter interface enabled');
  console.log('Total records:', <?php echo count($records); ?>);
  console.log('Applied filters:', <?php echo json_encode($filters); ?>);
  console.log('Statistics:', <?php echo json_encode($stats); ?>);
  </script>
</ons-page>