<?php
/**
 * home.php - Application Mobile Complète AppMobTimeTouch
 * 
 * Point d'entrée principal pour l'application mobile de pointage
 * Respecte l'architecture SOLID avec injection de dépendances
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
dol_include_once('/appmobtimetouch/class/weeklysummary.class.php');
dol_include_once('/appmobtimetouch/class/timeclockconfig.class.php');
dol_include_once('/appmobtimetouch/core/modules/modAppMobTimeTouch.class.php');

// Load SOLID architecture components
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/LocationHelper.php';

// Load SOLID architecture services
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/TimeclockServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/DataServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/DataService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/TimeclockService.php';

// Load SOLID architecture controllers
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/BaseController.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/HomeController.php';

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "users", "companies", "errors"));

// Vérifier si la fonction isModEnabled existe (compatibilité)
if (!function_exists('isModEnabled')) {
    function isModEnabled($module)
    {
        global $conf;
        return !empty($conf->$module->enabled);
    }
}

// Security check - Vérifier que le module est activé
if (!isModEnabled('appmobtimetouch')) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}

// Vérification droits lecture minimum
if (empty($user->rights->appmobtimetouch->timeclock->read)) {
    accessforbidden('Insufficient permissions for timeclock records');
}

// Get version number from module class
$moduleInstance = new modAppMobTimeTouch($db);
$version = $moduleInstance->version;

// Variables par défaut pour éviter les erreurs
$error = 0;
$errors = [];
$messages = [];

// SOLID Architecture - Dependency Injection (DIP)
try {
    // Injection des services avec leurs dépendances
    $dataService = new DataService($db);
    $timeclockService = new TimeclockService($db, $dataService);
    
    // Injection du contrôleur avec toutes ses dépendances
    $controller = new HomeController(
        $db, 
        $user, 
        $langs, 
        $conf,
        $timeclockService,
        $dataService
    );

    // Traitement des actions via le contrôleur (méthode publique index)
    $data = $controller->index();
    
    // Extraire les variables pour le template
    if (isset($data['error'])) {
        $error = $data['error'];
        $errors = array_merge($errors, $data['errors'] ?? []);
        $messages = array_merge($messages, $data['messages'] ?? []);
    }
    
    // Variables essentielles pour le template
    $is_clocked_in = $data['is_clocked_in'] ?? false;
    $clock_in_time = $data['clock_in_time'] ?? null;
    $current_duration = $data['current_duration'] ?? 0;
    $active_record = $data['active_record'] ?? null;
    $today_total_hours = $data['today_total_hours'] ?? 0;
    $today_total_breaks = $data['today_total_breaks'] ?? 0;
    $today_summary = $data['today_summary'] ?? ['total_hours' => 0, 'total_breaks' => 0];
    $weekly_summary = $data['weekly_summary'] ?? ['total_hours' => 0, 'days_worked' => 0];
    $recent_records = $data['recent_records'] ?? [];
    $timeclock_types = $data['timeclock_types'] ?? [];
    $default_type_id = $data['default_type_id'] ?? 1;
    $require_location = $data['require_location'] ?? 0;
    $overtime_threshold = $data['overtime_threshold'] ?? 8;
    $max_hours_per_day = $data['max_hours_per_day'] ?? 12;
    $overtime_alert = $data['overtime_alert'] ?? false;
    
    // Variables pour compatibilité rightmenu.tpl
    $pending_validation_count = 0;
    
    // Données JavaScript pour l'interface mobile
    $js_data = [
        'is_clocked_in' => $is_clocked_in,
        'clock_in_time' => $clock_in_time,
        'require_location' => $require_location,
        'default_type_id' => $default_type_id,
        'max_hours_per_day' => $max_hours_per_day,
        'overtime_threshold' => $overtime_threshold,
        'api_token' => function_exists('newToken') ? newToken() : '',
        'user_id' => $user->id,
        'version' => $version
    ];
    
} catch (Exception $e) {
    // Gestion d'erreur avec fallback
    $error = 1;
    $errors[] = $e->getMessage();
    dol_syslog("HomeController Error: " . $e->getMessage(), LOG_ERR);
    
    // Variables par défaut en cas d'erreur AVEC DONNÉES TEST
    $is_clocked_in = false; // Test: mettre à true pour voir
    $clock_in_time = null;
    $current_duration = 0;
    $active_record = null;
    $today_total_hours = 8.5; // Test: données non nulles
    $today_total_breaks = 1.0;
    $today_summary = [
        'total_hours' => 8.5,
        'total_breaks' => 1.0,
        'first_clock_in' => '08:30',
        'last_clock_out' => '17:30'
    ];
    $weekly_summary = [
        'total_hours' => 35.5,
        'days_worked' => 4,
        'week_number' => date('W'),
        'overtime_hours' => 0,
        'expected_hours' => 40
    ];
    $recent_records = [
        [
            'rowid' => 1,
            'clock_in_time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'clock_out_time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'work_duration' => 3600,
            'status' => 3
        ]
    ];
    $timeclock_types = [
        ['rowid' => 1, 'label' => 'Bureau', 'color' => '#4CAF50']
    ];
    $default_type_id = 1;
    $require_location = 0;
    $overtime_threshold = 8;
    $max_hours_per_day = 12;
    $overtime_alert = false;
    $pending_validation_count = 0;
    
    $js_data = [
        'is_clocked_in' => false,
        'clock_in_time' => null,
        'require_location' => 0,
        'default_type_id' => 1,
        'max_hours_per_day' => 12,
        'overtime_threshold' => 8,
        'api_token' => function_exists('newToken') ? newToken() : '',
        'user_id' => $user->id ?? 0,
        'version' => $version
    ];
}

// Variables pour template view
$view = 'home';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>AppMobTimeTouch for Dolibarr <?php echo $version;?></title>
    
    <!-- CSS files with version parameter -->
    <link rel="stylesheet" href="css/onsenui.min.css?v=<?php echo $version;?>">
    <link rel="stylesheet" href="css/onsen-css-components.min.css?v=<?php echo $version;?>">
    <link rel="stylesheet" href="css/font_awesome/css/fontawesome.min.css?v=<?php echo $version;?>">
    <link rel="stylesheet" href="css/index.css?v=<?php echo $version;?>">
    
    <!-- Add manifest link -->
    <link rel="manifest" href="manifest.json" />
    
    <!-- Custom styles for mobile app -->
    <style>
        /* Page content scrollable */
        .page__content {
            padding-bottom: 60px; /* Space for tabbar */
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Home content container */
        .home-content {
            min-height: calc(100vh - 120px); /* Account for topbar and tabbar */
        }
        
        /* Tabbar styles */
        #mainTabbar {
            border-top: 1px solid #e0e0e0;
            background: white;
            z-index: 1000;
        }
        
        /* Fix OnsenUI tab interaction */
        ons-tab {
            cursor: pointer;
            user-select: none;
        }
        
        ons-tab[active] {
            color: #4CAF50;
        }
        
        /* Ensure content is clickable and visible */
        .home-content * {
            pointer-events: auto;
        }
        
        .home-content {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Debug: Ensure components are visible */
        ons-card {
            display: block !important;
            margin: 10px 0 !important;
        }
        
        /* Fix modal z-index */
        ons-modal {
            z-index: 10000;
        }
    </style>

    <!-- JavaScript files with version parameter - Load in correct order -->
    <script type="text/javascript" src="js/onsenui.min.js?v=<?php echo $version;?>"></script>
    <!-- Include navigation functions -->
    <script type="text/javascript" src="js/navigation.js?v=<?php echo $version;?>"></script>
    <!-- Include TimeclockAPI after OnsenUI -->
    <script type="text/javascript" src="js/timeclock-api.js?v=<?php echo $version;?>"></script> 
</head>
<body>

<!-- Application mobile complète avec Splitter -->
<ons-splitter id="mySplitter">
    <ons-splitter-side id="sidemenu" side="right" width="250px" collapse="portrait" swipeable>
        <!-- Menu latéral -->
        <?php include 'tpl/parts/rightmenu.tpl'; ?>
    </ons-splitter-side>
    
    <ons-splitter-content>
        <!-- Page principale avec structure similaire à myrecords.php et validation.php -->
        <ons-page id="homePage">
            
            <!-- TopBar avec même style que les autres pages -->
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

                <!-- Contenu principal de l'application mobile -->
                <div class="home-content">
                    <?php 
                    // Debug: Afficher les variables disponibles
                    echo "<!-- DEBUG: Variables disponibles -->\n";
                    echo "<!-- is_clocked_in: " . ($is_clocked_in ? 'true' : 'false') . " -->\n";
                    echo "<!-- today_summary: " . (isset($today_summary) ? 'set' : 'not set') . " -->\n";
                    echo "<!-- weekly_summary: " . (isset($weekly_summary) ? 'set' : 'not set') . " -->\n";
                    echo "<!-- recent_records: " . (isset($recent_records) ? 'set' : 'not set') . " -->\n";
                    
                    include 'tpl/home.tpl'; 
                    ?>
                </div>
            </div>
            
            <!-- Tabbar en bas de page -->
            <ons-tabbar position="bottom" id="mainTabbar">
                <!-- Onglet Accueil / Today -->
                <ons-tab id="tabHome" onclick="showHomePage();" label="<?php echo $langs->trans('Today'); ?>" icon="fa-home" badge="" active>
                </ons-tab>
                
                <!-- Onglet Mes Enregistrements -->
                <ons-tab id="tabMyRecords" onclick="loadMyRecords();" label="<?php echo $langs->trans('MyRecords'); ?>" icon="fa-clock-o" badge="">
                </ons-tab>
                
                <?php if (!empty($user->rights->appmobtimetouch->timeclock->readall)): ?>
                <!-- Onglet Gestion (pour les managers) -->
                <ons-tab id="tabManagement" onclick="loadManagement();" label="<?php echo $langs->trans('Management'); ?>" icon="fa-users" badge="">
                </ons-tab>
                <?php else: ?>
                <!-- Onglet Rapports pour les utilisateurs normaux -->
                <ons-tab id="tabSummary" onclick="loadSummary();" label="<?php echo $langs->trans('Reports'); ?>" icon="fa-bar-chart" badge="">
                </ons-tab>
                <?php endif; ?>
                
                <!-- Onglet Paramètres -->
                <ons-tab id="tabSettings" onclick="loadSettings();" label="<?php echo $langs->trans('Settings'); ?>" icon="fa-cog" badge="">
                </ons-tab>
            </ons-tabbar>
            
        </ons-page>
    </ons-splitter-content>
</ons-splitter>

<!-- Modal de chargement -->
<ons-modal direction="up" id="sablier">
    <div style="text-align: center;">
        <p>
            <ons-icon icon="md-spinner" size="45px" spin></ons-icon>
        </p>
        <p id="loadingmessage"><span><?php echo $langs->trans("loadingInProgress"); ?></span></p>
    </div>
</ons-modal>

    <script type="text/javascript">
        // Variables globales pour le time tracking
        var globalCurrentPage = "homeApplication";
        var globalMyNavigator = null;
        var userTimeclockStatus = null; // null, 'clocked_in', 'clocked_out'

        // Configuration globale pour l'application
        window.appMobTimeTouch = {
            DOL_URL_ROOT: '<?php echo DOL_URL_ROOT; ?>',
            version: '<?php echo $version; ?>',
            isClocked: <?php echo json_encode($is_clocked_in); ?>,
            clockInTime: <?php echo json_encode($clock_in_time); ?>,
            userId: <?php echo $user->id; ?>,
            apiToken: '<?php echo newToken(); ?>',
            requireLocation: <?php echo json_encode($require_location); ?>,
            defaultTypeId: <?php echo json_encode($default_type_id); ?>,
            user_rights: {
                read: <?php echo (!empty($user->rights->appmobtimetouch->timeclock->read)) ? 'true' : 'false'; ?>,
                write: <?php echo (!empty($user->rights->appmobtimetouch->timeclock->write)) ? 'true' : 'false'; ?>,
                readall: <?php echo (!empty($user->rights->appmobtimetouch->timeclock->readall)) ? 'true' : 'false'; ?>,
                validate: <?php echo (!empty($user->rights->appmobtimetouch->timeclock->validate)) ? 'true' : 'false'; ?>,
                export: <?php echo (!empty($user->rights->appmobtimetouch->timeclock->export)) ? 'true' : 'false'; ?>
            }
        };

        // Initialisation sécurisée avec vérification OnsenUI
        function initializeApp() {
            console.log("ONS ready in AppMobTimeTouch home.php");

            globalCurrentPage = "homeApplication";
            
            console.log('AppMobTimeTouch mobile app initialized');
            console.log('User rights:', window.appMobTimeTouch.user_rights);
            console.log('Clock status:', window.appMobTimeTouch.isClocked);

            // S'assurer que le menu est fermé au chargement
            setTimeout(function() {
                let sideMenu = document.getElementById('sidemenu');
                if (sideMenu) {
                    try {
                        sideMenu.close();
                    } catch (e) {
                        console.log('Menu already closed');
                    }
                }
            }, 100);

            //On nettoie l'historique au lancement de l'appli
            cleanHistorique();

            //stockage des donnees de base
            localStoreData("email", "<?php echo $user->email ?>");
            localStoreData("firstname", "<?php echo $user->firstname ?>");
            localStoreData("name", "<?php echo $user->lastname ?>");
            localStoreData("api_server", "<?php echo $_SERVER['SERVER_NAME'] ?>");
            localStoreData("api_token", "<?php echo newToken() ?>");
            localStoreData("api_uri", "<?php echo $_SERVER['SCRIPT_NAME'] ?>");

            // Initialiser les données de time tracking avec délai
            setTimeout(function() {
                initializeTimeclockData();
            }, 200);
        }

        // Initialisation avec fallback pour OnsenUI
        if (typeof ons !== 'undefined') {
            ons.ready(initializeApp);
        } else {
            // Fallback si OnsenUI n'est pas encore chargé
            document.addEventListener('DOMContentLoaded', function() {
                function waitForOns() {
                    if (typeof ons !== 'undefined') {
                        ons.ready(initializeApp);
                    } else {
                        setTimeout(waitForOns, 100);
                    }
                }
                waitForOns();
            });
        }

        // Fonction pour initialiser les données de time tracking
        function initializeTimeclockData() {
            try {
                // Status depuis les données PHP
                userTimeclockStatus = window.appMobTimeTouch.isClocked ? 'clocked_in' : 'clocked_out';
                console.log('Current timeclock status:', userTimeclockStatus);
                
                // Initialiser TimeclockAPI si disponible avec vérification
                if (typeof window.TimeclockAPI !== 'undefined' && window.TimeclockAPI && typeof window.TimeclockAPI.init === 'function') {
                    window.TimeclockAPI.init(window.appMobTimeTouch);
                    console.log('TimeclockAPI initialized successfully');
                } else {
                    console.log('TimeclockAPI not available or not fully loaded');
                    // Réessayer après un délai
                    setTimeout(function() {
                        if (typeof window.TimeclockAPI !== 'undefined' && window.TimeclockAPI && typeof window.TimeclockAPI.init === 'function') {
                            window.TimeclockAPI.init(window.appMobTimeTouch);
                            console.log('TimeclockAPI initialized on retry');
                        }
                    }, 500);
                }
            } catch (error) {
                console.error('Error initializing timeclock data:', error);
            }
        }

        // Fonction de nettoyage de l'historique
        function cleanHistorique() {
            console.log('Cleaning history...');
        }

        // Fonctions de stockage local
        function localStoreData(key, value) {
            if (typeof(Storage) !== "undefined") {
                localStorage.setItem(key, value);
            }
        }

        function localGetData(key) {
            if (typeof(Storage) !== "undefined") {
                return localStorage.getItem(key);
            }
            return null;
        }

        /**
         * Go to home page (pour toolbar logo)
         */
        function goToHome() {
            console.log('Already on home page - refreshing instead');
            
            // Si on est déjà sur la page d'accueil, on actualise
            ons.notification.toast('<?php echo $langs->trans("RefreshingData"); ?>...', {timeout: 1000});
            setTimeout(function() {
                location.reload();
            }, 500);
        }
        
        /**
         * Show home page (pour tabbar)
         */
        function showHomePage() {
            console.log('Show home page - already on home');
            
            // Marquer l'onglet comme actif
            var tabHome = document.getElementById('tabHome');
            if (tabHome) {
                // Réinitialiser tous les onglets
                var allTabs = document.querySelectorAll('ons-tab');
                allTabs.forEach(function(tab) {
                    tab.removeAttribute('active');
                });
                
                // Activer l'onglet home
                tabHome.setAttribute('active', '');
            }
            
            // Optionnel: actualiser la page si nécessaire
            // location.reload();
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

        // Fonction pour fermer la session
        function closeSession() {
            // Fermer le menu latéral
            var sideMenu = document.getElementById('sidemenu');
            if (sideMenu) {
                try {
                    sideMenu.close();
                } catch (e) {
                    console.log('Menu already closed');
                }
            }
            // Rediriger vers la déconnexion
            window.location.href = '<?php echo DOL_URL_ROOT; ?>/user/logout.php';
        }

        // Fonction pour aller à une page
        function gotoPage(pageId) {
            // Fermer le menu latéral
            var sideMenu = document.getElementById('sidemenu');
            if (sideMenu) {
                try {
                    sideMenu.close();
                } catch (e) {
                    console.log('Menu already closed');
                }
            }
            
            console.log('Going to page:', pageId);
        }
        
        // Exposer les fonctions globalement pour la toolbar et tabbar
        window.goToHome = goToHome;
        window.toggleMenu = toggleMenu;
        window.showHomePage = showHomePage;
    </script>

</body>
</html>
<?php
// Log pour debug
dol_syslog("AppMobTimeTouch home.php loaded for user " . $user->id . " with status: " . ($is_clocked_in ? 'clocked_in' : 'clocked_out'), LOG_INFO);
?>