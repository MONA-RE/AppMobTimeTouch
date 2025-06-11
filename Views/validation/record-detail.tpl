<?php
/**
 * Page détail enregistrement pour validation - MVP 3.2
 * 
 * Responsabilité unique : Affichage détaillé d'un enregistrement à valider (SRP)
 * Interface graphique testable pour actions de validation
 */
?>

<ons-page id="ValidationRecordDetail">
  <!-- TopBar spécifique détail -->
  <ons-toolbar modifier="material">
    <div class="left">
      <ons-back-button animation="fade" onclick="history.back()"></ons-back-button>
      <span>
        <?php echo $langs->trans("RecordDetails"); ?>
      </span>
    </div>
    <div class="right">
      <ons-toolbar-button onclick="refreshRecordDetail()" title="<?php echo $langs->trans('Refresh'); ?>">
        <ons-icon icon="md-refresh"></ons-icon>
      </ons-toolbar-button>
    </div>
  </ons-toolbar>

  <!-- Messages Component -->
  <?php include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/components/Messages.tpl'; ?>

  <?php if (isset($record) && !empty($record)): ?>
  
  <!-- Informations utilisateur -->
  <div style="padding: 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <h3 style="margin: 0; color: #495057; display: flex; align-items: center;">
          <ons-icon icon="md-person" style="color: #007bff; margin-right: 8px; font-size: 20px;"></ons-icon>
          <?php echo dol_escape_htmltag($record['user_name']); ?>
        </h3>
      </div>
      <div class="content" style="padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <span style="font-weight: 500; color: #495057;"><?php echo $langs->trans('Date'); ?> :</span>
          <span style="color: #6c757d;"><?php echo dol_print_date($record['clock_in_time'], 'day'); ?></span>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <span style="font-weight: 500; color: #495057;"><?php echo $langs->trans('Type'); ?> :</span>
          <span style="background-color: <?php echo $record['type']['color']; ?>; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
            <?php echo dol_escape_htmltag($record['type']['label']); ?>
          </span>
        </div>
      </div>
    </ons-card>
  </div>

  <!-- Horaires de travail -->
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #e3f2fd; border-bottom: 1px solid #90caf9;">
        <h4 style="margin: 0; color: #1565c0;">
          <ons-icon icon="md-schedule" style="color: #2196f3; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('WorkHours'); ?>
        </h4>
      </div>
      <div class="content" style="padding: 20px;">
        
        <!-- Clock In -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
          <div>
            <div style="font-weight: 500; color: #28a745; margin-bottom: 3px;">
              <ons-icon icon="md-login" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('ClockIn'); ?>
            </div>
            <div style="font-size: 18px; font-weight: bold; color: #495057;">
              <?php echo dol_print_date($record['clock_in_time'], 'hour'); ?>
            </div>
            <?php if (!empty($record['location_in'])): ?>
            <div style="font-size: 12px; color: #6c757d; margin-top: 3px;">
              <ons-icon icon="md-place" style="font-size: 12px; margin-right: 3px;"></ons-icon>
              <?php echo dol_escape_htmltag($record['location_in']); ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Clock Out -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;">
          <div>
            <div style="font-weight: 500; color: #dc3545; margin-bottom: 3px;">
              <ons-icon icon="md-logout" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('ClockOut'); ?>
            </div>
            <?php if (!empty($record['clock_out_time'])): ?>
            <div style="font-size: 18px; font-weight: bold; color: #495057;">
              <?php echo dol_print_date($record['clock_out_time'], 'hour'); ?>
            </div>
            <?php if (!empty($record['location_out'])): ?>
            <div style="font-size: 12px; color: #6c757d; margin-top: 3px;">
              <ons-icon icon="md-place" style="font-size: 12px; margin-right: 3px;"></ons-icon>
              <?php echo dol_escape_htmltag($record['location_out']); ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <div style="font-size: 14px; color: #ffc107; font-style: italic;">
              <ons-icon icon="md-warning" style="margin-right: 5px;"></ons-icon>
              <?php echo $langs->trans('NotClockedOut'); ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        
        <!-- Durées -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
          <span style="font-weight: 500; color: #495057;"><?php echo $langs->trans('WorkDuration'); ?> :</span>
          <span style="color: #007bff; font-weight: bold;">
            <?php echo TimeHelper::convertSecondsToReadableTime($record['work_duration'] * 60); ?>
          </span>
        </div>
        
        <?php if ($record['break_duration'] > 0): ?>
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
          <span style="font-weight: 500; color: #495057;"><?php echo $langs->trans('BreakDuration'); ?> :</span>
          <span style="color: #6c757d;">
            <?php echo $record['break_duration']; ?> <?php echo $langs->trans('minutes'); ?>
          </span>
        </div>
        <?php endif; ?>
      </div>
    </ons-card>
  </div>

  <!-- Anomalies détectées -->
  <?php if (!empty($record['anomalies'])): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #fff3cd; border-bottom: 1px solid #ffeaa7;">
        <h4 style="margin: 0; color: #856404;">
          <ons-icon icon="md-warning" style="color: #ffc107; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('AnomaliesDetected'); ?>
        </h4>
      </div>
      <div class="content" style="padding: 15px;">
        <?php foreach ($record['anomalies'] as $anomaly): ?>
        <div style="display: flex; align-items: center; margin-bottom: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 6px;">
          <ons-icon 
            icon="<?php echo $anomaly['level'] === 'critical' ? 'md-error' : ($anomaly['level'] === 'warning' ? 'md-warning' : 'md-info'); ?>" 
            style="color: <?php echo $anomaly['level'] === 'critical' ? '#dc3545' : ($anomaly['level'] === 'warning' ? '#ffc107' : '#17a2b8'); ?>; margin-right: 10px;">
          </ons-icon>
          <div style="flex: 1;">
            <div style="font-weight: 500; color: #495057; margin-bottom: 2px;">
              <?php echo $langs->trans(ucfirst($anomaly['type'])); ?>
            </div>
            <div style="font-size: 14px; color: #6c757d;">
              <?php echo dol_escape_htmltag($anomaly['message']); ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </ons-card>
  </div>
  <?php endif; ?>

  <!-- Note/commentaire -->
  <?php if (!empty($record['note'])): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
        <h4 style="margin: 0; color: #495057;">
          <ons-icon icon="md-note" style="color: #6c757d; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('Note'); ?>
        </h4>
      </div>
      <div class="content" style="padding: 15px;">
        <div style="font-style: italic; color: #495057;">
          "<?php echo dol_escape_htmltag($record['note']); ?>"
        </div>
      </div>
    </ons-card>
  </div>
  <?php endif; ?>

  <!-- Statut validation actuel -->
  <?php if (!empty($record['validation_status'])): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 15px; background-color: #f0f8ff; border-bottom: 1px solid #b3d9ff;">
        <h4 style="margin: 0; color: #0d47a1;">
          <ons-icon icon="md-check-circle" style="color: #2196f3; margin-right: 8px;"></ons-icon>
          <?php echo $langs->trans('ValidationStatus'); ?>
        </h4>
      </div>
      <div class="content" style="padding: 15px;">
        <?php 
        $statusColors = [
            0 => '#6c757d', // Pending
            1 => '#28a745', // Approved 
            2 => '#dc3545', // Rejected
            3 => '#ffc107'  // Partial
        ];
        $statusColor = $statusColors[$record['validation_status']['status']] ?? '#6c757d';
        ?>
        <div style="display: flex; align-items: center; margin-bottom: 10px;">
          <div style="width: 12px; height: 12px; border-radius: 50%; background-color: <?php echo $statusColor; ?>; margin-right: 10px;"></div>
          <span style="font-weight: 500; color: <?php echo $statusColor; ?>;">
            <?php echo $langs->trans($record['validation_status']['status_label']); ?>
          </span>
        </div>
        
        <?php if (!empty($record['validation_status']['validated_by'])): ?>
        <div style="font-size: 14px; color: #6c757d; margin-bottom: 5px;">
          <?php echo $langs->trans('ValidatedBy'); ?>: 
          <?php 
          $validator = new User($db);
          $validator->fetch($record['validation_status']['validated_by']);
          echo $validator->getFullName($langs);
          ?>
        </div>
        <?php if (!empty($record['validation_status']['validated_date'])): ?>
        <div style="font-size: 14px; color: #6c757d;">
          <?php echo dol_print_date($record['validation_status']['validated_date'], 'dayhour'); ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if (!empty($record['validation_status']['comment'])): ?>
        <div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 6px; font-style: italic;">
          "<?php echo dol_escape_htmltag($record['validation_status']['comment']); ?>"
        </div>
        <?php endif; ?>
      </div>
    </ons-card>
  </div>
  <?php endif; ?>

  <!-- Actions de validation (MVP 3.2) - Seulement pour les managers -->
  <?php if (isset($isEmployeeView) && $isEmployeeView): ?>
    <!-- Vue employé : Information sur le statut seulement -->
    <div style="padding: 15px; background-color: #e9ecef; border-radius: 8px; margin: 15px;">
      <h4 style="margin: 0 0 10px 0; color: #495057;">
        <ons-icon icon="md-info" style="color: #17a2b8; margin-right: 8px;"></ons-icon>
        <?php echo $langs->trans('ValidationInformation'); ?>
      </h4>
      <p style="margin: 0; color: #6c757d; font-size: 14px;">
        <?php if ($record['validation_status']['status'] == 0): ?>
          <?php echo $langs->trans('RecordPendingValidation'); ?>
        <?php else: ?>
          <?php echo $langs->trans('RecordAlreadyValidated'); ?>
        <?php endif; ?>
      </p>
    </div>
  <?php else: ?>
    <!-- Vue manager : Actions de validation -->
    
    
    <?php if (isset($record['validation_status']['status']) && $record['validation_status']['status'] == 0): // Seulement si en attente ?>
    <?php include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/components/ValidationActions.tpl'; ?>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Espacement pour éviter que le contenu soit masqué -->
  <div style="height: 80px;"></div>

  <?php else: ?>
  <!-- Erreur : enregistrement non trouvé -->
  <div style="padding: 20px; text-align: center;">
    <ons-icon icon="md-error" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></ons-icon>
    <h3 style="color: #dc3545; margin-bottom: 10px;">
      <?php echo $langs->trans('RecordNotFound'); ?>
    </h3>
    <p style="color: #6c757d; margin-bottom: 20px;">
      <?php echo $langs->trans('RecordNotFoundDescription'); ?>
    </p>
    <ons-button onclick="history.back()" style="background-color: #007bff; color: white;">
      <ons-icon icon="md-arrow-back" style="margin-right: 5px;"></ons-icon>
      <?php echo $langs->trans('GoBack'); ?>
    </ons-button>
  </div>
  <?php endif; ?>
  
</ons-page>

<!-- JavaScript pour page détail validation -->
<script>
function refreshRecordDetail() {
    ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
    setTimeout(() => location.reload(), 500);
}

// Debug MVP 3.2
console.log('Record detail page loaded for record:', <?php echo json_encode($record['id'] ?? null); ?>);
</script>