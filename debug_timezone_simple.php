<?php
// Simple debug script for timezone issue
echo "<h2>Timezone Debug Analysis</h2>\n";

// Database connection
$host = 'localhost';
$user = 'dev-doli'; 
$pass = 'dev-doli';
$dbname = 'dev-smta';

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Get record 67 data
$result = $mysqli->query("SELECT rowid, clock_in_time, UNIX_TIMESTAMP(clock_in_time) as unix_ts FROM llx_timeclock_records WHERE rowid = 67");
$row = $result->fetch_assoc();

echo "<h3>Analysis of Record ID 67</h3>\n";
echo "<strong>Database timezone (CEST):</strong> " . $mysqli->query("SELECT @@system_time_zone")->fetch_row()[0] . "<br>\n";
echo "<strong>Raw clock_in_time:</strong> " . $row['clock_in_time'] . "<br>\n";
echo "<strong>UNIX_TIMESTAMP():</strong> " . $row['unix_ts'] . "<br>\n";

// Current time analysis
$now = time();
$now_mysql = $mysqli->query("SELECT NOW()")->fetch_row()[0];
$now_unix_mysql = $mysqli->query("SELECT UNIX_TIMESTAMP(NOW())")->fetch_row()[0];

echo "<h3>Current Time Analysis</h3>\n";
echo "<strong>PHP time():</strong> $now (" . date('Y-m-d H:i:s', $now) . " " . date('T') . ")<br>\n";  
echo "<strong>MySQL NOW():</strong> $now_mysql<br>\n";
echo "<strong>MySQL UNIX_TIMESTAMP(NOW()):</strong> $now_unix_mysql (" . date('Y-m-d H:i:s', $now_unix_mysql) . " " . date('T', $now_unix_mysql) . ")<br>\n";

// Test different time interpretations
$timestamp_record = $row['unix_ts'];
echo "<h3>Time Display Tests</h3>\n";
echo "<strong>Record timestamp as PHP date:</strong> " . date('Y-m-d H:i:s', $timestamp_record) . " (" . date('T', $timestamp_record) . ")<br>\n";

// Test what happens with different timezone assumptions
echo "<h3>Manual Timezone Calculations</h3>\n";

// If MySQL stored in UTC and we need local time
$utc_offset = 2 * 3600; // CEST is UTC+2  
echo "<strong>If DB is UTC, local CEST would be:</strong> " . date('Y-m-d H:i:s', $timestamp_record + $utc_offset) . "<br>\n";

// If MySQL stored in local time and we convert again
echo "<strong>If DB is local CEST, UTC would be:</strong> " . date('Y-m-d H:i:s', $timestamp_record - $utc_offset) . "<br>\n";

// What should be the expected display times
echo "<h3>Expected Display Times</h3>\n";
echo "<strong>list.php shows:</strong> 11:38 (from 09:38 + 2h = CEST conversion)<br>\n";
echo "<strong>ClockOut modal shows:</strong> 14:38 (from 09:38 + 5h = ??? double conversion?)<br>\n";

$mysqli->close();
?>