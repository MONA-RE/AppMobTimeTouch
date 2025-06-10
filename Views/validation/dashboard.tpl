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

  <!-- Enregistrements Récents (limité pour MVP 3.1) -->
  <?php if (!empty($pending_records)): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #e3f2fd; border-bottom: 1px solid #90caf9;">
        <h4 style="margin: 0; color: #1565c0;">
          <ons-icon icon="md-schedule" style="color: #2196f3; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('TodaysRecords'); ?>
        </h4>
      </div>
      <ons-list style="margin: 0;">
        <?php foreach ($pending_records as $record): 
          $hasAnomalies = !empty($record['anomalies']);
          $priorityColor = $hasAnomalies ? '#ffc107' : '#28a745';
          
          // Utilisateur déjà enrichi dans le service
          $userName = isset($record['user']['fullname']) ? $record['user']['fullname'] : 'Utilisateur inconnu';
        ?>
        <ons-list-item tappable onclick="showRecordDetails(<?php echo $record['rowid']; ?>)">
          <div class="left">
            <div style="width: 6px; height: 40px; background-color: <?php echo $priorityColor; ?>; border-radius: 3px;"></div>
          </div>
          <div class="center">
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
                // Déterminer le statut et la couleur selon le status du record
                $recordStatus = (int)$record['status'];
                if ($recordStatus == 2) { // STATUS_IN_PROGRESS
                  $statusLabel = $langs->trans('InProgress');
                  $statusColor = '#007bff'; // Bleu pour en cours
                  $statusIcon = 'md-play-circle';
                } elseif ($recordStatus == 3) { // STATUS_COMPLETED  
                  $statusLabel = $langs->trans('PendingValidation');
                  $statusColor = '#28a745'; // Vert pour validation
                  $statusIcon = 'md-schedule';
                } else {
                  $statusLabel = $langs->trans('Unknown');
                  $statusColor = '#6c757d'; // Gris pour inconnu
                  $statusIcon = 'md-help';
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
   * Aller vers liste complète (MVP 3.2+)
   */
  function gotoFullList() {
    ons.notification.alert('<?php echo $langs->trans("FeatureComingInMVP32"); ?>');
  }
  
  /**
   * Afficher détails d'un enregistrement (MVP 3.2+)
   */
  function showRecordDetails(recordId) {
    ons.notification.alert('<?php echo $langs->trans("RecordDetails"); ?> #' + recordId + '\n<?php echo $langs->trans("FeatureComingInMVP32"); ?>');
  }
  
  // Debug MVP 3.1
  console.log('ValidationDashboard MVP 3.1 loaded');
  console.log('Stats:', <?php echo json_encode($stats); ?>);
  console.log('Pending records:', <?php echo count($pending_records); ?>);
  console.log('Notifications:', <?php echo count($notifications); ?>);
  </script>
</ons-page>