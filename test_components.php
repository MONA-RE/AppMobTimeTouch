<?php
/**
 * Script de test pour vérifier les composants Views/components/
 * Test de compatibilité array/objet pour les templates
 */

// Load Dolibarr environment minimal
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Load required helpers
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Test Components</title></head><body>\n";
echo "<h1>🧪 Test Components AppMobTimeTouch</h1>\n";

// Test 1: WeeklySummary avec array
echo "<h2>Test 1: WeeklySummary avec array</h2>\n";
$weekly_summary = [
    'total_hours' => 35.5,
    'days_worked' => 4,
    'week_number' => 24,
    'overtime_hours' => 0,
    'expected_hours' => 40
];

try {
    ob_start();
    include 'Views/components/WeeklySummary.tpl';
    $output = ob_get_clean();
    echo "✅ WeeklySummary array test passed<br>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>$output</div>\n";
} catch (Exception $e) {
    echo "❌ WeeklySummary array test failed: " . $e->getMessage() . "<br>\n";
}

// Test 2: WeeklySummary avec array vide
echo "<h2>Test 2: WeeklySummary avec array vide</h2>\n";
$weekly_summary = ['total_hours' => 0, 'days_worked' => 0];

try {
    ob_start();
    include 'Views/components/WeeklySummary.tpl';
    $output = ob_get_clean();
    echo "✅ WeeklySummary empty array test passed<br>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>$output</div>\n";
} catch (Exception $e) {
    echo "❌ WeeklySummary empty array test failed: " . $e->getMessage() . "<br>\n";
}

// Test 3: Messages component
echo "<h2>Test 3: Messages component</h2>\n";
$error = 0;
$errors = [];
$messages = ['Test message de succès'];

try {
    ob_start();
    include 'Views/components/Messages.tpl';
    $output = ob_get_clean();
    echo "✅ Messages component test passed<br>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>$output</div>\n";
} catch (Exception $e) {
    echo "❌ Messages component test failed: " . $e->getMessage() . "<br>\n";
}

// Test 4: StatusCard component
echo "<h2>Test 4: StatusCard component</h2>\n";
$is_clocked_in = false;
$clock_in_time = null;
$current_duration = 0;
$active_record = null;

try {
    ob_start();
    include 'Views/components/StatusCard.tpl';
    $output = ob_get_clean();
    echo "✅ StatusCard component test passed<br>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>$output</div>\n";
} catch (Exception $e) {
    echo "❌ StatusCard component test failed: " . $e->getMessage() . "<br>\n";
}

// Test 5: SummaryCard component
echo "<h2>Test 5: SummaryCard component</h2>\n";
$today_summary = [
    'total_hours' => 8.5,
    'total_breaks' => 1.0,
    'first_clock_in' => '08:30',
    'last_clock_out' => '17:30'
];

try {
    ob_start();
    include 'Views/components/SummaryCard.tpl';
    $output = ob_get_clean();
    echo "✅ SummaryCard component test passed<br>\n";
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>$output</div>\n";
} catch (Exception $e) {
    echo "❌ SummaryCard component test failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>✅ Tests Terminés</h2>\n";
echo "<p>Si tous les tests passent, les composants sont compatibles avec la nouvelle architecture.</p>\n";

echo "</body></html>\n";
?>