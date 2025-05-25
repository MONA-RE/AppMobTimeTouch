<?php
/* Copyright (C) 2025 SuperAdmin
 * Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 *	\file       appmobtimetouch/index.php
 *	\ingroup    appmobtimetouch
 *	\brief      Home page of appmobtimetouch mobile application
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
    $res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/appmobtimetouch/lib/appmobtimetouch.lib.php');
dol_include_once('/appmobtimetouch/core/modules/modAppMobTimeTouch.class.php');

// Load translation files required by the page
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

$action = GETPOST('action', 'aZ09');
$mainmenu = GETPOST('mainmenu', 'aZ09');

// Security check
if (!$user->rights->appmobtimetouch->timeclock->read) {
    accessforbidden($langs->trans('AppmobtimetouchReadRights'));
}

$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
    $action = '';
    $socid = $user->socid;
}

$max = 5;
$now = dol_now();

// Get version number from module class for cache busting
$moduleInstance = new modAppMobTimeTouch($db);
$version = $moduleInstance->version;

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
    <meta name="viewport" content="width=navigator-width, height=navigator-height, initial-scale=1, user-scalable=no">
    <title>AppMobTimeTouch for Dolibarr <?php echo $version;?></title>
    
    <!-- CSS files with version parameter -->
    <link rel="stylesheet" href="css/onsenui.min.css?v=<?php echo $version;?>">
    <link rel="stylesheet" href="css/onsen-css-components.min.css?v=<?php echo $version;?>">
    <link rel="stylesheet" href="css/font_awesome/css/font-awesome.min.css?v=<?php echo $version;?>">
    <link rel="stylesheet" href="css/index.css?v=<?php echo $version;?>">
    
    <!-- Add manifest link -->
    <link rel="manifest" href="manifest.json" />

    <!-- JavaScript files with version parameter -->
    <script type="text/javascript" src="js/onsenui.min.js?v=<?php echo $version;?>"></script>
    
</head>

<body>
    <ons-navigator id="myNavigator" page="splitter.html">
    </ons-navigator>

    <template id="splitter.html">
        <ons-page>
            <ons-splitter id="mySplitter">
                <ons-splitter-side page="rightmenu.html" id="rightmenu" side="right" width="220px" collapse swipeable swipe-target-width="50px">
                </ons-splitter-side>
                <ons-splitter-content page="tabbar.html">
                </ons-splitter-content>
            </ons-splitter>
        </ons-page>
    </template>

    <template id="tabbar.html">
        <?php include "tpl/parts/tabbar.tpl"; ?>
    </template>

    <template id="rightmenu.html">
        <?php include "tpl/parts/rightmenu.tpl"; ?>
    </template>

    <ons-modal direction="up" id="sablier">
        <div style="text-align: center;">
            <p>
                <ons-icon icon="md-spinner" size="45px" spin></ons-icon>
            </p>
            <p id="loadingmessage"><span><?php echo $langs->trans("loadingInProgress"); ?></span></p>
        </div>
    </ons-modal>

    <?php
    //Build complet de l'application via php
    $dir = "tpl";
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..' || $file == 'parts') {
                continue;
            }
            echo "<template id=\"" . str_replace(".tpl", "Application", $file) . "\">\n";
            include $dir . '/' . $file;
            echo "</template>\n";
        }
        closedir($dh);
    }
    ?>

    <script type="text/javascript">
        // Variables globales pour le time tracking
        var globalCurrentPage = "homeApplication";
        var globalMyNavigator = null;
        var userTimeclockStatus = null; // null, 'clocked_in', 'clocked_out'

        ons.ready(function () {
            console.log("ONS ready in AppMobTimeTouch index.php");

            globalCurrentPage = "homeApplication";
            globalMyNavigator = document.getElementById('myNavigator');

            console.log('AppMobTimeTouch navigator initialized');

            let onsRightMenu = document.getElementById('rightmenu');
            if (onsRightMenu != undefined)
                onsRightMenu.close();

            //pour gérer les retours sur une page
            globalMyNavigator.addEventListener('postpop', function (event) {
                let lapage = event.enterPage.id;

                if (lapage == 'ONSConfig') {
                    return;
                }
            });

            //On nettoie l'historique au lancement de l'appli
            cleanHistorique();

            //stockage des donnees de base
            localStoreData("email", "<?php echo $user->email ?>");
            localStoreData("firstname", "<?php echo $user->firstname ?>");
            localStoreData("name", "<?php echo $user->lastname ?>");
            localStoreData("api_server", "<?php echo $_SERVER['SERVER_NAME'] ?>");
            localStoreData("api_token", "<?php echo newToken() ?>");
            localStoreData("api_uri", "<?php echo $_SERVER['SCRIPT_NAME'] ?>");

            // Initialiser les données de time tracking
            initializeTimeclockData();
        });

        // Fonction pour initialiser les données de time tracking
        function initializeTimeclockData() {
            // Pour l'instant, on simule un statut
            // Dans la vraie version, cela viendra de la base de données
            userTimeclockStatus = localGetData('timeclock_status') || 'clocked_out';
            console.log('Current timeclock status:', userTimeclockStatus);
        }

        // Fonction de nettoyage de l'historique (à implémenter)
        function cleanHistorique() {
            // Placeholder pour la fonction de nettoyage
            console.log('Cleaning history...');
        }

        // Fonctions de stockage local (à implémenter)
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

        // Fonction pour fermer la session
        function closeSession() {
            // Fermer le menu latéral
            document.getElementById('rightmenu').close();
            // Rediriger vers la déconnexion
            window.location.href = '<?php echo DOL_URL_ROOT; ?>/user/logout.php';
        }

        // Fonction pour aller à une page
        function gotoPage(pageId) {
            // Fermer le menu latéral
            document.getElementById('rightmenu').close();
            
            // Pour l'instant, juste un log
            console.log('Going to page:', pageId);
            
            // Navigation vers la page (à implémenter)
            // globalMyNavigator.pushPage(pageId);
        }
    </script>
    
    <script type="text/javascript" src="js/index.min.js?ver=<?php echo time();?>"></script>

</body>
</html>