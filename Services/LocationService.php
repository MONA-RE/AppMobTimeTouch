<?php

/**
 * LocationService - Location validation and management
 * 
 * Handles GPS coordinates validation and location requirements
 * based on module configuration.
 */
class LocationService 
{
    private $configService;
    
    public function __construct($configService) 
    {
        $this->configService = $configService;
    }
    
    /**
     * Validate coordinates if location is required
     * @param float|string $latitude Latitude coordinate
     * @param float|string $longitude Longitude coordinate
     * @return bool True if valid or not required, false if invalid
     */
    public function validateCoordinates($latitude, $longitude) 
    {
        if (!$this->configService->getRequireLocation()) {
            return true; // Location not required
        }
        
        if (empty($latitude) || empty($longitude)) {
            return false;
        }
        
        // Basic coordinate validation
        return (
            is_numeric($latitude) && 
            is_numeric($longitude) &&
            $latitude >= -90 && $latitude <= 90 &&
            $longitude >= -180 && $longitude <= 180
        );
    }
    
    /**
     * Check if location is required for operations
     * @return bool True if location is required
     */
    public function isLocationRequired() 
    {
        return $this->configService->getRequireLocation();
    }
    
    /**
     * Validate a single coordinate value
     * @param float|string $coordinate Coordinate value
     * @param string $type Type of coordinate ('latitude' or 'longitude')
     * @return bool True if valid
     */
    public function validateSingleCoordinate($coordinate, $type = 'latitude')
    {
        if (!is_numeric($coordinate)) {
            return false;
        }
        
        $coordinate = (float) $coordinate;
        
        if ($type === 'latitude') {
            return $coordinate >= -90 && $coordinate <= 90;
        } elseif ($type === 'longitude') {
            return $coordinate >= -180 && $coordinate <= 180;
        }
        
        return false;
    }
    
    /**
     * Get validation error message for coordinates
     * @param float|string $latitude Latitude coordinate
     * @param float|string $longitude Longitude coordinate
     * @return string|null Error message or null if valid
     */
    public function getCoordinateValidationError($latitude, $longitude)
    {
        if (!$this->isLocationRequired()) {
            return null;
        }
        
        if (empty($latitude) && empty($longitude)) {
            return 'LocationRequiredForClockIn';
        }
        
        if (empty($latitude)) {
            return 'LatitudeRequired';
        }
        
        if (empty($longitude)) {
            return 'LongitudeRequired';
        }
        
        if (!$this->validateSingleCoordinate($latitude, 'latitude')) {
            return 'InvalidLatitude';
        }
        
        if (!$this->validateSingleCoordinate($longitude, 'longitude')) {
            return 'InvalidLongitude';
        }
        
        return null;
    }
    
    /**
     * Format coordinates for display
     * @param float $latitude Latitude coordinate
     * @param float $longitude Longitude coordinate
     * @param int $precision Number of decimal places
     * @return array Formatted coordinates
     */
    public function formatCoordinates($latitude, $longitude, $precision = 6)
    {
        return [
            'latitude' => round((float) $latitude, $precision),
            'longitude' => round((float) $longitude, $precision),
            'display' => round((float) $latitude, $precision) . ', ' . round((float) $longitude, $precision)
        ];
    }
    
    /**
     * Calculate approximate distance between two points (Haversine formula)
     * @param float $lat1 First point latitude
     * @param float $lon1 First point longitude
     * @param float $lat2 Second point latitude
     * @param float $lon2 Second point longitude
     * @return float Distance in meters
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters
        
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLatRad = deg2rad($lat2 - $lat1);
        $deltaLonRad = deg2rad($lon2 - $lon1);
        
        $a = sin($deltaLatRad / 2) * sin($deltaLatRad / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLonRad / 2) * sin($deltaLonRad / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Check if two locations are within a specified range
     * @param float $lat1 First point latitude
     * @param float $lon1 First point longitude
     * @param float $lat2 Second point latitude
     * @param float $lon2 Second point longitude
     * @param float $maxDistance Maximum allowed distance in meters
     * @return bool True if within range
     */
    public function isWithinRange($lat1, $lon1, $lat2, $lon2, $maxDistance = 100)
    {
        $distance = $this->calculateDistance($lat1, $lon1, $lat2, $lon2);
        return $distance <= $maxDistance;
    }
}