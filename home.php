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

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "users", "companies", "errors"));

// Security check
if (empty($user->rights->appmobtimetouch->timeclock->read)) {
    accessforbidden('Insufficient permissions for timeclock records');
}

// Function to get clock status
function getUserClockStatus($db, $user) {
    $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "appmobtimetouch_timeclockrecord";
    $sql .= " WHERE fk_user = " . (int)$user->id;
    $sql .= " AND clock_out_time IS NULL";
    $sql .= " ORDER BY clock_in_time DESC LIMIT 1";
    
    $resql = $db->query($sql);
    if ($resql) {
        return $db->num_rows($resql) > 0;
    }
    return false;
}

// Function to get today's records
function getTodayRecords($db, $user) {
    $today = date('Y-m-d');
    $sql = "SELECT rowid, clock_in_time, clock_out_time, work_duration, status";
    $sql .= " FROM " . MAIN_DB_PREFIX . "appmobtimetouch_timeclockrecord";
    $sql .= " WHERE fk_user = " . (int)$user->id;
    $sql .= " AND DATE(clock_in_time) = '" . $db->escape($today) . "'";
    $sql .= " ORDER BY clock_in_time DESC";
    
    $resql = $db->query($sql);
    $records = [];
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $records[] = [
                'rowid' => $obj->rowid,
                'clock_in_time' => $obj->clock_in_time,
                'clock_out_time' => $obj->clock_out_time,
                'work_duration' => $obj->work_duration,
                'status' => $obj->status
            ];
        }
    }
    return $records;
}

// Function to get recent records (last 7 days)
function getRecentRecords($db, $user) {
    $sql = "SELECT rowid, clock_in_time, clock_out_time, work_duration, status";
    $sql .= " FROM " . MAIN_DB_PREFIX . "appmobtimetouch_timeclockrecord";
    $sql .= " WHERE fk_user = " . (int)$user->id;
    $sql .= " AND clock_in_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $sql .= " ORDER BY clock_in_time DESC LIMIT 10";
    
    $resql = $db->query($sql);
    $records = [];
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $records[] = [
                'rowid' => $obj->rowid,
                'clock_in_time' => $obj->clock_in_time,
                'clock_out_time' => $obj->clock_out_time,
                'work_duration' => $obj->work_duration,
                'status' => $obj->status
            ];
        }
    }
    return $records;
}

// Function to get weekly summary
function getWeeklySummary($db, $user) {
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd = date('Y-m-d', strtotime('sunday this week'));
    
    $sql = "SELECT SUM(work_duration) as total_seconds, COUNT(DISTINCT DATE(clock_in_time)) as days_worked";
    $sql .= " FROM " . MAIN_DB_PREFIX . "appmobtimetouch_timeclockrecord";
    $sql .= " WHERE fk_user = " . (int)$user->id;
    $sql .= " AND DATE(clock_in_time) BETWEEN '" . $db->escape($weekStart) . "' AND '" . $db->escape($weekEnd) . "'";
    $sql .= " AND status = 3"; // Completed records
    
    $resql = $db->query($sql);
    if ($resql && $obj = $db->fetch_object($resql)) {
        return [
            'total_hours' => round($obj->total_seconds / 3600, 2),
            'days_worked' => $obj->days_worked
        ];
    }
    return ['total_hours' => 0, 'days_worked' => 0];
}

// Function to get timeclock types
function getTimeclockTypes($db) {
    $sql = "SELECT rowid, label, color FROM " . MAIN_DB_PREFIX . "appmobtimetouch_timeclocktype";
    $sql .= " WHERE active = 1 ORDER BY position, label";
    
    $resql = $db->query($sql);
    $types = [];
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $types[] = [
                'rowid' => $obj->rowid,
                'label' => $obj->label,
                'color' => $obj->color ?: '#4CAF50'
            ];
        }
    }
    return $types;
}

// Get data
$is_clocked_in = getUserClockStatus($db, $user);
$today_records = getTodayRecords($db, $user);
$recent_records = getRecentRecords($db, $user);
$weekly_summary = getWeeklySummary($db, $user);
$timeclock_types = getTimeclockTypes($db);
$pending_validation_count = 0;

// Calculate today's total hours
$today_total_hours = 0;
foreach ($today_records as $record) {
    if ($record['work_duration'] && $record['status'] == 3) {
        $today_total_hours += $record['work_duration'] / 3600;
    }
}

// Get current session info if clocked in
$current_session_start = null;
$current_duration = 0;
if ($is_clocked_in && !empty($today_records)) {
    foreach ($today_records as $record) {
        if (!$record['clock_out_time']) {
            $current_session_start = $record['clock_in_time'];
            $current_duration = time() - strtotime($record['clock_in_time']);
            break;
        }
    }
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
                                <?php if ($current_session_start): ?>
                                    <p style="color: #666; margin-bottom: 15px;">
                                        <?php echo $langs->trans("Since"); ?>: <?php echo date('H:i', strtotime($current_session_start)); ?>
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
                                    <div class="stat-value"><?php echo count($today_records); ?></div>
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
                                    <div class="stat-value"><?php echo number_format($weekly_summary['total_hours'], 1); ?>h</div>
                                    <div class="stat-label"><?php echo $langs->trans("TotalHours"); ?></div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $weekly_summary['days_worked']; ?></div>
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
                                <div class="record-item" onclick="viewRecord(<?php echo $record['rowid']; ?>)">
                                    <div class="record-time">
                                        <?php 
                                        $record_date = date('Y-m-d', strtotime($record['clock_in_time']));
                                        $today = date('Y-m-d');
                                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                                        
                                        if ($record_date == $today) {
                                            echo $langs->trans("Today") . " ";
                                        } elseif ($record_date == $yesterday) {
                                            echo $langs->trans("Yesterday") . " ";
                                        } else {
                                            echo date('d/m', strtotime($record['clock_in_time'])) . " ";
                                        }
                                        ?>
                                        <?php echo date('H:i', strtotime($record['clock_in_time'])); ?>
                                        <?php if ($record['clock_out_time']): ?>
                                            - <?php echo date('H:i', strtotime($record['clock_out_time'])); ?>
                                        <?php else: ?>
                                            - <span style="color: #4CAF50;"><?php echo $langs->trans("InProgress"); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="record-duration">
                                        <?php if ($record['work_duration']): ?>
                                            <?php echo gmdate('H:i', $record['work_duration']); ?>
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

<!-- Clock In Modal -->
<ons-modal direction="up" id="clockInModal">
    <div style="background: white; padding: 20px; border-radius: 10px; margin: 20px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h3><?php echo $langs->trans("ClockIn"); ?></h3>
        </div>
        
        <form id="clockInForm" method="post" action="timeclock-action.php">
            <input type="hidden" name="action" value="clock_in">
            <input type="hidden" name="token" value="<?php echo newToken(); ?>">
            <input type="hidden" id="clockin_latitude" name="latitude" value="">
            <input type="hidden" id="clockin_longitude" name="longitude" value="">
            <input type="hidden" id="selected_timeclock_type" name="timeclock_type_id" value="<?php echo !empty($timeclock_types) ? $timeclock_types[0]['rowid'] : 1; ?>">
            
            <!-- Type Selection -->
            <?php if (!empty($timeclock_types)): ?>
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; margin-bottom: 10px; display: block;"><?php echo $langs->trans("TimeclockType"); ?></label>
                <?php foreach ($timeclock_types as $type): ?>
                <ons-list-item class="timeclock-type-item" data-type-id="<?php echo $type['rowid']; ?>" 
                               onclick="selectTimeclockType(<?php echo $type['rowid']; ?>, '<?php echo addslashes($type['label']); ?>', '<?php echo $type['color']; ?>')">
                    <div class="left">
                        <div style="width: 20px; height: 20px; background-color: <?php echo $type['color']; ?>; border-radius: 50%;"></div>
                    </div>
                    <div class="center">
                        <div><?php echo $type['label']; ?></div>
                    </div>
                    <div class="right">
                        <ons-icon icon="fa-check" class="type-selected-icon" style="display: none; color: <?php echo $type['color']; ?>;"></ons-icon>
                    </div>
                </ons-list-item>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- GPS Status -->
            <div id="gps-status" style="padding: 10px; margin: 10px 0; border-radius: 5px; text-align: center;">
                <ons-icon icon="md-gps-fixed"></ons-icon>
                <span id="gps-status-text"><?php echo $langs->trans("ReadyToStart"); ?></span>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <ons-button onclick="document.getElementById('clockInModal').hide()" style="margin-right: 10px;">
                    <?php echo $langs->trans("Cancel"); ?>
                </ons-button>
                <ons-button modifier="cta" onclick="submitClockIn()">
                    <?php echo $langs->trans("ClockIn"); ?>
                </ons-button>
            </div>
        </form>
    </div>
</ons-modal>

<!-- Clock Out Modal -->
<ons-modal direction="up" id="clockOutModal">
    <div style="background: white; padding: 20px; border-radius: 10px; margin: 20px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h3><?php echo $langs->trans("ClockOut"); ?></h3>
            <?php if ($current_session_start): ?>
            <p style="color: #666;">
                <?php echo $langs->trans("SessionDuration"); ?>: <span id="session-duration"><?php echo gmdate('H:i', $current_duration); ?></span>
            </p>
            <?php endif; ?>
        </div>
        
        <form id="clockOutForm" method="post" action="timeclock-action.php">
            <input type="hidden" name="action" value="clock_out">
            <input type="hidden" name="token" value="<?php echo newToken(); ?>">
            <input type="hidden" id="clockout_latitude" name="latitude" value="">
            <input type="hidden" id="clockout_longitude" name="longitude" value="">
            
            <!-- GPS Status -->
            <div id="gps-status-out" style="padding: 10px; margin: 10px 0; border-radius: 5px; text-align: center;">
                <ons-icon icon="md-gps-fixed"></ons-icon>
                <span id="gps-status-out-text"><?php echo $langs->trans("ReadyToClockOut"); ?></span>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <ons-button onclick="document.getElementById('clockOutModal').hide()" style="margin-right: 10px;">
                    <?php echo $langs->trans("Cancel"); ?>
                </ons-button>
                <ons-button modifier="cta" onclick="confirmClockOut()">
                    <?php echo $langs->trans("ClockOut"); ?>
                </ons-button>
            </div>
        </form>
    </div>
</ons-modal>

<script>
// Configuration globale
window.appMobTimeTouch = {
    DOL_URL_ROOT: '<?php echo DOL_URL_ROOT; ?>',
    isClocked: <?php echo json_encode($is_clocked_in); ?>,
    clockInTime: <?php echo json_encode($current_session_start ? strtotime($current_session_start) : null); ?>,
    userId: <?php echo $user->id; ?>,
    apiToken: '<?php echo newToken(); ?>'
};

// Initialisation OnsenUI
ons.ready(function() {
    console.log('AppMobTimeTouch Home loaded');
    
    // Démarrer le timer de durée si pointé
    <?php if ($is_clocked_in && $current_session_start): ?>
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
    <?php if ($is_clocked_in && $current_session_start): ?>
    var startTime = <?php echo strtotime($current_session_start); ?>;
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
</script>

</body>
</html>