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
 * \file    api/timeclock.php
 * \ingroup appmobtimetouch
 * \brief   API endpoints for timeclock operations
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
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

// Load required libraries
dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');
dol_include_once('/appmobtimetouch/class/weeklysummary.class.php');
dol_include_once('/appmobtimetouch/class/timeclockconfig.class.php');

// Load translations
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "errors"));

// Vérifier si la fonction isModEnabled existe (compatibilité)
if (!function_exists('isModEnabled')) {
    function isModEnabled($module)
    {
        global $conf;
        return !empty($conf->$module->enabled);
    }
}

/**
 * API Response handler
 */
class TimeclockAPI
{
    private $db;
    private $user;
    private $langs;

    public function __construct($db, $user, $langs)
    {
        $this->db = $db;
        $this->user = $user;
        $this->langs = $langs;
    }

    /**
     * Send JSON response
     */
    private function sendResponse($success, $data = null, $message = '', $error = '', $httpCode = 200)
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        
        $response = array(
            'success' => $success,
            'timestamp' => dol_now(),
            'message' => $message
        );

        if ($success) {
            $response['data'] = $data;
        } else {
            $response['error'] = $error;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Check user permissions
     */
    private function checkPermissions($action = 'read')
    {
        if (!isModEnabled('appmobtimetouch')) {
            $this->sendResponse(false, null, '', $this->langs->trans('ModuleNotEnabled'), 403);
        }

        switch ($action) {
            case 'read':
                if (empty($this->user->rights->appmobtimetouch->timeclock->read)) {
                    $this->sendResponse(false, null, '', $this->langs->trans('NotEnoughPermissions'), 403);
                }
                break;
            case 'write':
                if (empty($this->user->rights->appmobtimetouch->timeclock->write)) {
                    $this->sendResponse(false, null, '', $this->langs->trans('NotEnoughPermissions'), 403);
                }
                break;
            case 'readall':
                if (empty($this->user->rights->appmobtimetouch->timeclock->readall)) {
                    $this->sendResponse(false, null, '', $this->langs->trans('NotEnoughPermissions'), 403);
                }
                break;
        }
    }

    /**
     * Get current timeclock status
     */
    public function getStatus()
    {
        $this->checkPermissions('read');

        $timeclockrecord = new TimeclockRecord($this->db);
        $active_record_id = $timeclockrecord->getActiveRecord($this->user->id);
        
        $status = array(
            'is_clocked_in' => false,
            'active_record' => null,
            'clock_in_time' => null,
            'current_duration' => 0,
            'timeclock_type' => null
        );

        if ($active_record_id > 0) {
            $active_record = new TimeclockRecord($this->db);
            if ($active_record->fetch($active_record_id) > 0) {
                $status['is_clocked_in'] = true;
                $status['active_record'] = array(
                    'id' => $active_record->id,
                    'ref' => $active_record->ref,
                    'clock_in_time' => $active_record->clock_in_time,
                    'location_in' => $active_record->location_in,
                    'fk_timeclock_type' => $active_record->fk_timeclock_type
                );
                $status['clock_in_time'] = $this->db->jdate($active_record->clock_in_time);
                $status['current_duration'] = dol_now() - $status['clock_in_time'];

                // Get timeclock type info
                if (!empty($active_record->fk_timeclock_type)) {
                    $type = new TimeclockType($this->db);
                    if ($type->fetch($active_record->fk_timeclock_type) > 0) {
                        $status['timeclock_type'] = array(
                            'id' => $type->id,
                            'code' => $type->code,
                            'label' => $type->label,
                            'color' => $type->color
                        );
                    }
                }
            }
        }

        $this->sendResponse(true, $status, $this->langs->trans('StatusRetrieved'));
    }

    /**
     * Clock in
     */
    public function clockIn()
    {
        $this->checkPermissions('write');

        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $timeclock_type_id = isset($input['timeclock_type_id']) ? intval($input['timeclock_type_id']) : TimeclockType::getDefaultType($this->db);
        $location = isset($input['location']) ? trim($input['location']) : '';
        $latitude = isset($input['latitude']) ? floatval($input['latitude']) : null;
        $longitude = isset($input['longitude']) ? floatval($input['longitude']) : null;
        $note = isset($input['note']) ? trim($input['note']) : '';

        // Validate required location if configured
        $require_location = TimeclockConfig::getValue($this->db, 'REQUIRE_LOCATION', 0);
        if ($require_location && (empty($latitude) || empty($longitude))) {
            $this->sendResponse(false, null, '', $this->langs->trans('LocationRequiredForClockIn'), 400);
        }

        // Create timeclock record
        $timeclockrecord = new TimeclockRecord($this->db);
        $result = $timeclockrecord->clockIn($this->user, $timeclock_type_id, $location, $latitude, $longitude, $note);

        if ($result > 0) {
            // Return the created record info
            $record_data = array(
                'id' => $result,
                'clock_in_time' => dol_now(),
                'location' => $location,
                'timeclock_type_id' => $timeclock_type_id
            );

            $this->sendResponse(true, $record_data, $this->langs->trans('ClockInSuccess'));
        } else {
            $error_msg = !empty($timeclockrecord->error) ? $timeclockrecord->error : $this->langs->trans('ClockInError');
            $this->sendResponse(false, null, '', $error_msg, 400);
        }
    }

    /**
     * Clock out
     */
    public function clockOut()
    {
        $this->checkPermissions('write');

        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $location = isset($input['location']) ? trim($input['location']) : '';
        $latitude = isset($input['latitude']) ? floatval($input['latitude']) : null;
        $longitude = isset($input['longitude']) ? floatval($input['longitude']) : null;
        $note = isset($input['note']) ? trim($input['note']) : '';

        // Clock out
        $timeclockrecord = new TimeclockRecord($this->db);
        $result = $timeclockrecord->clockOut($this->user, $location, $latitude, $longitude, $note);

        if ($result > 0) {
            // Get the updated record info
            $record_data = array(
                'clock_out_time' => dol_now(),
                'location' => $location,
                'work_duration' => $timeclockrecord->work_duration
            );

            $this->sendResponse(true, $record_data, $this->langs->trans('ClockOutSuccess'));
        } else {
            $error_msg = !empty($timeclockrecord->error) ? $timeclockrecord->error : $this->langs->trans('ClockOutError');
            $this->sendResponse(false, null, '', $error_msg, 400);
        }
    }

    /**
     * Get recent records
     */
    public function getRecords()
    {
        $this->checkPermissions('read');

        $limit = GETPOST('limit', 'int') ?: 10;
        $date_start = GETPOST('date_start', 'alpha') ?: date('Y-m-d', strtotime('-30 days'));
        $date_end = GETPOST('date_end', 'alpha') ?: date('Y-m-d');

        // Limit to reasonable values
        $limit = min($limit, 100);

        $timeclockrecord = new TimeclockRecord($this->db);
        $records = $timeclockrecord->getRecordsByUserAndDate($this->user->id, $date_start, $date_end);

        $records_data = array();
        $count = 0;

        foreach ($records as $record) {
            if ($count >= $limit) break;

            $record_data = array(
                'id' => $record->id,
                'ref' => $record->ref,
                'clock_in_time' => $record->clock_in_time,
                'clock_out_time' => $record->clock_out_time,
                'work_duration' => $record->work_duration,
                'break_duration' => $record->break_duration,
                'status' => $record->status,
                'location_in' => $record->location_in,
                'location_out' => $record->location_out,
                'note_public' => $record->note_public
            );

            // Get timeclock type info
            if (!empty($record->fk_timeclock_type)) {
                $type = new TimeclockType($this->db);
                if ($type->fetch($record->fk_timeclock_type) > 0) {
                    $record_data['timeclock_type'] = array(
                        'id' => $type->id,
                        'code' => $type->code,
                        'label' => $type->label,
                        'color' => $type->color
                    );
                }
            }

            $records_data[] = $record_data;
            $count++;
        }

        $response_data = array(
            'records' => $records_data,
            'total' => count($records_data),
            'limit' => $limit,
            'date_start' => $date_start,
            'date_end' => $date_end
        );

        $this->sendResponse(true, $response_data, $this->langs->trans('RecordsRetrieved'));
    }

    /**
     * Get available timeclock types
     */
    public function getTypes()
    {
        $this->checkPermissions('read');

        $types = TimeclockType::getActiveTypes($this->db);
        $types_data = array();

        foreach ($types as $type) {
            $types_data[] = array(
                'id' => $type->id,
                'code' => $type->code,
                'label' => $type->label,
                'color' => $type->color,
                'position' => $type->position
            );
        }

        $this->sendResponse(true, $types_data, $this->langs->trans('TypesRetrieved'));
    }

    /**
     * Get today's summary
     */
    public function getTodaySummary()
    {
        $this->checkPermissions('read');

        $today = date('Y-m-d');
        $timeclockrecord = new TimeclockRecord($this->db);
        $records = $timeclockrecord->getRecordsByUserAndDate($this->user->id, $today, $today, 3); // STATUS_COMPLETED

        $summary = array(
            'date' => $today,
            'total_hours' => 0,
            'total_breaks' => 0,
            'records_count' => count($records)
        );

        foreach ($records as $record) {
            if (!empty($record->work_duration)) {
                $summary['total_hours'] += $record->work_duration / 60; // Convert minutes to hours
            }
            if (!empty($record->break_duration)) {
                $summary['total_breaks'] += $record->break_duration;
            }
        }

        $summary['total_hours'] = round($summary['total_hours'], 2);
        $summary['total_breaks'] = round($summary['total_breaks'] / 60, 2); // Convert to hours

        $this->sendResponse(true, $summary, $this->langs->trans('SummaryRetrieved'));
    }

    /**
     * Get weekly summary
     */
    public function getWeeklySummary()
    {
        $this->checkPermissions('read');

        $current_week = WeeklySummary::getCurrentWeek();
        $weeklysummary = new WeeklySummary($this->db);
        
        // Try to get existing summary
        $existing_summary = $weeklysummary->getWeeklySummaryByUserAndWeek(
            $this->user->id, 
            $current_week['year'], 
            $current_week['week_number']
        );

        if ($existing_summary) {
            $summary_data = array(
                'year' => $existing_summary->year,
                'week_number' => $existing_summary->week_number,
                'total_hours' => $existing_summary->total_hours,
                'expected_hours' => $existing_summary->expected_hours,
                'overtime_hours' => $existing_summary->overtime_hours,
                'days_worked' => $existing_summary->days_worked,
                'status' => $existing_summary->status
            );
        } else {
            // Generate summary on the fly if not exists
            $summary_data = array(
                'year' => $current_week['year'],
                'week_number' => $current_week['week_number'],
                'total_hours' => 0,
                'expected_hours' => 40, // Default
                'overtime_hours' => 0,
                'days_worked' => 0,
                'status' => 0 // In progress
            );
        }

        $this->sendResponse(true, $summary_data, $this->langs->trans('WeeklySummaryRetrieved'));
    }
}

// Main execution
try {
    // Security check
    if (empty($user) || !is_object($user) || $user->id <= 0) {
        http_response_code(401);
        echo json_encode(array('success' => false, 'error' => 'Authentication required'));
        exit;
    }

    // Get request method and action
    $method = $_SERVER['REQUEST_METHOD'];
    $action = GETPOST('action', 'alpha');
    
    // Parse URL path for RESTful routing
    $request_uri = $_SERVER['REQUEST_URI'];
    $path_parts = explode('/', parse_url($request_uri, PHP_URL_PATH));
    $api_action = end($path_parts);

    // Create API instance
    $api = new TimeclockAPI($db, $user, $langs);

    // Route requests
    switch ($method) {
        case 'GET':
            switch ($action ?: $api_action) {
                case 'status':
                    $api->getStatus();
                    break;
                case 'records':
                    $api->getRecords();
                    break;
                case 'types':
                    $api->getTypes();
                    break;
                case 'today':
                    $api->getTodaySummary();
                    break;
                case 'weekly':
                    $api->getWeeklySummary();
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(array('success' => false, 'error' => 'Endpoint not found'));
                    break;
            }
            break;

        case 'POST':
            switch ($action ?: $api_action) {
                case 'clockin':
                    $api->clockIn();
                    break;
                case 'clockout':
                    $api->clockOut();
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(array('success' => false, 'error' => 'Endpoint not found'));
                    break;
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
            break;
    }

} catch (Exception $e) {
    error_log('TimeClock API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false, 
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ));
}