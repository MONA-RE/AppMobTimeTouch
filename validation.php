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

// Gestion des actions
try {
    switch ($action) {
        case 'dashboard':
        default:
            // MVP 3.1 : Dashboard manager
            $data = $controller->dashboard();
            break;
            
        case 'validate_record':
            // MVP 3.2 : Validation individuelle (placeholder)
            $result = $controller->validateRecord();
            if ($result['error']) {
                $errors = $result['errors'];
                $error = 1;
            } else {
                $messages = $result['messages'] ?? ['Validation completed'];
            }
            // Redirection vers dashboard après action
            $data = $controller->dashboard();
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

// En-têtes page
$title = $langs->trans('ValidationManager') . ' - MVP 3.1';
llxHeader('', $title, '');

// Debug MVP 3.1
if ($conf->global->MAIN_FEATURES_LEVEL >= 2) {
    print '<!-- DEBUG MVP 3.1: ValidationManager Dashboard -->';
    print '<!-- Action: ' . $action . ' -->';
    print '<!-- Manager ID: ' . $user->id . ' -->';
    print '<!-- Pending records: ' . count($data['pending_records'] ?? []) . ' -->';
    print '<!-- Notifications: ' . count($data['notifications'] ?? []) . ' -->';
}
?>

<!-- Messages d'erreur/succès -->
<?php if ($error): ?>
<div class="error">
    <?php foreach ($errors as $err): ?>
    <div><?php echo dol_escape_htmltag($err); ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($messages)): ?>
<div class="ok">
    <?php foreach ($messages as $msg): ?>
    <div><?php echo dol_escape_htmltag($msg); ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Interface mobile OnsenUI -->
<div id="validation-app">
    <!-- Injection variables PHP pour template -->
    <?php 
    // Rendre variables disponibles pour template
    extract($data);
    
    // Inclure template dashboard MVP 3.1
    include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/validation/dashboard.tpl'; 
    ?>
</div>

<!-- JavaScript OnsenUI et App -->
<script>
// Configuration app validation MVP 3.1
document.addEventListener('DOMContentLoaded', function() {
    console.log('Validation Manager MVP 3.1 initialized');
    
    // Test des fonctionnalités MVP 3.1
    console.log('Dashboard data loaded:');
    console.log('- Pending records: <?php echo count($data['pending_records'] ?? []); ?>');
    console.log('- Notifications: <?php echo count($data['notifications'] ?? []); ?>');
    console.log('- Statistics: <?php echo json_encode($data['stats'] ?? []); ?>');
    
    // Message de bienvenue MVP 3.1
    <?php if (count($data['pending_records'] ?? []) > 0): ?>
    ons.notification.toast('<?php echo $langs->trans("WelcomeValidationManager"); ?> - <?php echo count($data['pending_records']); ?> validation(s) en attente', {timeout: 3000});
    <?php else: ?>
    ons.notification.toast('<?php echo $langs->trans("WelcomeValidationManager"); ?> - Aucune validation en attente', {timeout: 3000});
    <?php endif; ?>
});

// Token CSRF pour futures actions
var token = '<?php echo newToken(); ?>';
</script>

<!-- CSS spécifique validation -->
<style>
/* Styles MVP 3.1 pour dashboard manager */
.validation-dashboard {
    background-color: #f8f9fa;
    min-height: 100vh;
}

.stat-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 15px;
}

.priority-indicator {
    width: 6px;
    height: 100%;
    border-radius: 3px;
    margin-right: 10px;
}

.notification-badge {
    background-color: #ffc107;
    color: #856404;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
}

/* Responsive pour mobile */
@media (max-width: 768px) {
    .stat-card {
        margin: 10px;
        padding: 15px;
    }
}
</style>

<?php
llxFooter();

// Log MVP 3.1 complet
dol_syslog("ValidationManager MVP 3.1 page loaded for user " . $user->id . " with " . count($data['pending_records'] ?? []) . " pending records", LOG_INFO);
?>