/**
 * UI Components Module
 * Handles modal interactions, form validation, and UI component functionality
 */

class UIComponents {
    constructor() {
        this.selectedTimeclockType = null;
        this.modals = {};
        this.currentModal = null;
        
        this.debug('UIComponents initialized');
        this.init();
    }
    
    /**
     * Initialize UI components
     */
    init() {
        this.initializeModals();
        this.initializeTimeclockTypeSelection();
        this.addCustomStyles();
    }
    
    /**
     * Initialize modal references
     */
    initializeModals() {
        this.modals = {
            clockIn: document.getElementById('clockInModal'),
            clockOut: document.getElementById('clockOutModal')
        };
        
        this.debug('Modals initialized:', Object.keys(this.modals));
    }
    
    /**
     * Show clock-in modal
     */
    showClockInModal() {
        const modal = this.modals.clockIn;
        if (!modal) {
            this.debug('Clock-in modal not found');
            return;
        }
        
        modal.show();
        this.currentModal = 'clockIn';
        
        // Initialize components after modal is shown
        setTimeout(() => {
            this.initializeTimeclockTypeSelection();
            this.handleLocationRequirement('in');
        }, 100);
        
        this.debug('Clock-in modal shown');
    }
    
    /**
     * Show clock-out modal
     */
    showClockOutModal() {
        const modal = this.modals.clockOut;
        if (!modal) {
            this.debug('Clock-out modal not found');
            return;
        }
        
        modal.show();
        this.currentModal = 'clockOut';
        
        // Start session timer and handle location
        setTimeout(() => {
            if (window.timeclockApp) {
                window.timeclockApp.startSessionTimer();
            }
            this.handleLocationRequirement('out');
        }, 100);
        
        this.debug('Clock-out modal shown');
    }
    
    /**
     * Hide all modals
     */
    hideAllModals() {
        Object.values(this.modals).forEach(modal => {
            if (modal && modal.visible) {
                modal.hide();
            }
        });
        this.currentModal = null;
        this.debug('All modals hidden');
    }
    
    /**
     * Handle location requirement for modals
     */
    handleLocationRequirement(type) {
        const config = window.appConfig || {};
        
        if (config.require_location && window.locationManager) {
            window.locationManager.getCurrentPosition(type).catch(error => {
                this.debug(`Location error for ${type}:`, error);
            });
        } else {
            // Update status as ready
            this.updateGPSStatusReady(type);
        }
    }
    
    /**
     * Update GPS status to ready state
     */
    updateGPSStatusReady(type) {
        const statusId = type === 'out' ? 'gps-status-out' : 'gps-status';
        const textId = type === 'out' ? 'gps-status-out-text' : 'gps-status-text';
        
        const gpsStatus = document.getElementById(statusId);
        const gpsStatusText = document.getElementById(textId);
        
        if (gpsStatus && gpsStatusText) {
            gpsStatus.classList.remove('success', 'error', 'loading');
            gpsStatus.classList.add('ready');
            
            const message = type === 'out' ? 'Ready to clock out' : 'Ready to start';
            gpsStatusText.textContent = message;
        }
    }
    
    /**
     * Initialize timeclock type selection
     */
    initializeTimeclockTypeSelection() {
        // Set default selection
        const defaultTypeId = document.getElementById('selected_timeclock_type')?.value;
        if (defaultTypeId) {
            const defaultItem = document.querySelector(`[data-type-id="${defaultTypeId}"]`);
            if (defaultItem) {
                const colorElement = defaultItem.querySelector('[style*="background-color"]');
                const typeColor = this.extractBackgroundColor(colorElement);
                const typeLabel = defaultItem.querySelector('.center div')?.textContent?.trim();
                
                this.selectTimeclockType(parseInt(defaultTypeId), typeLabel, typeColor);
            }
        }
        
        this.debug('Timeclock type selection initialized');
    }
    
    /**
     * Select a timeclock type
     */
    selectTimeclockType(typeId, typeLabel, typeColor) {
        this.debug('Selecting timeclock type:', typeId, typeLabel, typeColor);
        
        // Update hidden input
        const hiddenInput = document.getElementById('selected_timeclock_type');
        if (hiddenInput) {
            hiddenInput.value = typeId;
        }
        
        // Clear all selections
        const allItems = document.querySelectorAll('.timeclock-type-item');
        allItems.forEach(item => {
            item.classList.remove('selected');
            item.style.backgroundColor = '';
            item.style.borderLeft = '';
            
            const icon = item.querySelector('.type-selected-icon');
            if (icon) {
                icon.style.display = 'none';
            }
        });
        
        // Mark selected item
        const selectedItem = document.querySelector(`[data-type-id="${typeId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
            selectedItem.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
            selectedItem.style.borderLeft = `4px solid ${typeColor}`;
            
            const icon = selectedItem.querySelector('.type-selected-icon');
            if (icon) {
                icon.style.display = 'block';
                icon.style.color = typeColor;
            }
            
            // Visual feedback
            this.animateSelection(selectedItem);
        }
        
        // Haptic feedback
        this.provideHapticFeedback();
        
        // Audio feedback
        this.playSelectionSound();
        
        this.selectedTimeclockType = { typeId, typeLabel, typeColor };
        this.debug('Timeclock type selected:', typeId);
    }
    
    /**
     * Animate selection feedback
     */
    animateSelection(element) {
        element.style.transform = 'scale(0.98)';
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 150);
    }
    
    /**
     * Provide haptic feedback if supported
     */
    provideHapticFeedback() {
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    }
    
    /**
     * Play selection sound
     */
    playSelectionSound() {
        try {
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
            // Ignore audio errors (not critical)
        }
    }
    
    /**
     * Submit clock-in form
     */
    submitClockIn() {
        const config = window.appConfig || {};
        
        // Validate location if required
        if (config.require_location) {
            const lat = document.getElementById('clockin_latitude')?.value;
            const lon = document.getElementById('clockin_longitude')?.value;
            
            if (!lat || !lon) {
                this.showAlert('Location is required for clock in');
                return;
            }
        }
        
        // Use TimeclockAPI if available
        if (window.TimeclockAPI && window.TimeclockAPI.clockIn) {
            const form = document.getElementById('clockInForm');
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            window.TimeclockAPI.clockIn(data).then(() => {
                this.hideModal('clockIn');
                if (window.timeclockApp) {
                    window.timeclockApp.clearFormData('clockInForm');
                }
            }).catch(error => {
                this.debug('Clock in failed:', error);
                this.showAlert('Clock in failed. Please try again.');
            });
        } else {
            // Fallback to form submission
            document.getElementById('clockInForm')?.submit();
        }
    }
    
    /**
     * Submit clock-out form
     */
    submitClockOut() {
        // Use TimeclockAPI if available
        if (window.TimeclockAPI && window.TimeclockAPI.clockOut) {
            const form = document.getElementById('clockOutForm');
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            window.TimeclockAPI.clockOut(data).then(() => {
                this.hideModal('clockOut');
                if (window.timeclockApp) {
                    window.timeclockApp.clearFormData('clockOutForm');
                }
            }).catch(error => {
                this.debug('Clock out failed:', error);
                this.showAlert('Clock out failed. Please try again.');
            });
        } else {
            // Fallback to form submission
            document.getElementById('clockOutForm')?.submit();
        }
    }
    
    /**
     * Confirm clock-out with dialog
     */
    confirmClockOut() {
        const config = window.appConfig || {};
        
        // Validate location if required
        if (config.require_location) {
            const lat = document.getElementById('clockout_latitude')?.value;
            const lon = document.getElementById('clockout_longitude')?.value;
            
            if (!lat || !lon) {
                this.showAlert('Location is required for clock out');
                return;
            }
        }
        
        this.showConfirm('Are you sure you want to clock out?', (result) => {
            if (result === 1) {
                this.submitClockOut();
            }
        });
    }
    
    /**
     * Hide specific modal
     */
    hideModal(modalName) {
        const modal = this.modals[modalName];
        if (modal && modal.hide) {
            modal.hide();
        }
        this.currentModal = null;
    }
    
    /**
     * View record details
     */
    viewRecord(recordId) {
        this.debug('View record:', recordId);
        this.showToast(`Record details: ${recordId}`, 1500);
        // TODO: Navigate to record detail page
    }
    
    /**
     * Add shake animation to element
     */
    addShakeAnimation(element) {
        if (!element) return;
        
        element.classList.add('shake');
        setTimeout(() => {
            element.classList.remove('shake');
        }, 600);
    }
    
    /**
     * Validate form field
     */
    validateField(field, validationRules = {}) {
        if (!field) return false;
        
        const value = field.value.trim();
        let isValid = true;
        let message = '';
        
        // Required validation
        if (validationRules.required && !value) {
            isValid = false;
            message = 'This field is required';
        }
        
        // Email validation
        if (validationRules.email && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address';
            }
        }
        
        // Length validation
        if (validationRules.minLength && value.length < validationRules.minLength) {
            isValid = false;
            message = `Minimum ${validationRules.minLength} characters required`;
        }
        
        // Update field styling
        field.classList.remove('form-field-valid', 'form-field-invalid');
        field.classList.add(isValid ? 'form-field-valid' : 'form-field-invalid');
        
        if (!isValid) {
            this.addShakeAnimation(field);
        }
        
        return { isValid, message };
    }
    
    /**
     * Extract background color from element
     */
    extractBackgroundColor(element) {
        if (!element) return '#4CAF50';
        
        const style = element.style.backgroundColor;
        if (style) return style;
        
        // Try to get computed style
        const computed = window.getComputedStyle(element);
        return computed.backgroundColor || '#4CAF50';
    }
    
    /**
     * Add custom styles for UI components
     */
    addCustomStyles() {
        if (document.querySelector('#ui-components-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'ui-components-styles';
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
            
            .shake {
                animation: shake 0.6s ease-in-out;
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * Show alert dialog
     */
    showAlert(message) {
        if (window.ons && ons.notification) {
            ons.notification.alert(message);
        } else {
            alert(message);
        }
    }
    
    /**
     * Show confirmation dialog
     */
    showConfirm(message, callback) {
        if (window.ons && ons.notification) {
            ons.notification.confirm(message).then(callback);
        } else {
            const result = confirm(message);
            callback(result ? 1 : 0);
        }
    }
    
    /**
     * Show toast notification
     */
    showToast(message, timeout = 2000) {
        if (window.ons && ons.notification) {
            ons.notification.toast(message, { timeout });
        }
    }
    
    /**
     * Debug logging
     */
    debug(...args) {
        if (this.isDevelopmentMode()) {
            console.log('[UIComponents]', ...args);
        }
    }
    
    /**
     * Check if in development mode
     */
    isDevelopmentMode() {
        return window.location.hostname === 'localhost' || 
               window.location.hostname === '127.0.0.1' ||
               window.location.hostname.includes('dev-');
    }
}

// Global functions for backward compatibility
window.showClockInModal = function() {
    window.uiComponents?.showClockInModal();
};

window.showClockOutModal = function() {
    window.uiComponents?.showClockOutModal();
};

window.selectTimeclockType = function(typeId, typeLabel, typeColor) {
    window.uiComponents?.selectTimeclockType(typeId, typeLabel, typeColor);
};

window.submitClockIn = function() {
    window.uiComponents?.submitClockIn();
};

window.submitClockOut = function() {
    window.uiComponents?.submitClockOut();
};

window.confirmClockOut = function() {
    window.uiComponents?.confirmClockOut();
};

window.viewRecord = function(recordId) {
    window.uiComponents?.viewRecord(recordId);
};

// GPS status functions for backward compatibility
window.updateGPSStatus = function(status, message) {
    if (window.locationManager) {
        window.locationManager.updateGPSStatus('in', status, message);
    }
};

window.updateGPSStatusOut = function(status, message) {
    if (window.locationManager) {
        window.locationManager.updateGPSStatus('out', status, message);
    }
};

window.getCurrentPosition = function(type) {
    if (window.locationManager) {
        return window.locationManager.getCurrentPosition(type);
    }
};

window.getCurrentPositionOut = function() {
    if (window.locationManager) {
        return window.locationManager.getCurrentPosition('out');
    }
};

// Initialize UI components when DOM is ready
ons.ready(() => {
    window.uiComponents = new UIComponents();
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UIComponents;
}