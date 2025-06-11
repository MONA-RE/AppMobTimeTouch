<?php
/**
 * Composant ValidationActions - Responsabilité unique : Actions de validation
 * 
 * MVP 3.2 : Interface graphique pour actions approve/reject avec feedback
 * Respecte le principe SRP : Seule responsabilité l'affichage des actions de validation
 */
?>

<?php if (isset($record) && !empty($record)): ?>
<div class="validation-actions-container" id="validation-actions-<?php echo $record['id']; ?>" style="padding: 15px; border-top: 1px solid #dee2e6; background-color: #f8f9fa;">
  
  <!-- Titre section actions -->
  <h4 style="margin: 0 0 15px 0; color: #495057; font-size: 16px;">
    <ons-icon icon="md-gavel" style="color: #007bff; margin-right: 8px;"></ons-icon>
    <?php echo $langs->trans('ValidationActions'); ?>
  </h4>
  
  <!-- Actions principales (MVP 3.2) -->
  <div class="validation-main-actions" style="display: flex; gap: 10px; margin-bottom: 15px;">
    
    <!-- Action Approuver -->
    <ons-button 
      id="approve-btn-<?php echo $record['id']; ?>"
      onclick="validateRecord(<?php echo $record['id']; ?>, 'approve')"
      style="background-color: #28a745; color: white; flex: 1; border-radius: 8px; padding: 12px;">
      <ons-icon icon="md-check-circle" style="margin-right: 5px;"></ons-icon>
      <?php echo $langs->trans('Approve'); ?>
    </ons-button>
    
    <!-- Action Rejeter -->
    <ons-button 
      id="reject-btn-<?php echo $record['id']; ?>"
      onclick="validateRecord(<?php echo $record['id']; ?>, 'reject')"
      style="background-color: #dc3545; color: white; flex: 1; border-radius: 8px; padding: 12px;">
      <ons-icon icon="md-cancel" style="margin-right: 5px;"></ons-icon>
      <?php echo $langs->trans('Reject'); ?>
    </ons-button>
    
    <!-- Action Partiel -->
    <ons-button 
      id="partial-btn-<?php echo $record['id']; ?>"
      onclick="validateRecord(<?php echo $record['id']; ?>, 'partial')"
      style="background-color: #ffc107; color: #212529; flex: 1; border-radius: 8px; padding: 12px;">
      <ons-icon icon="md-remove-circle" style="margin-right: 5px;"></ons-icon>
      <?php echo $langs->trans('Partial'); ?>
    </ons-button>
  </div>
  
  <!-- Action Commentaire (MVP 3.2) -->
  <div class="validation-comment-section">
    <ons-button 
      id="comment-btn-<?php echo $record['id']; ?>"
      onclick="toggleCommentSection(<?php echo $record['id']; ?>)"
      modifier="quiet"
      style="width: 100%; border: 1px solid #6c757d; border-radius: 6px; padding: 8px;">
      <ons-icon icon="md-comment" style="margin-right: 8px; color: #6c757d;"></ons-icon>
      <span style="color: #6c757d;"><?php echo $langs->trans('AddComment'); ?></span>
    </ons-button>
  </div>
  
  <!-- Section commentaire cachée (MVP 3.2) -->
  <div id="comment-section-<?php echo $record['id']; ?>" class="validation-comment-form" style="display: none; margin-top: 15px; padding: 15px; background-color: white; border-radius: 8px; border: 1px solid #dee2e6;">
    
    <label for="comment-<?php echo $record['id']; ?>" style="display: block; margin-bottom: 8px; font-weight: 500; color: #495057;">
      <?php echo $langs->trans('ValidationComment'); ?> :
    </label>
    
    <textarea 
      id="comment-<?php echo $record['id']; ?>"
      placeholder="<?php echo $langs->trans('EnterValidationComment'); ?>"
      rows="3"
      style="width: 100%; border: 1px solid #ced4da; border-radius: 6px; padding: 10px; font-family: inherit; resize: vertical;">
    </textarea>
    
    <!-- Actions commentaire -->
    <div class="comment-actions" style="display: flex; justify-content: space-between; margin-top: 10px;">
      <ons-button 
        onclick="hideCommentSection(<?php echo $record['id']; ?>)" 
        modifier="quiet"
        style="color: #6c757d;">
        <ons-icon icon="md-close" style="margin-right: 5px;"></ons-icon>
        <?php echo $langs->trans('Cancel'); ?>
      </ons-button>
      
      <div style="display: flex; gap: 8px;">
        <ons-button 
          onclick="validateWithComment(<?php echo $record['id']; ?>, 'approve')"
          style="background-color: #28a745; color: white; border-radius: 6px;">
          <ons-icon icon="md-check" style="margin-right: 5px;"></ons-icon>
          <?php echo $langs->trans('ApproveWithComment'); ?>
        </ons-button>
        
        <ons-button 
          onclick="validateWithComment(<?php echo $record['id']; ?>, 'reject')"
          style="background-color: #dc3545; color: white; border-radius: 6px;">
          <ons-icon icon="md-close" style="margin-right: 5px;"></ons-icon>
          <?php echo $langs->trans('RejectWithComment'); ?>
        </ons-button>
      </div>
    </div>
  </div>
  
  <!-- Statut loading (MVP 3.2) -->
  <div id="validation-loading-<?php echo $record['id']; ?>" class="validation-loading-state" style="display: none; text-align: center; padding: 20px;">
    <ons-icon icon="md-autorenew" spin style="font-size: 24px; color: #007bff;"></ons-icon>
    <div style="margin-top: 10px; color: #6c757d; font-size: 14px;">
      <?php echo $langs->trans('ProcessingValidation'); ?>...
    </div>
  </div>
  
  <!-- Résultat validation (MVP 3.2) -->
  <div id="validation-result-<?php echo $record['id']; ?>" class="validation-result" style="display: none; margin-top: 15px;">
    <!-- Sera peuplé dynamiquement par JavaScript -->
  </div>
</div>

<!-- JavaScript pour ValidationActions MVP 3.2 -->
<script>
/**
 * Valider un enregistrement sans commentaire (MVP 3.2)
 */
function validateRecord(recordId, action) {
    console.log(`Validating record ${recordId} with action: ${action}`);
    
    // Vérifier que l'action est valide
    const validActions = ['approve', 'reject', 'partial'];
    if (!validActions.includes(action)) {
        ons.notification.alert('<?php echo $langs->trans("InvalidAction"); ?>');
        return;
    }
    
    // Afficher loading
    showValidationLoading(recordId);
    
    // Désactiver boutons
    disableValidationButtons(recordId);
    
    // Envoyer requête validation
    fetch('<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams({
            'action': 'validate_record',
            'record_id': recordId,
            'validation_action': action,
            'token': '<?php echo newToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideValidationLoading(recordId);
        handleValidationResponse(recordId, action, data);
    })
    .catch(error => {
        hideValidationLoading(recordId);
        enableValidationButtons(recordId);
        console.error('Validation error:', error);
        ons.notification.alert('<?php echo $langs->trans("ValidationError"); ?>: ' + error.message);
    });
}

/**
 * Valider avec commentaire (MVP 3.2)
 */
function validateWithComment(recordId, action) {
    const comment = document.getElementById(`comment-${recordId}`).value.trim();
    
    if (!comment) {
        ons.notification.alert('<?php echo $langs->trans("CommentRequired"); ?>');
        return;
    }
    
    console.log(`Validating record ${recordId} with action: ${action} and comment`);
    
    // Afficher loading
    showValidationLoading(recordId);
    
    // Désactiver boutons
    disableValidationButtons(recordId);
    
    // Envoyer requête validation avec commentaire
    fetch('<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams({
            'action': 'validate_record',
            'record_id': recordId,
            'validation_action': action,
            'comment': comment,
            'token': '<?php echo newToken(); ?>'
        })
    })
    .then(response => response.json())
    .then(data => {
        hideValidationLoading(recordId);
        handleValidationResponse(recordId, action, data);
    })
    .catch(error => {
        hideValidationLoading(recordId);
        enableValidationButtons(recordId);
        console.error('Validation with comment error:', error);
        ons.notification.alert('<?php echo $langs->trans("ValidationError"); ?>: ' + error.message);
    });
}

/**
 * Afficher/masquer section commentaire (MVP 3.2)
 */
function toggleCommentSection(recordId) {
    const section = document.getElementById(`comment-section-${recordId}`);
    const button = document.getElementById(`comment-btn-${recordId}`);
    
    if (section.style.display === 'none') {
        section.style.display = 'block';
        button.style.backgroundColor = '#e9ecef';
        document.getElementById(`comment-${recordId}`).focus();
    } else {
        section.style.display = 'none';
        button.style.backgroundColor = '';
        document.getElementById(`comment-${recordId}`).value = '';
    }
}

function hideCommentSection(recordId) {
    document.getElementById(`comment-section-${recordId}`).style.display = 'none';
    document.getElementById(`comment-btn-${recordId}`).style.backgroundColor = '';
    document.getElementById(`comment-${recordId}`).value = '';
}

/**
 * Gestion état loading validation (MVP 3.2)
 */
function showValidationLoading(recordId) {
    document.getElementById(`validation-loading-${recordId}`).style.display = 'block';
    document.getElementById(`validation-result-${recordId}`).style.display = 'none';
    hideCommentSection(recordId);
}

function hideValidationLoading(recordId) {
    document.getElementById(`validation-loading-${recordId}`).style.display = 'none';
}

/**
 * Gestion état boutons validation (MVP 3.2)
 */
function disableValidationButtons(recordId) {
    const buttons = ['approve-btn', 'reject-btn', 'partial-btn', 'comment-btn'];
    buttons.forEach(btnId => {
        const btn = document.getElementById(`${btnId}-${recordId}`);
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.pointerEvents = 'none';
        }
    });
}

function enableValidationButtons(recordId) {
    const buttons = ['approve-btn', 'reject-btn', 'partial-btn', 'comment-btn'];
    buttons.forEach(btnId => {
        const btn = document.getElementById(`${btnId}-${recordId}`);
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
        }
    });
}

/**
 * Traitement réponse validation (MVP 3.2)
 */
function handleValidationResponse(recordId, action, data) {
    const resultDiv = document.getElementById(`validation-result-${recordId}`);
    
    if (data.error === 0) {
        // Succès
        resultDiv.innerHTML = `
            <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; border: 1px solid #c3e6cb;">
                <ons-icon icon="md-check-circle" style="color: #28a745; margin-right: 8px;"></ons-icon>
                <strong><?php echo $langs->trans("ValidationCompleted"); ?></strong><br>
                ${data.messages ? data.messages[0] : '<?php echo $langs->trans("RecordValidated"); ?>'}
            </div>
        `;
        
        // Toast succès
        ons.notification.toast(data.messages ? data.messages[0] : '<?php echo $langs->trans("ValidationCompleted"); ?>', {
            timeout: 3000
        });
        
        // Actualiser la page après 2 secondes
        setTimeout(() => {
            location.reload();
        }, 2000);
        
    } else {
        // Erreur
        resultDiv.innerHTML = `
            <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; border: 1px solid #f5c6cb;">
                <ons-icon icon="md-error" style="color: #dc3545; margin-right: 8px;"></ons-icon>
                <strong><?php echo $langs->trans("ValidationFailed"); ?></strong><br>
                ${data.errors ? data.errors[0] : '<?php echo $langs->trans("UnknownError"); ?>'}
            </div>
        `;
        
        // Réactiver boutons
        enableValidationButtons(recordId);
    }
    
    resultDiv.style.display = 'block';
}

// Debug MVP 3.2
console.log('ValidationActions component loaded for record <?php echo $record['id'] ?? 'unknown'; ?>');
</script>

<?php else: ?>
<!-- Erreur : pas d'enregistrement fourni -->
<div style="padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 6px; margin: 15px;">
    <ons-icon icon="md-error" style="margin-right: 8px;"></ons-icon>
    <?php echo $langs->trans('NoRecordForValidation'); ?>
</div>
<?php endif; ?>