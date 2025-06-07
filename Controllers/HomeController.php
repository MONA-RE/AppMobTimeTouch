<?php
require_once 'Controllers/BaseController.php';
require_once 'Services/TimeclockService.php';
require_once 'Services/ConfigService.php';
require_once 'Utils/DataFormatter.php';
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');

/**
 * HomeController - Main controller for the home page
 * 
 * Handles the main timeclock interface display and coordinate
 * data preparation for the mobile template.
 */
class HomeController extends BaseController 
{
    private $timeclockService;
    private $configService;
    
    public function __construct($db, $user, $langs) 
    {
        parent::__construct($db, $user, $langs);
        
        $this->configService = new ConfigService($db);
        $this->timeclockService = new TimeclockService($db, $this->configService);
    }
    
    /**
     * Main index action for home page
     * @param int $view View type (1=today, 2=week, 3=all)
     */
    public function index($view = 1) 
    {
        $this->debugLog("HomeController index called with view: " . $view);
        
        // Handle redirect messages
        $this->handleRedirectMessages();
        
        try {
            // Get timeclock data using services
            $activeSession = $this->timeclockService->getActiveSession($this->user->id);
            $todaySummary = $this->timeclockService->getTodaySummary($this->user->id);
            $weeklySummary = $this->timeclockService->getWeeklySummary($this->user->id);
            
            // Get recent records based on view
            $recentRecords = $this->getRecentRecords($view);
            
            // Get available timeclock types
            $timeclockTypes = TimeclockType::getActiveTypes($this->db);
            $defaultTypeId = TimeclockType::getDefaultType($this->db);
            
            // Prepare template data
            $templateData = $this->prepareHomeTemplateData($activeSession, $todaySummary, $weeklySummary, $recentRecords, $timeclockTypes, $defaultTypeId, $view);
            
            $this->debugLog("Template data prepared successfully");
            
            // Extract variables for template
            extract($templateData);
            
            // Include template
            include "tpl/home.tpl";
            
        } catch (Exception $e) {
            $this->debugLog("Error in HomeController index: " . $e->getMessage(), LOG_ERROR);
            $this->addError($this->langs->trans("ErrorLoadingData"));
            
            // Fallback data for error case
            $templateData = $this->prepareErrorTemplateData();
            extract($templateData);
            include "tpl/home.tpl";
        }
    }
    
    /**
     * Get recent records based on view type
     * @param int $view View type
     * @return array Recent records
     */
    private function getRecentRecords($view)
    {
        $timeclockrecord = new TimeclockRecord($this->db);
        $today = date('Y-m-d');
        
        switch ($view) {
            case 1: // Today
                return $timeclockrecord->getRecordsByUserAndDate($this->user->id, $today, $today);
                
            case 2: // This week
                $currentWeek = WeeklySummary::getCurrentWeek();
                $weekDates = WeeklySummary::getWeekDates($currentWeek['year'], $currentWeek['week_number']);
                return $timeclockrecord->getRecordsByUserAndDate($this->user->id, $weekDates['start_date'], $weekDates['end_date']);
                
            case 3: // All time (last 30 days)
                $dateStart = date('Y-m-d', strtotime('-30 days'));
                return $timeclockrecord->getRecordsByUserAndDate($this->user->id, $dateStart, $today);
                
            default:
                return $timeclockrecord->getRecordsByUserAndDate($this->user->id, $today, $today);
        }
    }
    
    /**
     * Prepare complete template data for home page
     */
    private function prepareHomeTemplateData($activeSession, $todaySummary, $weeklySummary, $recentRecords, $timeclockTypes, $defaultTypeId, $view) 
    {
        $data = $this->getCommonData();
        
        // Add timeclock-specific data
        $data['is_clocked_in'] = !empty($activeSession);
        $data['active_record'] = $activeSession['record'] ?? null;
        $data['clock_in_time'] = $activeSession['clock_in_time'] ?? null;
        $data['current_duration'] = $activeSession['current_duration'] ?? 0;
        
        // Summary data
        $data['today_total_hours'] = $todaySummary['total_hours'];
        $data['today_total_breaks'] = 0; // Will be implemented later
        $data['overtime_alert'] = $todaySummary['overtime_alert'];
        $data['weekly_summary'] = $weeklySummary;
        
        // Configuration
        $data['require_location'] = $this->configService->getRequireLocation();
        $data['max_hours_per_day'] = $this->configService->getMaxHoursPerDay();
        $data['overtime_threshold'] = $this->configService->getOvertimeThreshold();
        
        // Records and types
        $data['recent_records'] = $recentRecords;
        $data['timeclock_types'] = $timeclockTypes;
        $data['default_type_id'] = $defaultTypeId;
        $data['num_records'] = count($recentRecords);
        $data['view'] = $view;
        
        // JavaScript configuration
        $data['js_data'] = DataFormatter::prepareJavaScriptConfig([
            'is_clocked_in' => $data['is_clocked_in'],
            'clock_in_time' => $data['clock_in_time'],
            'require_location' => $data['require_location'],
            'default_type_id' => $defaultTypeId,
            'max_hours_per_day' => $data['max_hours_per_day'],
            'overtime_threshold' => $data['overtime_threshold'],
            'api_token' => newToken(),
            'user_id' => $this->user->id,
            'version' => '1.0'
        ]);
        
        $this->debugLog("Template data prepared with " . count($recentRecords) . " recent records");
        
        return $data;
    }
    
    /**
     * Prepare fallback template data for error cases
     */
    private function prepareErrorTemplateData()
    {
        $data = $this->getCommonData();
        
        // Set safe default values
        $data['is_clocked_in'] = false;
        $data['active_record'] = null;
        $data['clock_in_time'] = null;
        $data['current_duration'] = 0;
        $data['today_total_hours'] = 0;
        $data['today_total_breaks'] = 0;
        $data['overtime_alert'] = false;
        $data['weekly_summary'] = null;
        $data['require_location'] = 0;
        $data['max_hours_per_day'] = 8;
        $data['overtime_threshold'] = 8;
        $data['recent_records'] = [];
        $data['timeclock_types'] = [];
        $data['default_type_id'] = 1;
        $data['num_records'] = 0;
        $data['view'] = 1;
        
        // Minimal JavaScript configuration
        $data['js_data'] = DataFormatter::prepareJavaScriptConfig([
            'is_clocked_in' => false,
            'clock_in_time' => null,
            'require_location' => false,
            'default_type_id' => 1,
            'max_hours_per_day' => 8,
            'overtime_threshold' => 8,
            'api_token' => newToken(),
            'user_id' => $this->user->id,
            'version' => '1.0'
        ]);
        
        return $data;
    }
    
    /**
     * Get user statistics for dashboard
     * @return array User statistics
     */
    public function getUserStats()
    {
        try {
            $todaySummary = $this->timeclockService->getTodaySummary($this->user->id);
            $weeklySummary = $this->timeclockService->getWeeklySummary($this->user->id);
            
            return [
                'today_hours' => $todaySummary['total_hours'],
                'today_overtime' => $todaySummary['overtime_alert'],
                'week_hours' => $weeklySummary ? $weeklySummary->total_hours : 0,
                'week_overtime' => $weeklySummary ? $weeklySummary->overtime_hours : 0,
                'week_days_worked' => $weeklySummary ? $weeklySummary->days_worked : 0
            ];
        } catch (Exception $e) {
            $this->debugLog("Error getting user stats: " . $e->getMessage(), LOG_ERROR);
            return null;
        }
    }
}