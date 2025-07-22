<?php
/**
 * Template principal refactorisé avec architecture modulaire SOLID
 * 
 * Respecte les principes SOLID :
 * - SRP : Chaque composant a une responsabilité unique
 * - OCP : Extensible via de nouveaux composants
 * - ISP : Interfaces spécialisées par composant
 * - DIP : Dépendances injectées via variables partagées
 * 
 * Note: Plus de balise ons-page ici car inclus dans home.php structure
 */
?>

<!-- Pull to refresh -->
<ons-pull-hook id="pull-hook">
  <?php echo $langs->trans("PullToRefresh"); ?>
</ons-pull-hook>

<!-- Messages Component (SRP: Affichage messages) -->
<?php include 'Views/components/Messages.tpl'; ?>

<!-- Status Card Component (SRP: Statut timeclock) -->
<?php include 'Views/components/StatusCard.tpl'; ?>

<!-- Summary Card Component (SRP: Résumé journalier) -->
<?php include 'Views/components/SummaryCard.tpl'; ?>

<!-- Weekly Summary Component (SRP: Résumé hebdomadaire) - TK2507-0344 MVP 2: Contrôle d'affichage -->
<?php if (getDolGlobalString('APPMOBTIMETOUCH_SHOW_WEEK_SUMMARY', '1') == '1'): ?>
    <?php include 'Views/components/WeeklySummary.tpl'; ?>
<?php endif; ?>

<!-- Recent Records Component (SRP: Liste enregistrements) -->
<?php include 'Views/components/RecordsList.tpl'; ?>

  <!-- Modal Components (ISP: Interfaces spécialisées) -->
  <?php include 'Views/components/ClockInModal.tpl'; ?>
  <?php include 'Views/components/ClockOutModal.tpl'; ?>
  
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
    
    function showClockInModal() {
        const modal = document.getElementById('clockInModal');
        modal.show();
        
        // Initialiser la sélection des types
        setTimeout(function() {
            initializeTimeclockTypeSelection();
        }, 100);
        
        // Get GPS location if required
        if (appConfig && appConfig.require_location) {
            getCurrentPosition('in');
        } else {
            // Mettre à jour le statut GPS comme "prêt"
            updateGPSStatus('ready', '<?php echo $langs->trans("ReadyToStart"); ?>');
        }
    }
    
// Show Clock Out Modal  
function showClockOutModal() {
        const modal = document.getElementById('clockOutModal');
        modal.show();
        
        // Démarrer le timer de session
        setTimeout(function() {
            startSessionTimer();
        }, 100);
        
        // Get GPS location if required
        if (appConfig && appConfig.require_location) {
            getCurrentPositionOut();
        } else {
            updateGPSStatusOut('ready', '<?php echo $langs->trans("ReadyToClockOut"); ?>');
        }
    }



    function getCurrentPosition(type) {
        const statusElement = document.getElementById('gps-status' + (type === 'out' ? '-out' : ''));
        
        updateGPSStatus('loading', '<?php echo $langs->trans("GettingLocation"); ?>...');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lon = position.coords.longitude;
                    var accuracy = Math.round(position.coords.accuracy);
                    
                    document.getElementById('clock' + type + '_latitude').value = lat;
                    document.getElementById('clock' + type + '_longitude').value = lon;
                    
                    updateGPSStatus('success', '<?php echo $langs->trans("LocationFound"); ?> (±' + accuracy + 'm)');
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
                    updateGPSStatus('error', errorMsg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        } else {
            updateGPSStatus('error', '<?php echo $langs->trans("LocationNotSupported"); ?>');
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
    
    // View record function - Adaptation MVP 3.2 pour employés
    function viewRecord(recordId) {
      console.log('View record:', recordId);
      
      if (!recordId) {
        ons.notification.alert('<?php echo $langs->trans("MissingRecordId"); ?>');
        return;
      }
      
      // Feedback utilisateur
      ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
      
      // Navigation vers page détail employé
      setTimeout(function() {
        window.location.href = './employee-record-detail.php?id=' + recordId;
      }, 300);
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
      
      /* Animation d'entrée pour le modal clock-out */
      #clockOutModal .modal__content {
        animation: slideInUp 0.3s ease-out;
      }
      
      @keyframes slideInUp {
        from {
          transform: translateY(100%);
          opacity: 0;
        }
        to {
          transform: translateY(0);
          opacity: 1;
        }
      }
      
      /* Styles pour les champs de saisie du clock-out */
      #clockout_location:focus, 
      #clockout_note:focus {
        outline: none;
        box-shadow: 0 0 10px rgba(244, 67, 54, 0.2);
        transform: translateY(-1px);
        transition: all 0.3s ease;
      }
      
      /* Style pour le statut GPS du clock-out */
      #gps-status-out.success {
        background: rgba(76, 175, 80, 0.1);
        border: 1px solid rgba(76, 175, 80, 0.3);
        color: #2e7d32;
      }
      
      #gps-status-out.error {
        background: rgba(244, 67, 54, 0.1);
        border: 1px solid rgba(244, 67, 54, 0.3);
        color: #c62828;
      }
      
      #gps-status-out.loading {
        background: rgba(255, 152, 0, 0.1);
        border: 1px solid rgba(255, 152, 0, 0.3);
        color: #ef6c00;
      }
      
      /* Animation pour le timer de session */
      #session-duration {
        animation: pulseGreen 2s infinite;
      }
      
      @keyframes pulseGreen {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
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
        
        #clockOutModal .modal__content {
          margin: 10px;
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

        /**
     * Sélectionner un type de pointage
     * @param {number} typeId - ID du type de pointage
     * @param {string} typeLabel - Libellé du type
     * @param {string} typeColor - Couleur du type
     */
     function selectTimeclockType(typeId, typeLabel, typeColor) {
        console.log('Selecting timeclock type:', typeId, typeLabel, typeColor);
        
        // Mettre à jour le champ caché
        const hiddenInput = document.getElementById('selected_timeclock_type');
        if (hiddenInput) {
            hiddenInput.value = typeId;
        }
        
        // Supprimer la sélection de tous les éléments
        const allItems = document.querySelectorAll('.timeclock-type-item');
        allItems.forEach(function(item) {
            item.classList.remove('selected');
            item.style.backgroundColor = '';
            item.style.borderLeft = '';
            
            // Masquer l'icône de validation
            const icon = item.querySelector('.type-selected-icon');
            if (icon) {
                icon.style.display = 'none';
            }
        });
        
        // Marquer l'élément sélectionné
        const selectedItem = document.querySelector(`[data-type-id="${typeId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
            selectedItem.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
            selectedItem.style.borderLeft = '4px solid ' + typeColor;
            
            // Afficher l'icône de validation
            const icon = selectedItem.querySelector('.type-selected-icon');
            if (icon) {
                icon.style.display = 'block';
                icon.style.color = typeColor;
            }
            
            // Animation de feedback
            selectedItem.style.transform = 'scale(0.98)';
            setTimeout(function() {
                selectedItem.style.transform = 'scale(1)';
            }, 150);
        }
        
        // Feedback visuel avec vibration (si supporté)
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
        
        // Feedback sonore léger (optionnel)
        playSelectionSound();
        
        console.log('Timeclock type selected:', typeId);
    }
    
    /**
     * Jouer un son de sélection discret
     */
     function playSelectionSound() {
        try {
            // Créer un son très court et discret
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            // Ignorer les erreurs de son (pas critique)
        }
    }
    
    /**
     * Initialiser la sélection des types de pointage
     */
     function initializeTimeclockTypeSelection() {
        console.log('Initializing timeclock type selection');
        
        // Ajouter les styles CSS pour les transitions
        if (!document.querySelector('#timeclock-type-styles')) {
            const style = document.createElement('style');
            style.id = 'timeclock-type-styles';
            style.textContent = `
                .timeclock-type-item {
                    transition: all 0.3s ease;
                    cursor: pointer;
                }
                
                .timeclock-type-item:hover {
                    background-color: rgba(76, 175, 80, 0.05) !important;
                }
                
                .timeclock-type-item.selected {
                    background-color: rgba(76, 175, 80, 0.1) !important;
                    transform: translateX(2px);
                }
                
                .timeclock-type-item:active {
                    transform: scale(0.98);
                }
                
                .type-selected-icon {
                    transition: all 0.3s ease;
                }
                
                /* Animation d'entrée pour le modal */
                #clockInModal .modal__content {
                    animation: slideInUp 0.3s ease-out;
                }
                
                @keyframes slideInUp {
                    from {
                        transform: translateY(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                /* Styles pour les champs de saisie */
                input:focus, textarea:focus {
                    outline: none;
                    box-shadow: 0 0 10px rgba(76, 175, 80, 0.2);
                    transform: translateY(-1px);
                    transition: all 0.3s ease;
                }
                
                /* Style pour le statut GPS */
                #gps-status.success {
                    background: rgba(76, 175, 80, 0.1);
                    border: 1px solid rgba(76, 175, 80, 0.3);
                    color: #2e7d32;
                }
                
                #gps-status.error {
                    background: rgba(244, 67, 54, 0.1);
                    border: 1px solid rgba(244, 67, 54, 0.3);
                    color: #c62828;
                }
                
                #gps-status.loading {
                    background: rgba(255, 152, 0, 0.1);
                    border: 1px solid rgba(255, 152, 0, 0.3);
                    color: #ef6c00;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Marquer le type par défaut comme sélectionné au chargement
        const defaultTypeId = document.getElementById('selected_timeclock_type').value;
        if (defaultTypeId) {
            const defaultItem = document.querySelector(`[data-type-id="${defaultTypeId}"]`);
            if (defaultItem) {
                // Récupérer la couleur depuis l'élément
                const colorElement = defaultItem.querySelector('[style*="background-color"]');
                const typeColor = colorElement ? colorElement.style.backgroundColor : '#4CAF50';
                const typeLabel = defaultItem.querySelector('.center div').textContent.trim();
                
                selectTimeclockType(parseInt(defaultTypeId), typeLabel, typeColor);
            }
        }
    }


    /**
     * Mettre à jour le statut GPS avec un style approprié
     */
     function updateGPSStatus(status, message) {
        const gpsStatus = document.getElementById('gps-status');
        const gpsStatusText = document.getElementById('gps-status-text');
        
        if (gpsStatus && gpsStatusText) {
            // Supprimer les classes existantes
            gpsStatus.classList.remove('success', 'error', 'loading');
            
            // Ajouter la classe appropriée
            gpsStatus.classList.add(status);
            
            // Mettre à jour le texte
            gpsStatusText.textContent = message;
            
            // Ajouter une icône appropriée
            let icon = 'md-gps-fixed';
            switch (status) {
                case 'success':
                    icon = 'md-gps-fixed';
                    break;
                case 'error':
                    icon = 'md-gps-off';
                    break;
                case 'loading':
                    icon = 'md-gps-not-fixed';
                    break;
                default:
                    icon = 'md-gps-fixed';
            }
            
            const iconElement = gpsStatus.querySelector('ons-icon');
            if (iconElement) {
                iconElement.setAttribute('icon', icon);
            }
        }
    }


        // Variable pour stocker l'option de session sélectionnée
        let selectedSessionOption = 'complete';
    




    /**
     * Démarrer le timer de session en temps réel
     */
    function startSessionTimer() {
        const durationElement = document.getElementById('session-duration');
        
        if (durationElement && appConfig && appConfig.is_clocked_in && appConfig.clock_in_time) {
            setInterval(function() {
                const now = Math.floor(Date.now() / 1000);
                const duration = now - appConfig.clock_in_time;
                const durationText = formatDuration(duration);
                durationElement.textContent = durationText;
            }, 1000); // Mise à jour chaque seconde
        }
    }


    /**
     * Confirmer le clock-out simplifié
     */
     function confirmClockOut() {
        console.log('Confirming simple clock-out');
        
        // Validation de localisation si requise
        if (appConfig && appConfig.require_location) {
            const lat = document.getElementById('clockout_latitude').value;
            const lon = document.getElementById('clockout_longitude').value;
            
            if (!lat || !lon) {
                ons.notification.alert('<?php echo $langs->trans("LocationRequiredForClockOut"); ?>');
                return;
            }
        }
        
        // Message de confirmation simple
        ons.notification.confirm({
            title: '<?php echo $langs->trans("ClockOut"); ?>',
            message: '<?php echo $langs->trans("ConfirmClockOut"); ?>',
            buttonLabels: ['<?php echo $langs->trans("Cancel"); ?>', '<?php echo $langs->trans("Confirm"); ?>']
        }).then(function(index) {
            if (index === 1) {
                // Procéder au clock-out
                submitClockOut();
            }
        });
    }

    /**
     * Mettre à jour le statut GPS pour le clock-out
     */
     function updateGPSStatusOut(status, message) {
        const gpsStatus = document.getElementById('gps-status-out');
        const gpsStatusText = document.getElementById('gps-status-out-text');
        
        if (gpsStatus && gpsStatusText) {
            // Supprimer les classes existantes
            gpsStatus.classList.remove('success', 'error', 'loading');
            
            // Ajouter la classe appropriée
            gpsStatus.classList.add(status);
            
            // Mettre à jour le texte
            gpsStatusText.textContent = message;
            
            // Ajouter une icône appropriée
            let icon = 'md-gps-fixed';
            switch (status) {
                case 'success':
                    icon = 'md-gps-fixed';
                    break;
                case 'error':
                    icon = 'md-gps-off';
                    break;
                case 'loading':
                    icon = 'md-gps-not-fixed';
                    break;
                default:
                    icon = 'md-gps-fixed';
            }
            
            const iconElement = gpsStatus.querySelector('ons-icon');
            if (iconElement) {
                iconElement.setAttribute('icon', icon);
            }
        }
    }


    /**
     * Obtenir la position GPS pour le clock-out
     */
     function getCurrentPositionOut() {
        updateGPSStatusOut('loading', '<?php echo $langs->trans("GettingLocation"); ?>...');
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lon = position.coords.longitude;
                    var accuracy = Math.round(position.coords.accuracy);
                    
                    document.getElementById('clockout_latitude').value = lat;
                    document.getElementById('clockout_longitude').value = lon;
                    
                    updateGPSStatusOut('success', '<?php echo $langs->trans("LocationFound"); ?> (±' + accuracy + 'm)');
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
                    updateGPSStatusOut('error', errorMsg);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        } else {
            updateGPSStatusOut('error', '<?php echo $langs->trans("LocationNotSupported"); ?>');
        }
    }

        /**
     * Animation de shake pour les champs requis
     */
     function addShakeAnimation() {
        if (!document.querySelector('#shake-animation')) {
            const style = document.createElement('style');
            style.id = 'shake-animation';
            style.textContent = `
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                    20%, 40%, 60%, 80% { transform: translateX(5px); }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Initialiser l'animation shake au chargement
    addShakeAnimation();

    /**
     * Go to home page (pour toolbar logo)
     * Fonction pour compatibilité avec topbar-home.tpl
     */
    function goToHome() {
        console.log('Already on home page - refreshing instead');
        
        // Si on est déjà sur la page d'accueil, on actualise
        ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
        setTimeout(function() {
            location.reload();
        }, 500);
    }
    
    /**
     * Toggle menu - Compatible avec index.php et home.php standalone
     */
    function toggleMenu() {
        console.log('Toggle hamburger menu from home template');
        
        // Chercher le splitter parent (depuis index.php)
        var sideMenu = document.getElementById('sidemenu');
        if (sideMenu) {
            console.log('Found parent sidemenu, toggling...');
            try {
                sideMenu.toggle();
                return;
            } catch (e) {
                console.error('Side menu toggle failed:', e);
            }
        }
        
        var splitter = document.getElementById('mySplitter');
        if (splitter && splitter.right) {
            console.log('Using parent splitter.right API...');
            try {
                splitter.right.toggle();
                return;
            } catch (e) {
                console.error('Splitter right toggle failed:', e);
            }
        }
        
        if (splitter) {
            console.log('Forcing parent splitter open...');
            try {
                splitter.openSide('right');
                return;
            } catch (e) {
                console.error('Force open failed:', e);
            }
        }
        
        // Fallback si pas de splitter parent
        console.log('No parent splitter found - home page standalone');
        ons.notification.toast('<?php echo $langs->trans("MenuNotAvailable"); ?>', {timeout: 1500});
    }
    
    // Exposer les fonctions globalement pour la toolbar
    window.goToHome = goToHome;
    window.toggleMenu = toggleMenu;

  </script>
</ons-page>