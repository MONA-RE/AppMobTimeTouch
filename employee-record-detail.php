<?php
/**
 * Page Détail Enregistrement Employé - Adaptation MVP 3.2 pour les salariés
 * 
 * Point d'entrée pour la consultation des détails d'un enregistrement par l'employé
 * Respecte l'architecture SOLID, sans actions de validation
 */

// Chargement Dolibarr
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Chargement classes nécessaires
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclocktype.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';

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

// Récupération ID enregistrement
$recordId = GETPOST('id', 'int');

if (!$recordId) {
    accessforbidden('Missing record ID');
}

// Variables pour template
$error = 0;
$errors = [];
$messages = [];
$record = null;

// Chargement de l'enregistrement
try {
    $timeclockRecord = new TimeclockRecord($db);
    
    if ($timeclockRecord->fetch($recordId) <= 0) {
        $error = 1;
        $errors[] = $langs->trans('RecordNotFound');
    } else {
        // Vérifier que l'utilisateur peut voir cet enregistrement
        // Un employé ne peut voir que ses propres enregistrements
        // Un manager avec droits readall peut voir tous les enregistrements
        if ($timeclockRecord->fk_user != $user->id && empty($user->rights->appmobtimetouch->timeclock->readall)) {
            accessforbidden('You can only view your own records');
        }
        
        // Récupérer infos utilisateur
        $user_obj = new User($db);
        $user_obj->fetch($timeclockRecord->fk_user);
        
        // Récupérer type de pointage
        $type = new TimeclockType($db);
        $type_valid = false;
        if (!empty($timeclockRecord->fk_timeclock_type) && $type->fetch($timeclockRecord->fk_timeclock_type) > 0) {
            $type_valid = true;
        } else {
            // Fallback pour type par défaut
            $type->label = 'Default';
            $type->color = '#666666';
        }
        
        // Détecter anomalies pour information (sans validation)
        $anomalies = detectEmployeeRecordAnomalies($timeclockRecord);
        
        // Préparer données pour le template
        $record = [
            'id' => $timeclockRecord->rowid,
            'user_id' => $timeclockRecord->fk_user,
            'user_name' => $user_obj->getFullName($langs),
            'clock_in_time' => $timeclockRecord->clock_in_time,
            'clock_out_time' => $timeclockRecord->clock_out_time,
            'work_duration' => $timeclockRecord->work_duration ?: 0,
            'break_duration' => $timeclockRecord->break_duration ?: 0,
            'location_in' => $timeclockRecord->location_in,
            'location_out' => $timeclockRecord->location_out,
            'note' => $timeclockRecord->note,
            'status' => $timeclockRecord->status,
            'type' => [
                'id' => $type_valid ? $type->id : 0,
                'label' => $type->label,
                'color' => $type->color
            ],
            'anomalies' => $anomalies,
            'validation_status' => getSimpleValidationStatus($timeclockRecord)
        ];
    }
    
} catch (Exception $e) {
    $error = 1;
    $errors[] = $e->getMessage();
    dol_syslog("EmployeeRecordDetail Error: " . $e->getMessage(), LOG_ERR);
}

/**
 * Détecter anomalies pour information employé
 */
function detectEmployeeRecordAnomalies($record): array 
{
    global $langs;
    $anomalies = [];
    
    // Overtime (plus de 8h)
    if ($record->work_duration > 480) { // 8h en minutes
        $anomalies[] = [
            'type' => 'overtime',
            'level' => 'info',
            'message' => $langs->trans('OvertimeDetected') . ': ' . 
                       TimeHelper::convertSecondsToReadableTime($record->work_duration * 60)
        ];
    }
    
    // Clock-out manquant
    if (empty($record->clock_out_time) && $record->status == 2) { // Status in progress
        $anomalies[] = [
            'type' => 'missing_clockout',
            'level' => 'warning',
            'message' => $langs->trans('NotClockedOut')
        ];
    }
    
    // Pause longue (plus de 90 minutes)
    if ($record->break_duration > 90) {
        $anomalies[] = [
            'type' => 'long_break',
            'level' => 'info',
            'message' => $langs->trans('ExtendedBreak') . ': ' . $record->break_duration . ' minutes'
        ];
    }
    
    return $anomalies;
}

/**
 * Statut de validation simplifié pour employé
 */
function getSimpleValidationStatus($record): array 
{
    global $langs;
    
    // Simuler statut validation basé sur le status du record
    $status = $record->validation_status ?? 0;
    
    $statusLabels = [
        0 => 'ValidationPending',
        1 => 'ValidationApproved', 
        2 => 'ValidationRejected',
        3 => 'ValidationPartial'
    ];
    
    return [
        'status' => $status,
        'status_label' => $statusLabels[$status] ?? 'ValidationPending',
        'validated_by' => $record->validated_by ?? null,
        'validated_date' => $record->validated_date ?? null,
        'comment' => $record->validation_comment ?? null
    ];
}

// Préparer les variables pour templates
$page_title = $langs->trans('RecordDetails');
$isEmployeeView = true;

// Debug
if ($conf->global->MAIN_FEATURES_LEVEL >= 2) {
    dol_syslog('DEBUG: Employee Record Detail Page', LOG_DEBUG);
    dol_syslog('Record ID: ' . $recordId, LOG_DEBUG);
    dol_syslog('User ID: ' . $user->id, LOG_DEBUG);
    dol_syslog('Record found: ' . ($record ? 'yes' : 'no'), LOG_DEBUG);
}
?>

<!DOCTYPE html>
<html lang="<?php echo $langs->getDefaultLang(); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title><?php echo $page_title; ?> - <?php echo $conf->global->MAIN_INFO_SOCIETE_NOM; ?></title>
    
    <!-- OnsenUI CSS -->
    <link rel="stylesheet" href="css/onsenui.min.css">
    <link rel="stylesheet" href="css/onsen-css-components.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="css/font_awesome/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">
    
    <!-- Employee Record Detail specific styles -->
    <style>
    .employee-record-detail {
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    
    .record-detail-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    
    .anomaly-info {
        background-color: #e3f2fd;
        border-left: 4px solid #2196f3;
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
    }
    
    .validation-status-readonly {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        margin: 10px 0;
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
            <ons-page id="employeeRecordDetailPage">
                
                <!-- TopBar -->
                <ons-toolbar modifier="material">
                    <div class="left">
                        <ons-back-button animation="fade" onclick="goBackToHome()"></ons-back-button>
                        <span><?php echo $page_title; ?></span>
                    </div>
                    <div class="right">
                        <ons-toolbar-button onclick="refreshPage()" title="<?php echo $langs->trans('Refresh'); ?>">
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

                <!-- Contenu détail enregistrement employé -->
                <div class="employee-record-detail">
                    <?php if ($record): ?>
                        <?php include DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Views/validation/record-detail.tpl'; ?>
                    <?php else: ?>
                        <!-- Erreur : enregistrement non trouvé -->
                        <div style="padding: 20px; text-align: center;">
                            <ons-icon icon="md-error" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></ons-icon>
                            <h3 style="color: #dc3545; margin-bottom: 10px;">
                                <?php echo $langs->trans('RecordNotFound'); ?>
                            </h3>
                            <p style="color: #6c757d; margin-bottom: 20px;">
                                <?php echo $langs->trans('RecordNotFoundDescription'); ?>
                            </p>
                            <ons-button onclick="goBackToHome()" style="background-color: #007bff; color: white;">
                                <ons-icon icon="md-arrow-back" style="margin-right: 5px;"></ons-icon>
                                <?php echo $langs->trans('GoBack'); ?>
                            </ons-button>
                        </div>
                    <?php endif; ?>
                </div>
                
            </ons-page>
        </ons-navigator>
    </ons-splitter-content>
</ons-splitter>

<!-- OnsenUI JavaScript -->
<script src="js/onsenui.min.js"></script>

<!-- Navigation JavaScript -->
<script src="js/navigation.js"></script>

<!-- Configuration pour employé -->
<script>
// Configuration employé record detail
window.employeeRecordConfig = {
    recordId: <?php echo $recordId; ?>,
    isEmployeeView: true,
    canEdit: false,
    userId: <?php echo $user->id; ?>
};

// Initialisation page détail employé
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Employee Record Detail Page Initialized ===');
    console.log('Record ID: ' + window.employeeRecordConfig.recordId);
    console.log('Employee view: ' + window.employeeRecordConfig.isEmployeeView);
    
    <?php if ($record): ?>
    console.log('Record loaded for user: <?php echo $record["user_name"]; ?>');
    console.log('Work duration: <?php echo $record["work_duration"]; ?> minutes');
    console.log('Anomalies detected: <?php echo count($record["anomalies"]); ?>');
    <?php endif; ?>
});

// Fonction pour actualiser la page
function refreshPage() {
    console.log('Refreshing employee record detail...');
    ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
    
    setTimeout(function() {
        location.reload();
    }, 500);
}

// Fonction pour navigation retour
function goBackToHome() {
    console.log('Navigating back to home...');
    
    // Vérifier si on vient de myrecords.php via le referer ou un paramètre
    var referrer = document.referrer;
    var fromParam = new URLSearchParams(window.location.search).get('from');
    
    console.log('Referrer:', referrer);
    console.log('From parameter:', fromParam);
    
    if (fromParam === 'myrecords' || (referrer && referrer.includes('myrecords.php'))) {
        console.log('Returning to myrecords.php');
        window.location.href = './myrecords.php';
    } else {
        console.log('Returning to home.php');
        window.location.href = './home.php';
    }
}

// Export fonctions globales
window.refreshPage = refreshPage;
window.goBackToHome = goBackToHome;
</script>

</body>
</html>

<?php
// Log page chargée
dol_syslog("Employee Record Detail page loaded for user " . $user->id . " for record " . $recordId, LOG_INFO);
?>