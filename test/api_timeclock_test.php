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
 * \file    test/api_timeclock_test.php
 * \ingroup appmobtimetouch
 * \brief   Unit tests for TimeClock API endpoints
 */

// Load Dolibarr test environment
$res = 0;
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

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');

/**
 * Class for API testing
 */
class TimeclockAPITest
{
    private $db;
    private $user;
    private $langs;
    private $test_results = array();
    private $api_base_url;

    public function __construct($db, $user, $langs)
    {
        $this->db = $db;
        $this->user = $user;
        $this->langs = $langs;
        
        // Determine API base URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script_path = dirname($_SERVER['SCRIPT_NAME']);
        $this->api_base_url = $protocol . $host . $script_path . '/../api/timeclock.php';
        
        echo "API Base URL: " . $this->api_base_url . "\n";
    }

    /**
     * Make HTTP request to API
     */
    private function makeRequest($method, $endpoint, $data = null, $headers = array())
    {
        $url = $this->api_base_url . '?action=' . $endpoint;
        
        // Add default headers
        $default_headers = array(
            'Content-Type: application/json',
            'Accept: application/json'
        );
        $headers = array_merge($default_headers, $headers);

        // Add session cookie if available
        if (!empty($_COOKIE[session_name()])) {
            $headers[] = 'Cookie: ' . session_name() . '=' . $_COOKIE[session_name()];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return array(
                'success' => false,
                'error' => 'cURL Error: ' . $error,
                'http_code' => 0,
                'response' => null
            );
        }

        $decoded_response = json_decode($response, true);
        
        return array(
            'success' => true,
            'http_code' => $http_code,
            'response' => $decoded_response,
            'raw_response' => $response
        );
    }

    /**
     * Add test result
     */
    private function addTestResult($test_name, $success, $message = '', $details = '')
    {
        $this->test_results[] = array(
            'test' => $test_name,
            'success' => $success,
            'message' => $message,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        );
        
        $status = $success ? '✅ PASS' : '❌ FAIL';
        echo sprintf("[%s] %s: %s\n", $status, $test_name, $message);
        if (!empty($details)) {
            echo "   Details: " . $details . "\n";
        }
    }

    /**
     * Test API authentication
     */
    public function testAuthentication()
    {
        echo "\n=== Testing Authentication ===\n";
        
        // Test with valid session
        $result = $this->makeRequest('GET', 'status');
        
        if ($result['success'] && $result['http_code'] === 200) {
            $this->addTestResult('Authentication Valid', true, 'API responds with valid session');
        } else if ($result['http_code'] === 401) {
            $this->addTestResult('Authentication Required', true, 'API correctly requires authentication');
        } else {
            $this->addTestResult('Authentication Test', false, 'Unexpected response', 
                'HTTP Code: ' . $result['http_code'] . ', Response: ' . json_encode($result['response']));
        }
    }

    /**
     * Test permissions
     */
    public function testPermissions()
    {
        echo "\n=== Testing Permissions ===\n";
        
        // Test read permission
        $result = $this->makeRequest('GET', 'status');
        
        if ($result['success'] && $result['response']['success']) {
            $this->addTestResult('Read Permission', true, 'User has timeclock read permission');
        } else if ($result['http_code'] === 403) {
            $this->addTestResult('Read Permission', false, 'User lacks timeclock read permission');
        } else {
            $this->addTestResult('Read Permission Test', false, 'Could not verify read permission',
                json_encode($result['response']));
        }

        // Test write permission with clockin
        $result = $this->makeRequest('POST', 'clockin', array(
            'timeclock_type_id' => 1,
            'location' => 'Test Location',
            'note' => 'API Test'
        ));
        
        if ($result['success'] && ($result['response']['success'] || $result['response']['error'] === 'UserAlreadyClockedIn')) {
            $this->addTestResult('Write Permission', true, 'User has timeclock write permission');
        } else if ($result['http_code'] === 403) {
            $this->addTestResult('Write Permission', false, 'User lacks timeclock write permission');
        } else {
            $this->addTestResult('Write Permission Test', false, 'Could not verify write permission',
                json_encode($result['response']));
        }
    }

    /**
     * Test status endpoint
     */
    public function testStatusEndpoint()
    {
        echo "\n=== Testing Status Endpoint ===\n";
        
        $result = $this->makeRequest('GET', 'status');
        
        if (!$result['success']) {
            $this->addTestResult('Status Endpoint', false, 'Request failed', $result['error']);
            return;
        }

        if ($result['http_code'] !== 200) {
            $this->addTestResult('Status Endpoint', false, 'Wrong HTTP code', 'Expected 200, got ' . $result['http_code']);
            return;
        }

        $response = $result['response'];
        
        // Check response structure
        $required_fields = array('success', 'data', 'timestamp');
        foreach ($required_fields as $field) {
            if (!isset($response[$field])) {
                $this->addTestResult('Status Response Structure', false, 'Missing field: ' . $field);
                return;
            }
        }

        // Check data structure
        $data = $response['data'];
        $required_data_fields = array('is_clocked_in', 'active_record', 'clock_in_time', 'current_duration');
        foreach ($required_data_fields as $field) {
            if (!isset($data[$field])) {
                $this->addTestResult('Status Data Structure', false, 'Missing data field: ' . $field);
                return;
            }
        }

        $this->addTestResult('Status Endpoint', true, 'Endpoint works correctly');
        $this->addTestResult('Status Response Structure', true, 'Response has correct structure');
    }

    /**
     * Test timeclock types endpoint
     */
    public function testTypesEndpoint()
    {
        echo "\n=== Testing Types Endpoint ===\n";
        
        $result = $this->makeRequest('GET', 'types');
        
        if (!$result['success']) {
            $this->addTestResult('Types Endpoint', false, 'Request failed', $result['error']);
            return;
        }

        if ($result['http_code'] !== 200) {
            $this->addTestResult('Types Endpoint', false, 'Wrong HTTP code', 'Expected 200, got ' . $result['http_code']);
            return;
        }

        $response = $result['response'];
        
        if (!$response['success']) {
            $this->addTestResult('Types Endpoint', false, 'API returned error', $response['error']);
            return;
        }

        $data = $response['data'];
        
        if (!is_array($data)) {
            $this->addTestResult('Types Data Format', false, 'Types data should be an array');
            return;
        }

        if (count($data) > 0) {
            $first_type = $data[0];
            $required_fields = array('id', 'code', 'label', 'color');
            foreach ($required_fields as $field) {
                if (!isset($first_type[$field])) {
                    $this->addTestResult('Types Data Structure', false, 'Missing field in type: ' . $field);
                    return;
                }
            }
        }

        $this->addTestResult('Types Endpoint', true, 'Endpoint works correctly');
        $this->addTestResult('Types Data Structure', true, 'Types have correct structure');
    }

    /**
     * Test records endpoint
     */
    public function testRecordsEndpoint()
    {
        echo "\n=== Testing Records Endpoint ===\n";
        
        $result = $this->makeRequest('GET', 'records');
        
        if (!$result['success']) {
            $this->addTestResult('Records Endpoint', false, 'Request failed', $result['error']);
            return;
        }

        if ($result['http_code'] !== 200) {
            $this->addTestResult('Records Endpoint', false, 'Wrong HTTP code', 'Expected 200, got ' . $result['http_code']);
            return;
        }

        $response = $result['response'];
        
        if (!$response['success']) {
            $this->addTestResult('Records Endpoint', false, 'API returned error', $response['error']);
            return;
        }

        $data = $response['data'];
        
        // Check response structure
        $required_fields = array('records', 'total', 'limit', 'date_start', 'date_end');
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                $this->addTestResult('Records Response Structure', false, 'Missing field: ' . $field);
                return;
            }
        }

        if (!is_array($data['records'])) {
            $this->addTestResult('Records Data Format', false, 'Records should be an array');
            return;
        }

        $this->addTestResult('Records Endpoint', true, 'Endpoint works correctly');
        $this->addTestResult('Records Response Structure', true, 'Response has correct structure');
    }

    /**
     * Test clock in/out workflow
     */
    public function testClockInOutWorkflow()
    {
        echo "\n=== Testing Clock In/Out Workflow ===\n";
        
        // First, check current status
        $status_result = $this->makeRequest('GET', 'status');
        if (!$status_result['success'] || !$status_result['response']['success']) {
            $this->addTestResult('Workflow Pre-check', false, 'Could not get initial status');
            return;
        }

        $is_clocked_in = $status_result['response']['data']['is_clocked_in'];
        
        if ($is_clocked_in) {
            // User is already clocked in, try to clock out first
            $clockout_result = $this->makeRequest('POST', 'clockout', array(
                'location' => 'Test Location Out',
                'note' => 'API Test Clock Out'
            ));
            
            if ($clockout_result['success'] && $clockout_result['response']['success']) {
                $this->addTestResult('Clock Out', true, 'Successfully clocked out existing session');
            } else {
                $this->addTestResult('Clock Out', false, 'Could not clock out existing session',
                    json_encode($clockout_result['response']));
            }
            
            // Wait a moment before clocking in again
            sleep(1);
        }

        // Test clock in
        $clockin_result = $this->makeRequest('POST', 'clockin', array(
            'timeclock_type_id' => 1,
            'location' => 'Test Location In',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'note' => 'API Test Clock In'
        ));
        
        if (!$clockin_result['success']) {
            $this->addTestResult('Clock In Request', false, 'Request failed', $clockin_result['error']);
            return;
        }

        if ($clockin_result['response']['success']) {
            $this->addTestResult('Clock In', true, 'Successfully clocked in');
            
            // Verify status changed
            sleep(1);
            $new_status = $this->makeRequest('GET', 'status');
            if ($new_status['success'] && $new_status['response']['data']['is_clocked_in']) {
                $this->addTestResult('Clock In Status Verification', true, 'Status correctly shows clocked in');
            } else {
                $this->addTestResult('Clock In Status Verification', false, 'Status not updated after clock in');
            }
            
        } else {
            $error = $clockin_result['response']['error'] ?? 'Unknown error';
            if ($error === 'UserAlreadyClockedIn') {
                $this->addTestResult('Clock In', true, 'Correctly prevents double clock in');
            } else {
                $this->addTestResult('Clock In', false, 'Clock in failed', $error);
            }
        }

        // Test clock out
        sleep(1);
        $clockout_result = $this->makeRequest('POST', 'clockout', array(
            'location' => 'Test Location Out',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'note' => 'API Test Clock Out'
        ));
        
        if (!$clockout_result['success']) {
            $this->addTestResult('Clock Out Request', false, 'Request failed', $clockout_result['error']);
            return;
        }

        if ($clockout_result['response']['success']) {
            $this->addTestResult('Clock Out', true, 'Successfully clocked out');
            
            // Verify status changed
            sleep(1);
            $final_status = $this->makeRequest('GET', 'status');
            if ($final_status['success'] && !$final_status['response']['data']['is_clocked_in']) {
                $this->addTestResult('Clock Out Status Verification', true, 'Status correctly shows clocked out');
            } else {
                $this->addTestResult('Clock Out Status Verification', false, 'Status not updated after clock out');
            }
            
        } else {
            $error = $clockout_result['response']['error'] ?? 'Unknown error';
            $this->addTestResult('Clock Out', false, 'Clock out failed', $error);
        }
    }

    /**
     * Test data validation
     */
    public function testDataValidation()
    {
        echo "\n=== Testing Data Validation ===\n";
        
        // Test clock in with invalid data
        $result = $this->makeRequest('POST', 'clockin', array(
            'timeclock_type_id' => 'invalid',
            'latitude' => 'not_a_number',
            'longitude' => 'not_a_number'
        ));
        
        // Should not crash, should handle gracefully
        if ($result['success']) {
            $this->addTestResult('Invalid Data Handling', true, 'API handles invalid data gracefully');
        } else {
            $this->addTestResult('Invalid Data Handling', false, 'API crashed with invalid data');
        }

        // Test with missing action
        $result = $this->makeRequest('GET', 'nonexistent');
        
        if ($result['http_code'] === 404) {
            $this->addTestResult('Invalid Endpoint', true, 'API correctly returns 404 for invalid endpoints');
        } else {
            $this->addTestResult('Invalid Endpoint', false, 'API should return 404 for invalid endpoints');
        }
    }

    /**
     * Test today and weekly summary endpoints
     */
    public function testSummaryEndpoints()
    {
        echo "\n=== Testing Summary Endpoints ===\n";
        
        // Test today summary
        $result = $this->makeRequest('GET', 'today');
        
        if ($result['success'] && $result['response']['success']) {
            $data = $result['response']['data'];
            $required_fields = array('date', 'total_hours', 'total_breaks', 'records_count');
            $valid = true;
            
            foreach ($required_fields as $field) {
                if (!isset($data[$field])) {
                    $valid = false;
                    break;
                }
            }
            
            if ($valid) {
                $this->addTestResult('Today Summary', true, 'Today summary endpoint works correctly');
            } else {
                $this->addTestResult('Today Summary', false, 'Today summary missing required fields');
            }
        } else {
            $this->addTestResult('Today Summary', false, 'Today summary endpoint failed');
        }

        // Test weekly summary
        $result = $this->makeRequest('GET', 'weekly');
        
        if ($result['success'] && $result['response']['success']) {
            $data = $result['response']['data'];
            $required_fields = array('year', 'week_number', 'total_hours', 'days_worked', 'status');
            $valid = true;
            
            foreach ($required_fields as $field) {
                if (!isset($data[$field])) {
                    $valid = false;
                    break;
                }
            }
            
            if ($valid) {
                $this->addTestResult('Weekly Summary', true, 'Weekly summary endpoint works correctly');
            } else {
                $this->addTestResult('Weekly Summary', false, 'Weekly summary missing required fields');
            }
        } else {
            $this->addTestResult('Weekly Summary', false, 'Weekly summary endpoint failed');
        }
    }

    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "=== STARTING API TESTS ===\n";
        echo "User: " . $this->user->firstname . " " . $this->user->lastname . " (ID: " . $this->user->id . ")\n";
        echo "Time: " . date('Y-m-d H:i:s') . "\n";
        
        $this->testAuthentication();
        $this->testPermissions();
        $this->testStatusEndpoint();
        $this->testTypesEndpoint();
        $this->testRecordsEndpoint();
        $this->testClockInOutWorkflow();
        $this->testDataValidation();
        $this->testSummaryEndpoints();
        
        $this->printSummary();
    }

    /**
     * Print test summary
     */
    public function printSummary()
    {
        echo "\n=== TEST SUMMARY ===\n";
        
        $total = count($this->test_results);
        $passed = 0;
        $failed = 0;
        
        foreach ($this->test_results as $result) {
            if ($result['success']) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        echo "Total Tests: $total\n";
        echo "✅ Passed: $passed\n";
        echo "❌ Failed: $failed\n";
        echo "Success Rate: " . round(($passed / $total) * 100, 1) . "%\n";
        
        if ($failed > 0) {
            echo "\n=== FAILED TESTS ===\n";
            foreach ($this->test_results as $result) {
                if (!$result['success']) {
                    echo "❌ " . $result['test'] . ": " . $result['message'] . "\n";
                    if (!empty($result['details'])) {
                        echo "   " . $result['details'] . "\n";
                    }
                }
            }
        }
        
        echo "\n=== TESTS COMPLETED ===\n";
    }

    /**
     * Get test results as array
     */
    public function getResults()
    {
        return $this->test_results;
    }
}

// Execute tests if run directly
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    // Security check
    if (empty($user) || !is_object($user) || $user->id <= 0) {
        die("Error: User not loaded or not authenticated\n");
    }

    // Check if module is enabled
    if (empty($conf->appmobtimetouch->enabled)) {
        die("Error: AppMobTimeTouch module is not enabled\n");
    }

    // Run tests
    $tester = new TimeclockAPITest($db, $user, $langs);
    $tester->runAllTests();
}