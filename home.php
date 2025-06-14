<?php
/**
 * home.php - Page d'accueil AppMobTimeTouch complète sans tabbar
 */

// Load Dolibarr environment
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Load required libraries
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

// Load module specific libraries
dol_include_once('/appmobtimetouch/lib/appmobtimetouch.lib.php');
dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');

// Load SOLID architecture components
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/LocationHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/TimeclockServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/DataServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/DataService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/TimeclockService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/BaseController.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/HomeController.php';

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "users", "companies", "errors"));

// Security check
if (empty($user->rights->appmobtimetouch->timeclock->read)) {
    accessforbidden('Insufficient permissions for timeclock records');
}

// Debug: Check user rights
dol_syslog("HOME.PHP DEBUG: User rights - read: " . (empty($user->rights->appmobtimetouch->timeclock->read) ? 'NO' : 'YES'), LOG_DEBUG);
dol_syslog("HOME.PHP DEBUG: User rights - write: " . (empty($user->rights->appmobtimetouch->timeclock->write) ? 'NO' : 'YES'), LOG_DEBUG);
dol_syslog("HOME.PHP DEBUG: User rights - validate: " . (empty($user->rights->appmobtimetouch->timeclock->validate) ? 'NO' : 'YES'), LOG_DEBUG);

// Handle POST actions using SOLID architecture
$action = GETPOST('action', 'aZ09');
$error = 0;
$errors = [];
$messages = [];

// Initialize SOLID services and controller for action processing
$dataService = new DataService($db);
$timeclockService = new TimeclockService($db, $dataService);
$controller = new HomeController(
    $db, 
    $user, 
    $langs, 
    $conf,
    $timeclockService,
    $dataService
);

// Debug: Log action and POST data
dol_syslog("HOME.PHP DEBUG: Action = '$action', POST data = " . json_encode($_POST), LOG_DEBUG);

// Process through controller using exact SOLID mechanism
try {
    // Traitement via le contrôleur SOLID
    $templateData = $controller->index();
    
    // Variables pour compatibilité template (extraction des données) - mécanisme SOLID original
    extract($templateData);
    
    // Debug: Vérifier les données extraites
    dol_syslog("HOME.PHP: Timeclock types count: " . count($timeclock_types ?? []), LOG_DEBUG);
    dol_syslog("HOME.PHP: Default type ID: " . ($default_type_id ?? 'undefined'), LOG_DEBUG);
    
    dol_syslog("HOME.PHP SOLID: Controller processing completed successfully", LOG_DEBUG);
    
} catch (Exception $e) {
    // Gestion centralisée des erreurs comme dans l'original
    dol_syslog("HOME.PHP SOLID: Controller error - " . $e->getMessage(), LOG_ERROR);
    accessforbidden($e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>AppMobTimeTouch - <?php echo $conf->global->MAIN_INFO_SOCIETE_NOM; ?></title>
    
    <!-- OnsenUI CSS -->
    <link rel="stylesheet" href="css/onsenui.min.css">
    <link rel="stylesheet" href="css/onsen-css-components.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="css/font_awesome/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/index.css">
    
    <!-- OnsenUI JavaScript -->
    <script src="js/onsenui.min.js"></script>
    <script src="js/navigation.js"></script>
    <script src="js/timeclock-api.js"></script>
    
    <style>
    .page__content {
        padding-bottom: 20px;
    }
    
    .home-section {
        margin: 15px 10px;
    }
    
    .status-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }
    
    .status-active {
        border-left: 4px solid #4CAF50;
    }
    
    .status-inactive {
        border-left: 4px solid #f44336;
    }
    
    .clock-button {
        width: 100%;
        margin: 10px 0;
        height: 50px;
        font-size: 16px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin: 10px 0;
    }
    
    .stat-item {
        text-align: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #2196f3;
    }
    
    .stat-label {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    
    .recent-records-section {
        margin: 15px 10px;
    }
    
    .records-list {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .record-item {
        padding: 10px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }
    
    .record-item:hover {
        background-color: #f5f5f5;
    }
    
    .record-time {
        font-weight: bold;
        color: #333;
    }
    
    .record-duration {
        color: #666;
        font-size: 12px;
    }
    </style>
</head>
<body>

<!-- Structure OnsenUI -->
<ons-splitter id="mySplitter">
    <ons-splitter-side id="sidemenu" side="right" width="250px" collapse="portrait" swipeable>
        <?php include 'tpl/parts/rightmenu.tpl'; ?>
    </ons-splitter-side>
    
    <ons-splitter-content>
        <ons-page id="homePage">
            
            <!-- TopBar -->
            <?php include 'tpl/parts/topbar-home.tpl'; ?>
            
            <!-- Contenu principal -->
            <div class="page__content">
                
                <!-- Section Statut Pointage -->
                <div class="home-section">
                    <ons-card class="status-card <?php echo $is_clocked_in ? 'status-active' : 'status-inactive'; ?>">
                        <div class="title" style="text-align: center; padding: 15px 0 10px 0;">
                            <h2><?php echo $langs->trans("TimeclockStatus"); ?></h2>
                        </div>
                        <div class="content" style="text-align: center; padding: 20px;">
                            <?php if ($is_clocked_in): ?>
                                <ons-icon icon="fa-clock" size="40px" style="color: #4CAF50; margin-bottom: 10px;"></ons-icon>
                                <p style="color: #4CAF50; font-size: 18px; margin-bottom: 15px;">
                                    <strong><?php echo $langs->trans("ClockedIn"); ?></strong>
                                </p>
                                <?php if ($clock_in_time): ?>
                                    <p style="color: #666; margin-bottom: 15px;">
                                        <?php echo $langs->trans("Since"); ?>: <?php echo dol_print_date($clock_in_time, 'hour', 'tzuser'); ?>
                                        <br>
                                        <span id="current-duration"><?php echo gmdate('H:i', $current_duration); ?></span>
                                    </p>
                                <?php endif; ?>
                                <ons-button modifier="large--cta" class="clock-button" onclick="showClockOutModal()">
                                    <ons-icon icon="fa-sign-out-alt"></ons-icon>
                                    <?php echo $langs->trans("ClockOut"); ?>
                                </ons-button>
                            <?php else: ?>
                                <ons-icon icon="fa-clock-o" size="40px" style="color: #f44336; margin-bottom: 10px;"></ons-icon>
                                <p style="color: #f44336; font-size: 18px; margin-bottom: 15px;">
                                    <strong><?php echo $langs->trans("NotClockedIn"); ?></strong>
                                </p>
                                <ons-button modifier="large--cta" class="clock-button" onclick="showClockInModal()">
                                    <ons-icon icon="fa-sign-in-alt"></ons-icon>
                                    <?php echo $langs->trans("ClockIn"); ?>
                                </ons-button>
                            <?php endif; ?>
                        </div>
                    </ons-card>
                </div>
                
                <!-- Section Statistiques Aujourd'hui -->
                <div class="home-section">
                    <ons-card class="status-card">
                        <div class="title" style="padding: 15px;">
                            <h3><?php echo $langs->trans("TodayStatistics"); ?></h3>
                        </div>
                        <div class="content" style="padding: 0 15px 15px 15px;">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo number_format($today_total_hours, 1); ?>h</div>
                                    <div class="stat-label"><?php echo $langs->trans("HoursWorked"); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo count($today_summary['records'] ?? []); ?></div>
                                    <div class="stat-label"><?php echo $langs->trans("Sessions"); ?></div>
                                </div>
                            </div>
                        </div>
                    </ons-card>
                </div>
                
                <!-- Section Résumé Hebdomadaire -->
                <div class="home-section">
                    <ons-card class="status-card">
                        <div class="title" style="padding: 15px;">
                            <h3><?php echo $langs->trans("WeeklySummary"); ?></h3>
                        </div>
                        <div class="content" style="padding: 0 15px 15px 15px;">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-value"><?php 
                                        $total_hours = is_object($weekly_summary) ? ($weekly_summary->total_hours ?? 0) : ($weekly_summary['total_hours'] ?? 0);
                                        echo number_format($total_hours, 1); 
                                    ?>h</div>
                                    <div class="stat-label"><?php echo $langs->trans("TotalHours"); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php 
                                        $days_worked = is_object($weekly_summary) ? ($weekly_summary->days_worked ?? 0) : ($weekly_summary['days_worked'] ?? 0);
                                        echo $days_worked; 
                                    ?></div>
                                    <div class="stat-label"><?php echo $langs->trans("DaysWorked"); ?></div>
                                </div>
                            </div>
                        </div>
                    </ons-card>
                </div>
                
                <!-- Section Enregistrements Récents -->
                <div class="home-section">
                    <ons-card class="status-card">
                        <div class="title" style="padding: 15px;">
                            <h3><?php echo $langs->trans("RecentRecords"); ?></h3>
                            <p style="color: #666; font-size: 12px; margin: 5px 0 0 0;"><?php echo $langs->trans("Last7Days"); ?></p>
                        </div>
                        <div class="content" style="padding: 0;">
                            <?php if (!empty($recent_records)): ?>
                            <div class="records-list">
                                <?php foreach ($recent_records as $record): ?>
                                <?php
                                // Handle both object and array formats
                                $rowid = is_object($record) ? ($record->rowid ?? $record->id ?? 0) : ($record['rowid'] ?? 0);
                                $clock_in_time = is_object($record) ? ($record->clock_in_time ?? '') : ($record['clock_in_time'] ?? '');
                                $clock_out_time = is_object($record) ? ($record->clock_out_time ?? null) : ($record['clock_out_time'] ?? null);
                                $work_duration = is_object($record) ? ($record->work_duration ?? 0) : ($record['work_duration'] ?? 0);
                                ?>
                                <div class="record-item" onclick="viewRecord(<?php echo $rowid; ?>)">
                                    <div class="record-time">
                                        <?php 
                                        // Utiliser les fonctions Dolibarr pour la gestion des dates
                                        $record_date = dol_print_date($clock_in_time, '%Y-%m-%d', 'tzuser');
                                        $today = dol_print_date(dol_now(), '%Y-%m-%d', 'tzuser');
                                        $yesterday = dol_print_date(dol_now() - 86400, '%Y-%m-%d', 'tzuser');
                                        
                                        if ($record_date == $today) {
                                            echo $langs->trans("Today") . " ";
                                        } elseif ($record_date == $yesterday) {
                                            echo $langs->trans("Yesterday") . " ";
                                        } else {
                                            echo dol_print_date($clock_in_time, '%d/%m', 'tzuser') . " ";
                                        }
                                        ?>
                                        <?php echo dol_print_date($clock_in_time, 'hour', 'tzuser'); ?>
                                        <?php if ($clock_out_time): ?>
                                            - <?php echo dol_print_date($clock_out_time, 'hour', 'tzuser'); ?>
                                        <?php else: ?>
                                            - <span style="color: #4CAF50;"><?php echo $langs->trans("InProgress"); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-duration">
                                        <?php if ($work_duration): ?>
                                            <?php echo gmdate('H:i', $work_duration); ?>
                                        <?php else: ?>
                                            <?php echo $langs->trans("Active"); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div style="text-align: center; padding: 20px; color: #666;">
                                <ons-icon icon="fa-clock-o" size="30px" style="margin-bottom: 10px;"></ons-icon>
                                <p><?php echo $langs->trans("NoRecentRecords"); ?></p>
                                <ons-button modifier="outline" onclick="showClockInModal()" style="margin-top: 10px;">
                                    <?php echo $langs->trans("StartFirstRecord"); ?>
                                </ons-button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </ons-card>
                </div>
                
            </div>
            
        </ons-page>
    </ons-splitter-content>
</ons-splitter>

<!-- Modal Components (Architecture SOLID) -->
<?php include 'Views/components/ClockInModal.tpl'; ?>
<?php include 'Views/components/ClockOutModal.tpl'; ?>

<script>
// Configuration globale
window.appMobTimeTouch = {
    DOL_URL_ROOT: '<?php echo DOL_URL_ROOT; ?>',
    isClocked: <?php echo json_encode($is_clocked_in); ?>,
    clockInTime: <?php echo json_encode($clock_in_time); ?>,
    userId: <?php echo $user->id; ?>,
    apiToken: '<?php echo newToken(); ?>'
};

// Initialisation OnsenUI
ons.ready(function() {
    console.log('AppMobTimeTouch Home loaded');
    
    // Démarrer le timer de durée si pointé
    <?php if ($is_clocked_in && $clock_in_time): ?>
    setInterval(updateCurrentDuration, 60000); // Mise à jour chaque minute
    <?php endif; ?>
});

// Fonctions pour les modales
function showClockInModal() {
    document.getElementById('clockInModal').show();
}

function showClockOutModal() {
    document.getElementById('clockOutModal').show();
}

function submitClockIn() {
    document.getElementById('clockInForm').submit();
}

function confirmClockOut() {
    ons.notification.confirm('<?php echo $langs->trans("ConfirmClockOut"); ?>').then(function(result) {
        if (result === 1) {
            document.getElementById('clockOutForm').submit();
        }
    });
}

// Fonction pour sélectionner le type de pointage
function selectTimeclockType(typeId, typeLabel, typeColor) {
    document.getElementById('selected_timeclock_type').value = typeId;
    
    // Mettre à jour l'affichage visuel
    var allItems = document.querySelectorAll('.timeclock-type-item');
    allItems.forEach(function(item) {
        item.style.backgroundColor = '';
        var icon = item.querySelector('.type-selected-icon');
        if (icon) icon.style.display = 'none';
    });
    
    var selectedItem = document.querySelector('[data-type-id="' + typeId + '"]');
    if (selectedItem) {
        selectedItem.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
        var icon = selectedItem.querySelector('.type-selected-icon');
        if (icon) icon.style.display = 'block';
    }
}

// Fonction pour mettre à jour la durée courante
function updateCurrentDuration() {
    <?php if ($is_clocked_in && $clock_in_time): ?>
    var startTime = <?php echo $clock_in_time; ?>;
    var now = Math.floor(Date.now() / 1000);
    var duration = now - startTime;
    var hours = Math.floor(duration / 3600);
    var minutes = Math.floor((duration % 3600) / 60);
    var timeStr = hours + ':' + (minutes < 10 ? '0' + minutes : minutes);
    
    var durationElement = document.getElementById('current-duration');
    if (durationElement) {
        durationElement.textContent = timeStr;
    }
    
    var sessionElement = document.getElementById('session-duration');
    if (sessionElement) {
        sessionElement.textContent = timeStr;
    }
    <?php endif; ?>
}

// Submit Clock In - Compatible with SOLID templates
function submitClockIn() {
    console.log('Submit Clock In called');
    
    // Debug: Check form existence
    var form = document.getElementById('clockInForm');
    if (!form) {
        console.error('ClockInForm not found!');
        ons.notification.alert('Erreur: Formulaire non trouvé');
        return;
    }
    
    console.log('Form found:', form);
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);
    
    // Check form data
    var formData = new FormData(form);
    console.log('Form data:');
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Check required fields
    var typeId = document.getElementById('selected_timeclock_type').value;
    if (!typeId) {
        console.error('No timeclock type selected');
        ons.notification.alert('Veuillez sélectionner un type de pointage');
        return;
    }
    
    // Validate required location if needed
    if (window.appMobTimeTouch && window.appMobTimeTouch.requireLocation) {
        var lat = document.getElementById('clockin_latitude').value;
        var lon = document.getElementById('clockin_longitude').value;
        
        if (!lat || !lon) {
            ons.notification.alert('<?php echo $langs->trans("LocationRequiredForClockIn"); ?>');
            return;
        }
    }
    
    console.log('Submitting form...');
    // Submit form directly (controller handles the action)
    document.getElementById('clockInForm').submit();
}

// Submit Clock Out - Compatible with SOLID templates  
function submitClockOut() {
    console.log('Submit Clock Out called');
    
    // Validate required location if needed
    if (window.appMobTimeTouch && window.appMobTimeTouch.requireLocation) {
        var lat = document.getElementById('clockout_latitude').value;
        var lon = document.getElementById('clockout_longitude').value;
        
        if (!lat || !lon) {
            ons.notification.alert('<?php echo $langs->trans("LocationRequiredForClockOut"); ?>');
            return;
        }
    }
    
    // Submit form directly (controller handles the action)
    document.getElementById('clockOutForm').submit();
}

// Fonction pour confirmer le clock out
function confirmClockOut() {
    ons.notification.confirm('<?php echo $langs->trans("ConfirmClockOut"); ?>').then(function(result) {
        if (result === 1) {
            submitClockOut();
        }
    });
}

// Show Clock In Modal
function showClockInModal() {
    var modal = document.getElementById('clockInModal');
    if (modal) {
        modal.show();
        
        // Initialize type selection if available
        setTimeout(function() {
            if (typeof initializeTimeclockTypeSelection === 'function') {
                initializeTimeclockTypeSelection();
            }
        }, 100);
    }
}

// Show Clock Out Modal
function showClockOutModal() {
    var modal = document.getElementById('clockOutModal');
    if (modal) {
        modal.show();
    }
}

// Fonction pour sélectionner le type de pointage
function selectTimeclockType(typeId, typeLabel, typeColor) {
    console.log('Selecting timeclock type:', typeId, typeLabel, typeColor);
    
    // Mettre à jour le champ caché
    var hiddenInput = document.getElementById('selected_timeclock_type');
    if (hiddenInput) {
        hiddenInput.value = typeId;
    }
    
    // Supprimer la sélection de tous les éléments
    var allItems = document.querySelectorAll('.timeclock-type-item');
    allItems.forEach(function(item) {
        item.classList.remove('selected');
        item.style.backgroundColor = '';
        item.style.borderLeft = '';
        
        // Masquer l'icône de validation
        var icon = item.querySelector('.type-selected-icon');
        if (icon) {
            icon.style.display = 'none';
        }
    });
    
    // Marquer l'élément sélectionné
    var selectedItem = document.querySelector('[data-type-id="' + typeId + '"]');
    if (selectedItem) {
        selectedItem.classList.add('selected');
        selectedItem.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
        selectedItem.style.borderLeft = '4px solid ' + typeColor;
        
        // Afficher l'icône de validation
        var icon = selectedItem.querySelector('.type-selected-icon');
        if (icon) {
            icon.style.display = 'block';
            icon.style.color = typeColor;
        }
    }
}

// Fonction pour voir un enregistrement
function viewRecord(recordId) {
    window.location.href = './employee-record-detail.php?id=' + recordId;
}

// Fonctions toolbar
function goToHome() {
    location.reload();
}

function toggleMenu() {
    var sideMenu = document.getElementById('sidemenu');
    if (sideMenu) {
        sideMenu.toggle();
    }
}

// Exposer globalement
window.goToHome = goToHome;
window.toggleMenu = toggleMenu;
window.submitClockIn = submitClockIn;
window.submitClockOut = submitClockOut;
window.showClockInModal = showClockInModal;
window.showClockOutModal = showClockOutModal;
window.selectTimeclockType = selectTimeclockType;
</script>

</body>
</html>