<?php
/**
 * Page Mes Enregistrements - Interface personnelle salariés
 * 
 * Point d'entrée pour consultation des enregistrements personnels
 * Réutilise list-all.tpl avec filtrage automatique sur l'utilisateur connecté
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
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/ValidationController.php';

// Vérification module activé
if (!isModEnabled('appmobtimetouch')) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}

// Vérification droits lecture (requis pour consulter ses propres enregistrements)
if (empty($user->rights->appmobtimetouch->timeclock->read)) {
    accessforbidden('Insufficient permissions for viewing records');
}

// Chargement traductions
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

// Variables pour template
$data = [];
$error = 0;
$errors = [];
$messages = [];

// Action par défaut : récupérer tous les enregistrements personnels
$action = GETPOST('action', 'alpha') ?: 'get_my_records';

try {
    switch($action) {
        case 'get_my_records':
        default:
            // Récupérer directement les enregistrements de l'utilisateur connecté
            $records = getMyTimeclockRecords($db, $user);
            
            // Calculer les statistiques personnelles
            $stats = calculatePersonalStats($records);
            
            $data = [
                'records' => $records,
                'stats' => $stats,
                'page_title' => $langs->trans('MyTimeclockRecords'),
                'view_type' => 'list_all',
                'is_personal_view' => true,
                'show_user_column' => false,
                'show_validation_actions' => false,
                'filters' => [] // Pas de filtres complexes pour vue personnelle
            ];
            
            break;
    }
    
} catch (Exception $e) {
    $error = 1;
    $errors[] = $e->getMessage();
    dol_syslog("MyRecordsPage Error: " . $e->getMessage(), LOG_ERR);
    
    // Données par défaut en cas d'erreur
    $data = [
        'page_title' => $langs->trans('MyTimeclockRecords'),
        'view_type' => 'list_all',
        'is_personal_view' => true,
        'show_user_column' => false,
        'show_validation_actions' => false,
        'records' => [],
        'stats' => [
            'today' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'with_anomalies' => 0
        ]
    ];
}

/**
 * Récupère les enregistrements de temps de l'utilisateur connecté
 */
function getMyTimeclockRecords($db, $user) {
    $sql = "SELECT r.*, u.firstname, u.lastname, u.login";
    $sql .= " FROM " . MAIN_DB_PREFIX . "timeclock_records r";
    $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user u ON r.fk_user = u.rowid";
    $sql .= " WHERE r.fk_user = " . (int)$user->id;
    $sql .= " AND r.status IN (1, 3)"; // Validé ou Terminé
    $sql .= " ORDER BY r.clock_in_time DESC";
    $sql .= " LIMIT 100"; // Limite pour éviter surcharge
    
    $resql = $db->query($sql);
    $records = [];
    
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            // Enrichir avec informations utilisateur
            $record = [
                'rowid' => $obj->rowid,
                'fk_user' => $obj->fk_user,
                'clock_in_time' => $obj->clock_in_time,
                'clock_out_time' => $obj->clock_out_time,
                'work_duration' => $obj->work_duration,
                'status' => $obj->status,
                'validation_status' => $obj->validated_by > 0 ? 1 : 0, // 1 si validé, 0 sinon
                'validated_by' => $obj->validated_by,
                'validated_date' => $obj->validated_date,
                'validation_comment' => $obj->validation_comment,
                'user' => [
                    'fullname' => trim($obj->firstname . ' ' . $obj->lastname),
                    'login' => $obj->login
                ],
                'anomalies' => [] // TODO: implémenter détection anomalies si nécessaire
            ];
            
            $records[] = $record;
        }
        $db->free($resql);
    }
    
    return $records;
}

/**
 * Calcule les statistiques personnelles pour l'utilisateur
 */
function calculatePersonalStats($records) {
    $today = date('Y-m-d');
    $stats = [
        'today' => 0,
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'with_anomalies' => 0
    ];
    
    foreach ($records as $record) {
        // Statistiques par date
        $recordDate = date('Y-m-d', strtotime($record['clock_in_time']));
        if ($recordDate === $today) {
            $stats['today']++;
        }
        
        // Statistiques par statut de validation
        if ($record['validated_by'] > 0) {
            $stats['approved']++;
        } else {
            $stats['pending']++;
        }
        
        // Anomalies (si implémentées)
        if (!empty($record['anomalies'])) {
            $stats['with_anomalies']++;
        }
    }
    
    return $stats;
}

// Configuration page et extraction des variables pour le template
$page_title = $data['page_title'] ?? $langs->trans('MyTimeclockRecords');
$records = $data['records'] ?? [];
$stats = $data['stats'] ?? [];
$filters = $data['filters'] ?? [];
$is_personal_view = $data['is_personal_view'] ?? true;
$show_user_column = $data['show_user_column'] ?? false;
$show_validation_actions = $data['show_validation_actions'] ?? false;

// Variables pour compatibilité rightmenu.tpl selon INDEX_HOME_COMPATIBILITY.md
$pending_validation_count = 0; // Pas utilisé dans myrecords mais requis pour rightmenu.tpl
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
    
    .myrecords-content {
        padding-bottom: 20px;
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
    
    /* Personal view adaptations */
    .personal-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .personal-stats .stats-item {
        text-align: center;
        padding: 10px;
    }
    
    .personal-stats .stats-value {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .personal-stats .stats-label {
        font-size: 12px;
        opacity: 0.9;
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
        <ons-page id="myRecordsPage">
            
            <!-- TopBar Mes Enregistrements -->
            <ons-toolbar>
                <div class="left">
                    <ons-toolbar-button onclick="goBackToHome()">
                        <ons-icon icon="md-arrow-back"></ons-icon>
                    </ons-toolbar-button>
                </div>
                <div class="center"><?php echo $page_title; ?></div>
                <div class="right">
                    <ons-toolbar-button onclick="refreshMyRecords()">
                        <ons-icon icon="md-refresh"></ons-icon>
                    </ons-toolbar-button>
                    <ons-toolbar-button onclick="toggleMenu()">
                        <ons-icon icon="md-menu"></ons-icon>
                    </ons-toolbar-button>
                </div>
            </ons-toolbar>
            
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

                <!-- Contenu Mes Enregistrements -->
                <div class="myrecords-content">
                    <?php include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/validation/list-all.tpl'; ?>
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
// === MY RECORDS FUNCTIONS ===

/**
 * Refresh my records
 */
function refreshMyRecords() {
    ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
    setTimeout(function() {
        location.reload();
    }, 500);
}

/**
 * Go back to home page
 */
function goBackToHome() {
    console.log('Going back to home page');
    
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
 * Show employee record details
 */
function showEmployeeRecordDetails(recordId) {
    console.log('=== showEmployeeRecordDetails ===');
    console.log('Record ID:', recordId);
    
    // Construction de l'URL vers employee-record-detail.php
    var currentPath = window.location.pathname;
    var detailUrl;
    
    if (currentPath.includes('/appmobtimetouch/')) {
        // URL relative depuis le module actuel
        detailUrl = './employee-record-detail.php?id=' + recordId + '&from=myrecords';
    } else {
        // URL absolue depuis detectBaseUrl
        var baseUrl = detectBaseUrl();
        detailUrl = baseUrl + '/custom/appmobtimetouch/employee-record-detail.php?id=' + recordId + '&from=myrecords';
    }
    
    console.log('Employee detail URL constructed:', detailUrl);
    
    // Message de chargement
    if (typeof ons !== 'undefined') {
        ons.notification.toast('Chargement des détails...', {timeout: 1000});
    }
    
    // Navigation vers les détails de l'enregistrement
    setTimeout(function() {
        window.location.href = detailUrl;
    }, 300);
}

/**
 * Toggle hamburger menu (réutilise la fonction reports.php)
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

// Debug et initialisation OnsenUI
console.log('My Records page loaded');
console.log('User ID:', <?php echo $user->id; ?>);
console.log('Records count:', <?php echo count($records); ?>);

// S'assurer que OnsenUI est initialisé
if (typeof ons !== 'undefined') {
    ons.ready(function() {
        console.log('OnsenUI ready on my records page');
        
        // Vérifier les éléments OnsenUI
        var splitter = document.getElementById('mySplitter');
        if (splitter) {
            console.log('Splitter found and ready');
        } else {
            console.error('Splitter not found!');
        }
    });
} else {
    console.warn('OnsenUI not available');
}
</script>

</body>
</html>