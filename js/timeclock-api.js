/**
 * TimeClock API Integration Module
 * Handles all API calls and real-time updates for the mobile interface
 * Adapted to work with AppMobTimeTouch token management system
 */

// Global TimeClock API Manager
window.TimeclockAPI = (function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        API_BASE_URL: './api/timeclock.php',
        REFRESH_INTERVAL: 60000, // 1 minute
        LOCATION_TIMEOUT: 10000, // 10 seconds
        RETRY_ATTEMPTS: 3,
        CACHE_DURATION: 300000, // 5 minutes
        DEBUG: window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
    };
    
    // State management
    let state = {
        isOnline: navigator.onLine,
        currentStatus: null,
        updateTimer: null,
        requestQueue: [],
        cache: new Map()
    };
    
    // Utility functions
    const utils = {
        log: function(message, data = null) {
            if (CONFIG.DEBUG) {
                console.log('[TimeclockAPI]', message, data || '');
            }
        },
        
        error: function(message, error = null) {
            console.error('[TimeclockAPI]', message, error || '');
        },
        
        formatDuration: function(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return hours + 'h' + (minutes < 10 ? '0' : '') + minutes;
        },
        
        getCacheKey: function(endpoint, params = {}) {
            return endpoint + JSON.stringify(params);
        },
        
        isValidResponse: function(response) {
            return response && typeof response === 'object' && response.hasOwnProperty('success');
        },
        
        showLoading: function(message = 'Loading...') {
            if (typeof ons !== 'undefined') {
                ons.notification.toast(message, {timeout: 1000});
            }
        },
        
        showError: function(message) {
            if (typeof ons !== 'undefined') {
                ons.notification.alert(message);
            } else {
                alert(message);
            }
        },
        
        showSuccess: function(message) {
            if (typeof ons !== 'undefined') {
                ons.notification.toast(message, {timeout: 2000});
            }
        },
        
        // Get CSRF token using AppMobTimeTouch storage method
        getToken: function() {
            // Use localStorage.getItem instead of undefined localGetData
            const token = localStorage.getItem('api_token');
            utils.log('Retrieved token from localStorage', token ? 'Token found' : 'No token');
            return token;
        },
            
        // Update token in localStorage
        updateToken: function(token) {
            if (token) {
                localStorage.setItem('api_token', token);
                utils.log('Token updated in localStorage');
            }
        }
    };
    
    // HTTP Request handler with improved token management
    const http = {
        request: async function(method, endpoint, data = null, options = {}) {
            const url = CONFIG.API_BASE_URL + '?action=' + endpoint;
            
            // Get token using the same method as order-quantity.js
            const token = utils.getToken();
            
            const requestOptions = {
                method: method,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                ...options
            };
            
            let finalUrl = url;
            
            if (method === 'GET') {
                // For GET requests, add token to URL parameters
                const separator = url.includes('?') ? '&' : '?';
                if (token) {
                    finalUrl += `${separator}token=${encodeURIComponent(token)}`;
                }
                
                // Add other parameters for GET requests
                if (data && typeof data === 'object') {
                    const params = new URLSearchParams();
                    Object.keys(data).forEach(key => {
                        if (data[key] !== null && data[key] !== undefined) {
                            params.append(key, data[key]);
                        }
                    });
                    if (params.toString()) {
                        finalUrl += (finalUrl.includes('?') ? '&' : '?') + params.toString();
                    }
                }
            } else {
                // For POST requests, use form-encoded data like order-quantity.js
                requestOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                
                const formData = new URLSearchParams();
                
                // Add token first (like in order-quantity.js)
                if (token) {
                    formData.append('token', token);
                }
                
                // Add other data
                if (data && typeof data === 'object') {
                    Object.keys(data).forEach(key => {
                        if (data[key] !== null && data[key] !== undefined) {
                            formData.append(key, data[key]);
                        }
                    });
                }
                
                requestOptions.body = formData.toString();
            }
            
            utils.log(`${method} request to ${endpoint}`, {
                url: finalUrl,
                data: data,
                hasToken: !!token
            });
            
            try {
                const response = await fetch(finalUrl, requestOptions);
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                let responseData;
                
                if (contentType && contentType.includes('application/json')) {
                    responseData = await response.json();
                } else {
                    // Try to parse as JSON anyway, fallback to text
                    const text = await response.text();
                    try {
                        responseData = JSON.parse(text);
                    } catch (e) {
                    utils.error(`Non-JSON response from ${endpoint}`, {
                        status: response.status,
                        statusText: response.statusText,
                        contentType: contentType,
                            textPreview: text.substring(0, 200)
                    });
                    
                    // Try to extract meaningful error message from HTML
                    let errorMessage = `HTTP ${response.status}`;
                        if (text.includes('Access denied') || text.includes('Accès refusé')) {
                        errorMessage = 'Access denied - Check permissions';
                        } else if (text.includes('CSRF') || text.includes('token')) {
                        errorMessage = 'Security token error';
                        } else if (text.includes('Login') || text.includes('login')) {
                        errorMessage = 'Authentication required';
                    }
                    
                    throw new Error(errorMessage);
                }
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${responseData.error || responseData.message || 'Request failed'}`);
                }
                
                // Update token if provided in response (like the API does)
                if (responseData.csrf_token) {
                    utils.updateToken(responseData.csrf_token);
                }
                
                utils.log(`Response from ${endpoint}`, responseData);
                return responseData;
                
            } catch (error) {
                utils.error(`Request failed for ${endpoint}`, error);
                
                // Queue request for retry if offline
                if (!state.isOnline) {
                    this.queueRequest(method, endpoint, data);
                }
                
                throw error;
            }
        },
        
        get: function(endpoint, params = {}) {
            return this.request('GET', endpoint, params);
        },
        
        post: function(endpoint, data) {
            return this.request('POST', endpoint, data);
        },
        
        queueRequest: function(method, endpoint, data) {
            state.requestQueue.push({
                method,
                endpoint,
                data,
                timestamp: Date.now()
            });
            utils.log('Request queued for later execution', {method, endpoint});
        },
        
        processQueue: async function() {
            if (state.requestQueue.length === 0) return;
            
            utils.log('Processing queued requests', state.requestQueue.length);
            
            const queue = [...state.requestQueue];
            state.requestQueue = [];
            
            for (const request of queue) {
                try {
                    await this.request(request.method, request.endpoint, request.data);
                } catch (error) {
                    utils.error('Failed to process queued request', error);
                }
            }
        }
    };
    
    // Cache management
    const cache = {
        set: function(key, data) {
            state.cache.set(key, {
                data: data,
                timestamp: Date.now()
            });
        },
        
        get: function(key) {
            const cached = state.cache.get(key);
            if (!cached) return null;
            
            const age = Date.now() - cached.timestamp;
            if (age > CONFIG.CACHE_DURATION) {
                state.cache.delete(key);
                return null;
            }
            
            return cached.data;
        },
        
        clear: function() {
            state.cache.clear();
            utils.log('Cache cleared');
        }
    };
    
    // Geolocation handler
    const geolocation = {
        getCurrentPosition: function() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation not supported'));
                    return;
                }
                
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const result = {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy,
                            timestamp: position.timestamp
                        };
                        utils.log('Location obtained', result);
                        resolve(result);
                    },
                    (error) => {
                        let errorMessage = 'Location error';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Location permission denied';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Location unavailable';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Location timeout';
                                break;
                        }
                        utils.error(errorMessage, error);
                        reject(new Error(errorMessage));
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: CONFIG.LOCATION_TIMEOUT,
                        maximumAge: 300000 // 5 minutes
                    }
                );
            });
        },
        
        watchPosition: function(callback) {
            if (!navigator.geolocation) return null;
            
            return navigator.geolocation.watchPosition(
                callback,
                (error) => utils.error('Position watch error', error),
                {
                    enableHighAccuracy: true,
                    timeout: CONFIG.LOCATION_TIMEOUT,
                    maximumAge: 300000
                }
            );
        }
    };
    
    // API Methods
    const api = {
        // Get current timeclock status
        getStatus: async function(useCache = true) {
            const cacheKey = utils.getCacheKey('status');
            
            if (useCache) {
                const cached = cache.get(cacheKey);
                if (cached) {
                    utils.log('Using cached status', cached);
                    return cached;
                }
            }
            
            try {
                const response = await http.get('status');
                if (utils.isValidResponse(response) && response.success) {
                    cache.set(cacheKey, response.data);
                    state.currentStatus = response.data;
                    return response.data;
                } else {
                    throw new Error(response.error || 'Invalid response format');
                }
            } catch (error) {
                utils.error('Failed to get status', error);
                throw error;
            }
        },
        
        // Clock in
        clockIn: async function(data) {
            utils.log('Clock in request', data);
            
            try {
                // Get location if required and not provided
                if (window.appConfig && window.appConfig.require_location) {
                    if (!data.latitude || !data.longitude) {
                        utils.log('Getting location for clock in');
                        const position = await geolocation.getCurrentPosition();
                        data.latitude = position.latitude;
                        data.longitude = position.longitude;
                    }
                }
                
                const response = await http.post('clockin', data);
                
                if (utils.isValidResponse(response) && response.success) {
                    utils.log('Clock in successful', response.data);
                    cache.clear(); // Clear cache to force refresh
                    
                    // Update state
                    state.currentStatus = {
                        is_clocked_in: true,
                        clock_in_time: Math.floor(Date.now() / 1000),
                        active_record: response.data
                    };
                    
                    return response.data;
                } else {
                    throw new Error(response.error || 'Clock in failed');
                }
            } catch (error) {
                utils.error('Clock in failed', error);
                throw error;
            }
        },
        
        // Clock out
        clockOut: async function(data = {}) {
            utils.log('Clock out request', data);
            
            try {
                // Get location if required and not provided
                if (window.appConfig && window.appConfig.require_location) {
                    if (!data.latitude || !data.longitude) {
                        utils.log('Getting location for clock out');
                        const position = await geolocation.getCurrentPosition();
                        data.latitude = position.latitude;
                        data.longitude = position.longitude;
                    }
                }
                
                const response = await http.post('clockout', data);
                
                if (utils.isValidResponse(response) && response.success) {
                    utils.log('Clock out successful', response.data);
                    cache.clear(); // Clear cache to force refresh
                    
                    // Update state
                    state.currentStatus = {
                        is_clocked_in: false,
                        clock_in_time: null,
                        active_record: null
                    };
                    
                    return response.data;
                } else {
                    throw new Error(response.error || 'Clock out failed');
                }
            } catch (error) {
                utils.error('Clock out failed', error);
                throw error;
            }
        },
        
        // Get recent records
        getRecords: async function(params = {}) {
            const cacheKey = utils.getCacheKey('records', params);
            const cached = cache.get(cacheKey);
            
            if (cached) {
                utils.log('Using cached records');
                return cached;
            }
            
            try {
                const response = await http.get('records', params);
                
                if (utils.isValidResponse(response) && response.success) {
                    cache.set(cacheKey, response.data);
                    return response.data;
                } else {
                    throw new Error(response.error || 'Failed to get records');
                }
            } catch (error) {
                utils.error('Failed to get records', error);
                throw error;
            }
        },
        
        // Get timeclock types
        getTypes: async function() {
            const cacheKey = utils.getCacheKey('types');
            const cached = cache.get(cacheKey);
            
            if (cached) {
                return cached;
            }
            
            try {
                const response = await http.get('types');
                
                if (utils.isValidResponse(response) && response.success) {
                    cache.set(cacheKey, response.data);
                    return response.data;
                } else {
                    throw new Error(response.error || 'Failed to get types');
                }
            } catch (error) {
                utils.error('Failed to get types', error);
                throw error;
            }
        },
        
        // Get today's summary
        getTodaySummary: async function() {
            const cacheKey = utils.getCacheKey('today');
            const cached = cache.get(cacheKey);
            
            if (cached) {
                return cached;
            }
            
            try {
                const response = await http.get('today');
                
                if (utils.isValidResponse(response) && response.success) {
                    cache.set(cacheKey, response.data);
                    return response.data;
                } else {
                    throw new Error(response.error || 'Failed to get today summary');
                }
            } catch (error) {
                utils.error('Failed to get today summary', error);
                throw error;
            }
        },
        
        // Get weekly summary
        getWeeklySummary: async function() {
            const cacheKey = utils.getCacheKey('weekly');
            const cached = cache.get(cacheKey);
            
            if (cached) {
                return cached;
            }
            
            try {
                const response = await http.get('weekly');
                
                if (utils.isValidResponse(response) && response.success) {
                    cache.set(cacheKey, response.data);
                    return response.data;
                } else {
                    throw new Error(response.error || 'Failed to get weekly summary');
                }
            } catch (error) {
                utils.error('Failed to get weekly summary', error);
                throw error;
            }
        }
    };
    
    // Real-time updates
    const realtime = {
        start: function() {
            if (state.updateTimer) {
                clearInterval(state.updateTimer);
            }
            
            state.updateTimer = setInterval(async () => {
                try {
                    if (state.isOnline) {
                        await this.updateStatus();
                    }
                } catch (error) {
                    utils.error('Real-time update failed', error);
                }
            }, CONFIG.REFRESH_INTERVAL);
            
            utils.log('Real-time updates started');
        },
        
        stop: function() {
            if (state.updateTimer) {
                clearInterval(state.updateTimer);
                state.updateTimer = null;
            }
            utils.log('Real-time updates stopped');
        },
        
        updateStatus: async function() {
            try {
                const status = await api.getStatus(false); // Force fresh data
                
                if (status && state.currentStatus) {
                    // Check if status changed
                    if (status.is_clocked_in !== state.currentStatus.is_clocked_in) {
                        utils.log('Status changed, refreshing page');
                        // Status changed significantly, refresh the page
                        window.location.reload();
                    } else if (status.is_clocked_in) {
                        // Update duration display
                        this.updateDurationDisplay(status);
                    }
                }
                
                state.currentStatus = status;
                
            } catch (error) {
                utils.error('Status update failed', error);
            }
        },
        
        updateDurationDisplay: function(status) {
            if (!status.is_clocked_in || !status.clock_in_time) return;
            
            const now = Math.floor(Date.now() / 1000);
            const duration = now - status.clock_in_time;
            const durationText = utils.formatDuration(duration);
            
            const durationElement = document.getElementById('current-duration');
            if (durationElement) {
                durationElement.textContent = durationText;
            }
        }
    };
    
    // Network status monitoring
    const network = {
        init: function() {
            window.addEventListener('online', this.handleOnline.bind(this));
            window.addEventListener('offline', this.handleOffline.bind(this));
            
            // Initial check
            this.updateStatus();
        },
        
        handleOnline: function() {
            utils.log('Network online');
            state.isOnline = true;
            this.hideOfflineIndicator();
            
            // Process queued requests
            setTimeout(() => {
                http.processQueue();
            }, 1000);
        },
        
        handleOffline: function() {
            utils.log('Network offline');
            state.isOnline = false;
            this.showOfflineIndicator();
        },
        
        updateStatus: function() {
            state.isOnline = navigator.onLine;
            if (!state.isOnline) {
                this.showOfflineIndicator();
            }
        },
        
        showOfflineIndicator: function() {
            if (document.querySelector('.offline-indicator')) return;
            
            const indicator = document.createElement('div');
            indicator.className = 'offline-indicator';
            indicator.style.cssText = `
                position: fixed; 
                top: 0; 
                left: 0; 
                right: 0; 
                background: #f44336; 
                color: white; 
                text-align: center; 
                padding: 8px; 
                font-size: 12px; 
                z-index: 10000;
                animation: slideDown 0.3s ease;
            `;
            indicator.textContent = 'Offline Mode - Actions will be synced when online';
            document.body.insertBefore(indicator, document.body.firstChild);
        },
        
        hideOfflineIndicator: function() {
            const indicator = document.querySelector('.offline-indicator');
            if (indicator) {
                indicator.style.animation = 'slideUp 0.3s ease';
                setTimeout(() => {
                    if (indicator.parentNode) {
                        indicator.parentNode.removeChild(indicator);
                    }
                }, 300);
            }
        }
    };
    
    // Enhanced clock in/out functions for the UI
    const ui = {
        clockIn: async function(formData) {
            try {
                utils.showLoading('Clocking in...');
                
                const data = {
                    timeclock_type_id: formData.timeclock_type_id || 1,
                    location: formData.location || '',
                    note: formData.note || ''
                };
                
                // Add coordinates if available
                if (formData.latitude && formData.longitude) {
                    data.latitude = parseFloat(formData.latitude);
                    data.longitude = parseFloat(formData.longitude);
                }
                
                const result = await api.clockIn(data);
                
                utils.showSuccess('Successfully clocked in!');
                
                // Start real-time updates
                realtime.start();
                
                // Refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                
                return result;
                
            } catch (error) {
                utils.showError('Clock in failed: ' + error.message);
                throw error;
            }
        },
        
        clockOut: async function(formData) {
            try {
                utils.showLoading('Clocking out...');
                
                const data = {
                    location: formData.location || '',
                    note: formData.note || ''
                };
                
                // Add coordinates if available
                if (formData.latitude && formData.longitude) {
                    data.latitude = parseFloat(formData.latitude);
                    data.longitude = parseFloat(formData.longitude);
                }
                
                const result = await api.clockOut(data);
                
                utils.showSuccess('Successfully clocked out!');
                
                // Stop real-time updates
                realtime.stop();
                
                // Refresh page after short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                
                return result;
                
            } catch (error) {
                utils.showError('Clock out failed: ' + error.message);
                throw error;
            }
        },
        
        refreshData: async function() {
            try {
                utils.showLoading('Refreshing...');
                cache.clear();
                
                // Get fresh status
                await api.getStatus(false);
                
                // Refresh page
                window.location.reload();
                
            } catch (error) {
                utils.showError('Refresh failed: ' + error.message);
            }
        }
    };
    
    // Initialization
    const init = function(options = {}) {
        utils.log('Initializing TimeclockAPI');
        
        // Merge options with config
        Object.assign(CONFIG, options);
        
        // Initialize network monitoring
        network.init();
        
        // Set initial token if provided
        if (options.apiToken) {
            utils.updateToken(options.apiToken);
        }
        
        // Try to get token from localStorage
        const token = utils.getToken();
        if (token) {
            utils.log('Token found in localStorage');
        } else {
            utils.log('Warning: No token found in localStorage');
        }
        
        // Start real-time updates if user is clocked in
        if (window.appConfig && window.appConfig.is_clocked_in) {
            realtime.start();
        }
        
        // Add CSS animations
        if (!document.querySelector('#timeclock-animations')) {
            const style = document.createElement('style');
            style.id = 'timeclock-animations';
            style.textContent = `
                @keyframes slideDown {
                    from { transform: translateY(-100%); }
                    to { transform: translateY(0); }
                }
                @keyframes slideUp {
                    from { transform: translateY(0); }
                    to { transform: translateY(-100%); }
                }
            `;
            document.head.appendChild(style);
        }
        
        utils.log('TimeclockAPI initialized successfully');
    };
    
    // Public API
    return {
        init: init,
        api: api,
        ui: ui,
        geolocation: geolocation,
        utils: utils,
        cache: cache,
        realtime: realtime,
        
        // Direct access to main functions
        getStatus: api.getStatus,
        clockIn: ui.clockIn,
        clockOut: ui.clockOut,
        refreshData: ui.refreshData,
        
        // State information
        isOnline: () => state.isOnline,
        getCurrentStatus: () => state.currentStatus,
        getToken: utils.getToken,
        updateToken: utils.updateToken,
        
        // Debug helpers
        debug: {
            getState: () => state,
            getConfig: () => CONFIG,
            clearCache: cache.clear,
            testLocation: geolocation.getCurrentPosition,
            testToken: utils.getToken
        }
    };
})();

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with global config if available
    const initOptions = {};
    
    if (typeof window.appConfig !== 'undefined') {
        initOptions.apiToken = window.appConfig.api_token;
        initOptions.DEBUG = window.appConfig.debug || false;
    }
    
    window.TimeclockAPI.init(initOptions);
});

// Enhanced global functions for the UI (compatible with existing localStorage functions)
window.submitClockIn = async function() {
    const form = document.getElementById('clockInForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    try {
        await window.TimeclockAPI.clockIn(data);
        
        // Hide modal
        const modal = document.getElementById('clockInModal');
        if (modal && modal.hide) {
            modal.hide();
        }
        
    } catch (error) {
        console.error('Clock in failed:', error);
    }
};

window.submitClockOut = async function() {
    const form = document.getElementById('clockOutForm');
    if (!form) return;
    
    const formData = new FormData(form);
    const data = {};
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    try {
        await window.TimeclockAPI.clockOut(data);
        
        // Hide modal
        const modal = document.getElementById('clockOutModal');
        if (modal && modal.hide) {
            modal.hide();
        }
        
    } catch (error) {
        console.error('Clock out failed:', error);
    }
};

window.refreshTimeclockData = function() {
    window.TimeclockAPI.refreshData();
};

// Export for modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.TimeclockAPI;
}