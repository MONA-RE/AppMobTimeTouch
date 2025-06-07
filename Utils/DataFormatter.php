<?php
/**
 * Data Formatter utilities for AppMobTimeTouch module
 * 
 * Provides static methods for data formatting, sanitization and preparation
 * Extracted from home.php and templates during architecture refactoring
 * 
 * @package AppMobTimeTouch
 * @subpackage Utils
 * @author Architecture refactoring
 * @version 1.0
 */

class DataFormatter 
{
    /**
     * Prepare JavaScript configuration data with proper type conversion
     * Extracted from home.php lines 415-426
     * 
     * @param array $data Raw configuration data
     * @return array Properly typed JavaScript configuration
     */
    public static function prepareJavaScriptConfig($data) 
    {
        $jsConfig = [
            'is_clocked_in' => (bool) ($data['is_clocked_in'] ?? false),
            'clock_in_time' => !empty($data['clock_in_time']) ? (int) $data['clock_in_time'] : null,
            'require_location' => (bool) ($data['require_location'] ?? false),
            'default_type_id' => (int) ($data['default_type_id'] ?? 0),
            'max_hours_per_day' => (float) ($data['max_hours_per_day'] ?? 12),
            'overtime_threshold' => (float) ($data['overtime_threshold'] ?? 8),
            'api_token' => $data['api_token'] ?? '',
            'user_id' => (int) ($data['user_id'] ?? 0),
            'version' => $data['version'] ?? '1.0'
        ];
        
        // Debug logging if available
        self::debugLog("DataFormatter::prepareJavaScriptConfig - is_clocked_in: " . 
                      ($jsConfig['is_clocked_in'] ? 'true' : 'false') . 
                      ", clock_in_time: " . $jsConfig['clock_in_time']);
        
        return $jsConfig;
    }
    
    /**
     * Sanitize input for safe HTML display
     * Uses Dolibarr's dol_escape_htmltag if available, otherwise htmlspecialchars
     * 
     * @param string|mixed $input Input to sanitize
     * @return string Sanitized output safe for HTML display
     */
    public static function sanitizeForDisplay($input) 
    {
        if ($input === null || $input === '') {
            return '';
        }
        
        // Convert to string if not already
        $input = (string) $input;
        
        // Use Dolibarr function if available
        if (function_exists('dol_escape_htmltag')) {
            return dol_escape_htmltag($input);
        }
        
        // Fallback to standard PHP function
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitize and prepare user data for display
     * 
     * @param object $user Dolibarr user object
     * @return array Sanitized user data for templates
     */
    public static function prepareUserData($user) 
    {
        if (!$user || !is_object($user)) {
            return [
                'id' => 0,
                'name' => 'Unknown',
                'firstname' => '',
                'lastname' => '',
                'login' => ''
            ];
        }
        
        return [
            'id' => (int) ($user->id ?? 0),
            'name' => self::sanitizeForDisplay($user->getFullName()),
            'firstname' => self::sanitizeForDisplay($user->firstname ?? ''),
            'lastname' => self::sanitizeForDisplay($user->lastname ?? ''),
            'login' => self::sanitizeForDisplay($user->login ?? '')
        ];
    }
    
    /**
     * Format location string for display
     * 
     * @param string $location Location name
     * @param float|null $latitude GPS latitude
     * @param float|null $longitude GPS longitude
     * @return string Formatted location string
     */
    public static function formatLocation($location, $latitude = null, $longitude = null) 
    {
        $location = self::sanitizeForDisplay($location);
        
        if (empty($location) && !empty($latitude) && !empty($longitude)) {
            return sprintf("GPS: %.6f, %.6f", $latitude, $longitude);
        }
        
        if (!empty($location) && !empty($latitude) && !empty($longitude)) {
            return $location . sprintf(" (%.6f, %.6f)", $latitude, $longitude);
        }
        
        return $location ?: '';
    }
    
    /**
     * Format timeclock type for display with color and styling
     * 
     * @param object $timeclockType TimeclockType object
     * @return array Formatted type data for templates
     */
    public static function formatTimeclockType($timeclockType) 
    {
        if (!$timeclockType || !is_object($timeclockType)) {
            return [
                'id' => 0,
                'label' => 'Unknown',
                'code' => '',
                'color' => '#999999',
                'style' => 'background-color: #999999; color: white;'
            ];
        }
        
        $color = $timeclockType->color ?? '#4CAF50';
        
        return [
            'id' => (int) ($timeclockType->id ?? 0),
            'label' => self::sanitizeForDisplay($timeclockType->label ?? ''),
            'code' => self::sanitizeForDisplay($timeclockType->code ?? ''),
            'color' => $color,
            'style' => "background-color: {$color}; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;"
        ];
    }
    
    /**
     * Prepare error and success messages for display
     * 
     * @param array $errors Array of error messages
     * @param array $messages Array of success messages
     * @return array Formatted messages for templates
     */
    public static function prepareMessages($errors = [], $messages = []) 
    {
        $formattedErrors = [];
        $formattedMessages = [];
        
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $formattedErrors[] = self::sanitizeForDisplay($error);
            }
        }
        
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $formattedMessages[] = self::sanitizeForDisplay($message);
            }
        }
        
        return [
            'errors' => $formattedErrors,
            'messages' => $formattedMessages,
            'has_errors' => !empty($formattedErrors),
            'has_messages' => !empty($formattedMessages)
        ];
    }
    
    /**
     * Format progress percentage for display
     * 
     * @param float $current Current value
     * @param float $target Target value
     * @param int $precision Decimal precision (default: 1)
     * @return array Progress data for templates
     */
    public static function formatProgress($current, $target, $precision = 1) 
    {
        if (!is_numeric($current) || !is_numeric($target) || $target <= 0) {
            return [
                'percentage' => 0,
                'display' => '0%',
                'status' => 'unknown',
                'color' => '#999999'
            ];
        }
        
        $percentage = ($current / $target) * 100;
        $rawPercentage = $percentage; // Keep raw percentage for status determination
        $percentage = max(0, $percentage); // Don't clamp max to show actual percentage
        
        // Determine status and color
        $status = 'normal';
        $color = '#4CAF50'; // Green
        
        if ($rawPercentage > 100) {
            $status = 'overtime';
            $color = '#f44336'; // Red
        } elseif ($rawPercentage > 80) {
            $status = 'warning';
            $color = '#FF9800'; // Orange
        }
        
        return [
            'percentage' => round($percentage, $precision),
            'display' => round($percentage, $precision) . '%',
            'status' => $status,
            'color' => $color,
            'current' => $current,
            'target' => $target
        ];
    }
    
    /**
     * Format status badge for timeclock records
     * 
     * @param int $status Status code
     * @param object|null $langs Dolibarr langs object for translation
     * @return array Status data for templates
     */
    public static function formatStatus($status, $langs = null) 
    {
        // Load constants if not already loaded
        if (!defined('TIMECLOCK_STATUS_ACTIVE')) {
            require_once __DIR__ . '/Constants.php';
        }
        
        $statusMap = [
            TIMECLOCK_STATUS_DRAFT => [
                'label' => 'Draft',
                'class' => 'status-draft',
                'color' => '#999999',
                'icon' => 'md-edit'
            ],
            TIMECLOCK_STATUS_VALIDATED => [
                'label' => 'Validated',
                'class' => 'status-validated',
                'color' => '#2196F3',
                'icon' => 'md-check'
            ],
            TIMECLOCK_STATUS_ACTIVE => [
                'label' => 'Active',
                'class' => 'status-active',
                'color' => '#4CAF50',
                'icon' => 'md-play-arrow'
            ],
            TIMECLOCK_STATUS_COMPLETED => [
                'label' => 'Completed',
                'class' => 'status-completed',
                'color' => '#2196F3',
                'icon' => 'md-done'
            ],
            TIMECLOCK_STATUS_CANCELLED => [
                'label' => 'Cancelled',
                'class' => 'status-cancelled',
                'color' => '#f44336',
                'icon' => 'md-close'
            ]
        ];
        
        $statusData = $statusMap[$status] ?? $statusMap[TIMECLOCK_STATUS_DRAFT];
        
        // Translate label if langs object available
        if ($langs && method_exists($langs, 'trans')) {
            $translatedLabel = $langs->trans($statusData['label']);
            if ($translatedLabel !== $statusData['label']) {
                $statusData['label'] = $translatedLabel;
            }
        }
        
        $statusData['code'] = $status;
        return $statusData;
    }
    
    /**
     * Format currency values for display
     * 
     * @param float $amount Amount to format
     * @param string $currency Currency code (default: EUR)
     * @param int $decimals Number of decimal places (default: 2)
     * @return string Formatted currency string
     */
    public static function formatCurrency($amount, $currency = 'EUR', $decimals = 2) 
    {
        if (!is_numeric($amount)) {
            return '0.00 ' . $currency;
        }
        
        return number_format($amount, $decimals, '.', ' ') . ' ' . $currency;
    }
    
    /**
     * Prepare data for JSON encoding (removes sensitive data)
     * 
     * @param array $data Raw data array
     * @param array $sensitiveKeys Keys to remove from output
     * @return array Clean data safe for JSON encoding
     */
    public static function prepareForJson($data, $sensitiveKeys = ['password', 'token', 'secret', 'key']) 
    {
        if (!is_array($data)) {
            return [];
        }
        
        $cleanData = [];
        
        foreach ($data as $key => $value) {
            // Skip sensitive keys (check for partial matches too)
            $keyLower = strtolower($key);
            $shouldSkip = false;
            
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (strpos($keyLower, strtolower($sensitiveKey)) !== false) {
                    $shouldSkip = true;
                    break;
                }
            }
            
            if ($shouldSkip) {
                continue;
            }
            
            // Recursively clean nested arrays
            if (is_array($value)) {
                $cleanData[$key] = self::prepareForJson($value, $sensitiveKeys);
            } elseif (is_object($value)) {
                // Convert object to array and clean
                $cleanData[$key] = self::prepareForJson((array) $value, $sensitiveKeys);
            } else {
                $cleanData[$key] = $value;
            }
        }
        
        return $cleanData;
    }
    
    /**
     * Debug logging helper - uses Dolibarr dol_syslog if available
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