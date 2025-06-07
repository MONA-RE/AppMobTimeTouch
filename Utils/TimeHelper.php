<?php
/**
 * Time Helper utilities for AppMobTimeTouch module
 * 
 * Provides static methods for time conversion, formatting and parsing
 * Extracted from home.php lines 431-468 during architecture refactoring
 * 
 * @package AppMobTimeTouch
 * @subpackage Utils
 * @author Architecture refactoring
 * @version 1.0
 */

class TimeHelper 
{
    /**
     * Convert seconds to readable time format (HhMM)
     * 
     * @param int|float $seconds Number of seconds to convert
     * @return string Formatted time string (e.g., "2h30", "0h00")
     */
    public static function convertSecondsToReadableTime($seconds) 
    {
        // CORRECTION: S'assurer que $seconds est numérique
        if (!is_numeric($seconds) || $seconds <= 0) {
            self::debugLog("TimeHelper::convertSecondsToReadableTime - Invalid input: " . print_r($seconds, true));
            return '0h00';
        }
        
        $seconds = (int) $seconds; // Cast explicite en entier
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $result = sprintf('%dh%02d', $hours, $minutes);
        self::debugLog("TimeHelper::convertSecondsToReadableTime - Input: " . $seconds . ", Output: " . $result);
        
        return $result;
    }
    
    /**
     * Format duration in minutes to readable format (HhMM)
     * 
     * @param int|float $minutes Number of minutes to format
     * @return string Formatted duration string (e.g., "1h30", "0h00")
     */
    public static function formatDuration($minutes) 
    {
        // CORRECTION: S'assurer que $minutes est numérique
        if (!is_numeric($minutes) || $minutes <= 0) {
            self::debugLog("TimeHelper::formatDuration - Invalid input: " . print_r($minutes, true));
            return '0h00';
        }
        
        $minutes = (int) $minutes; // Cast explicite en entier
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        $result = sprintf('%dh%02d', $hours, $mins);
        self::debugLog("TimeHelper::formatDuration - Input: " . $minutes . ", Output: " . $result);
        
        return $result;
    }
    
    /**
     * Parse timestamp from various formats (Dolibarr database formats)
     * 
     * @param DoliDB $db Database connection for jdate conversion
     * @param mixed $rawValue Raw timestamp value from database
     * @return int|null Unix timestamp or null if parsing failed
     */
    public static function parseTimestamp($db, $rawValue) 
    {
        if (empty($rawValue)) {
            return null;
        }
        
        // Méthode 1: Vérifier si c'est déjà un timestamp Unix valide
        if (is_numeric($rawValue) && $rawValue > 946684800 && $rawValue < 4102444800) {
            // C'est déjà un timestamp Unix valide (entre 2000 et 2100)
            self::debugLog("TimeHelper::parseTimestamp - Raw value is already a valid Unix timestamp: " . $rawValue);
            return (int) $rawValue;
        }
        
        // Méthode 2: Essayer la conversion jdate pour les formats de date Dolibarr
        $timestamp = $db->jdate($rawValue);
        self::debugLog("TimeHelper::parseTimestamp - Converted with jdate: " . $timestamp);
        
        // Méthode 3: Fallback avec strtotime si jdate échoue
        if (empty($timestamp) || !is_numeric($timestamp)) {
            $timestamp = strtotime($rawValue);
            self::debugLog("TimeHelper::parseTimestamp - Fallback conversion with strtotime: " . $timestamp);
            
            // Validation du résultat strtotime
            if ($timestamp === false || $timestamp <= 0) {
                self::debugLog("TimeHelper::parseTimestamp - All conversion methods failed for value: " . $rawValue);
                return null;
            }
        }
        
        return (int) $timestamp;
    }
    
    /**
     * Get current week information
     * 
     * @return array Array with 'year' and 'week_number' keys
     */
    public static function getCurrentWeek() 
    {
        $currentDate = new DateTime();
        return [
            'year' => (int) $currentDate->format('Y'),
            'week_number' => (int) $currentDate->format('W')
        ];
    }
    
    /**
     * Get week start and end dates for given year and week number
     * 
     * @param int $year Year (e.g., 2025)
     * @param int $weekNumber Week number (1-53)
     * @return array Array with 'start_date' and 'end_date' keys (Y-m-d format)
     */
    public static function getWeekDates($year, $weekNumber) 
    {
        $dto = new DateTime();
        $dto->setISODate($year, $weekNumber);
        $startDate = $dto->format('Y-m-d');
        
        $dto->modify('+6 days');
        $endDate = $dto->format('Y-m-d');
        
        return [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
    
    /**
     * Convert duration seconds to hours with precision
     * 
     * @param int $seconds Duration in seconds
     * @param int $precision Number of decimal places (default: 2)
     * @return float Duration in hours
     */
    public static function secondsToHours($seconds, $precision = 2) 
    {
        if (!is_numeric($seconds) || $seconds < 0) {
            return 0.0;
        }
        
        return round($seconds / 3600, $precision);
    }
    
    /**
     * Convert minutes to hours with precision
     * 
     * @param int $minutes Duration in minutes
     * @param int $precision Number of decimal places (default: 2)
     * @return float Duration in hours
     */
    public static function minutesToHours($minutes, $precision = 2) 
    {
        if (!is_numeric($minutes) || $minutes < 0) {
            return 0.0;
        }
        
        return round($minutes / 60, $precision);
    }
    
    /**
     * Validate that a duration is within reasonable bounds
     * 
     * @param int $seconds Duration in seconds
     * @param int $maxHours Maximum allowed hours (default: 24)
     * @return bool True if duration is valid
     */
    public static function isValidDuration($seconds, $maxHours = 24) 
    {
        if (!is_numeric($seconds) || $seconds < 0) {
            return false;
        }
        
        $maxSeconds = $maxHours * 3600;
        return $seconds <= $maxSeconds;
    }
    
    /**
     * Format timestamp for display using Dolibarr conventions
     * 
     * @param int $timestamp Unix timestamp
     * @param string $format Format type: 'dayhour', 'day', 'hour' (Dolibarr formats)
     * @param string $timezone Timezone to use ('tzuser' for user timezone)
     * @return string Formatted date/time string
     */
    public static function formatForDisplay($timestamp, $format = 'dayhour', $timezone = 'tzuser') 
    {
        if (empty($timestamp) || !is_numeric($timestamp)) {
            return '';
        }
        
        // Use Dolibarr's formatting function if available
        if (function_exists('dol_print_date')) {
            return dol_print_date($timestamp, $format, $timezone);
        }
        
        // Fallback to PHP date formatting
        switch ($format) {
            case 'dayhour':
                return date('d/m/Y H:i', $timestamp);
            case 'day':
                return date('d/m/Y', $timestamp);
            case 'hour':
                return date('H:i', $timestamp);
            default:
                return date('Y-m-d H:i:s', $timestamp);
        }
    }
    
    /**
     * Debug logging helper - uses Dolibarr dol_syslog if available, otherwise silent
     * 
     * @param string $message Debug message to log
     * @param int $level Log level (LOG_DEBUG by default)
     */
    private static function debugLog($message, $level = LOG_DEBUG) 
    {
        if (function_exists('dol_syslog')) {
            dol_syslog($message, $level);
        }
        // Silent if dol_syslog not available (standalone testing)
    }
}