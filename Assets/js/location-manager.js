/**
 * Location Manager
 * Handles GPS location functionality, validation, and status updates
 */

class LocationManager {
    constructor(config = {}) {
        this.config = config;
        this.lastKnownPosition = null;
        this.watchId = null;
        this.isWatching = false;
        
        this.debug('LocationManager initialized');
    }
    
    /**
     * Get current position for clock in/out
     */
    getCurrentPosition(type = 'in') {
        return new Promise((resolve, reject) => {
            const statusElement = document.getElementById(`gps-status${type === 'out' ? '-out' : ''}`);
            
            this.updateGPSStatus(type, 'loading', this.getTranslation('gettingLocation', 'Getting location...'));
            
            if (!navigator.geolocation) {
                const error = this.getTranslation('locationNotSupported', 'Geolocation is not supported');
                this.updateGPSStatus(type, 'error', error);
                reject(new Error(error));
                return;
            }
            
            const options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 300000 // 5 minutes
            };
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.handleLocationSuccess(position, type, resolve);
                },
                (error) => {
                    this.handleLocationError(error, type, reject);
                },
                options
            );
        });
    }
    
    /**
     * Handle successful location retrieval
     */
    handleLocationSuccess(position, type, resolve) {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        const accuracy = Math.round(position.coords.accuracy);
        
        // Store last known position
        this.lastKnownPosition = {
            latitude: lat,
            longitude: lon,
            accuracy: accuracy,
            timestamp: Date.now()
        };
        
        // Update form fields
        this.updateLocationFields(type, lat, lon);
        
        // Update status
        const message = this.getTranslation('locationFound', 'Location found') + ` (±${accuracy}m)`;
        this.updateGPSStatus(type, 'success', message);
        
        this.debug(`Location acquired for ${type}:`, { lat, lon, accuracy });
        
        if (resolve) {
            resolve({
                latitude: lat,
                longitude: lon,
                accuracy: accuracy
            });
        }
    }
    
    /**
     * Handle location error
     */
    handleLocationError(error, type, reject) {
        let errorMsg = '';
        
        switch(error.code) {
            case error.PERMISSION_DENIED:
                errorMsg = this.getTranslation('locationPermissionDenied', 'Location permission denied');
                break;
            case error.POSITION_UNAVAILABLE:
                errorMsg = this.getTranslation('locationUnavailable', 'Location unavailable');
                break;
            case error.TIMEOUT:
                errorMsg = this.getTranslation('locationTimeout', 'Location request timeout');
                break;
            default:
                errorMsg = this.getTranslation('locationError', 'Location error occurred');
                break;
        }
        
        this.updateGPSStatus(type, 'error', errorMsg);
        this.debug(`Location error for ${type}:`, error);
        
        if (reject) {
            reject(new Error(errorMsg));
        }
    }
    
    /**
     * Update location input fields
     */
    updateLocationFields(type, lat, lon) {
        const latField = document.getElementById(`clock${type}_latitude`);
        const lonField = document.getElementById(`clock${type}_longitude`);
        
        if (latField) latField.value = lat;
        if (lonField) lonField.value = lon;
    }
    
    /**
     * Update GPS status display
     */
    updateGPSStatus(type, status, message) {
        const statusId = type === 'out' ? 'gps-status-out' : 'gps-status';
        const textId = type === 'out' ? 'gps-status-out-text' : 'gps-status-text';
        
        const gpsStatus = document.getElementById(statusId);
        const gpsStatusText = document.getElementById(textId);
        
        if (!gpsStatus || !gpsStatusText) return;
        
        // Remove existing status classes
        gpsStatus.classList.remove('success', 'error', 'loading');
        
        // Add new status class
        gpsStatus.classList.add(status);
        
        // Update text
        gpsStatusText.textContent = message;
        
        // Update icon
        const iconElement = gpsStatus.querySelector('ons-icon');
        if (iconElement) {
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
            }
            iconElement.setAttribute('icon', icon);
        }
        
        this.debug(`GPS status updated (${type}):`, status, message);
    }
    
    /**
     * Validate coordinates
     */
    validateCoordinates(latitude, longitude) {
        if (!this.isLocationRequired()) {
            return { valid: true, message: 'Location not required' };
        }
        
        if (!latitude || !longitude) {
            return { 
                valid: false, 
                message: this.getTranslation('locationRequired', 'Location is required') 
            };
        }
        
        const lat = parseFloat(latitude);
        const lon = parseFloat(longitude);
        
        if (isNaN(lat) || isNaN(lon)) {
            return { 
                valid: false, 
                message: this.getTranslation('invalidCoordinates', 'Invalid coordinates') 
            };
        }
        
        if (lat < -90 || lat > 90 || lon < -180 || lon > 180) {
            return { 
                valid: false, 
                message: this.getTranslation('coordinatesOutOfRange', 'Coordinates out of range') 
            };
        }
        
        return { valid: true, coordinates: { latitude: lat, longitude: lon } };
    }
    
    /**
     * Check if location is required
     */
    isLocationRequired() {
        return this.config.require_location || window.appConfig?.require_location || false;
    }
    
    /**
     * Start watching position (for continuous tracking)
     */
    startWatching() {
        if (this.isWatching || !navigator.geolocation) return;
        
        const options = {
            enableHighAccuracy: true,
            timeout: 30000,
            maximumAge: 60000 // 1 minute
        };
        
        this.watchId = navigator.geolocation.watchPosition(
            (position) => {
                this.lastKnownPosition = {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy,
                    timestamp: Date.now()
                };
                this.debug('Position updated:', this.lastKnownPosition);
            },
            (error) => {
                this.debug('Watch position error:', error);
            },
            options
        );
        
        this.isWatching = true;
        this.debug('Started watching position');
    }
    
    /**
     * Stop watching position
     */
    stopWatching() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
            this.isWatching = false;
            this.debug('Stopped watching position');
        }
    }
    
    /**
     * Get last known position
     */
    getLastKnownPosition() {
        return this.lastKnownPosition;
    }
    
    /**
     * Calculate distance between two points (in meters)
     */
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // Earth's radius in meters
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;
        
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        
        return R * c; // Distance in meters
    }
    
    /**
     * Check if user is in allowed location
     */
    isInAllowedLocation(currentLat, currentLon, allowedLocations = []) {
        if (!allowedLocations.length) return true; // No restrictions
        
        for (const location of allowedLocations) {
            const distance = this.calculateDistance(
                currentLat, currentLon,
                location.latitude, location.longitude
            );
            
            if (distance <= (location.radius || 100)) { // Default 100m radius
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get address from coordinates (reverse geocoding)
     */
    async getAddressFromCoordinates(lat, lon) {
        try {
            // Note: This would require a geocoding service API
            // For now, just return coordinates as a formatted string
            return `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
        } catch (error) {
            this.debug('Reverse geocoding failed:', error);
            return `${lat.toFixed(6)}, ${lon.toFixed(6)}`;
        }
    }
    
    /**
     * Request location permission
     */
    async requestLocationPermission() {
        if (!('permissions' in navigator)) {
            return 'unsupported';
        }
        
        try {
            const result = await navigator.permissions.query({name: 'geolocation'});
            
            if (result.state === 'granted') {
                return 'granted';
            } else if (result.state === 'denied') {
                return 'denied';
            } else {
                // Try to trigger permission request
                await this.getCurrentPosition();
                return 'granted';
            }
        } catch (error) {
            this.debug('Permission request failed:', error);
            return 'denied';
        }
    }
    
    /**
     * Get translation or fallback text
     */
    getTranslation(key, fallback) {
        return this.config.translations?.[key] || 
               window.appConfig?.translations?.[key] || 
               fallback;
    }
    
    /**
     * Debug logging
     */
    debug(...args) {
        if (this.isDevelopmentMode()) {
            console.log('[LocationManager]', ...args);
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
    
    /**
     * Cleanup
     */
    destroy() {
        this.stopWatching();
        this.lastKnownPosition = null;
        this.debug('LocationManager destroyed');
    }
}

// Create global instance
window.locationManager = new LocationManager(window.appConfig || {});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LocationManager;
}