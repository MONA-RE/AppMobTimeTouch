<?php
// Test Dolibarr timezone functions
// We need to include Dolibarr but avoid login redirect

// Direct database access for the test
$host = 'localhost';
$user = 'dev-doli'; 
$pass = 'dev-doli';
$dbname = 'dev-smta';

$mysqli = new mysqli($host, $user, $pass, $dbname);
$result = $mysqli->query("SELECT clock_in_time, UNIX_TIMESTAMP(clock_in_time) as unix_ts FROM llx_timeclock_records WHERE rowid = 67");
$row = $result->fetch_assoc();

echo "<h2>Dolibarr dol_print_date() Analysis</h2>\n";

// Simulate what different approaches would show
$raw_datetime = $row['clock_in_time'];        // "2025-08-12 09:38:20"
$unix_timestamp = $row['unix_ts'];            // 1754984300

echo "<strong>Raw MySQL datetime:</strong> $raw_datetime<br>\n";
echo "<strong>MySQL UNIX_TIMESTAMP():</strong> $unix_timestamp<br>\n";
echo "<strong>PHP date() of timestamp:</strong> " . date('Y-m-d H:i:s', $unix_timestamp) . "<br>\n";

echo "<h3>The Issue Analysis</h3>\n";

echo "<p><strong>MySQL Behavior:</strong></p>\n";
echo "- MySQL stores '2025-08-12 09:38:20' as CEST local time<br>\n";
echo "- UNIX_TIMESTAMP() converts it to Unix timestamp: $unix_timestamp<br>\n"; 
echo "- PHP date() interprets the timestamp back in CEST: " . date('H:i', $unix_timestamp) . "<br>\n";

echo "<p><strong>list.php (Working correctly):</strong></p>\n";
echo "- Uses raw MySQL string directly in dol_print_date()<br>\n";
echo "- dol_print_date('$raw_datetime', 'dayhour', 'tzuser') → 11:38<br>\n";
echo "- This adds +2h to 09:38 = 11:38 ✓<br>\n";

echo "<p><strong>ClockOut Modal (Incorrect):</strong></p>\n";
echo "- TimeclockRecord.fetch() converts to Unix: $unix_timestamp<br>\n";
echo "- HomeController passes this timestamp to template<br>\n";
echo "- dol_print_date($unix_timestamp, 'hour', 'tzuser') → 14:38<br>\n";
echo "- This adds +2h to an already-local timestamp = double conversion ✗<br>\n";

echo "<h3>Root Cause:</h3>\n";
echo "<strong style='color: red;'>Double timezone conversion!</strong><br>\n";
echo "1. MySQL UNIX_TIMESTAMP() already considers local timezone<br>\n";
echo "2. dol_print_date(..., 'tzuser') adds timezone offset again<br>\n";
echo "3. Result: 09:38 → timestamp (09:38 CEST) → +2h = 11:38 → +3h more = 14:38<br>\n";

echo "<h3>Solution:</h3>\n";
echo "Either:<br>\n";
echo "1. Pass raw MySQL datetime string (like list.php does)<br>\n";
echo "2. Use 'tzserver' instead of 'tzuser' for converted timestamps<br>\n";
echo "3. Store timestamps in UTC and convert properly<br>\n";

$mysqli->close();
?>