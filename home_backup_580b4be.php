<?php
/**
 * home.php refactorisé - SOLID Étape 4: Contrôleurs (SRP + OCP + DIP)
 * 
 * Respecte le principe SRP : Responsabilité unique pour l'entrée de l'application
 * Respecte le principe OCP : Extensible via contrôleurs
 * Respecte le principe DIP : Dépend d'abstractions (contrôleurs et services)
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

// Vérifier si la fonction isModEnabled existe (compatibilité)
if (!function_exists('isModEnabled')) {
	function isModEnabled($module)
	{
		global $conf;
		return !empty($conf->$module->enabled);
	}
}

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

// Load SOLID architecture components - Étape 1: Configuration centralisée
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';

// Load SOLID architecture components - Étape 2: Helpers utilitaires (SRP + OCP)
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/LocationHelper.php';

// Load SOLID architecture components - Étape 3: Services métier avec interfaces (DIP + ISP)
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/TimeclockServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/DataServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/DataService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/TimeclockService.php';

// Load SOLID architecture components - Étape 4: Contrôleurs (SRP + OCP + DIP)
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/BaseController.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/HomeController.php';

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "users", "companies", "errors"));

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
    
    // Traitement via le contrôleur SOLID
    $templateData = $controller->index();
    
    // Variables pour compatibilité template (extraction des données)
    extract($templateData);
    
    // Debug: Vérifier les types de pointage
    dol_syslog("HOME.PHP: Timeclock types count: " . count($timeclock_types ?? []), LOG_DEBUG);
    dol_syslog("HOME.PHP: Default type ID: " . ($default_type_id ?? 'undefined'), LOG_DEBUG);
    
    dol_syslog("HOME.PHP SOLID: Controller processing completed successfully", LOG_DEBUG);
    
    // Inclusion du template avec les données préparées
    include "tpl/home.tpl";
    
} catch (Exception $e) {
    // Gestion centralisée des erreurs
    dol_syslog("HOME.PHP SOLID: Controller error - " . $e->getMessage(), LOG_ERROR);
    accessforbidden($e->getMessage());
}
?>