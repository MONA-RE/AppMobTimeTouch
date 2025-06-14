<?php
/**
 * home.php simplifié - Page d'accueil compatible avec structure index.php
 * 
 * Version simplifiée pour éviter les conflits avec index.php/tabbar.tpl
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Vérification module activé
if (!isModEnabled('appmobtimetouch')) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}

// Vérification droits lecture
if (empty($user->rights->appmobtimetouch->timeclock->read)) {
    accessforbidden('Insufficient permissions for timeclock records');
}

// Chargement traductions
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

/**
 * Récupère le statut de pointage de l'utilisateur (pour toolbar)
 */
function getUserClockStatus($db, $user) {
    $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "timeclock_records";
    $sql .= " WHERE fk_user = " . (int)$user->id;
    $sql .= " AND status = 2"; // Status 2 = En cours (pointé)
    $sql .= " AND clock_out_time IS NULL";
    $sql .= " ORDER BY clock_in_time DESC LIMIT 1";
    
    $resql = $db->query($sql);
    if ($resql) {
        $num = $db->num_rows($resql);
        $db->free($resql);
        return $num > 0;
    }
    
    return false;
}

// Variables minimales pour le template
$is_clocked_in = getUserClockStatus($db, $user);
$page_title = $langs->trans('Home');
$error = 0;
$errors = [];
$messages = [];

// Variables pour compatibilité rightmenu.tpl
$pending_validation_count = 0;

// Variables pour compatibilité home.tpl (valeurs par défaut)
$view = 'home';
$active_record = null;
$clock_in_time = null;
$current_duration = 0;
$today_total_hours = 0;
$today_total_breaks = 0;
$today_summary = ['total_hours' => 0, 'total_breaks' => 0];
$weekly_summary = ['total_hours' => 0, 'days_worked' => 0];
$recent_records = [];
$timeclock_types = [];
$default_type_id = 1;
$require_location = 0;
$overtime_threshold = 8;
$max_hours_per_day = 12;
$overtime_alert = false;

// Données JavaScript simplifiées
$js_data = [
    'is_clocked_in' => $is_clocked_in,
    'clock_in_time' => $clock_in_time,
    'require_location' => $require_location,
    'default_type_id' => $default_type_id,
    'max_hours_per_day' => $max_hours_per_day,
    'overtime_threshold' => $overtime_threshold,
    'api_token' => function_exists('newToken') ? newToken() : '',
    'user_id' => $user->id,
    'version' => '1.0'
];

// Inclusion du template
include "tpl/home.tpl";
?>