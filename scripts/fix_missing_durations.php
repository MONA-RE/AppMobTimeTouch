<?php
/**
 * Script to fix missing work_duration in existing timeclock records
 * Run this once to calculate missing durations for records with clock_in_time and clock_out_time
 */

require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';

// Security check - only admin can run this
if (!$user->admin) {
    accessforbidden('Admin rights required');
}

print "=== Fixing Missing Work Durations ===\n";

// Find records with missing work_duration but having both clock_in_time and clock_out_time
$sql = "SELECT rowid, clock_in_time, clock_out_time, work_duration 
        FROM ".MAIN_DB_PREFIX."timeclock_records 
        WHERE clock_in_time IS NOT NULL 
        AND clock_out_time IS NOT NULL 
        AND (work_duration IS NULL OR work_duration = 0)
        ORDER BY rowid";

$resql = $db->query($sql);
if (!$resql) {
    print "ERROR: ".$db->lasterror()."\n";
    exit(1);
}

$count_fixed = 0;
$count_errors = 0;

while ($obj = $db->fetch_object($resql)) {
    print "Processing record ID {$obj->rowid}...\n";
    
    // Convert datetime strings to timestamps
    $clock_in_ts = strtotime($obj->clock_in_time);
    $clock_out_ts = strtotime($obj->clock_out_time);
    
    if ($clock_in_ts && $clock_out_ts) {
        $duration_seconds = $clock_out_ts - $clock_in_ts;
        $duration_minutes = round($duration_seconds / 60);
        
        print "  Clock In: {$obj->clock_in_time} ({$clock_in_ts})\n";
        print "  Clock Out: {$obj->clock_out_time} ({$clock_out_ts})\n";
        print "  Duration: {$duration_seconds} seconds = {$duration_minutes} minutes\n";
        
        if ($duration_minutes >= 0) {
            // Update the record
            $update_sql = "UPDATE ".MAIN_DB_PREFIX."timeclock_records 
                          SET work_duration = ".((int)$duration_minutes)." 
                          WHERE rowid = ".((int)$obj->rowid);
            
            if ($db->query($update_sql)) {
                print "  ✅ FIXED: Duration updated to {$duration_minutes} minutes\n";
                $count_fixed++;
            } else {
                print "  ❌ ERROR: Failed to update - ".$db->lasterror()."\n";
                $count_errors++;
            }
        } else {
            print "  ⚠️ SKIPPED: Invalid duration (negative)\n";
        }
    } else {
        print "  ❌ ERROR: Failed to convert timestamps\n";
        $count_errors++;
    }
    print "\n";
}

$db->free($resql);

print "=== Summary ===\n";
print "Records fixed: {$count_fixed}\n";
print "Errors: {$count_errors}\n";
print "Done!\n";
?>