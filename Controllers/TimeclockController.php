<?php
require_once 'Controllers/BaseController.php';
require_once 'Services/TimeclockService.php';
require_once 'Services/LocationService.php';
require_once 'Services/ValidationService.php';
dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');

/**
 * TimeclockController - Handles timeclock actions (clock-in/out)
 * 
 * Manages clock-in and clock-out operations with validation,
 * location checking, and proper error handling.
 */
class TimeclockController extends BaseController 
{
    private $timeclockService;
    private $locationService;
    private $validationService;
    
    public function __construct($db, $user, $langs) 
    {
        parent::__construct($db, $user, $langs);
        
        $configService = new ConfigService($db);
        $this->timeclockService = new TimeclockService($db, $configService);
        $this->locationService = new LocationService($configService);
        $this->validationService = new ValidationService($configService, $this->locationService);
    }
    
    /**
     * Handle clock-in action
     * @return bool Success/failure
     */
    public function clockIn() 
    {
        $this->debugLog("Clock-in action started");
        
        // Check write permissions
        if (!$this->hasPermission('write')) {
            $this->debugLog("Clock-in denied - insufficient permissions", LOG_WARNING);
            accessforbidden();
        }
        
        // Validate CSRF token
        if (!$this->validateToken()) {
            $this->debugLog("Clock-in denied - invalid CSRF token", LOG_WARNING);
            $this->addError($this->langs->trans("SecurityTokenError"));
            return false;
        }
        
        // Get and sanitize input parameters
        $params = $this->getClockInParameters();
        $this->debugLog("Clock-in parameters: " . json_encode($params));
        
        // Validate input data
        $validation = $this->validationService->validateClockIn($params);
        if (!$validation['success']) {
            foreach ($validation['errors'] as $error) {
                $this->addError($this->langs->trans($error));
            }
            $this->debugLog("Clock-in validation failed: " . implode(', ', $validation['errors']), LOG_WARNING);
            return false;
        }
        
        try {
            // Create timeclock record and perform clock-in
            $timeclockrecord = new TimeclockRecord($this->db);
            $result = $timeclockrecord->clockIn(
                $this->user, 
                $params['timeclock_type_id'], 
                $params['location'], 
                $params['latitude'], 
                $params['longitude'], 
                $params['note']
            );
            
            if ($result > 0) {
                $this->debugLog("Clock-in successful for user " . $this->user->id . " with record ID: " . $result);
                $this->redirectWithSuccess('clockin');
            } else {
                $errorMsg = !empty($timeclockrecord->error) ? 
                    $timeclockrecord->error : 
                    $this->langs->trans("ClockInError");
                    
                $this->debugLog("Clock-in failed: " . $errorMsg, LOG_ERROR);
                $this->addError($errorMsg);
                return false;
            }
            
        } catch (Exception $e) {
            $this->debugLog("Clock-in exception: " . $e->getMessage(), LOG_ERROR);
            $this->addError($this->langs->trans("ClockInError"));
            return false;
        }
        
        return true;
    }
    
    /**
     * Handle clock-out action
     * @return bool Success/failure
     */
    public function clockOut() 
    {
        $this->debugLog("Clock-out action started");
        
        // Check write permissions
        if (!$this->hasPermission('write')) {
            $this->debugLog("Clock-out denied - insufficient permissions", LOG_WARNING);
            accessforbidden();
        }
        
        // Validate CSRF token
        if (!$this->validateToken()) {
            $this->debugLog("Clock-out denied - invalid CSRF token", LOG_WARNING);
            $this->addError($this->langs->trans("SecurityTokenError"));
            return false;
        }
        
        // Get and sanitize input parameters
        $params = $this->getClockOutParameters();
        $this->debugLog("Clock-out parameters: " . json_encode($params));
        
        // Validate input data
        $validation = $this->validationService->validateClockOut($params);
        if (!$validation['success']) {
            foreach ($validation['errors'] as $error) {
                $this->addError($this->langs->trans($error));
            }
            $this->debugLog("Clock-out validation failed: " . implode(', ', $validation['errors']), LOG_WARNING);
            return false;
        }
        
        try {
            // Create timeclock record and perform clock-out
            $timeclockrecord = new TimeclockRecord($this->db);
            $result = $timeclockrecord->clockOut(
                $this->user, 
                $params['location'], 
                $params['latitude'], 
                $params['longitude'], 
                $params['note']
            );
            
            if ($result > 0) {
                $this->debugLog("Clock-out successful for user " . $this->user->id);
                $this->redirectWithSuccess('clockout');
            } else {
                $errorMsg = !empty($timeclockrecord->error) ? 
                    $timeclockrecord->error : 
                    $this->langs->trans("ClockOutError");
                    
                $this->debugLog("Clock-out failed: " . $errorMsg, LOG_ERROR);
                $this->addError($errorMsg);
                return false;
            }
            
        } catch (Exception $e) {
            $this->debugLog("Clock-out exception: " . $e->getMessage(), LOG_ERROR);
            $this->addError($this->langs->trans("ClockOutError"));
            return false;
        }
        
        return true;
    }
    
    /**
     * Get and sanitize clock-in parameters
     * @return array Sanitized parameters
     */
    private function getClockInParameters()
    {
        $params = [
            'timeclock_type_id' => $this->getParam('timeclock_type_id', 'int', 1),
            'location' => $this->getParam('location', 'alphanohtml', ''),
            'latitude' => $this->getParam('latitude', 'float', null),
            'longitude' => $this->getParam('longitude', 'float', null),
            'note' => $this->getParam('note', 'restricthtml', '')
        ];
        
        // Sanitize using validation service
        return $this->validationService->sanitizeInput($params);
    }
    
    /**
     * Get and sanitize clock-out parameters
     * @return array Sanitized parameters
     */
    private function getClockOutParameters()
    {
        $params = [
            'location' => $this->getParam('location', 'alphanohtml', ''),
            'latitude' => $this->getParam('latitude', 'float', null),
            'longitude' => $this->getParam('longitude', 'float', null),
            'note' => $this->getParam('note', 'restricthtml', '')
        ];
        
        // Sanitize using validation service
        return $this->validationService->sanitizeInput($params);
    }
    
    /**
     * Get current user's timeclock status
     * @return array Status information
     */
    public function getStatus()
    {
        try {
            $activeSession = $this->timeclockService->getActiveSession($this->user->id);
            $todaySummary = $this->timeclockService->getTodaySummary($this->user->id);
            
            return [
                'success' => true,
                'is_clocked_in' => !empty($activeSession),
                'active_record' => $activeSession['record'] ?? null,
                'clock_in_time' => $activeSession['clock_in_time'] ?? null,
                'current_duration' => $activeSession['current_duration'] ?? 0,
                'today_hours' => $todaySummary['total_hours'],
                'overtime_alert' => $todaySummary['overtime_alert']
            ];
            
        } catch (Exception $e) {
            $this->debugLog("Error getting timeclock status: " . $e->getMessage(), LOG_ERROR);
            return [
                'success' => false,
                'error' => $this->langs->trans("ErrorGettingStatus")
            ];
        }
    }
    
    /**
     * Validate current user session
     * @return bool True if user has active session
     */
    public function hasActiveSession()
    {
        try {
            $activeSession = $this->timeclockService->getActiveSession($this->user->id);
            return !empty($activeSession);
        } catch (Exception $e) {
            $this->debugLog("Error checking active session: " . $e->getMessage(), LOG_ERROR);
            return false;
        }
    }
    
    /**
     * Force clock-out (admin function)
     * @param int $userId User ID to force clock-out
     * @return bool Success/failure
     */
    public function forceClockOut($userId = null)
    {
        // Check admin permissions
        if (!$this->hasPermission('validate')) {
            $this->debugLog("Force clock-out denied - insufficient permissions", LOG_WARNING);
            accessforbidden();
        }
        
        $targetUserId = $userId ?? $this->user->id;
        $this->debugLog("Force clock-out requested for user: " . $targetUserId);
        
        try {
            $timeclockrecord = new TimeclockRecord($this->db);
            $result = $timeclockrecord->clockOut(
                (object)['id' => $targetUserId], 
                'Forced clock-out by admin', 
                null, 
                null, 
                'Administrative clock-out'
            );
            
            if ($result > 0) {
                $this->debugLog("Force clock-out successful for user " . $targetUserId);
                $this->addMessage($this->langs->trans("ForceClockOutSuccess"));
                return true;
            } else {
                $this->debugLog("Force clock-out failed for user " . $targetUserId, LOG_ERROR);
                $this->addError($this->langs->trans("ForceClockOutError"));
                return false;
            }
            
        } catch (Exception $e) {
            $this->debugLog("Force clock-out exception: " . $e->getMessage(), LOG_ERROR);
            $this->addError($this->langs->trans("ForceClockOutError"));
            return false;
        }
    }
}