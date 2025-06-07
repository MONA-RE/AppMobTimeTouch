<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/weeklysummary.class.php');
require_once 'Utils/TimeHelper.php';

/**
 * TimeclockService - Business logic for timeclock operations
 * 
 * Extracted from home.php lines 180-369 to provide centralized
 * business logic for active sessions, daily and weekly summaries.
 */
class TimeclockService 
{
    private $db;
    private $configService;
    
    public function __construct($db, $configService) 
    {
        $this->db = $db;
        $this->configService = $configService;
    }
    
    /**
     * Get active timeclock session for user
     * @param int $userId User ID
     * @return array|null Session data with record, timestamps and duration
     */
    public function getActiveSession($userId) 
    {
        $this->debugLog("Getting active session for user: " . $userId);
        
        $timeclockrecord = new TimeclockRecord($this->db);
        $activeRecordId = $timeclockrecord->getActiveRecord($userId);
        
        if ($activeRecordId <= 0) {
            $this->debugLog("No active record found for user");
            return null;
        }
        
        $this->debugLog("Active record ID: " . $activeRecordId);
        
        $activeRecord = new TimeclockRecord($this->db);
        if ($activeRecord->fetch($activeRecordId) <= 0) {
            $this->debugLog("Failed to fetch active record details", LOG_ERROR);
            return null;
        }
        
        $this->debugLog("Active record fetched successfully");
        
        return $this->createSessionFromRecord($activeRecord);
    }
    
    /**
     * Get today's summary for user
     * @param int $userId User ID
     * @return array Summary with total hours, breaks and overtime alert
     */
    public function getTodaySummary($userId) 
    {
        $today = date('Y-m-d');
        $this->debugLog("Getting today's summary for date: " . $today);
        
        $timeclockrecord = new TimeclockRecord($this->db);
        $records = $timeclockrecord->getRecordsByUserAndDate($userId, $today, $today, 3); // STATUS_COMPLETED
        
        $totalHours = 0;
        $totalBreaks = 0;
        
        foreach ($records as $record) {
            if (!empty($record->work_duration) && is_numeric($record->work_duration)) {
                $totalHours += $record->work_duration / 60; // Convert minutes to hours
            }
            if (!empty($record->break_duration) && is_numeric($record->break_duration)) {
                $totalBreaks += $record->break_duration;
            }
        }
        
        $this->debugLog("Today's totals - Hours: " . $totalHours . ", Breaks: " . $totalBreaks);
        
        // Add active session duration if user is clocked in
        $activeSession = $this->getActiveSession($userId);
        if ($activeSession && $activeSession['current_duration'] > 0) {
            $activeDurationHours = $activeSession['current_duration'] / 3600;
            $totalHours += $activeDurationHours;
            $this->debugLog("Added active duration to today's total: " . $activeDurationHours . " hours");
        }
        
        $overtimeThreshold = $this->configService->getOvertimeThreshold();
        $overtimeAlert = $totalHours > $overtimeThreshold;
        
        if ($overtimeAlert) {
            $this->debugLog("Overtime alert triggered - Hours: " . $totalHours . ", Threshold: " . $overtimeThreshold);
        }
        
        return [
            'total_hours' => $totalHours,
            'total_breaks' => $totalBreaks,
            'overtime_alert' => $overtimeAlert,
            'overtime_threshold' => $overtimeThreshold
        ];
    }
    
    /**
     * Get weekly summary for user
     * @param int $userId User ID
     * @return WeeklySummary|null Weekly summary object
     */
    public function getWeeklySummary($userId)
    {
        $currentWeek = WeeklySummary::getCurrentWeek();
        $this->debugLog("Current week: " . $currentWeek['year'] . "-W" . $currentWeek['week_number']);
        
        // Try to get existing weekly summary
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_weekly_summary";
        $sql .= " WHERE fk_user = ".((int) $userId);
        $sql .= " AND year = ".((int) $currentWeek['year']);
        $sql .= " AND week_number = ".((int) $currentWeek['week_number']);
        $sql .= " AND entity IN (".getEntity('weeklysummary').")";
        
        $resql = $this->db->query($sql);
        if ($resql && $this->db->num_rows($resql)) {
            $obj = $this->db->fetch_object($resql);
            $weeklySummary = new WeeklySummary($this->db);
            $weeklySummary->fetch($obj->rowid);
            $this->db->free($resql);
            $this->debugLog("Existing weekly summary found");
            return $weeklySummary;
        }
        
        $this->debugLog("No existing weekly summary, creating temporary one");
        return $this->createTemporaryWeeklySummary($userId, $currentWeek);
    }
    
    /**
     * Create session data from active record
     * @param TimeclockRecord $record Active timeclock record
     * @return array Session data
     */
    private function createSessionFromRecord($record) 
    {
        $this->debugLog("Raw clock_in_time from DB: " . $record->clock_in_time);
        
        $clockInTime = $this->parseClockInTime($record->clock_in_time);
        $currentDuration = 0;
        
        if (!empty($clockInTime) && is_numeric($clockInTime) && $clockInTime > 0) {
            $currentTimestamp = dol_now();
            $currentDuration = $currentTimestamp - $clockInTime;
            
            $this->debugLog("Current timestamp: " . $currentTimestamp);
            $this->debugLog("Clock in timestamp: " . $clockInTime);
            $this->debugLog("Current duration calculated: " . $currentDuration . " seconds");
            
            // Validation que la durée est raisonnable (pas plus de 24h et pas négative)
            if ($currentDuration < 0) {
                $this->debugLog("Warning: Negative duration detected, clock_in_time seems to be in the future", LOG_WARNING);
                $currentDuration = 0;
            } elseif ($currentDuration > 86400) {
                $this->debugLog("Warning: Duration over 24 hours (" . ($currentDuration/3600) . "h), possible data issue", LOG_WARNING);
            }
        } else {
            $this->debugLog("Unable to calculate duration - invalid clock_in_time", LOG_WARNING);
        }
        
        $this->debugLog("Final values - clock_in_time: " . $clockInTime . ", current_duration: " . $currentDuration);
        
        // Test de la fonction TimeHelper::convertSecondsToReadableTime
        if ($currentDuration > 0) {
            $durationReadable = TimeHelper::convertSecondsToReadableTime($currentDuration);
            $this->debugLog("Duration readable: " . $durationReadable);
        }
        
        return [
            'record' => $record,
            'is_active' => true,
            'clock_in_time' => $clockInTime,
            'current_duration' => $currentDuration
        ];
    }
    
    /**
     * Parse clock-in timestamp from various formats
     * @param mixed $rawValue Raw timestamp value from database
     * @return int|null Parsed Unix timestamp
     */
    private function parseClockInTime($rawValue)
    {
        // Méthode 1: Vérifier si c'est déjà un timestamp Unix valide
        if (is_numeric($rawValue) && $rawValue > 946684800 && $rawValue < 4102444800) {
            // C'est déjà un timestamp Unix valide (entre 2000 et 2100)
            $timestamp = (int) $rawValue;
            $this->debugLog("Raw value is already a valid Unix timestamp: " . $timestamp);
            return $timestamp;
        }
        
        // Méthode 2: Essayer la conversion jdate pour les formats de date Dolibarr
        $timestamp = $this->db->jdate($rawValue);
        $this->debugLog("Converted with jdate: " . $timestamp);
        
        // Méthode 3: Fallback avec strtotime si jdate échoue
        if (empty($timestamp) || !is_numeric($timestamp)) {
            $timestamp = strtotime($rawValue);
            $this->debugLog("Fallback conversion with strtotime: " . $timestamp);
            
            // Validation du résultat strtotime
            if ($timestamp === false || $timestamp <= 0) {
                $this->debugLog("All conversion methods failed", LOG_ERROR);
                return null;
            }
        }
        
        return $timestamp;
    }
    
    /**
     * Create temporary weekly summary with current data
     * @param int $userId User ID
     * @param array $currentWeek Current week data
     * @return WeeklySummary
     */
    private function createTemporaryWeeklySummary($userId, $currentWeek)
    {
        $weeklySummary = new WeeklySummary($this->db);
        $weeklySummary->fk_user = $userId;
        $weeklySummary->year = $currentWeek['year'];
        $weeklySummary->week_number = $currentWeek['week_number'];
        
        // Calculate week dates
        $weekDates = WeeklySummary::getWeekDates($currentWeek['year'], $currentWeek['week_number']);
        $weeklySummary->week_start_date = $weekDates['start_date'];
        $weeklySummary->week_end_date = $weekDates['end_date'];
        
        // Get week's records for calculation
        $timeclockrecord = new TimeclockRecord($this->db);
        $weekRecords = $timeclockrecord->getRecordsByUserAndDate($userId, $weekDates['start_date'], $weekDates['end_date'], 3);
        
        $weeklyTotalHours = 0;
        $weeklyTotalBreaks = 0;
        $daysWorked = array();
        
        foreach ($weekRecords as $record) {
            if (!empty($record->work_duration) && is_numeric($record->work_duration)) {
                $weeklyTotalHours += $record->work_duration / 60;
            }
            if (!empty($record->break_duration) && is_numeric($record->break_duration)) {
                $weeklyTotalBreaks += $record->break_duration;
            }
            
            // CORRECTION: Conversion correcte pour la date de travail
            $workDateTimestamp = $this->db->jdate($record->clock_in_time);
            if (!empty($workDateTimestamp)) {
                $workDate = date('Y-m-d', $workDateTimestamp);
                if (!in_array($workDate, $daysWorked)) {
                    $daysWorked[] = $workDate;
                }
            }
        }
        
        // Add today's active time if it's in current week
        $today = date('Y-m-d');
        $activeSession = $this->getActiveSession($userId);
        if ($activeSession && $today >= $weekDates['start_date'] && $today <= $weekDates['end_date']) {
            $weeklyTotalHours += $activeSession['current_duration'] / 3600;
            if (!in_array($today, $daysWorked)) {
                $daysWorked[] = $today;
            }
            $this->debugLog("Added active time to weekly summary");
        }
        
        $weeklySummary->total_hours = round($weeklyTotalHours, 2);
        $weeklySummary->total_breaks = $weeklyTotalBreaks;
        $weeklySummary->days_worked = count($daysWorked);
        $weeklySummary->expected_hours = 40; // Default - could be configured
        $weeklySummary->overtime_hours = max(0, $weeklySummary->total_hours - $weeklySummary->expected_hours);
        $weeklySummary->status = 0; // In progress
        
        $this->debugLog("Weekly summary calculated - Total hours: " . $weeklySummary->total_hours);
        
        return $weeklySummary;
    }
    
    /**
     * Debug logging helper
     * @param string $message Log message
     * @param int $level Log level (LOG_DEBUG by default)
     */
    private function debugLog($message, $level = LOG_DEBUG)
    {
        // Check if dol_syslog function exists before using it
        if (function_exists('dol_syslog')) {
            dol_syslog("TIMECLOCK SERVICE DEBUG: " . $message, $level);
        }
    }
}