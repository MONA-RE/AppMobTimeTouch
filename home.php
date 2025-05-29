<?php
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; 
$tmp2 = realpath(__FILE__); 
$i = strlen($tmp) - 1; 
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

// Vérifier si la fonction isModEnabled existe (compatibilité)
if (!function_exists('isModEnabled')) {
	function isModEnabled($module)
	{
		global $conf;
		return !empty($conf->$module->enabled);
	}
}

// Load required libraries
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Load module specific libraries
dol_include_once('/appmobtimetouch/lib/appmobtimetouch.lib.php');
dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');
dol_include_once('/appmobtimetouch/class/weeklysummary.class.php');
dol_include_once('/appmobtimetouch/class/timeclockconfig.class.php');

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "users", "companies", "errors"));

// Get parameters
$action = GETPOST('action', 'aZ09');
$view = GETPOST('view','int'); // 1 = today, 2 = this week, 3 = all time
$targetId = "feedMyTimeclock";

// Set default values if not provided
if (empty($view)) $view = 1;

// Security check - vérifier que le module est activé
if (!isModEnabled('appmobtimetouch')) {
    accessforbidden('Module not enabled');
}

// Security check - vérifier les droits
if (!$user->rights->appmobtimetouch->timeclock->read) {
    accessforbidden();
}

// Initialize variables for messages
$error = 0;
$errors = array();
$message = '';
$messages = array();

// Handle actions from mobile interface
if ($action) {
    if ($action == 'clockin' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
        $timeclock_type_id = GETPOST('timeclock_type_id', 'int');
        $location = GETPOST('location', 'alphanohtml');
        $latitude = GETPOST('latitude', 'float');
        $longitude = GETPOST('longitude', 'float');
        $note = GETPOST('note', 'restricthtml');

        // Validate required location if configured
        $require_location = TimeclockConfig::getValue($db, 'REQUIRE_LOCATION', 0);
        if ($require_location && (empty($latitude) || empty($longitude))) {
            $error++;
            $errors[] = $langs->trans("LocationRequiredForClockIn");
        }

        if (!$error) {
            $timeclockrecord = new TimeclockRecord($db);
            $result = $timeclockrecord->clockIn($user, $timeclock_type_id, $location, $latitude, $longitude, $note);
            
            if ($result > 0) {
                $messages[] = $langs->trans("ClockInSuccess");
                // Redirect to avoid resubmission
                header('Location: '.$_SERVER['PHP_SELF'].'?clockin_success=1');
                exit;
            } else {
                $error++;
                $errors[] = !empty($timeclockrecord->error) ? $langs->trans($timeclockrecord->error) : $langs->trans("ClockInError");
            }
        }
    }

    if ($action == 'clockout' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
        $location = GETPOST('location', 'alphanohtml');
        $latitude = GETPOST('latitude', 'float');
        $longitude = GETPOST('longitude', 'float');
        $note = GETPOST('note', 'restricthtml');

        $timeclockrecord = new TimeclockRecord($db);
        $result = $timeclockrecord->clockOut($user, $location, $latitude, $longitude, $note);
        
        if ($result > 0) {
            $messages[] = $langs->trans("ClockOutSuccess");
            // Redirect to avoid resubmission
            header('Location: '.$_SERVER['PHP_SELF'].'?clockout_success=1');
            exit;
        } else {
            $error++;
            $errors[] = !empty($timeclockrecord->error) ? $langs->trans($timeclockrecord->error) : $langs->trans("ClockOutError");
        }
    }
}

// Handle success messages from redirects
if (GETPOST('clockin_success', 'int')) {
    $messages[] = $langs->trans("ClockInSuccess");
}
if (GETPOST('clockout_success', 'int')) {
    $messages[] = $langs->trans("ClockOutSuccess");
}

// Initialize time tracking objects
$timeclockrecord = new TimeclockRecord($db);
$weeklysummary = new WeeklySummary($db);

// Get current user's active timeclock record
$active_record_id = $timeclockrecord->getActiveRecord($user->id);
$active_record = null;
$is_clocked_in = false;
$clock_in_time = null;
$current_duration = 0;

if ($active_record_id > 0) {
    $active_record = new TimeclockRecord($db);
    if ($active_record->fetch($active_record_id) > 0) {
        $is_clocked_in = true;
        $clock_in_time = $db->jdate($active_record->clock_in_time);
        $current_duration = dol_now() - $clock_in_time;
    }
}

// Get today's summary
$today = date('Y-m-d');
$today_records = $timeclockrecord->getRecordsByUserAndDate($user->id, $today, $today, 3); // STATUS_COMPLETED
$today_total_hours = 0;
$today_total_breaks = 0;

foreach ($today_records as $record) {
    if (!empty($record->work_duration)) {
        $today_total_hours += $record->work_duration / 60; // Convert minutes to hours
    }
    if (!empty($record->break_duration)) {
        $today_total_breaks += $record->break_duration;
    }
}

// Add active record duration to today's total
if ($is_clocked_in) {
    $active_duration_hours = $current_duration / 3600; // Convert seconds to hours
    $today_total_hours += $active_duration_hours;
}

// Get current week summary
$current_week = WeeklySummary::getCurrentWeek();
$weekly_summary = null;

// Try to get existing weekly summary
$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_weekly_summary";
$sql .= " WHERE fk_user = ".((int) $user->id);
$sql .= " AND year = ".((int) $current_week['year']);
$sql .= " AND week_number = ".((int) $current_week['week_number']);
$sql .= " AND entity IN (".getEntity('weeklysummary').")";

$resql = $db->query($sql);
if ($resql && $db->num_rows($resql)) {
    $obj = $db->fetch_object($resql);
    $weekly_summary = new WeeklySummary($db);
    $weekly_summary->fetch($obj->rowid);
    $db->free($resql);
} else {
    // Create a temporary weekly summary with current data
    $weekly_summary = new WeeklySummary($db);
    $weekly_summary->fk_user = $user->id;
    $weekly_summary->year = $current_week['year'];
    $weekly_summary->week_number = $current_week['week_number'];
    
    // Calculate week dates
    $week_dates = WeeklySummary::getWeekDates($current_week['year'], $current_week['week_number']);
    $weekly_summary->week_start_date = $week_dates['start_date'];
    $weekly_summary->week_end_date = $week_dates['end_date'];
    
    // Get week's records for calculation
    $week_records = $timeclockrecord->getRecordsByUserAndDate($user->id, $week_dates['start_date'], $week_dates['end_date'], 3);
    $weekly_total_hours = 0;
    $weekly_total_breaks = 0;
    $days_worked = array();
    
    foreach ($week_records as $record) {
        if (!empty($record->work_duration)) {
            $weekly_total_hours += $record->work_duration / 60;
        }
        if (!empty($record->break_duration)) {
            $weekly_total_breaks += $record->break_duration;
        }
        
        $work_date = date('Y-m-d', $db->jdate($record->clock_in_time));
        if (!in_array($work_date, $days_worked)) {
            $days_worked[] = $work_date;
        }
    }
    
    // Add today's active time if it's in current week
    if ($is_clocked_in && $today >= $week_dates['start_date'] && $today <= $week_dates['end_date']) {
        $weekly_total_hours += $current_duration / 3600;
        if (!in_array($today, $days_worked)) {
            $days_worked[] = $today;
        }
    }
    
    $weekly_summary->total_hours = round($weekly_total_hours, 2);
    $weekly_summary->total_breaks = $weekly_total_breaks;
    $weekly_summary->days_worked = count($days_worked);
    $weekly_summary->expected_hours = 40; // Default - could be configured
    $weekly_summary->overtime_hours = max(0, $weekly_summary->total_hours - $weekly_summary->expected_hours);
    $weekly_summary->status = 0; // In progress
}

// Get recent records based on view
$recent_records = array();
switch ($view) {
    case 1: // Today
        $recent_records = $timeclockrecord->getRecordsByUserAndDate($user->id, $today, $today);
        break;
    case 2: // This week
        $week_dates = WeeklySummary::getWeekDates($current_week['year'], $current_week['week_number']);
        $recent_records = $timeclockrecord->getRecordsByUserAndDate($user->id, $week_dates['start_date'], $week_dates['end_date']);
        break;
    case 3: // All time (last 30 days)
        $date_start = date('Y-m-d', strtotime('-30 days'));
        $recent_records = $timeclockrecord->getRecordsByUserAndDate($user->id, $date_start, $today);
        break;
    default:
        $recent_records = $timeclockrecord->getRecordsByUserAndDate($user->id, $today, $today);
        break;
}

// Get available timeclock types for the interface
$timeclock_types = TimeclockType::getActiveTypes($db);
$default_type_id = TimeclockType::getDefaultType($db);

// Prepare data for template
$num_records = count($recent_records);

// Check if location is required
$require_location = TimeclockConfig::getValue($db, 'REQUIRE_LOCATION', 0);

// Get configuration values for display
$max_hours_per_day = TimeclockConfig::getValue($db, 'MAX_HOURS_PER_DAY', 12);
$overtime_threshold = TimeclockConfig::getValue($db, 'OVERTIME_THRESHOLD', 8);

// Calculate overtime alert for today
$overtime_alert = false;
if ($today_total_hours > $overtime_threshold) {
    $overtime_alert = true;
}

// Prepare JavaScript data
$js_data = array(
    'is_clocked_in' => $is_clocked_in,
    'clock_in_time' => $clock_in_time,
    'require_location' => $require_location,
    'default_type_id' => $default_type_id,
    'max_hours_per_day' => $max_hours_per_day,
    'overtime_threshold' => $overtime_threshold,
    'api_token' => newToken(),
    'user_id' => $user->id
);

// Fonction helper pour convertir les secondes en format lisible
if (!function_exists('convertSecondsToReadableTime')) {
    function convertSecondsToReadableTime($seconds) {
        if ($seconds <= 0) return '0h00';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return sprintf('%dh%02d', $hours, $minutes);
    }
}

// Additional helper function for duration formatting
if (!function_exists('formatDuration')) {
    function formatDuration($minutes) {
        if ($minutes <= 0) return '0h00';
        
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%dh%02d', $hours, $mins);
    }
}

// Set page title
$title = $langs->trans("TimeTracking");

// Include template
include "tpl/home.tpl";
?>