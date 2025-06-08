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

// Load SOLID architecture components - Étape 3: Services métier avec interfaces (DIP + ISP)
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/TimeclockServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/DataServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/DataService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/TimeclockService.php';

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

// Initialize SOLID Services with Dependency Injection (DIP)
$dataService = new DataService($db);
$timeclockService = new TimeclockService($db, $dataService);

// Handle actions from mobile interface
if ($action) {
    dol_syslog("HOME.PHP DEBUG: Processing action: " . $action, LOG_DEBUG);
    
    if ($action == 'clockin' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
        // Prepare parameters using SOLID approach
        $params = [
            'timeclock_type_id' => GETPOST('timeclock_type_id', 'int'),
            'location' => GETPOST('location', 'alphanohtml'),
            'latitude' => GETPOST('latitude', 'float'),
            'longitude' => GETPOST('longitude', 'float'),
            'note' => GETPOST('note', 'restricthtml')
        ];

        dol_syslog("HOME.PHP DEBUG: Clock-in parameters - Type: " . $params['timeclock_type_id'] . ", Location: " . $params['location'], LOG_DEBUG);

        try {
            // Use SOLID TimeclockService with validation
            $result = $timeclockService->clockIn($user, $params);
            
            dol_syslog("HOME.PHP DEBUG: Clock-in success via SOLID service, record ID: " . $result, LOG_INFO);
            $messages[] = $langs->trans(TimeclockConstants::MSG_CLOCKIN_SUCCESS);
            
            // Redirect to avoid resubmission
            header('Location: '.$_SERVER['PHP_SELF'].'?clockin_success=1');
            exit;
            
        } catch (Exception $e) {
            $error++;
            $errors[] = $langs->trans($e->getMessage());
            dol_syslog("HOME.PHP DEBUG: Clock-in failed via SOLID service - Error: " . $e->getMessage(), LOG_ERROR);
        }
    }

    if ($action == 'clockout' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
        // Prepare parameters using SOLID approach
        $params = [
            'location' => GETPOST('location', 'alphanohtml'),
            'latitude' => GETPOST('latitude', 'float'),
            'longitude' => GETPOST('longitude', 'float'),
            'note' => GETPOST('note', 'restricthtml')
        ];

        dol_syslog("HOME.PHP DEBUG: Clock-out parameters - Location: " . $params['location'], LOG_DEBUG);

        try {
            // Use SOLID TimeclockService with validation
            $result = $timeclockService->clockOut($user, $params);
            
            dol_syslog("HOME.PHP DEBUG: Clock-out success via SOLID service, record ID: " . $result, LOG_INFO);
            $messages[] = $langs->trans(TimeclockConstants::MSG_CLOCKOUT_SUCCESS);
            
            // Redirect to avoid resubmission
            header('Location: '.$_SERVER['PHP_SELF'].'?clockout_success=1');
            exit;
            
        } catch (Exception $e) {
            $error++;
            $errors[] = $langs->trans($e->getMessage());
            dol_syslog("HOME.PHP DEBUG: Clock-out failed via SOLID service - Error: " . $e->getMessage(), LOG_ERROR);
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

// Get current user's active timeclock record using SOLID Service
$active_record = $timeclockService->getActiveRecord($user->id);
$is_clocked_in = !is_null($active_record);
$clock_in_time = null;
$current_duration = 0;

dol_syslog("HOME.PHP DEBUG: Active record from SOLID service: " . ($active_record ? 'found' : 'none'), LOG_DEBUG);

if ($active_record) {
    dol_syslog("HOME.PHP DEBUG: Found active record via SOLID service", LOG_DEBUG);
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

// Get today's summary using SOLID DataService
$today_summary = $dataService->calculateTodaySummary($user->id);
$today_total_hours = $today_summary['total_hours'] ?? 0;
$today_total_breaks = $today_summary['total_breaks'] ?? 0;

dol_syslog("HOME.PHP DEBUG: Today's summary via SOLID service - Hours: " . $today_total_hours . ", Breaks: " . $today_total_breaks, LOG_DEBUG);

// Add active record duration to today's total
if ($is_clocked_in && $current_duration > 0) {
    $active_duration_hours = $current_duration / 3600; // Convert seconds to hours
    $today_total_hours += $active_duration_hours;
    dol_syslog("HOME.PHP DEBUG: Added active duration to today's total: " . $active_duration_hours . " hours", LOG_DEBUG);
}

// Get weekly summary using SOLID DataService
$weekly_summary = $dataService->calculateWeeklySummary($user->id);

if ($weekly_summary) {
    dol_syslog("HOME.PHP DEBUG: Weekly summary from SOLID service - Week: " . $weekly_summary->year . "-W" . $weekly_summary->week_number, LOG_DEBUG);
} else {
    dol_syslog("HOME.PHP DEBUG: No weekly summary available from SOLID service", LOG_DEBUG);
}

// Get recent records using SOLID DataService
$recent_records = $dataService->getRecentRecords($user->id, 5);

dol_syslog("HOME.PHP DEBUG: Recent records from SOLID service count: " . count($recent_records), LOG_DEBUG);

// Get available timeclock types using SOLID DataService
$timeclock_types = $dataService->getActiveTimeclockTypes();
$default_type_id = $dataService->getDefaultTimeclockType();

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