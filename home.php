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
dol_include_once('/appmobtimetouch/class/weekly_summary.class.php');

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "users", "companies"));

// Get parameters
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

// Get current week summary
$current_week = WeeklySummary::getCurrentWeek();
$weekly_summary = $weeklysummary->getWeeklySummaryByUserAndWeek($user->id, $current_week['year'], $current_week['week_number']);

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

// Get available timeclock types
$timeclock_types = TimeclockType::getActiveTypes($db);
$default_type_id = TimeclockType::getDefaultType($db);

// Prepare data for template
$num_records = count($recent_records);

// Fonction helper pour convertir les secondes en format lisible
if (!function_exists('convertSecondsToReadableTime')) {
    function convertSecondsToReadableTime($seconds) {
        if ($seconds <= 0) return '0h00';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return sprintf('%dh%02d', $hours, $minutes);
    }
}

// Include template
include "tpl/home.tpl";
?>