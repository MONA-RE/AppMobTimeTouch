<?php
/**
 * Page Validation Manager - MVP 3.1 Interface testable
 * 
 * Point d'entrée pour le dashboard manager de validation
 * Respecte l'architecture SOLID avec injection de dépendances
 */

// Chargement Dolibarr
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Chargement classes nécessaires
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/ValidationController.php';

// Vérification module activé
if (!isModEnabled('appmobtimetouch')) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}

// Vérification droits validation
if (empty($user->rights->appmobtimetouch->timeclock->validate)) {
    accessforbidden('Insufficient permissions for validation');
}

// Chargement traductions
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

// Initialisation contrôleur avec injection dépendances (DIP)
$controller = new ValidationController($db, $user, $langs, $conf);

// Action demandée
$action = GETPOST('action', 'alpha');

// Variables pour template
$data = [];
$error = 0;
$errors = [];
$messages = [];

// Récupérer le statut de pointage pour la toolbar (comme dans les autres pages)
$is_clocked_in = getUserClockStatus($db, $user);

// Gestion des actions
try {
    switch ($action) {
        case 'dashboard':
        default:
            // MVP 3.1 : Dashboard manager
            $data = $controller->dashboard();
            // Ajouter le statut de pointage aux données
            $data['is_clocked_in'] = $is_clocked_in;
            break;
            
        case 'validate_record':
            // MVP 3.2 : Validation individuelle avec retour JSON
            $result = $controller->validateRecord();
            
            // Si c'est une requête AJAX, retourner JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            
            // Sinon traitement normal avec redirection
            if ($result['error']) {
                $errors = $result['errors'];
                $error = 1;
            } else {
                $messages = $result['messages'] ?? ['Validation completed'];
            }
            // Redirection vers dashboard après action
            $data = $controller->dashboard();
            break;
            
        case 'get_record_details':
            // MVP 3.2 : Récupération détails enregistrement
            $result = $controller->getRecordDetails();
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
            break;
            
        case 'batch_validate':
            // MVP 3.3 : Validation en lot avec retour JSON
            $result = $controller->batchValidate();
            
            // Si c'est une requête AJAX, retourner JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            }
            
            // Sinon traitement normal avec redirection
            if ($result['error']) {
                $errors = $result['errors'];
                $error = 1;
            } else {
                $messages = $result['messages'] ?? ['Batch validation completed'];
            }
            // Redirection vers dashboard après action
            $data = $controller->dashboard();
            break;
            
        case 'get_all_pending':
            // MVP 3.3 : Récupération de tous les enregistrements en attente
            try {
                $result = $controller->getAllPending();
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } catch (Exception $e) {
                dol_syslog("validation.php: Error in get_all_pending - " . $e->getMessage(), LOG_ERROR);
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'error' => 1,
                    'errors' => ['Internal server error: ' . $e->getMessage()]
                ]);
                exit;
            }
            break;
            
        case 'viewRecord':
            // MVP 3.2 : Affichage page détail enregistrement avec actions validation
            $recordId = GETPOST('id', 'int');
            if (!$recordId) {
                setEventMessages($langs->trans('InvalidRecordId'), null, 'errors');
                header('Location: validation.php');
                exit;
            }
            
            $data = $controller->viewRecord($recordId);
            break;
            
        case 'list_all':
            // MVP 3.3 : Page liste complète avec filtres
            $data = $controller->listAll();
            break;
    }
    
    // Merger données et messages
    if (isset($data['error'])) {
        $error = $data['error'];
        $errors = array_merge($errors, $data['errors'] ?? []);
        $messages = array_merge($messages, $data['messages'] ?? []);
    }
    
} catch (Exception $e) {
    $error = 1;
    $errors[] = $e->getMessage();
    dol_syslog("ValidationPage Error: " . $e->getMessage(), LOG_ERR);
    
    // Fallback vers dashboard vide
    $data = [
        'page_title' => $langs->trans('ValidationDashboard'),
        'pending_records' => [],
        'notifications' => [],
        'stats' => ['total_pending' => 0, 'with_anomalies' => 0, 'urgent_count' => 0, 'today_pending' => 0],
        'is_manager' => true
    ];
}

// Préparation des actions pour la topbar validation
$page_actions = array(
    array(
        'icon' => 'md-refresh',
        'onclick' => 'refreshDashboard()',
        'title' => $langs->trans('Refresh')
    )
);

// Configuration pour le layout mobile
$isValidationPage = true;
$showTabbar = false; // Pas de tabbar sur la page validation
$useValidationTopbar = true;

// Debug MVP 3.1
if ($conf->global->MAIN_FEATURES_LEVEL >= 2) {
    dol_syslog('DEBUG MVP 3.1: ValidationManager Dashboard', LOG_DEBUG);
    dol_syslog('Action: ' . $action, LOG_DEBUG);
    dol_syslog('Manager ID: ' . $user->id, LOG_DEBUG);
    dol_syslog('Pending records: ' . count($data['pending_records'] ?? []), LOG_DEBUG);
    dol_syslog('Notifications: ' . count($data['notifications'] ?? []), LOG_DEBUG);
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

// S'assurer que $is_clocked_in est disponible pour le template
if (!isset($data['is_clocked_in'])) {
    $data['is_clocked_in'] = $is_clocked_in;
}

// Variables pour compatibilité rightmenu.tpl
$pending_validation_count = count($data['pending_records'] ?? []);

// Préparer les variables pour templates
extract($data);
?>

<!DOCTYPE html>
<html lang="<?php echo $langs->getDefaultLang(); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title><?php echo $langs->trans('ValidationManager'); ?> - <?php echo $conf->global->MAIN_INFO_SOCIETE_NOM; ?></title>
    
    <!-- OnsenUI CSS -->
    <link rel="stylesheet" href="css/onsenui.min.css">
    <link rel="stylesheet" href="css/onsen-css-components.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="css/font_awesome/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">
    
    <!-- Validation Manager specific styles -->
    <style>
    .validation-dashboard {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    
    .validation-stat-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    
    .validation-priority-indicator {
        width: 6px;
        height: 100%;
        border-radius: 3px;
        margin-right: 10px;
    }
    
    .validation-notification-badge {
        background-color: #ffc107;
        color: #856404;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .validation-loading {
        opacity: 0.6;
        pointer-events: none;
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
            <ons-page id="validationPage">
                
                <!-- TopBar avec même style que les autres pages -->
                <?php include 'tpl/parts/topbar-home.tpl'; ?>
                
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

                <!-- Contenu dynamique selon view_type -->
                <div class="validation-content">
                    <?php 
                    $viewType = $data['view_type'] ?? 'dashboard';
                    switch ($viewType) {
                        case 'record_detail':
                            include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/validation/record-detail.tpl';
                            break;
                        case 'list_all':
                            include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/validation/list-all.tpl';
                            break;
                        case 'dashboard':
                        default:
                            include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/validation/dashboard.tpl';
                            break;
                    }
                    ?>
                </div>
                
            </ons-page>
        </ons-navigator>
    </ons-splitter-content>
</ons-splitter>

<!-- OnsenUI JavaScript -->
<script src="js/onsenui.min.js"></script>

<!-- Navigation JavaScript -->
<script src="js/navigation.js"></script>

<!-- TimeClock API JavaScript -->
<script src="js/timeclock-api.js"></script>

<!-- Configuration globale pour validation -->
<script>
// Configuration validation manager
window.validationConfig = {
    isManager: true,
    totalPending: <?php echo count($data['pending_records'] ?? []); ?>,
    withAnomalies: <?php echo $data['stats']['with_anomalies'] ?? 0; ?>,
    urgentCount: <?php echo $data['stats']['urgent_count'] ?? 0; ?>,
    token: '<?php echo newToken(); ?>'
};

// Initialisation app validation MVP 3.1
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Validation Manager MVP 3.1 Initialized ===');
    
    // Log des données chargées
    console.log('Dashboard data loaded:');
    console.log('- Pending records: ' + window.validationConfig.totalPending);
    console.log('- With anomalies: ' + window.validationConfig.withAnomalies);
    console.log('- Urgent count: ' + window.validationConfig.urgentCount);
    console.log('- Statistics: <?php echo json_encode($data['stats'] ?? []); ?>');
    
    // Message de bienvenue MVP 3.1
    setTimeout(function() {
        <?php if (count($data['pending_records'] ?? []) > 0): ?>
        ons.notification.toast('<?php echo $langs->trans("WelcomeValidationManager"); ?> - <?php echo count($data['pending_records']); ?> validation(s) en attente', {timeout: 3000});
        <?php else: ?>
        ons.notification.toast('<?php echo $langs->trans("WelcomeValidationManager"); ?> - Aucune validation en attente', {timeout: 3000});
        <?php endif; ?>
    }, 500);
    
    // Initialiser TimeclockAPI si disponible
    if (window.TimeclockAPI) {
        window.TimeclockAPI.init({
            apiToken: window.validationConfig.token,
            DEBUG: true
        });
        console.log('TimeclockAPI initialized for validation page');
    }
});

// Fonction globale pour actualiser le dashboard
function refreshDashboard() {
    console.log('Refreshing validation dashboard...');
    ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
    
    setTimeout(function() {
        location.reload();
    }, 500);
}

/**
 * Go to home page (pour toolbar logo)
 */
function goToHome() {
    console.log('Going to home page from validation');
    
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

// Fonction globale pour actions futures MVP
function showValidationActions() {
    ons.notification.alert('Validation actions coming in MVP 3.2');
}

// Fonction pour navigation retour
function goBackToHome() {
    console.log('Navigating back to home...');
    window.location.href = './home.php';
}

// Fonction pour afficher détails d'un enregistrement (MVP 3.2)
function showRecordDetails(recordId) {
    if (!recordId) {
        ons.notification.alert('ID d\'enregistrement invalide');
        return;
    }
    
    console.log('Navigating to record details for ID:', recordId);
    // Navigation vers la page de détail avec validation actions
    window.location.href = '<?php echo DOL_URL_ROOT; ?>/custom/appmobtimetouch/validation.php?action=viewRecord&id=' + recordId;
}

// Export fonctions globales
window.goToHome = goToHome;
window.toggleMenu = toggleMenu;
window.refreshDashboard = refreshDashboard;
window.showValidationActions = showValidationActions;
window.goBackToHome = goBackToHome;
window.showRecordDetails = showRecordDetails;
</script>

</body>
</html>

<?php
// Log MVP 3.1 complet
dol_syslog("ValidationManager MVP 3.1 page loaded for user " . $user->id . " with " . count($data['pending_records'] ?? []) . " pending records", LOG_INFO);
?>