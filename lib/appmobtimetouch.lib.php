<?php
/* Copyright (C) 2025 SuperAdmin
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    appmobtimetouch/lib/appmobtimetouch.lib.php
 * \ingroup appmobtimetouch
 * \brief   Library files with common functions for AppMobTimeTouch
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function appmobtimetouchAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("appmobtimetouch@appmobtimetouch");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/appmobtimetouch/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/appmobtimetouch/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/appmobtimetouch/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@appmobtimetouch:/appmobtimetouch/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@appmobtimetouch:/appmobtimetouch/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'appmobtimetouch@appmobtimetouch');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'appmobtimetouch@appmobtimetouch', 'remove');

	return $head;
}

// ============================================================================
// ÉTAPE 2: FONCTIONS UTILITAIRES POUR LE TIME TRACKING
// ============================================================================

/**
 * Convert seconds to readable time format (HhMM)
 *
 * @param int $seconds Number of seconds
 * @return string Formatted time string (e.g., "2h30")
 */
function convertSecondsToReadableTime($seconds)
{
    if ($seconds <= 0) {
        return '0h00';
    }
    
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    return sprintf('%dh%02d', $hours, $minutes);
}

/**
 * Convert minutes to readable time format (HhMM)
 *
 * @param int $minutes Number of minutes
 * @return string Formatted time string (e.g., "2h30")
 */
function convertMinutesToReadableTime($minutes)
{
    if ($minutes <= 0) {
        return '0h00';
    }
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    return sprintf('%dh%02d', $hours, $mins);
}

/**
 * Format duration for display (alternative to convertSecondToTime from Dolibarr)
 *
 * @param int $seconds Duration in seconds
 * @param string $format Format type ('short', 'long', 'decimal')
 * @return string Formatted duration
 */
function formatTimeclockDuration($seconds, $format = 'short')
{
    if ($seconds <= 0) {
        return ($format == 'decimal') ? '0.00' : '0h00';
    }
    
    switch ($format) {
        case 'decimal':
            return number_format($seconds / 3600, 2);
            
        case 'long':
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $secs = $seconds % 60;
            
            $parts = array();
            if ($hours > 0) $parts[] = $hours . 'h';
            if ($minutes > 0) $parts[] = $minutes . 'm';
            if ($secs > 0 && $hours == 0) $parts[] = $secs . 's';
            
            return implode(' ', $parts);
            
        case 'short':
        default:
            return convertSecondsToReadableTime($seconds);
    }
}

/**
 * Get current timeclock status for a user
 *
 * @param int $user_id User ID
 * @param DoliDB $db Database connection
 * @return array Status information
 */
function getTimeclockStatus($user_id, $db)
{
    $status = array(
        'is_clocked_in' => false,
        'active_record_id' => 0,
        'clock_in_time' => null,
        'current_duration' => 0,
        'location' => '',
        'type_id' => 0
    );
    
    if (empty($user_id) || !is_object($db)) {
        return $status;
    }
    
    $sql = "SELECT rowid, clock_in_time, location_in, fk_timeclock_type";
    $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_records";
    $sql .= " WHERE fk_user = ".((int) $user_id);
    $sql .= " AND status = 2"; // STATUS_IN_PROGRESS
    $sql .= " AND clock_out_time IS NULL";
    $sql .= " AND entity IN (".getEntity('user').")";
    $sql .= " ORDER BY clock_in_time DESC";
    $sql .= " LIMIT 1";
    
    $resql = $db->query($sql);
    if ($resql) {
        if ($db->num_rows($resql)) {
            $obj = $db->fetch_object($resql);
            $clock_in_timestamp = $db->jdate($obj->clock_in_time);
            
            $status['is_clocked_in'] = true;
            $status['active_record_id'] = $obj->rowid;
            $status['clock_in_time'] = $clock_in_timestamp;
            $status['current_duration'] = dol_now() - $clock_in_timestamp;
            $status['location'] = $obj->location_in;
            $status['type_id'] = $obj->fk_timeclock_type;
        }
        $db->free($resql);
    }
    
    return $status;
}

/**
 * Get today's work summary for a user
 *
 * @param int $user_id User ID
 * @param DoliDB $db Database connection
 * @return array Today's summary
 */
function getTodayWorkSummary($user_id, $db)
{
    $summary = array(
        'total_hours' => 0,
        'total_breaks' => 0,
        'records_count' => 0,
        'is_overtime' => false
    );
    
    if (empty($user_id) || !is_object($db)) {
        return $summary;
    }
    
    $today = date('Y-m-d');
    
    $sql = "SELECT COUNT(*) as nb_records,";
    $sql .= " SUM(CASE WHEN work_duration IS NOT NULL THEN work_duration ELSE 0 END) as total_minutes,";
    $sql .= " SUM(CASE WHEN break_duration IS NOT NULL THEN break_duration ELSE 0 END) as total_break_minutes";
    $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_records";
    $sql .= " WHERE fk_user = ".((int) $user_id);
    $sql .= " AND DATE(clock_in_time) = '".$db->escape($today)."'";
    $sql .= " AND status = 3"; // STATUS_COMPLETED
    $sql .= " AND entity IN (".getEntity('user').")";
    
    $resql = $db->query($sql);
    if ($resql) {
        $obj = $db->fetch_object($resql);
        if ($obj) {
            $summary['total_hours'] = round($obj->total_minutes / 60, 2);
            $summary['total_breaks'] = $obj->total_break_minutes;
            $summary['records_count'] = $obj->nb_records;
            
            // Check for overtime (default threshold: 8 hours)
            $overtime_threshold = 8;
            if (function_exists('TimeclockConfig::getValue')) {
                $overtime_threshold = TimeclockConfig::getValue($db, 'OVERTIME_THRESHOLD', 8);
            }
            $summary['is_overtime'] = ($summary['total_hours'] > $overtime_threshold);
        }
        $db->free($resql);
    }
    
    return $summary;
}

/**
 * Get recent timeclock records for a user
 *
 * @param int $user_id User ID
 * @param DoliDB $db Database connection
 * @param int $limit Number of records to retrieve
 * @param string $date_start Start date (YYYY-MM-DD)
 * @param string $date_end End date (YYYY-MM-DD)
 * @return array Array of record objects
 */
function getRecentTimeclockRecords($user_id, $db, $limit = 5, $date_start = '', $date_end = '')
{
    $records = array();
    
    if (empty($user_id) || !is_object($db)) {
        return $records;
    }
    
    if (empty($date_start)) {
        $date_start = date('Y-m-d', strtotime('-7 days'));
    }
    if (empty($date_end)) {
        $date_end = date('Y-m-d');
    }
    
    $sql = "SELECT r.*, t.label as type_label, t.color as type_color";
    $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_records r";
    $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."timeclock_types t ON t.rowid = r.fk_timeclock_type";
    $sql .= " WHERE r.fk_user = ".((int) $user_id);
    $sql .= " AND DATE(r.clock_in_time) >= '".$db->escape($date_start)."'";
    $sql .= " AND DATE(r.clock_in_time) <= '".$db->escape($date_end)."'";
    $sql .= " AND r.entity IN (".getEntity('user').")";
    $sql .= " ORDER BY r.clock_in_time DESC";
    $sql .= " LIMIT ".((int) $limit);
    
    $resql = $db->query($sql);
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $records[] = $obj;
        }
        $db->free($resql);
    }
    
    return $records;
}

/**
 * Check if a user has sufficient permissions for timeclock operations
 *
 * @param object $user User object
 * @param string $permission Permission to check (read, write, readall, validate, export)
 * @return bool True if user has permission
 */
function checkTimeclockPermission($user, $permission = 'read')
{
    if (!is_object($user) || $user->id <= 0) {
        return false;
    }
    
    // Check if user rights structure exists
    if (!isset($user->rights->appmobtimetouch->timeclock)) {
        return false;
    }
    
    switch ($permission) {
        case 'read':
            return !empty($user->rights->appmobtimetouch->timeclock->read);
        case 'write':
            return !empty($user->rights->appmobtimetouch->timeclock->write);
        case 'delete':
            return !empty($user->rights->appmobtimetouch->timeclock->delete);
        case 'readall':
            return !empty($user->rights->appmobtimetouch->timeclock->readall);
        case 'validate':
            return !empty($user->rights->appmobtimetouch->timeclock->validate);
        case 'export':
            return !empty($user->rights->appmobtimetouch->timeclock->export);
        case 'config':
            return !empty($user->rights->appmobtimetouch->timeclock->config);
        default:
            return false;
    }
}

/**
 * Format a time value for mobile display
 *
 * @param mixed $time Time value (timestamp, datetime string, or seconds)
 * @param string $format Display format ('time', 'datetime', 'duration')
 * @return string Formatted time
 */
function formatTimeclockDisplay($time, $format = 'time')
{
    global $langs;
    
    if (empty($time)) {
        return '-';
    }
    
    switch ($format) {
        case 'datetime':
            if (is_numeric($time)) {
                return dol_print_date($time, 'dayhour');
            } else {
                // Assume it's a database datetime string
                global $db;
                return dol_print_date($db->jdate($time), 'dayhour');
            }
            break;
            
        case 'time':
            if (is_numeric($time)) {
                return dol_print_date($time, 'hour');
            } else {
                global $db;
                return dol_print_date($db->jdate($time), 'hour');
            }
            break;
            
        case 'duration':
            // Assume $time is in seconds
            return convertSecondsToReadableTime($time);
            break;
            
        default:
            return $time;
    }
}

/**
 * Get available timeclock types for mobile interface
 *
 * @param DoliDB $db Database connection
 * @param bool $active_only Only return active types
 * @return array Array of types
 */
function getTimeclockTypesForMobile($db, $active_only = true)
{
    $types = array();
    
    if (!is_object($db)) {
        return $types;
    }
    
    $sql = "SELECT rowid, code, label, color, position";
    $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_types";
    $sql .= " WHERE entity IN (".getEntity('timeclocktype').")";
    if ($active_only) {
        $sql .= " AND active = 1";
    }
    $sql .= " ORDER BY position ASC, label ASC";
    
    $resql = $db->query($sql);
    if ($resql) {
        while ($obj = $db->fetch_object($resql)) {
            $types[] = array(
                'id' => $obj->rowid,
                'code' => $obj->code,
                'label' => $obj->label,
                'color' => $obj->color,
                'position' => $obj->position
            );
        }
        $db->free($resql);
    }
    
    return $types;
}

/**
 * Get weekly summary data for mobile display
 *
 * @param int $user_id User ID
 * @param DoliDB $db Database connection
 * @param int $year Year (optional, default current year)
 * @param int $week_number Week number (optional, default current week)
 * @return array|null Weekly summary data or null if not found
 */
function getWeeklySummaryForMobile($user_id, $db, $year = null, $week_number = null)
{
    if (empty($user_id) || !is_object($db)) {
        return null;
    }
    
    if (empty($year)) {
        $year = date('Y');
    }
    if (empty($week_number)) {
        $week_number = date('W');
    }
    
    $sql = "SELECT *";
    $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_weekly_summary";
    $sql .= " WHERE fk_user = ".((int) $user_id);
    $sql .= " AND year = ".((int) $year);
    $sql .= " AND week_number = ".((int) $week_number);
    $sql .= " AND entity IN (".getEntity('weeklysummary').")";
    
    $resql = $db->query($sql);
    if ($resql) {
        if ($db->num_rows($resql)) {
            $obj = $db->fetch_object($resql);
            $db->free($resql);
            return $obj;
        }
        $db->free($resql);
    }
    
    return null;
}

/**
 * Generate a safe message for mobile display
 *
 * @param string $message Original message
 * @param string $type Message type (error, warning, success, info)
 * @return string Formatted message for mobile
 */
function formatMobileMessage($message, $type = 'info')
{
    // Sanitize the message
    $message = dol_escape_htmltag($message);
    
    // Add appropriate icon based on type
    $icon = '';
    switch ($type) {
        case 'error':
            $icon = '<ons-icon icon="md-warning" style="color: #f44336;"></ons-icon> ';
            break;
        case 'warning':
            $icon = '<ons-icon icon="md-warning" style="color: #ff9800;"></ons-icon> ';
            break;
        case 'success':
            $icon = '<ons-icon icon="md-check-circle" style="color: #4caf50;"></ons-icon> ';
            break;
        case 'info':
        default:
            $icon = '<ons-icon icon="md-info" style="color: #2196f3;"></ons-icon> ';
            break;
    }
    
    return $icon . $message;
}

/**
 * Check if the current request is from a mobile device
 *
 * @return bool True if mobile device detected
 */
function isMobileDevice()
{
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        
        $mobile_agents = array(
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 
            'BlackBerry', 'webOS', 'Windows Phone'
        );
        
        foreach ($mobile_agents as $agent) {
            if (strpos($user_agent, $agent) !== false) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Get configuration value with mobile-specific defaults
 *
 * @param DoliDB $db Database connection
 * @param string $name Configuration name
 * @param mixed $default Default value
 * @return mixed Configuration value
 */
function getTimeclockConfig($db, $name, $default = null)
{
    // Try to use TimeclockConfig class if available
    if (class_exists('TimeclockConfig')) {
        return TimeclockConfig::getValue($db, $name, $default);
    }
    
    // Fallback to global configuration
    global $conf;
    $const_name = 'APPMOBTIMETOUCH_' . $name;
    
    if (isset($conf->global->$const_name)) {
        return $conf->global->$const_name;
    }
    
    return $default;
}

/**
 * Log activity for debugging purposes
 *
 * @param string $action Action performed
 * @param mixed $data Additional data
 * @param int $user_id User ID (optional)
 */
function logTimeclockActivity($action, $data = null, $user_id = null)
{
    $message = 'AppMobTimeTouch Mobile: ' . $action;
    
    if ($user_id) {
        $message .= ' (User: ' . $user_id . ')';
    }
    
    if ($data) {
        $message .= ' - Data: ' . json_encode($data);
    }
    
    dol_syslog($message, LOG_DEBUG);
}