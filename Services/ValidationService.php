<?php

/**
 * ValidationService - Input validation and business rules
 * 
 * Centralized validation logic for timeclock operations,
 * user inputs, and business rule enforcement.
 */
class ValidationService 
{
    private $configService;
    private $locationService;
    
    public function __construct($configService, $locationService = null) 
    {
        $this->configService = $configService;
        $this->locationService = $locationService;
    }
    
    /**
     * Validate clock-in parameters
     * @param array $params Clock-in parameters
     * @return array Validation result with success flag and errors
     */
    public function validateClockIn($params)
    {
        $errors = [];
        
        // Validate timeclock type
        if (empty($params['timeclock_type_id']) || !is_numeric($params['timeclock_type_id'])) {
            $errors[] = 'TimeclockTypeRequired';
        }
        
        // Validate location if required
        if ($this->locationService && $this->locationService->isLocationRequired()) {
            $locationError = $this->locationService->getCoordinateValidationError(
                $params['latitude'] ?? null,
                $params['longitude'] ?? null
            );
            if ($locationError) {
                $errors[] = $locationError;
            }
        }
        
        // Validate optional fields
        if (!empty($params['location']) && strlen($params['location']) > 255) {
            $errors[] = 'LocationTooLong';
        }
        
        if (!empty($params['note']) && strlen($params['note']) > 1000) {
            $errors[] = 'NoteTooLong';
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate clock-out parameters
     * @param array $params Clock-out parameters
     * @return array Validation result with success flag and errors
     */
    public function validateClockOut($params)
    {
        $errors = [];
        
        // Validate location if required
        if ($this->locationService && $this->locationService->isLocationRequired()) {
            $locationError = $this->locationService->getCoordinateValidationError(
                $params['latitude'] ?? null,
                $params['longitude'] ?? null
            );
            if ($locationError) {
                $errors[] = $locationError;
            }
        }
        
        // Validate optional fields
        if (!empty($params['location']) && strlen($params['location']) > 255) {
            $errors[] = 'LocationTooLong';
        }
        
        if (!empty($params['note']) && strlen($params['note']) > 1000) {
            $errors[] = 'NoteTooLong';
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validate working time limits
     * @param float $hoursWorked Hours worked today
     * @param int $userId User ID
     * @return array Validation result with warnings
     */
    public function validateWorkingTime($hoursWorked, $userId)
    {
        $warnings = [];
        $maxHours = $this->configService->getMaxHoursPerDay();
        $overtimeThreshold = $this->configService->getOvertimeThreshold();
        
        if ($hoursWorked > $maxHours) {
            $warnings[] = [
                'type' => 'error',
                'message' => 'MaxDailyHoursExceeded',
                'value' => $hoursWorked,
                'limit' => $maxHours
            ];
        } elseif ($hoursWorked > $overtimeThreshold) {
            $warnings[] = [
                'type' => 'warning',
                'message' => 'OvertimeThresholdReached',
                'value' => $hoursWorked,
                'threshold' => $overtimeThreshold
            ];
        }
        
        return [
            'valid' => $hoursWorked <= $maxHours,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Validate timeclock record data
     * @param array $recordData Record data to validate
     * @return array Validation result
     */
    public function validateTimeclockRecord($recordData)
    {
        $errors = [];
        
        // Required fields validation
        $requiredFields = ['fk_user', 'clock_in_time'];
        foreach ($requiredFields as $field) {
            if (empty($recordData[$field])) {
                $errors[] = 'Required field missing: ' . $field;
            }
        }
        
        // Validate user ID
        if (!empty($recordData['fk_user']) && !is_numeric($recordData['fk_user'])) {
            $errors[] = 'InvalidUserId';
        }
        
        // Validate timestamps
        if (!empty($recordData['clock_in_time']) && !$this->isValidTimestamp($recordData['clock_in_time'])) {
            $errors[] = 'InvalidClockInTime';
        }
        
        if (!empty($recordData['clock_out_time']) && !$this->isValidTimestamp($recordData['clock_out_time'])) {
            $errors[] = 'InvalidClockOutTime';
        }
        
        // Validate duration logic
        if (!empty($recordData['clock_in_time']) && !empty($recordData['clock_out_time'])) {
            $clockIn = is_numeric($recordData['clock_in_time']) ? $recordData['clock_in_time'] : strtotime($recordData['clock_in_time']);
            $clockOut = is_numeric($recordData['clock_out_time']) ? $recordData['clock_out_time'] : strtotime($recordData['clock_out_time']);
            
            if ($clockOut <= $clockIn) {
                $errors[] = 'ClockOutBeforeClockIn';
            }
            
            $duration = $clockOut - $clockIn;
            if ($duration > 86400) { // 24 hours
                $errors[] = 'SessionTooLong';
            }
        }
        
        // Validate status
        if (!empty($recordData['status']) && !in_array($recordData['status'], [0, 1, 2, 3, 9])) {
            $errors[] = 'InvalidStatus';
        }
        
        return [
            'success' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Sanitize input data for timeclock operations
     * @param array $data Raw input data
     * @return array Sanitized data
     */
    public function sanitizeInput($data)
    {
        $sanitized = [];
        
        // Sanitize location
        if (isset($data['location'])) {
            $sanitized['location'] = trim(strip_tags($data['location']));
            $sanitized['location'] = substr($sanitized['location'], 0, 255);
        }
        
        // Sanitize note
        if (isset($data['note'])) {
            $sanitized['note'] = trim($data['note']);
            // Allow basic HTML but limit length
            $sanitized['note'] = substr($sanitized['note'], 0, 1000);
        }
        
        // Sanitize numeric values
        foreach (['latitude', 'longitude', 'timeclock_type_id'] as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = is_numeric($data[$field]) ? (float) $data[$field] : null;
            }
        }
        
        // Preserve action and token as-is
        if (isset($data['action'])) {
            $sanitized['action'] = $data['action'];
        }
        if (isset($data['token'])) {
            $sanitized['token'] = $data['token'];
        }
        
        return $sanitized;
    }
    
    /**
     * Check if a value is a valid timestamp
     * @param mixed $timestamp Timestamp to validate
     * @return bool True if valid
     */
    private function isValidTimestamp($timestamp)
    {
        if (is_numeric($timestamp)) {
            // Unix timestamp validation
            return $timestamp > 946684800 && $timestamp < 4102444800; // 2000-2100
        }
        
        if (is_string($timestamp)) {
            // Try to parse as date string
            $parsed = strtotime($timestamp);
            return $parsed !== false && $parsed > 946684800;
        }
        
        return false;
    }
    
    /**
     * Validate user permissions for timeclock operations
     * @param object $user User object
     * @param string $operation Operation type (read, write, validate, etc.)
     * @return bool True if user has permission
     */
    public function validateUserPermissions($user, $operation)
    {
        if (!is_object($user) || empty($user->rights->appmobtimetouch->timeclock)) {
            return false;
        }
        
        switch ($operation) {
            case 'read':
                return !empty($user->rights->appmobtimetouch->timeclock->read);
            case 'write':
                return !empty($user->rights->appmobtimetouch->timeclock->write);
            case 'readall':
                return !empty($user->rights->appmobtimetouch->timeclock->readall);
            case 'validate':
                return !empty($user->rights->appmobtimetouch->timeclock->validate);
            case 'export':
                return !empty($user->rights->appmobtimetouch->timeclock->export);
            default:
                return false;
        }
    }
    
    /**
     * Check for potential duplicate clock-in attempts
     * @param int $userId User ID
     * @param int $timestamp Clock-in timestamp
     * @param int $tolerance Tolerance in seconds (default: 60)
     * @return bool True if potentially duplicate
     */
    public function isDuplicateClockIn($userId, $timestamp, $tolerance = 60)
    {
        // This would typically check the database for recent clock-in attempts
        // For now, we return false as this requires database access
        // In a full implementation, this would query recent records
        return false;
    }
}