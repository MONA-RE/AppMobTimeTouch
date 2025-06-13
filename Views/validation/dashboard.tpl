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
   * Aller vers liste complète avec filtre du jour par défaut
   */
  function gotoFullList() {
    // Show loading message
    ons.notification.toast('<?php echo $langs->trans("LoadingTodaysRecords"); ?>...', {timeout: 1000});
    
    // Navigate to dedicated list page with today's filter
    const today = new Date().toISOString().split('T')[0]; // Format YYYY-MM-DD
    setTimeout(() => {
      window.location.href = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php?action=list_all&filter_date_from=' + today + '&filter_date_to=' + today;
    }, 500);
  }
  
  
  // Debug MVP 3.1
  console.log('ValidationDashboard MVP 3.1 loaded - Statistics only');
  console.log('Stats:', <?php echo json_encode($stats); ?>);
  console.log('Notifications:', <?php echo count($notifications); ?>);
  </script>

</ons-page>