/**
 * Main TimeClock Application
 * Manages the core application functionality, timers, and user interactions
 */

class TimeclockApp {
    constructor(config) {
        this.config = config || {};
        this.updateTimer = null;
        this.sessionTimer = null;
        this.initialized = false;
        
        this.debug('TimeclockApp initializing with config:', this.config);
        this.init();
    }
    
    /**
     * Initialize the application
     */
    init() {
        if (this.initialized) {
            this.debug('App already initialized, skipping');
            return;
        }
        
        try {
            this.initializePullRefresh();
            this.initializeTimers();
            this.initializeEventListeners();
            this.initializeNetworkMonitoring();
            this.initializeFormAutoSave();
            this.setupKeyboardShortcuts();
            this.checkPermissions();
            
            if (this.config.is_clocked_in) {
                this.startDurationTimer();
            }
            
            this.initialized = true;
            this.debug('TimeclockApp initialized successfully');
            
            // Auto-hide messages after 5 seconds
            this.autoHideMessages();
            
        } catch (error) {
            this.error('Failed to initialize TimeclockApp:', error);
        }
    }
    
    /**
     * Initialize pull-to-refresh functionality
     */
    initializePullRefresh() {
        const pullHook = document.getElementById('pull-hook');
        if (!pullHook) return;
        
        pullHook.addEventListener('changestate', (event) => {
            let message = '';
            switch (event.state) {
                case 'initial':
                    message = this.config.translations?.pullToRefresh || 'Pull to refresh';
                    break;
                case 'preaction':
                    message = this.config.translations?.release || 'Release';
                    break;
                case 'action':
                    message = this.config.translations?.loading || 'Loading...';
                    break;
            }
            pullHook.innerHTML = message;
        });
        
        pullHook.onAction = (done) => {
            setTimeout(() => {
                location.reload();
                done();
            }, 1000);
        };
        
        this.debug('Pull-to-refresh initialized');
    }
    
    /**
     * Initialize duration update timers
     */
    initializeTimers() {
        // Clean up existing timers
        this.stopAllTimers();
        
        if (this.config.is_clocked_in) {
            this.startDurationTimer();
        }
    }
    
    /**
     * Start the duration update timer
     */
    startDurationTimer() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
        }
        
        this.updateTimer = setInterval(() => {
            this.updateCurrentDuration();
        }, 60000); // Update every minute
        
        this.debug('Duration timer started');
    }
    
    /**
     * Start session timer for real-time updates
     */
    startSessionTimer() {
        if (this.sessionTimer) {
            clearInterval(this.sessionTimer);
        }
        
        const durationElement = document.getElementById('session-duration');
        if (!durationElement || !this.config.is_clocked_in || !this.config.clock_in_time) {
            return;
        }
        
        this.sessionTimer = setInterval(() => {
            const now = Math.floor(Date.now() / 1000);
            const duration = now - this.config.clock_in_time;
            const durationText = this.formatDuration(duration);
            durationElement.textContent = durationText;
        }, 1000); // Update every second
        
        this.debug('Session timer started');
    }
    
    /**
     * Stop all running timers
     */
    stopAllTimers() {
        if (this.updateTimer) {
            clearInterval(this.updateTimer);
            this.updateTimer = null;
        }
        
        if (this.sessionTimer) {
            clearInterval(this.sessionTimer);
            this.sessionTimer = null;
        }
        
        this.debug('All timers stopped');
    }
    
    /**
     * Update current duration display
     */
    updateCurrentDuration() {
        if (!this.config.is_clocked_in || !this.config.clock_in_time) {
            return;
        }
        
        const now = Math.floor(Date.now() / 1000);
        const duration = now - this.config.clock_in_time;
        const durationText = this.formatDuration(duration);
        
        const durationElement = document.getElementById('current-duration');
        if (durationElement) {
            durationElement.textContent = durationText;
        }
        
        // Update session duration in modal if visible
        const sessionDurationElement = document.getElementById('session-duration');
        if (sessionDurationElement) {
            sessionDurationElement.textContent = durationText;
        }
    }
    
    /**
     * Initialize event listeners
     */
    initializeEventListeners() {
        // Page visibility changes (for timer optimization)
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopAllTimers();
            } else if (this.config.is_clocked_in) {
                this.startDurationTimer();
                this.updateCurrentDuration(); // Update immediately
            }
        });
        
        // Online/offline events
        window.addEventListener('online', () => this.updateNetworkStatus());
        window.addEventListener('offline', () => this.updateNetworkStatus());
        
        this.debug('Event listeners initialized');
    }
    
    /**
     * Initialize network status monitoring
     */
    initializeNetworkMonitoring() {
        this.updateNetworkStatus(); // Check initial status
    }
    
    /**
     * Update network status indicator
     */
    updateNetworkStatus() {
        if (navigator.onLine) {
            // Online - clear any offline indicators
            const offlineIndicators = document.querySelectorAll('.offline-indicator');
            offlineIndicators.forEach(indicator => indicator.remove());
        } else {
            // Offline - show indicator
            if (!document.querySelector('.offline-indicator')) {
                const indicator = document.createElement('div');
                indicator.className = 'offline-indicator';
                indicator.textContent = this.config.translations?.offlineMode || 'Offline Mode';
                document.body.insertBefore(indicator, document.body.firstChild);
            }
        }
    }
    
    /**
     * Initialize form auto-save functionality
     */
    initializeFormAutoSave() {
        const forms = ['clockInForm', 'clockOutForm'];
        
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (!form) return;
            
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    this.saveFormData(formId, new FormData(form));
                });
            });
        });
        
        this.debug('Form auto-save initialized');
    }
    
    /**
     * Save form data to localStorage
     */
    saveFormData(formId, formData) {
        if (typeof(Storage) === "undefined") return;
        
        try {
            const data = {};
            for (const [key, value] of formData.entries()) {
                data[key] = value;
            }
            localStorage.setItem(`timeclock_${formId}`, JSON.stringify(data));
        } catch (error) {
            this.debug('Failed to save form data:', error);
        }
    }
    
    /**
     * Load form data from localStorage
     */
    loadFormData(formId) {
        if (typeof(Storage) === "undefined") return null;
        
        try {
            const data = localStorage.getItem(`timeclock_${formId}`);
            return data ? JSON.parse(data) : null;
        } catch (error) {
            this.debug('Failed to load form data:', error);
            return null;
        }
    }
    
    /**
     * Clear form data from localStorage
     */
    clearFormData(formId) {
        if (typeof(Storage) === "undefined") return;
        
        try {
            localStorage.removeItem(`timeclock_${formId}`);
        } catch (error) {
            this.debug('Failed to clear form data:', error);
        }
    }
    
    /**
     * Setup keyboard shortcuts for development
     */
    setupKeyboardShortcuts() {
        if (!this.isDevelopmentMode()) return;
        
        document.addEventListener('keydown', (e) => {
            // Alt + I = Clock In
            if (e.altKey && e.key === 'i') {
                e.preventDefault();
                if (!this.config.is_clocked_in && window.showClockInModal) {
                    window.showClockInModal();
                }
            }
            
            // Alt + O = Clock Out
            if (e.altKey && e.key === 'o') {
                e.preventDefault();
                if (this.config.is_clocked_in && window.showClockOutModal) {
                    window.showClockOutModal();
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
    
    /**
     * Check if we're in development mode
     */
    isDevelopmentMode() {
        return window.location.hostname === 'localhost' || 
               window.location.hostname === '127.0.0.1' ||
               window.location.hostname.includes('dev-');
    }
    
    /**
     * Check geolocation permissions
     */
    checkPermissions() {
        if (!this.config.require_location || !('permissions' in navigator)) return;
        
        navigator.permissions.query({name: 'geolocation'}).then(result => {
            if (result.state === 'denied') {
                this.showAlert(this.config.translations?.locationPermissionRequired || 'Location permission is required for this app');
            }
        }).catch(error => {
            this.debug('Permission check failed:', error);
        });
    }
    
    /**
     * Auto-hide success/error messages
     */
    autoHideMessages() {
        setTimeout(() => {
            const messages = document.querySelectorAll('.success-message, .error-message');
            messages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transition = 'opacity 0.5s';
                setTimeout(() => {
                    msg.style.display = 'none';
                }, 500);
            });
        }, 5000);
    }
    
    /**
     * Format duration in seconds to readable format
     */
    formatDuration(seconds) {
        if (!seconds || seconds < 0) return '0h00';
        
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h${minutes.toString().padStart(2, '0')}`;
    }
    
    /**
     * Show loading indicator
     */
    showLoading(message = 'Loading...') {
        if (window.ons && ons.notification) {
            ons.notification.toast(message, {timeout: 1000});
        }
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
     * Hide all modals
     */
    hideAllModals() {
        const modals = document.querySelectorAll('ons-modal');
        modals.forEach(modal => {
            if (modal.visible) {
                modal.hide();
            }
        });
    }
    
    /**
     * Debug logging
     */
    debug(...args) {
        if (this.isDevelopmentMode()) {
            console.log('[TimeclockApp]', ...args);
        }
    }
    
    /**
     * Error logging
     */
    error(...args) {
        console.error('[TimeclockApp]', ...args);
    }
    
    /**
     * Performance monitoring
     */
    monitorPerformance() {
        if (!this.isDevelopmentMode()) return;
        
        // Monitor page load time
        if (performance && performance.now) {
            console.log('Page load time:', (performance.now() / 1000).toFixed(2) + 's');
        }
        
        // Monitor memory usage
        if ('memory' in performance) {
            setInterval(() => {
                const mem = performance.memory;
                if (mem.usedJSHeapSize > 50 * 1024 * 1024) { // 50MB threshold
                    console.warn('High memory usage detected:', (mem.usedJSHeapSize / 1024 / 1024).toFixed(2) + 'MB');
                }
            }, 30000);
        }
    }
    
    /**
     * Cleanup resources
     */
    destroy() {
        this.stopAllTimers();
        this.clearFormData('clockInForm');
        this.clearFormData('clockOutForm');
        this.initialized = false;
        this.debug('TimeclockApp destroyed');
    }
}

// Initialize app when DOM is ready
ons.ready(() => {
    if (typeof appConfig !== 'undefined') {
        window.timeclockApp = new TimeclockApp(appConfig);
        
        // Initialize token in localStorage for timeclock-api.js
        if (appConfig && appConfig.api_token) {
            try {
                localStorage.setItem('timeclock_api_token', appConfig.api_token);
                console.log('TimeClock token initialized in localStorage');
            } catch (e) {
                console.error('Failed to store token in localStorage:', e);
            }
        }
        
        // Start performance monitoring in development
        if (window.timeclockApp.isDevelopmentMode()) {
            window.timeclockApp.monitorPerformance();
        }
    } else {
        console.warn('appConfig not found, TimeclockApp not initialized');
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TimeclockApp;
}