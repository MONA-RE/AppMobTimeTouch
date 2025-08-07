<?php
/**
 * Page Rapports Manager - Interface rapports mensuels
 * 
 * Point d'entrée pour les rapports de temps de travail
 * Respecte l'architecture SOLID avec injection de dépendances
 */

// Chargement Dolibarr
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Chargement classes nécessaires selon architecture SOLID
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/LocationHelper.php';

// Vérification module activé
if (!isModEnabled('appmobtimetouch')) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}

// Vérification droits export (requis pour les rapports)
if (empty($user->rights->appmobtimetouch->timeclock->export)) {
    accessforbidden('Insufficient permissions for reports');
}

// Chargement traductions
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

// Note: Services SOLID disponibles mais non utilisés pour cette page
// Le code utilise directement les requêtes SQL pour les rapports

// Variables pour template
$data = [];
$error = 0;
$errors = [];
$messages = [];

// Paramètres de filtre
$report_type = GETPOST('report_type', 'alpha') ?: 'monthly'; // Type de rapport par défaut
$filter_month = GETPOST('filter_month', 'int') ?: date('n'); // Mois actuel par défaut
$filter_year = GETPOST('filter_year', 'int') ?: date('Y');   // Année actuelle par défaut

try {
    // Récupération des rapports selon le type
    if ($report_type === 'annual') {
        $reports = getAnnualReports($db, $user, $filter_year);
        $page_title = $langs->trans('AnnualReports');
        $is_personal_view = (!$user->admin && empty($user->rights->appmobtimetouch->timeclock->readall));
        if ($is_personal_view) {
            $page_title = $langs->trans('MyAnnualReports');
        }
    } else {
        $reports = getMonthlyReports($db, $user, $filter_month, $filter_year);
        $page_title = $langs->trans('MonthlyReports');
        $is_personal_view = (!$user->admin && empty($user->rights->appmobtimetouch->timeclock->readall));
        if ($is_personal_view) {
            $page_title = $langs->trans('MyMonthlyReports');
        }
    }
    
    // Récupérer le statut de pointage pour la toolbar (comme index.php)
    $is_clocked_in = getUserClockStatus($db, $user);
    
    $data = [
        'reports' => $reports,
        'monthly_reports' => $report_type === 'monthly' ? $reports : [], // Pour compatibilité template
        'annual_reports' => $report_type === 'annual' ? $reports : [],
        'report_type' => $report_type,
        'filter_month' => $filter_month,
        'filter_year' => $filter_year,
        'page_title' => $page_title,
        'is_personal_view' => $is_personal_view,
        'is_clocked_in' => $is_clocked_in
    ];
    
} catch (Exception $e) {
    $error = 1;
    $errors[] = $e->getMessage();
    dol_syslog("ReportsPage Error: " . $e->getMessage(), LOG_ERR);
}

/**
 * Récupère les rapports annuels (year-to-date) pour tous les utilisateurs
 */
function getAnnualReports($db, $user, $year) {
    global $conf;
    
    // Date de début et fin de l'année (YTD - jusqu'à aujourd'hui)
    $startDate = sprintf('%04d-01-01', $year);
    $endDate = date('Y-m-d'); // Jusqu'à aujourd'hui pour YTD
    
    // Si on demande une année future ou si on est encore en janvier, prendre toute l'année
    if ($year > date('Y')) {
        $endDate = sprintf('%04d-12-31', $year);
    }
    
    // TK2507-0344: Calcul des heures théoriques annuelles
    $monthly_theoretical = !empty($conf->global->APPMOBTIMETOUCH_NB_HEURE_THEORIQUE_MENSUEL) ? 
        (int)$conf->global->APPMOBTIMETOUCH_NB_HEURE_THEORIQUE_MENSUEL : 140;
    
    // Pour YTD : heures théoriques = (mois écoulés) * heures mensuelles
    $current_month = ($year == date('Y')) ? date('n') : 12; // Si année actuelle, mois actuel, sinon 12 mois
    $theoretical_hours = $monthly_theoretical * $current_month;
    
    $sql = "SELECT 
                u.rowid as user_id,
                u.firstname,
                u.lastname,
                u.login,
                SUM(
                    CASE 
                        WHEN tr.clock_out_time IS NOT NULL 
                        THEN TIMESTAMPDIFF(SECOND, tr.clock_in_time, tr.clock_out_time)
                        ELSE 0 
                    END
                ) as total_seconds,
                COUNT(tr.rowid) as total_records,
                COUNT(CASE WHEN tr.clock_out_time IS NULL THEN 1 END) as incomplete_records
            FROM " . MAIN_DB_PREFIX . "user u
            LEFT JOIN " . MAIN_DB_PREFIX . "timeclock_records tr ON (
                tr.fk_user = u.rowid 
                AND DATE(tr.clock_in_time) >= '" . $db->escape($startDate) . "'
                AND DATE(tr.clock_in_time) <= '" . $db->escape($endDate) . "'
                AND tr.status IN (1, 3)
            )
            WHERE u.statut = 1";
    
    // Filtrage selon les permissions utilisateur (même logique que monthly)
    if (!$user->admin && empty($user->rights->appmobtimetouch->timeclock->readall)) {
        $sql .= " AND u.rowid = " . (int)$user->id;
    }
    
    $sql .= " GROUP BY u.rowid, u.firstname, u.lastname, u.login
              ORDER BY u.lastname, u.firstname";
    
    $resql = $db->query($sql);
    $reports = [];
    
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $worked_hours = round($obj->total_seconds / 3600, 2);
            $delta_hours = $worked_hours - $theoretical_hours;
            
            $reports[] = [
                'user_id' => $obj->user_id,
                'fullname' => trim($obj->firstname . ' ' . $obj->lastname),
                'login' => $obj->login,
                'total_seconds' => (int)$obj->total_seconds,
                'total_hours' => $worked_hours,
                'theoretical_hours' => $theoretical_hours,
                'delta_hours' => $delta_hours,
                'total_records' => (int)$obj->total_records,
                'incomplete_records' => (int)$obj->incomplete_records,
                'period_type' => 'annual',
                'year' => $year,
                'months_included' => $current_month
            ];
        }
        $db->free($resql);
    }
    
    return $reports;
}

/**
 * Récupère les rapports mensuels pour tous les utilisateurs
 */
function getMonthlyReports($db, $user, $month, $year) {
    global $conf;
    
    // Date de début et fin du mois
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate)); // Dernier jour du mois
    
    // TK2507-0344 MVP 4: Récupérer les heures théoriques mensuelles
    $theoretical_hours = !empty($conf->global->APPMOBTIMETOUCH_NB_HEURE_THEORIQUE_MENSUEL) ? 
        (int)$conf->global->APPMOBTIMETOUCH_NB_HEURE_THEORIQUE_MENSUEL : 140;
    
    $sql = "SELECT 
                u.rowid as user_id,
                u.firstname,
                u.lastname,
                u.login,
                SUM(
                    CASE 
                        WHEN tr.clock_out_time IS NOT NULL 
                        THEN TIMESTAMPDIFF(SECOND, tr.clock_in_time, tr.clock_out_time)
                        ELSE 0 
                    END
                ) as total_seconds,
                COUNT(tr.rowid) as total_records,
                COUNT(CASE WHEN tr.clock_out_time IS NULL THEN 1 END) as incomplete_records
            FROM " . MAIN_DB_PREFIX . "user u
            LEFT JOIN " . MAIN_DB_PREFIX . "timeclock_records tr ON (
                tr.fk_user = u.rowid 
                AND DATE(tr.clock_in_time) >= '" . $db->escape($startDate) . "'
                AND DATE(tr.clock_in_time) <= '" . $db->escape($endDate) . "'
                AND tr.status IN (1, 3)
            )
            WHERE u.statut = 1";
    
    // Filtrage selon les permissions utilisateur
    if (!$user->admin && empty($user->rights->appmobtimetouch->timeclock->readall)) {
        // Utilisateur normal (salarié) : voir seulement ses propres données
        $sql .= " AND u.rowid = " . (int)$user->id;
    } elseif (!$user->admin && !empty($user->rights->appmobtimetouch->timeclock->readall)) {
        // Manager non-admin : voir tous les utilisateurs actifs
        // Ici on pourrait ajouter une logique pour limiter aux équipes du manager
        // Pour l'instant, on affiche tous les utilisateurs actifs
    }
    // Admin : voir tous les utilisateurs (pas de filtre supplémentaire)
    
    $sql .= " GROUP BY u.rowid, u.firstname, u.lastname, u.login
              ORDER BY u.lastname, u.firstname";
    
    $resql = $db->query($sql);
    $reports = [];
    
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $worked_hours = round($obj->total_seconds / 3600, 2);
            $delta_hours = $worked_hours - $theoretical_hours;
            
            $reports[] = [
                'user_id' => $obj->user_id,
                'fullname' => trim($obj->firstname . ' ' . $obj->lastname),
                'login' => $obj->login,
                'total_seconds' => (int)$obj->total_seconds,
                'total_hours' => $worked_hours,
                'theoretical_hours' => $theoretical_hours,
                'delta_hours' => $delta_hours,
                'total_records' => (int)$obj->total_records,
                'incomplete_records' => (int)$obj->incomplete_records
            ];
        }
        $db->free($resql);
    }
    
    return $reports;
}

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

// Configuration page et extraction des variables pour le template
$page_title = $data['page_title'] ?? $langs->trans('Reports');
$reports = $data['reports'] ?? [];
$monthly_reports = $data['monthly_reports'] ?? [];
$annual_reports = $data['annual_reports'] ?? [];
$report_type = $data['report_type'] ?? 'monthly';
$filter_month = $data['filter_month'] ?? date('n');
$filter_year = $data['filter_year'] ?? date('Y');
$is_personal_view = $data['is_personal_view'] ?? false;
$is_clocked_in = $data['is_clocked_in'] ?? false;

// Variables pour compatibilité rightmenu.tpl selon INDEX_HOME_COMPATIBILITY.md
$pending_validation_count = 0; // Pas utilisé dans reports mais requis pour rightmenu.tpl
?>

<!DOCTYPE html>
<html lang="<?php echo $langs->getDefaultLang(); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    
    <title><?php echo $page_title; ?> - <?php echo $conf->global->MAIN_INFO_SOCIETE_NOM; ?></title>
    
    <!-- OnsenUI CSS -->
    <link rel="stylesheet" href="css/onsenui.min.css">
    <link rel="stylesheet" href="css/onsen-css-components.min.css">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">
    
    <style>
    /* Fix OnsenUI layout issues */
    .page__content {
        padding-top: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .reports-content {
        padding-bottom: 20px;
    }
    
    .reports-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    
    .reports-loading {
        opacity: 0.6;
        pointer-events: none;
    }
    
    .user-row:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .hours-display {
        font-weight: bold;
        color: #2196f3;
    }
    
    /* Ensure toolbar is visible */
    ons-toolbar {
        z-index: 10;
    }
    
    /* Hamburger menu button styling */
    ons-toolbar-button {
        color: #333;
    }
    
    ons-toolbar-button:active {
        background-color: rgba(0,0,0,0.1);
    }
    </style>
</head>
<body>

<!-- Application principale -->
<ons-splitter id="mySplitter">
    <ons-splitter-side id="sidemenu" side="right" width="250px" collapse="portrait" swipeable>
        <!-- Menu latéral -->
        <?php include 'tpl/parts/rightmenu.tpl'; ?>
    </ons-splitter-side>
    
    <ons-splitter-content>
        <ons-page id="reportsPage">
            
            <!-- TopBar avec même style que index.php -->
            <?php include 'tpl/parts/topbar-home.tpl'; ?>
            
            <!-- Contenu scrollable -->
            <div class="page__content">
                <!-- Messages d'erreur/succès -->
                <?php if ($error): ?>
                <div style="background: #ffebee; color: #c62828; padding: 10px; margin: 10px; border-radius: 5px;">
                    <?php foreach ($errors as $err): ?>
                    <div><?php echo dol_escape_htmltag($err); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($messages)): ?>
                <div style="background: #e8f5e8; color: #2e7d32; padding: 10px; margin: 10px; border-radius: 5px;">
                    <?php foreach ($messages as $msg): ?>
                    <div><?php echo dol_escape_htmltag($msg); ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Contenu Rapports -->
                <div class="reports-content">
                    <?php 
                    if ($report_type === 'annual') {
                        include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/reports/annual.tpl';
                    } else {
                        include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/reports/monthly.tpl';
                    }
                    ?>
                </div>
            </div>
            
        </ons-page>
    </ons-splitter-content>
</ons-splitter>

<!-- OnsenUI JavaScript -->
<script src="js/onsenui.min.js"></script>

<!-- Navigation JavaScript -->
<script src="js/navigation.js"></script>

<!-- TimeclockAPI JavaScript -->
<script src="js/timeclock-api.js"></script>

<script>
// === REPORTS FUNCTIONS ===

/**
 * Refresh reports
 */
function refreshReports() {
    ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
    setTimeout(function() {
        location.reload();
    }, 500);
}

/**
 * Go to home page (pour toolbar logo)
 */
function goToHome() {
    console.log('Going to home page from reports');
    
    // Navigation vers la page d'accueil
    var homeUrl;
    var currentPath = window.location.pathname;
    
    if (currentPath.includes('/appmobtimetouch/')) {
        homeUrl = './home.php';
    } else {
        var baseUrl = detectBaseUrl();
        homeUrl = baseUrl + '/custom/appmobtimetouch/home.php';
    }
    
    setTimeout(function() {
        window.location.href = homeUrl;
    }, 300);
}

/**
 * Toggle hamburger menu
 */
function toggleMenu() {
    console.log('Toggle hamburger menu');
    
    var sideMenu = document.getElementById('sidemenu');
    if (sideMenu) {
        console.log('Found side menu, toggling...');
        try {
            sideMenu.toggle();
            return;
        } catch (e) {
            console.error('Side menu toggle failed:', e);
        }
    }
    
    var splitter = document.getElementById('mySplitter');
    if (splitter && splitter.right) {
        console.log('Using splitter.right API...');
        try {
            splitter.right.toggle();
            return;
        } catch (e) {
            console.error('Splitter right toggle failed:', e);
        }
    }
    
    if (splitter) {
        console.log('Forcing splitter open...');
        try {
            splitter.openSide('right');
        } catch (e) {
            console.error('Force open failed:', e);
        }
    }
    
    console.error('All menu toggle methods failed');
}

/**
 * Apply filters
 */
function applyFilters() {
    const reportType = document.getElementById('report_type').value;
    const year = document.getElementById('filter_year').value;
    let url = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/reports.php?report_type=' + reportType + '&filter_year=' + year;
    
    // Ajouter le mois seulement pour les rapports mensuels
    if (reportType === 'monthly') {
        const month = document.getElementById('filter_month').value;
        url += '&filter_month=' + month;
    }
    
    // Show loading message
    ons.notification.toast('<?php echo $langs->trans("LoadingReports"); ?>...', {timeout: 1000});
    
    // Redirect with new filters
    setTimeout(() => {
        window.location.href = url;
    }, 500);
}

/**
 * Toggle month filter visibility based on report type
 */
function toggleMonthFilter() {
    const reportType = document.getElementById('report_type').value;
    const monthCol = document.getElementById('month_filter_col');
    
    if (monthCol) {
        monthCol.style.display = (reportType === 'monthly') ? 'block' : 'none';
    }
}

// Debug et initialisation OnsenUI
console.log('Reports page loaded');
console.log('Month:', <?php echo $filter_month; ?>);
console.log('Year:', <?php echo $filter_year; ?>);
console.log('Reports count:', <?php echo count($data['monthly_reports'] ?? []); ?>);

// S'assurer que OnsenUI est initialisé
if (typeof ons !== 'undefined') {
    ons.ready(function() {
        console.log('OnsenUI ready on reports page');
        
        // S'assurer que le splitter fonctionne
        var splitter = document.getElementById('mySplitter');
        if (splitter) {
            console.log('Splitter found and ready');
            console.log('Splitter methods available:', Object.getOwnPropertyNames(splitter));
            
            // Vérifier le menu latéral
            var sideMenu = document.getElementById('sidemenu');
            if (sideMenu) {
                console.log('Side menu found');
                console.log('Side menu methods:', Object.getOwnPropertyNames(sideMenu));
            } else {
                console.error('Side menu not found!');
            }
        } else {
            console.error('Splitter not found!');
        }
        
        // Test direct du bouton hamburger
        console.log('Testing hamburger button click...');
    });
} else {
    console.warn('OnsenUI not available');
}

// Exposer les fonctions globalement pour la toolbar
window.goToHome = goToHome;
window.refreshReports = refreshReports;
window.toggleMenu = toggleMenu;
</script>

</body>
</html>