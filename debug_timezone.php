<?php
// Debug script to understand timezone conversion issue
require_once '../../main.inc.php';

echo "<h2>Debug Timezone Conversion Issue - Record ID 67</h2>\n";

// Get the record from database
$sql = "SELECT rowid, clock_in_time, UNIX_TIMESTAMP(clock_in_time) as unix_ts FROM ".MAIN_DB_PREFIX."timeclock_records WHERE rowid = 67";
$resql = $db->query($sql);

if ($resql && $db->num_rows($resql)) {
    $obj = $db->fetch_object($resql);
    
    echo "<h3>Database Values:</h3>\n";
    echo "Raw clock_in_time: " . $obj->clock_in_time . "<br>\n";
    echo "UNIX_TIMESTAMP(): " . $obj->unix_ts . "<br>\n";
    echo "Unix timestamp in date: " . date('Y-m-d H:i:s', $obj->unix_ts) . "<br>\n";
    
    echo "<h3>PHP/System Info:</h3>\n";
    echo "PHP date_default_timezone_get(): " . date_default_timezone_get() . "<br>\n";
    echo "Current PHP time: " . date('Y-m-d H:i:s') . "<br>\n";
    echo "Current dol_now(): " . date('Y-m-d H:i:s', dol_now()) . "<br>\n";
    
    echo "<h3>Dolibarr Functions Test:</h3>\n";
    
    // Test 1: Direct string with tzserver
    echo "dol_print_date('{$obj->clock_in_time}', 'dayhour', 'tzserver'): " . dol_print_date($obj->clock_in_time, 'dayhour', 'tzserver') . "<br>\n";
    
    // Test 2: Direct string with tzuser  
    echo "dol_print_date('{$obj->clock_in_time}', 'dayhour', 'tzuser'): " . dol_print_date($obj->clock_in_time, 'dayhour', 'tzuser') . "<br>\n";
    
    // Test 3: Unix timestamp with tzserver
    echo "dol_print_date({$obj->unix_ts}, 'dayhour', 'tzserver'): " . dol_print_date($obj->unix_ts, 'dayhour', 'tzserver') . "<br>\n";
    
    // Test 4: Unix timestamp with tzuser
    echo "dol_print_date({$obj->unix_ts}, 'dayhour', 'tzuser'): " . dol_print_date($obj->unix_ts, 'dayhour', 'tzuser') . "<br>\n";
    
    // Test 5: What TimeclockRecord.fetch() would do
    echo "<h3>TimeclockRecord Simulation:</h3>\n";
    $converted_timestamp = $obj->unix_ts;  // What TimeclockRecord does
    echo "Converted timestamp: " . $converted_timestamp . "<br>\n";
    echo "dol_print_date(converted, 'hour', 'tzuser'): " . dol_print_date($converted_timestamp, 'hour', 'tzuser') . "<br>\n";
    
    // Test 6: Check user timezone settings
    echo "<h3>User Session Info:</h3>\n";
    echo "SESSION dol_tz_string: " . ($_SESSION['dol_tz_string'] ?? 'NOT SET') . "<br>\n";
    echo "SESSION dol_tz: " . ($_SESSION['dol_tz'] ?? 'NOT SET') . "<br>\n";
    echo "SESSION dol_dst: " . ($_SESSION['dol_dst'] ?? 'NOT SET') . "<br>\n";
}
?>