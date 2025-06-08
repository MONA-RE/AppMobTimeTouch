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

// DEBUG: Log du début de home.php
dol_syslog("HOME.PHP DEBUG: Starting home.php execution", LOG_DEBUG);

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

// Load SOLID architecture components - Étape 1: Configuration centralisée
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';

// Load SOLID architecture components - Étape 2: Helpers utilitaires (SRP + OCP)
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/LocationHelper.php';

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "users", "companies", "errors"));

// Get parameters
$action = GETPOST('action', 'aZ09');
$view = GETPOST('view','int'); // 1 = today, 2 = this week, 3 = all time
$targetId = "feedMyTimeclock";

// DEBUG: Log des paramètres
dol_syslog("HOME.PHP DEBUG: Action = " . $action . ", View = " . $view, LOG_DEBUG);

// Set default values if not provided
if (empty($view)) $view = 1;

// Security check - vérifier que le module est activé
if (!isModEnabled('appmobtimetouch')) {
    dol_syslog("HOME.PHP DEBUG: Module not enabled", LOG_WARNING);
    accessforbidden('Module not enabled');
}

// Security check - vérifier les droits
if (!$user->rights->appmobtimetouch->timeclock->read) {
    dol_syslog("HOME.PHP DEBUG: User has no read rights", LOG_WARNING);
    accessforbidden();
}

// DEBUG: Log des droits utilisateur
dol_syslog("HOME.PHP DEBUG: User ID = " . $user->id . ", Read rights = " . ($user->rights->appmobtimetouch->timeclock->read ? 'true' : 'false'), LOG_DEBUG);

// Initialize variables for messages
$error = 0;
$errors = array();
$message = '';
$messages = array();

// Handle actions from mobile interface
if ($action) {
    dol_syslog("HOME.PHP DEBUG: Processing action: " . $action, LOG_DEBUG);
    
    if ($action == 'clockin' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
        $timeclock_type_id = GETPOST('timeclock_type_id', 'int');
        $location = GETPOST('location', 'alphanohtml');
        $latitude = GETPOST('latitude', 'float');
        $longitude = GETPOST('longitude', 'float');
        $note = GETPOST('note', 'restricthtml');

        dol_syslog("HOME.PHP DEBUG: Clock-in parameters - Type: " . $timeclock_type_id . ", Location: " . $location, LOG_DEBUG);

        // Validate required location if configured - Using SOLID Constants
        $require_location = TimeclockConstants::getValue($db, TimeclockConstants::REQUIRE_LOCATION, 0);
        if ($require_location && (empty($latitude) || empty($longitude))) {
            $error++;
            $errors[] = $langs->trans(TimeclockConstants::MSG_LOCATION_REQUIRED);
            dol_syslog("HOME.PHP DEBUG: Location required but not provided", LOG_WARNING);
        }

        if (!$error) {
            $timeclockrecord = new TimeclockRecord($db);
            $result = $timeclockrecord->clockIn($user, $timeclock_type_id, $location, $latitude, $longitude, $note);
            
            dol_syslog("HOME.PHP DEBUG: Clock-in result: " . $result, LOG_DEBUG);
            
            if ($result > 0) {
                $messages[] = $langs->trans(TimeclockConstants::MSG_CLOCKIN_SUCCESS);
                dol_syslog("HOME.PHP DEBUG: Clock-in success, redirecting", LOG_DEBUG);
                // Redirect to avoid resubmission
                header('Location: '.$_SERVER['PHP_SELF'].'?clockin_success=1');
                exit;
            } else {
                $error++;
                $errors[] = !empty($timeclockrecord->error) ? $langs->trans($timeclockrecord->error) : $langs->trans(TimeclockConstants::MSG_CLOCKIN_ERROR);
                dol_syslog("HOME.PHP DEBUG: Clock-in failed - Error: " . $timeclockrecord->error, LOG_ERROR);
            }
        }
    }

    if ($action == 'clockout' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
        $location = GETPOST('location', 'alphanohtml');
        $latitude = GETPOST('latitude', 'float');
        $longitude = GETPOST('longitude', 'float');
        $note = GETPOST('note', 'restricthtml');

        dol_syslog("HOME.PHP DEBUG: Clock-out parameters - Location: " . $location, LOG_DEBUG);

        $timeclockrecord = new TimeclockRecord($db);
        $result = $timeclockrecord->clockOut($user, $location, $latitude, $longitude, $note);
        
        dol_syslog("HOME.PHP DEBUG: Clock-out result: " . $result, LOG_DEBUG);
        
        if ($result > 0) {
            $messages[] = $langs->trans(TimeclockConstants::MSG_CLOCKOUT_SUCCESS);
            dol_syslog("HOME.PHP DEBUG: Clock-out success, redirecting", LOG_DEBUG);
            // Redirect to avoid resubmission
            header('Location: '.$_SERVER['PHP_SELF'].'?clockout_success=1');
            exit;
        } else {
            $error++;
            $errors[] = !empty($timeclockrecord->error) ? $langs->trans($timeclockrecord->error) : $langs->trans(TimeclockConstants::MSG_CLOCKOUT_ERROR);
            dol_syslog("HOME.PHP DEBUG: Clock-out failed - Error: " . $timeclockrecord->error, LOG_ERROR);
        }
    }
}

// Handle success messages from redirects - Using SOLID Constants
if (GETPOST('clockin_success', 'int')) {
    $messages[] = $langs->trans(TimeclockConstants::MSG_CLOCKIN_SUCCESS);
    dol_syslog("HOME.PHP DEBUG: Clock-in success message displayed", LOG_DEBUG);
}
if (GETPOST('clockout_success', 'int')) {
    $messages[] = $langs->trans(TimeclockConstants::MSG_CLOCKOUT_SUCCESS);
    dol_syslog("HOME.PHP DEBUG: Clock-out success message displayed", LOG_DEBUG);
}

// Initialize time tracking objects
$timeclockrecord = new TimeclockRecord($db);
$weeklysummary = new WeeklySummary($db);

dol_syslog("HOME.PHP DEBUG: Time tracking objects initialized", LOG_DEBUG);

// Get current user's active timeclock record
$active_record_id = $timeclockrecord->getActiveRecord($user->id);
$active_record = null;
$is_clocked_in = false;
$clock_in_time = null;
$current_duration = 0;

dol_syslog("HOME.PHP DEBUG: Active record ID: " . $active_record_id, LOG_DEBUG);

if ($active_record_id > 0) {
    dol_syslog("HOME.PHP DEBUG: Found active record, fetching details", LOG_DEBUG);
    
    $active_record = new TimeclockRecord($db);
    if ($active_record->fetch($active_record_id) > 0) {
        dol_syslog("HOME.PHP DEBUG: Active record fetched successfully", LOG_DEBUG);
        dol_syslog("HOME.PHP DEBUG: Raw clock_in_time from DB: " . $active_record->clock_in_time, LOG_DEBUG);
        
        $is_clocked_in = true;
        
        // CORRECTION: Gestion intelligente du timestamp selon le format
        $raw_timestamp = $active_record->clock_in_time;
        
        // Méthode 1: Vérifier si c'est déjà un timestamp Unix valide
        if (is_numeric($raw_timestamp) && $raw_timestamp > 946684800 && $raw_timestamp < 4102444800) {
            // C'est déjà un timestamp Unix valide (entre 2000 et 2100)
            $clock_in_time = (int) $raw_timestamp;
            dol_syslog("HOME.PHP DEBUG: Raw value is already a valid Unix timestamp: " . $clock_in_time, LOG_DEBUG);
        } else {
            // Méthode 2: Essayer la conversion jdate pour les formats de date Dolibarr
            $clock_in_time = $db->jdate($raw_timestamp);
            dol_syslog("HOME.PHP DEBUG: Converted with jdate: " . $clock_in_time, LOG_DEBUG);
            
            // Méthode 3: Fallback avec strtotime si jdate échoue
            if (empty($clock_in_time) || !is_numeric($clock_in_time)) {
                $clock_in_time = strtotime($raw_timestamp);
                dol_syslog("HOME.PHP DEBUG: Fallback conversion with strtotime: " . $clock_in_time, LOG_DEBUG);
        
                // Validation du résultat strtotime
                if ($clock_in_time === false || $clock_in_time <= 0) {
                    $clock_in_time = null;
                    dol_syslog("HOME.PHP DEBUG: All conversion methods failed", LOG_ERROR);
                }
            }
        }
        
        dol_syslog("HOME.PHP DEBUG: Final clock_in_time: " . $clock_in_time, LOG_DEBUG);
        dol_syslog("HOME.PHP DEBUG: Type of clock_in_time: " . gettype($clock_in_time), LOG_DEBUG);
        
        // Calcul de la durée si on a un timestamp valide
        if (!empty($clock_in_time) && is_numeric($clock_in_time) && $clock_in_time > 0) {
            $current_timestamp = dol_now();
            $current_duration = $current_timestamp - $clock_in_time;
            
            dol_syslog("HOME.PHP DEBUG: Current timestamp: " . $current_timestamp, LOG_DEBUG);
            dol_syslog("HOME.PHP DEBUG: Clock in timestamp: " . $clock_in_time, LOG_DEBUG);
            dol_syslog("HOME.PHP DEBUG: Current duration calculated: " . $current_duration . " seconds", LOG_DEBUG);
            
            // Validation que la durée est raisonnable (pas plus de 24h et pas négative)
            if ($current_duration < 0) {
                dol_syslog("HOME.PHP DEBUG: Warning: Negative duration detected, clock_in_time seems to be in the future", LOG_WARNING);
                $current_duration = 0;
            } elseif ($current_duration > 86400) {
                dol_syslog("HOME.PHP DEBUG: Warning: Duration over 24 hours (" . ($current_duration/3600) . "h), possible data issue", LOG_WARNING);
            }
        } else {
            $current_duration = 0;
            dol_syslog("HOME.PHP DEBUG: Unable to calculate duration - invalid clock_in_time", LOG_WARNING);
}
    } else {
        dol_syslog("HOME.PHP DEBUG: Failed to fetch active record details", LOG_ERROR);
    }
} else {
    dol_syslog("HOME.PHP DEBUG: No active record found for user", LOG_DEBUG);
}

// DEBUG: Log des variables finales pour l'affichage
dol_syslog("HOME.PHP DEBUG: Final values - is_clocked_in: " . ($is_clocked_in ? 'true' : 'false'), LOG_DEBUG);
dol_syslog("HOME.PHP DEBUG: Final values - clock_in_time: " . $clock_in_time, LOG_DEBUG);
dol_syslog("HOME.PHP DEBUG: Final values - current_duration: " . $current_duration, LOG_DEBUG);

// Test de la fonction TimeHelper::convertSecondsToReadableTime (SOLID Helper)
if ($current_duration > 0) {
    $duration_readable = TimeHelper::convertSecondsToReadableTime($current_duration);
    dol_syslog("HOME.PHP DEBUG: Duration readable: " . $duration_readable, LOG_DEBUG);
} else {
    dol_syslog("HOME.PHP DEBUG: Current duration is 0 or negative, skipping readable conversion", LOG_DEBUG);
}

// Get today's summary
$today = date('Y-m-d');
$today_records = $timeclockrecord->getRecordsByUserAndDate($user->id, $today, $today, 3); // STATUS_COMPLETED
$today_total_hours = 0;
$today_total_breaks = 0;

dol_syslog("HOME.PHP DEBUG: Getting today's records for date: " . $today, LOG_DEBUG);

foreach ($today_records as $record) {
    if (!empty($record->work_duration) && is_numeric($record->work_duration)) {
        $today_total_hours += $record->work_duration / 60; // Convert minutes to hours
    }
    if (!empty($record->break_duration) && is_numeric($record->break_duration)) {
        $today_total_breaks += $record->break_duration;
    }
}

dol_syslog("HOME.PHP DEBUG: Today's totals - Hours: " . $today_total_hours . ", Breaks: " . $today_total_breaks, LOG_DEBUG);

// Add active record duration to today's total
if ($is_clocked_in && $current_duration > 0) {
    $active_duration_hours = $current_duration / 3600; // Convert seconds to hours
    $today_total_hours += $active_duration_hours;
    dol_syslog("HOME.PHP DEBUG: Added active duration to today's total: " . $active_duration_hours . " hours", LOG_DEBUG);
}

// Get current week summary
$current_week = WeeklySummary::getCurrentWeek();
$weekly_summary = null;

dol_syslog("HOME.PHP DEBUG: Current week: " . $current_week['year'] . "-W" . $current_week['week_number'], LOG_DEBUG);

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
    dol_syslog("HOME.PHP DEBUG: Existing weekly summary found", LOG_DEBUG);
} else {
    dol_syslog("HOME.PHP DEBUG: No existing weekly summary, creating temporary one", LOG_DEBUG);
    
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
        if (!empty($record->work_duration) && is_numeric($record->work_duration)) {
            $weekly_total_hours += $record->work_duration / 60;
        }
        if (!empty($record->break_duration) && is_numeric($record->break_duration)) {
            $weekly_total_breaks += $record->break_duration;
        }
        
        // CORRECTION: Conversion correcte pour la date de travail
        $work_date_timestamp = $db->jdate($record->clock_in_time);
        if (!empty($work_date_timestamp)) {
            $work_date = date('Y-m-d', $work_date_timestamp);
        if (!in_array($work_date, $days_worked)) {
            $days_worked[] = $work_date;
        }
    }
    }
    
    // Add today's active time if it's in current week
    if ($is_clocked_in && $today >= $week_dates['start_date'] && $today <= $week_dates['end_date']) {
        $weekly_total_hours += $current_duration / 3600;
        if (!in_array($today, $days_worked)) {
            $days_worked[] = $today;
        }
        dol_syslog("HOME.PHP DEBUG: Added active time to weekly summary", LOG_DEBUG);
    }
    
    $weekly_summary->total_hours = round($weekly_total_hours, 2);
    $weekly_summary->total_breaks = $weekly_total_breaks;
    $weekly_summary->days_worked = count($days_worked);
    $weekly_summary->expected_hours = 40; // Default - could be configured
    $weekly_summary->overtime_hours = max(0, $weekly_summary->total_hours - $weekly_summary->expected_hours);
    $weekly_summary->status = 0; // In progress
    
    dol_syslog("HOME.PHP DEBUG: Weekly summary calculated - Total hours: " . $weekly_summary->total_hours, LOG_DEBUG);
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

dol_syslog("HOME.PHP DEBUG: Recent records count: " . count($recent_records), LOG_DEBUG);

// Get available timeclock types for the interface
$timeclock_types = TimeclockType::getActiveTypes($db);
$default_type_id = TimeclockType::getDefaultType($db);

dol_syslog("HOME.PHP DEBUG: Timeclock types count: " . count($timeclock_types) . ", Default type: " . $default_type_id, LOG_DEBUG);

// Prepare data for template
$num_records = count($recent_records);

// Check if location is required - Using SOLID Constants
$require_location = TimeclockConstants::getValue($db, TimeclockConstants::REQUIRE_LOCATION, 0);

// Get configuration values for display - Using SOLID Constants
$max_hours_per_day = TimeclockConstants::getValue($db, TimeclockConstants::MAX_HOURS_PER_DAY, TimeclockConstants::DEFAULT_MAX_HOURS);
$overtime_threshold = TimeclockConstants::getValue($db, TimeclockConstants::OVERTIME_THRESHOLD, TimeclockConstants::DEFAULT_OVERTIME_THRESHOLD);

// Calculate overtime alert for today
$overtime_alert = false;
if ($today_total_hours > $overtime_threshold) {
    $overtime_alert = true;
    dol_syslog("HOME.PHP DEBUG: Overtime alert triggered - Hours: " . $today_total_hours . ", Threshold: " . $overtime_threshold, LOG_DEBUG);
}

// Prepare JavaScript data with proper type conversion
$js_data = array(
    'is_clocked_in' => $is_clocked_in,
    'clock_in_time' => $clock_in_time ? (int) $clock_in_time : null, // S'assurer que c'est un entier ou null
    'require_location' => $require_location,
    'default_type_id' => $default_type_id,
    'max_hours_per_day' => $max_hours_per_day,
    'overtime_threshold' => $overtime_threshold,
    'api_token' => newToken(),
    'user_id' => $user->id,
    'version' => isset($version) ? $version : '1.0' // Add version for debugging with fallback
);

dol_syslog("HOME.PHP DEBUG: JS data prepared - is_clocked_in: " . ($js_data['is_clocked_in'] ? 'true' : 'false') . ", clock_in_time: " . $js_data['clock_in_time'], LOG_DEBUG);

// SOLID Architecture - Étape 2: Fonctions helper migrées vers classes utilitaires
// Les fonctions convertSecondsToReadableTime() et formatDuration() sont maintenant disponibles via :
// - TimeHelper::convertSecondsToReadableTime($seconds)
// - TimeHelper::formatDuration($minutes)
// - LocationHelper::validateCoordinates($lat, $lon)
// - LocationHelper::calculateDistance($lat1, $lon1, $lat2, $lon2)
// Principe SRP: Responsabilité unique par classe helper
// Principe OCP: Extensible sans modification du code existant

// Set page title
$title = $langs->trans("TimeTracking");

dol_syslog("HOME.PHP DEBUG: About to include template - All variables prepared", LOG_DEBUG);

// Include template
include "tpl/home.tpl";
?>