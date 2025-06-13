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

// Chargement classes nécessaires
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';

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
$filter_month = GETPOST('filter_month', 'int') ?: date('n'); // Mois actuel par défaut
$filter_year = GETPOST('filter_year', 'int') ?: date('Y');   // Année actuelle par défaut

try {
    // Récupération des rapports mensuels
    $monthlyReports = getMonthlyReports($db, $user, $filter_month, $filter_year);
    
    $data = [
        'monthly_reports' => $monthlyReports,
        'filter_month' => $filter_month,
        'filter_year' => $filter_year,
        'page_title' => $langs->trans('MonthlyReports')
    ];
    
} catch (Exception $e) {
    $error = 1;
    $errors[] = $e->getMessage();
    dol_syslog("ReportsPage Error: " . $e->getMessage(), LOG_ERR);
}

/**
 * Récupère les rapports mensuels pour tous les utilisateurs
 */
function getMonthlyReports($db, $user, $month, $year) {
    // Date de début et fin du mois
    $startDate = sprintf('%04d-%02d-01', $year, $month);
    $endDate = date('Y-m-t', strtotime($startDate)); // Dernier jour du mois
    
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
    
    // Si pas admin, limiter aux utilisateurs de l'équipe du manager
    if (!$user->admin && !empty($user->rights->appmobtimetouch->timeclock->readall)) {
        // Ici on pourrait ajouter une logique pour limiter aux équipes du manager
        // Pour l'instant, on affiche tous les utilisateurs actifs
    }
    
    $sql .= " GROUP BY u.rowid, u.firstname, u.lastname, u.login
              ORDER BY u.lastname, u.firstname";
    
    $resql = $db->query($sql);
    $reports = [];
    
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $reports[] = [
                'user_id' => $obj->user_id,
                'fullname' => trim($obj->firstname . ' ' . $obj->lastname),
                'login' => $obj->login,
                'total_seconds' => (int)$obj->total_seconds,
                'total_hours' => round($obj->total_seconds / 3600, 2),
                'total_records' => (int)$obj->total_records,
                'incomplete_records' => (int)$obj->incomplete_records
            ];
        }
        $db->free($resql);
    }
    
    return $reports;
}

// Configuration page et extraction des variables pour le template
$page_title = $data['page_title'] ?? $langs->trans('Reports');
$monthly_reports = $data['monthly_reports'] ?? [];
$filter_month = $data['filter_month'] ?? date('n');
$filter_year = $data['filter_year'] ?? date('Y');
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
    <link rel="stylesheet" href="css/onsenui.css">
    <link rel="stylesheet" href="css/onsen-css-components.min.css">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/mobile.css">
    
    <style>
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
        <ons-navigator id="myNavigator">
            <ons-page id="reportsPage">
                
                <!-- TopBar Rapports -->
                <ons-toolbar>
                    <div class="left">
                        <ons-toolbar-button onclick="document.getElementById('mySplitter').right.toggle()">
                            <ons-icon icon="md-menu"></ons-icon>
                        </ons-toolbar-button>
                    </div>
                    <div class="center"><?php echo $page_title; ?></div>
                    <div class="right">
                        <ons-toolbar-button onclick="refreshReports()">
                            <ons-icon icon="md-refresh"></ons-icon>
                        </ons-toolbar-button>
                    </div>
                </ons-toolbar>
                
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
                    <?php include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/reports/monthly.tpl'; ?>
                </div>
                
            </ons-page>
        </ons-navigator>
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
 * Apply filters
 */
function applyFilters() {
    const month = document.getElementById('filter_month').value;
    const year = document.getElementById('filter_year').value;
    
    // Show loading message
    ons.notification.toast('<?php echo $langs->trans("LoadingReports"); ?>...', {timeout: 1000});
    
    // Redirect with new filters
    setTimeout(() => {
        window.location.href = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/reports.php?filter_month=' + month + '&filter_year=' + year;
    }, 500);
}

// Debug
console.log('Reports page loaded');
console.log('Month:', <?php echo $filter_month; ?>);
console.log('Year:', <?php echo $filter_year; ?>);
console.log('Reports count:', <?php echo count($data['monthly_reports'] ?? []); ?>);
</script>

</body>
</html>