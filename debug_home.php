<?php
/**
 * Script de debug pour home.php
 */

// Load Dolibarr environment minimal
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Load required helpers
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/DataService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/TimeclockService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/HomeController.php';

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

echo "<h1>🔧 Debug Home.php</h1>\n";

try {
    // Test HomeController
    $dataService = new DataService($db);
    $timeclockService = new TimeclockService($db, $dataService);
    $controller = new HomeController($db, $user, $langs, $conf, $timeclockService, $dataService);
    
    echo "<h2>✅ HomeController créé avec succès</h2>\n";
    
    // Test controller.index()
    $data = $controller->index();
    
    echo "<h2>📊 Données du contrôleur:</h2>\n";
    echo "<pre>";
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            echo "$key: array[" . count($value) . "]\n";
        } elseif (is_object($value)) {
            echo "$key: object " . get_class($value) . "\n";
        } else {
            echo "$key: " . var_export($value, true) . "\n";
        }
    }
    echo "</pre>";
    
    // Test variables essentielles
    $is_clocked_in = $data['is_clocked_in'] ?? false;
    $today_summary = $data['today_summary'] ?? ['total_hours' => 0, 'total_breaks' => 0];
    $weekly_summary = $data['weekly_summary'] ?? ['total_hours' => 0, 'days_worked' => 0];
    $recent_records = $data['recent_records'] ?? [];
    
    echo "<h2>🎯 Variables Template:</h2>\n";
    echo "is_clocked_in: " . ($is_clocked_in ? 'true' : 'false') . "<br>\n";
    echo "today_summary: " . (is_array($today_summary) ? 'array[' . count($today_summary) . ']' : 'not array') . "<br>\n";
    echo "weekly_summary: " . (is_array($weekly_summary) ? 'array[' . count($weekly_summary) . ']' : 'not array') . "<br>\n";
    echo "recent_records: " . (is_array($recent_records) ? 'array[' . count($recent_records) . ']' : 'not array') . "<br>\n";
    
    // Test inclusion template
    echo "<h2>🧪 Test Template StatusCard:</h2>\n";
    ob_start();
    include 'Views/components/StatusCard.tpl';
    $statusCard = ob_get_clean();
    echo "<div style='border: 1px solid #ccc; padding: 10px;'>$statusCard</div>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Erreur: " . $e->getMessage() . "</h2>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}
?>