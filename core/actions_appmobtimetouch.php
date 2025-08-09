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
 * \file    core/actions_appmobtimetouch.class.php
 * \ingroup appmobtimetouch
 * \brief   Hooks and actions for AppMobTimeTouch module
 */

/**
 * Class ActionsAppMobTimeTouch
 * 
 * Hooks and actions for time tracking module
 */
class ActionsAppMobTimeTouch
{
    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * @var string Error message
     */
    public $error = '';

    /**
     * @var array Error messages
     */
    public $errors = array();

    /**
     * @var array Results
     */
    public $results = array();

    /**
     * @var string Return value
     */
    public $resprints;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Execute action
     *
     * @param array  $parameters Hook metadatas (context, etc...)
     * @param object $object     The object to process (an invoice if you are in invoice module, a propale in propale module, etc...)
     * @param string $action     Current action (if set). Generally create or edit or null
     * @param object $hookmanager Hook manager propagated to allow calling another hook
     * @return int               <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActions($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        $error = 0; // Error counter

        /* print_r($parameters); print_r($object); echo "action: " . $action; */
        if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
            // do something only for the context 'somecontext1' or 'somecontext2'
        }

        if (!$error) {
            $this->results = array('myreturn' => 999);
            $this->resprints = 'A text to show';
            return 0; // or return 1 to replace standard code
        } else {
            $this->errors[] = 'Error message';
            return -1;
        }
    }

    /**
     * Overloading the doActionsUserCard function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale module, etc...)
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsUserCard($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $db;

        $error = 0; // Error counter

        if (!empty($conf->appmobtimetouch->enabled) && in_array('usercard', explode(':', $parameters['context']))) {
            $langs->load("appmobtimetouch@appmobtimetouch");

            // Actions on user card for time tracking
            if ($action == 'clock_in' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                $timeclock = new TimeclockRecord($db);
                $timeclock_type = GETPOST('timeclock_type', 'int');
                $location = GETPOST('location', 'alphanohtml');
                $latitude = GETPOST('latitude', 'float');
                $longitude = GETPOST('longitude', 'float');
                $note = GETPOST('note', 'restricthtml');

                $result = $timeclock->clockIn($user, $timeclock_type, $location, $latitude, $longitude, $note);
                
                if ($result > 0) {
                    setEventMessages($langs->trans("TimeclockClockInSuccess"), null, 'mesgs');
                } else {
                    setEventMessages($timeclock->error, null, 'errors');
                    $error++;
                }
            }

            if ($action == 'clock_out' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                $timeclock = new TimeclockRecord($db);
                $location = GETPOST('location', 'alphanohtml');
                $latitude = GETPOST('latitude', 'float');
                $longitude = GETPOST('longitude', 'float');
                $note = GETPOST('note', 'restricthtml');

                $result = $timeclock->clockOut($user, $location, $latitude, $longitude, $note);
                
                if ($result > 0) {
                    setEventMessages($langs->trans("TimeclockClockOutSuccess"), null, 'mesgs');
                } else {
                    setEventMessages($timeclock->error, null, 'errors');
                    $error++;
                }
            }

            if ($action == 'start_break' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockbreak.class.php';
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                $timeclock_record = new TimeclockRecord($db);
                $active_record_id = $timeclock_record->getActiveRecord($user->id);
                
                if ($active_record_id > 0) {
                    $break = new TimeclockBreak($db);
                    $break_type = GETPOST('break_type', 'alpha');
                    $location = GETPOST('location', 'alphanohtml');
                    $note = GETPOST('note', 'restricthtml');

                    $result = $break->startBreak($user, $active_record_id, $break_type, $location, $note);
                    
                    if ($result > 0) {
                        setEventMessages($langs->trans("TimeclockBreakStartSuccess"), null, 'mesgs');
                    } else {
                        setEventMessages($break->error, null, 'errors');
                        $error++;
                    }
                } else {
                    setEventMessages($langs->trans("NoActiveTimeclockRecord"), null, 'errors');
                    $error++;
                }
            }

            if ($action == 'end_break' && !empty($user->rights->appmobtimetouch->timeclock->write)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockbreak.class.php';
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                $break_id = GETPOST('break_id', 'int');
                $location = GETPOST('location', 'alphanohtml');
                $note = GETPOST('note', 'restricthtml');

                $break = new TimeclockBreak($db);
                if ($break->fetch($break_id) > 0) {
                    $result = $break->endBreak($user, $location, $note);
                    
                    if ($result > 0) {
                        setEventMessages($langs->trans("TimeclockBreakEndSuccess"), null, 'mesgs');
                    } else {
                        setEventMessages($break->error, null, 'errors');
                        $error++;
                    }
                } else {
                    setEventMessages($langs->trans("BreakNotFound"), null, 'errors');
                    $error++;
                }
            }
        }

        if (!$error) {
            return 0; // or return 1 to replace standard code
        } else {
            return -1;
        }
    }

    /**
     * Overloading the doActionsLeftBlock function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsLeftBlock($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $db;

        if (!empty($conf->appmobtimetouch->enabled) && in_array('leftblock', explode(':', $parameters['context']))) {
            $langs->load("appmobtimetouch@appmobtimetouch");

            // Add time tracking widget to left block
            if (!empty($user->rights->appmobtimetouch->timeclock->read)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                $timeclock = new TimeclockRecord($db);
                $active_record_id = $timeclock->getActiveRecord($user->id);
                
                $this->resprints = '';
                $this->resprints .= '<div class="div-table-responsive-no-min">';
                $this->resprints .= '<div class="oddeven">';
                $this->resprints .= '<div class="titre">'.$langs->trans("TimeTracking").'</div>';
                
                if ($active_record_id > 0) {
                    // User is clocked in
                    $timeclock->fetch($active_record_id);
                    $clock_in_time = $timeclock->db->jdate($timeclock->clock_in_time);
                    $duration = dol_now() - $clock_in_time;
                    
                    $this->resprints .= '<div class="timeclock-status active">';
                    $this->resprints .= '<span class="badge badge-status4">'.$langs->trans("ClockedIn").'</span><br>';
                    $this->resprints .= '<small>'.$langs->trans("Since").': '.dol_print_date($clock_in_time, 'dayhour').'</small><br>';
                    $this->resprints .= '<small>'.$langs->trans("Duration").': '.convertSecondToTime($duration, 'allhourmin').'</small>';
                    $this->resprints .= '</div>';
                } else {
                    // User is not clocked in
                    $this->resprints .= '<div class="timeclock-status inactive">';
                    $this->resprints .= '<span class="badge badge-status0">'.$langs->trans("NotClockedIn").'</span>';
                    $this->resprints .= '</div>';
                }
                
                $this->resprints .= '<div class="timeclock-actions">';
                $this->resprints .= '<a href="'.dol_buildpath('/appmobtimetouch/clockinout.php', 1).'" class="butAction">';
                $this->resprints .= $langs->trans("TimeClockManagement");
                $this->resprints .= '</a>';
                $this->resprints .= '</div>';
                
                $this->resprints .= '</div>';
                $this->resprints .= '</div>';
            }
        }

        return 0;
    }

    /**
     * Overloading the doActionsRightBlock function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsRightBlock($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $db;

        if (!empty($conf->appmobtimetouch->enabled) && in_array('rightblock', explode(':', $parameters['context']))) {
            $langs->load("appmobtimetouch@appmobtimetouch");

            // Add weekly summary to right block
            if (!empty($user->rights->appmobtimetouch->timeclock->read)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/weeklysummary.class.php';
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                $current_week = WeeklySummary::getCurrentWeek();
                $summary = new WeeklySummary($db);
                $weekly_summary = $summary->getWeeklySummaryByUserAndWeek($user->id, $current_week['year'], $current_week['week_number']);
                
                $this->resprints = '';
                $this->resprints .= '<div class="div-table-responsive-no-min">';
                $this->resprints .= '<div class="oddeven">';
                $this->resprints .= '<div class="titre">'.$langs->trans("WeeklySummary").'</div>';
                
                if ($weekly_summary) {
                    $this->resprints .= '<div class="weekly-summary">';
                    $this->resprints .= '<div><strong>'.$langs->trans("Week").' '.$weekly_summary->week_number.'/'.$weekly_summary->year.'</strong></div>';
                    $this->resprints .= '<div>'.$langs->trans("TotalHours").': '.convertSecondToTime($weekly_summary->total_hours * 3600, 'allhourmin').'</div>';
                    $this->resprints .= '<div>'.$langs->trans("DaysWorked").': '.$weekly_summary->days_worked.'</div>';
                    
                    if ($weekly_summary->overtime_hours > 0) {
                        $this->resprints .= '<div class="overtime">'.$langs->trans("OvertimeHours").': '.convertSecondToTime($weekly_summary->overtime_hours * 3600, 'allhourmin').'</div>';
                    }
                    
                    $this->resprints .= '<div class="status">'.$weekly_summary->getLibStatut(3).'</div>';
                    $this->resprints .= '</div>';
                } else {
                    $this->resprints .= '<div class="opacitymedium">'.$langs->trans("NoWeeklySummaryYet").'</div>';
                }
                
                $this->resprints .= '<div class="weekly-actions">';
                $this->resprints .= '<a href="'.dol_buildpath('/appmobtimetouch/list.php', 1).'" class="butAction">';
                $this->resprints .= $langs->trans("ViewMyRecords");
                $this->resprints .= '</a>';
                $this->resprints .= '</div>';
                
                $this->resprints .= '</div>';
                $this->resprints .= '</div>';
            }
        }

        return 0;
    }

    /**
     * Overloading the doActionsGlobalCard function
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsGlobalCard($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $db;

        if (!empty($conf->appmobtimetouch->enabled) && in_array('globalcard', explode(':', $parameters['context']))) {
            $langs->load("appmobtimetouch@appmobtimetouch");

            // Add global time tracking information
            if (!empty($user->rights->appmobtimetouch->timeclock->readall)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                // Get active users count
                $sql = "SELECT COUNT(DISTINCT tr.fk_user) as active_users";
                $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_records tr";
                $sql .= " WHERE tr.status = 2"; // STATUS_IN_PROGRESS
                $sql .= " AND tr.clock_out_time IS NULL";
                $sql .= " AND tr.entity IN (".getEntity('timeclockrecord').")";

                $resql = $db->query($sql);
                $active_users = 0;
                if ($resql) {
                    $obj = $db->fetch_object($resql);
                    $active_users = $obj->active_users;
                    $db->free($resql);
                }

                $this->resprints = '';
                $this->resprints .= '<div class="div-table-responsive-no-min">';
                $this->resprints .= '<div class="oddeven">';
                $this->resprints .= '<div class="titre">'.$langs->trans("GlobalTimeTracking").'</div>';
                $this->resprints .= '<div>'.$langs->trans("ActiveUsers").': <strong>'.$active_users.'</strong></div>';
                $this->resprints .= '<div class="global-actions">';
                $this->resprints .= '<a href="'.dol_buildpath('/appmobtimetouch/management.php', 1).'" class="butAction">';
                $this->resprints .= $langs->trans("TimeManagement");
                $this->resprints .= '</a>';
                $this->resprints .= '</div>';
                $this->resprints .= '</div>';
                $this->resprints .= '</div>';
            }
        }

        return 0;
    }

    /**
     * Overloading the addMoreActionsButtons function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled)) {
            $langs->load("appmobtimetouch@appmobtimetouch");

            $contextsarray = explode(':', $parameters['context']);

            // Add time tracking buttons on user cards
            if (in_array('usercard', $contextsarray) && is_object($object) && $object->element == 'user') {
                if (!empty($user->rights->appmobtimetouch->timeclock->read)) {
                    $this->resprints = '';
                    $this->resprints .= '<div class="inline-block divButAction">';
                    $this->resprints .= '<a class="butAction" href="'.dol_buildpath('/appmobtimetouch/timeclock_user.php?id='.$object->id, 1).'">';
                    $this->resprints .= $langs->trans('ViewUserTimeTracking');
                    $this->resprints .= '</a>';
                    $this->resprints .= '</div>';
                }
            }

            // Add validation buttons on timeclock records
            if (in_array('timeclockcard', $contextsarray) && is_object($object) && $object->element == 'timeclockrecord') {
                if (!empty($user->rights->appmobtimetouch->timeclock->validate) && $object->status == 3) { // STATUS_COMPLETED
                    $this->resprints = '';
                    $this->resprints .= '<div class="inline-block divButAction">';
                    $this->resprints .= '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=validate&token='.newToken().'">';
                    $this->resprints .= $langs->trans('ValidateTimeclockRecord');
                    $this->resprints .= '</a>';
                    $this->resprints .= '</div>';
                }
            }
        }

        return 0;
    }

    /**
     * Overloading the printFieldListWhere function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function printFieldListWhere($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $db;

        if (!empty($conf->appmobtimetouch->enabled)) {
            $contextsarray = explode(':', $parameters['context']);

            // Add filters for timeclock records lists
            if (in_array('timeclockrecordlist', $contextsarray)) {
                $search_user = GETPOST('search_user', 'int');
                $search_status = GETPOST('search_status', 'int');
                $search_date_start = GETPOST('search_date_start', 'alpha');
                $search_date_end = GETPOST('search_date_end', 'alpha');

                $this->resprints = '';

                if ($search_user > 0) {
                    $this->resprints .= " AND t.fk_user = ".((int) $search_user);
                }

                if (!empty($search_status)) {
                    $this->resprints .= " AND t.status = ".((int) $search_status);
                }

                if (!empty($search_date_start)) {
                    $this->resprints .= " AND DATE(t.clock_in_time) >= '".$db->escape($search_date_start)."'";
                }

                if (!empty($search_date_end)) {
                    $this->resprints .= " AND DATE(t.clock_in_time) <= '".$db->escape($search_date_end)."'";
                }

                // Only show user's own records if no readall permission
                if (empty($user->rights->appmobtimetouch->timeclock->readall)) {
                    $this->resprints .= " AND t.fk_user = ".((int) $user->id);
                }
            }
        }

        return 0;
    }

    /**
     * Overloading the printFieldListSelect function : replacing the parent's function with the one below
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function printFieldListSelect($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled)) {
            $contextsarray = explode(':', $parameters['context']);

            // Add additional fields for timeclock records lists
            if (in_array('timeclockrecordlist', $contextsarray)) {
                $this->resprints = '';
                $this->resprints .= ', u.firstname, u.lastname, u.login';
                $this->resprints .= ', tt.label as type_label, tt.color as type_color';
            }

            // Add additional fields for weekly summaries lists
            if (in_array('weeklysummarylist', $contextsarray)) {
                $this->resprints = '';
                $this->resprints .= ', u.firstname, u.lastname, u.login';
            }
        }

        return 0;
    }

    /**
     * Hook to validate timeclock record before creation
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsBeforeCreate($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled) && is_object($object) && $object->element == 'timeclockrecord') {
            $langs->load("appmobtimetouch@appmobtimetouch");

            // Check if user already has an active record
            $active_record_id = $object->getActiveRecord($object->fk_user);
            if ($active_record_id > 0 && $object->status == 2) { // STATUS_IN_PROGRESS
                $this->errors[] = $langs->trans("UserAlreadyHasActiveRecord");
                return -1;
            }

            // Check maximum hours per day
            if (!empty($conf->global->APPMOBTIMETOUCH_MAX_HOURS_DAY)) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockconfig.class.php';
                
                $max_hours = TimeclockConfig::getValue($this->db, 'MAX_HOURS_PER_DAY', 12);
                $date = date('Y-m-d', $this->db->jdate($object->clock_in_time));
                
                $records = $object->getRecordsByUserAndDate($object->fk_user, $date, $date, 3); // STATUS_COMPLETED
                $total_hours = 0;
                foreach ($records as $record) {
                    $total_hours += $record->work_duration / 60; // Convert minutes to hours
                }
                
                if ($total_hours >= $max_hours) {
                    $this->errors[] = $langs->trans("MaxHoursPerDayExceeded", $max_hours);
                    return -1;
                }
            }

            // Check location if required
            require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockconfig.class.php';
            $require_location = TimeclockConfig::getValue($this->db, 'REQUIRE_LOCATION', 0);
            
            if ($require_location && (empty($object->latitude_in) || empty($object->longitude_in))) {
                $this->errors[] = $langs->trans("LocationRequiredForClockIn");
                return -1;
            }
        }

        return 0;
    }

    /**
     * Hook to perform actions after timeclock record creation
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsAfterCreate($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled) && is_object($object) && $object->element == 'timeclockrecord') {
            // Generate weekly summary if needed
            if ($object->status == 3) { // STATUS_COMPLETED
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/weeklysummary.class.php';
                
                $clock_in_date = date('Y-m-d', $this->db->jdate($object->clock_in_time));
                $year = date('Y', strtotime($clock_in_date));
                $week_number = date('W', strtotime($clock_in_date));
                
                $summary = new WeeklySummary($this->db);
                $summary->generateWeeklySummary($object->fk_user, $year, $week_number, $user);
            }
        }

        return 0;
    }

    /**
     * Hook to perform actions after timeclock record update
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsAfterUpdate($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled) && is_object($object) && $object->element == 'timeclockrecord') {
            // Update weekly summary when record is modified
            if ($object->status == 3) { // STATUS_COMPLETED
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/weeklysummary.class.php';
                
                $clock_in_date = date('Y-m-d', $this->db->jdate($object->clock_in_time));
                $year = date('Y', strtotime($clock_in_date));
                $week_number = date('W', strtotime($clock_in_date));
                
                $summary = new WeeklySummary($this->db);
                $summary->generateWeeklySummary($object->fk_user, $year, $week_number, $user);
            }

            // Auto-close active breaks when clocking out
            if (!empty($object->clock_out_time) && $object->status == 3) {
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockbreak.class.php';
                
                $break = new TimeclockBreak($this->db);
                $break->forceEndActiveBreaks($object->id, $user);
            }
        }

        return 0;
    }

    /**
     * Hook to perform actions before timeclock record deletion
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsBeforeDelete($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled) && is_object($object) && $object->element == 'timeclockrecord') {
            $langs->load("appmobtimetouch@appmobtimetouch");

            // Check if user has permission to delete this record
            if (empty($user->rights->appmobtimetouch->timeclock->delete)) {
                $this->errors[] = $langs->trans("NotEnoughPermissions");
                return -1;
            }

            // Check if record belongs to current user (unless admin)
            if ($object->fk_user != $user->id && empty($user->rights->appmobtimetouch->timeclock->readall)) {
                $this->errors[] = $langs->trans("CannotDeleteOtherUserRecord");
                return -1;
            }

            // Cannot delete validated records
            if ($object->status == 1) { // STATUS_VALIDATED
                $this->errors[] = $langs->trans("CannotDeleteValidatedRecord");
                return -1;
            }
        }

        return 0;
    }

    /**
     * Hook to add CSS and JS files
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsHead($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled)) {
            $this->resprints = '';
            
            // Add module specific CSS
            $this->resprints .= '<link rel="stylesheet" type="text/css" href="'.dol_buildpath('/appmobtimetouch/css/appmobtimetouch.css.php', 1).'">'."\n";
            
            // Add module specific JS
            $this->resprints .= '<script type="text/javascript" src="'.dol_buildpath('/appmobtimetouch/js/appmobtimetouch.js.php', 1).'"></script>'."\n";
            
            // Add specific styles for time tracking widgets
            $this->resprints .= '<style type="text/css">
                .timeclock-status.active {
                    color: #468847;
                    font-weight: bold;
                }
                .timeclock-status.inactive {
                    color: #999;
                }
                .timeclock-actions {
                    margin-top: 10px;
                }
                .weekly-summary {
                    font-size: 12px;
                }
                .weekly-summary .overtime {
                    color: #d9534f;
                    font-weight: bold;
                }
                .weekly-actions {
                    margin-top: 10px;
                }
                .global-actions {
                    margin-top: 10px;
                }
            </style>'."\n";
        }

        return 0;
    }

    /**
     * Hook to add content in the page footer
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsFooter($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled)) {
            $contextsarray = explode(':', $parameters['context']);

            // Add geolocation script for mobile interfaces
            if (in_array('mobile', $contextsarray) || strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false) {
                $this->resprints = '';
                $this->resprints .= '<script type="text/javascript">
                    // Geolocation functions for mobile time tracking
                    var timeclockGeolocation = {
                        getCurrentPosition: function(callback) {
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    function(position) {
                                        callback({
                                            latitude: position.coords.latitude,
                                            longitude: position.coords.longitude,
                                            accuracy: position.coords.accuracy
                                        });
                                    },
                                    function(error) {
                                        console.log("Geolocation error: " + error.message);
                                        callback(null);
                                    },
                                    {
                                        enableHighAccuracy: true,
                                        timeout: 10000,
                                        maximumAge: 300000
                                    }
                                );
                            } else {
                                callback(null);
                            }
                        },
                        
                        addLocationToForm: function(formId) {
                            this.getCurrentPosition(function(position) {
                                if (position) {
                                    var form = document.getElementById(formId);
                                    if (form) {
                                        var latInput = form.querySelector("input[name=\'latitude\']");
                                        var lonInput = form.querySelector("input[name=\'longitude\']");
                                        
                                        if (latInput) latInput.value = position.latitude;
                                        if (lonInput) lonInput.value = position.longitude;
                                    }
                                }
                            });
                        }
                    };
                    
                    // Auto-refresh timeclock status every 60 seconds
                    if (typeof(timeclockAutoRefresh) == "undefined") {
                        var timeclockAutoRefresh = setInterval(function() {
                            var statusElements = document.querySelectorAll(".timeclock-status");
                            if (statusElements.length > 0) {
                                // Only refresh if on a time tracking related page
                                var currentUrl = window.location.href;
                                if (currentUrl.indexOf("appmobtimetouch") !== -1 || 
                                    currentUrl.indexOf("clockinout") !== -1) {
                                    // Could implement AJAX refresh here
                                }
                            }
                        }, 60000); // 60 seconds
                    }
                </script>'."\n";
            }
        }

        return 0;
    }

    /**
     * Hook to check permissions on objects
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsCheckRights($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs;

        if (!empty($conf->appmobtimetouch->enabled) && is_object($object)) {
            $langs->load("appmobtimetouch@appmobtimetouch");

            // Check permissions for timeclock records
            if ($object->element == 'timeclockrecord') {
                // Users can only access their own records unless they have readall permission
                if ($object->fk_user != $user->id && empty($user->rights->appmobtimetouch->timeclock->readall)) {
                    $this->errors[] = $langs->trans("AccessDeniedToThisRecord");
                    return -1;
                }
            }

            // Check permissions for weekly summaries
            if ($object->element == 'weeklysummary') {
                // Users can only access their own summaries unless they have readall permission
                if ($object->fk_user != $user->id && empty($user->rights->appmobtimetouch->timeclock->readall)) {
                    $this->errors[] = $langs->trans("AccessDeniedToThisSummary");
                    return -1;
                }
            }

            // Check permissions for timeclock breaks
            if ($object->element == 'timeclockbreak') {
                // Check if user owns the parent timeclock record
                require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
                
                $timeclock = new TimeclockRecord($this->db);
                if ($timeclock->fetch($object->fk_timeclock_record) > 0) {
                    if ($timeclock->fk_user != $user->id && empty($user->rights->appmobtimetouch->timeclock->readall)) {
                        $this->errors[] = $langs->trans("AccessDeniedToThisBreak");
                        return -1;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Hook to add content to search forms
     *
     * @param   array           $parameters     Hook metadatas (context, etc...)
     * @param   CommonObject    $object         The object to process
     * @param   string          $action         Current action (if set). Generally create or edit or null
     * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
     * @return  int                             <0 if KO, 0 if no action are done, >0 if OK
     */
    public function doActionsSearchForm($parameters, &$object, &$action, $hookmanager)
    {
        global $conf, $user, $langs, $form;

        if (!empty($conf->appmobtimetouch->enabled)) {
            $contextsarray = explode(':', $parameters['context']);

            // Add time tracking search filters
            if (in_array('timeclockrecordlist', $contextsarray)) {
                $langs->load("appmobtimetouch@appmobtimetouch");

                $search_user = GETPOST('search_user', 'int');
                $search_status = GETPOST('search_status', 'int');
                $search_date_start = GETPOST('search_date_start', 'alpha');
                $search_date_end = GETPOST('search_date_end', 'alpha');

                $this->resprints = '';
                
                // User filter (only for managers)
                if (!empty($user->rights->appmobtimetouch->timeclock->readall)) {
                    $this->resprints .= '<tr class="liste_titre_filter">';
                    $this->resprints .= '<td class="liste_titre">';
                    $this->resprints .= $form->select_dolusers($search_user, 'search_user', 1, '', 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth200');
                    $this->resprints .= '</td>';
                    $this->resprints .= '</tr>';
                }

                // Status filter
                $this->resprints .= '<tr class="liste_titre_filter">';
                $this->resprints .= '<td class="liste_titre">';
                $status_array = array(
                    '' => '',
                    '0' => $langs->trans('Draft'),
                    '1' => $langs->trans('Validated'),
                    '2' => $langs->trans('InProgress'),
                    '3' => $langs->trans('Completed'),
                    '9' => $langs->trans('Cancelled')
                );
                $this->resprints .= $form->selectarray('search_status', $status_array, $search_status, 1, 0, 0, '', 0, 0, 0, '', 'maxwidth150');
                $this->resprints .= '</td>';
                $this->resprints .= '</tr>';

                // Date range filter
                $this->resprints .= '<tr class="liste_titre_filter">';
                $this->resprints .= '<td class="liste_titre">';
                $this->resprints .= $langs->trans('From').': '.$form->selectDate($search_date_start, 'search_date_start', 0, 0, 1, '', 1, 0);
                $this->resprints .= '<br>'.$langs->trans('To').': '.$form->selectDate($search_date_end, 'search_date_end', 0, 0, 1, '', 1, 0);
                $this->resprints .= '</td>';
                $this->resprints .= '</tr>';
            }
        }

        return 0;
    }

    /**
     * Utility method to check if user can perform time tracking actions
     *
     * @param User   $user   User object
     * @param string $action Action to check
     * @return bool          True if allowed
     */
    private function checkTimeclockPermission($user, $action)
    {
        switch ($action) {
            case 'read':
                return !empty($user->rights->appmobtimetouch->timeclock->read);
            case 'write':
                return !empty($user->rights->appmobtimetouch->timeclock->write);
            case 'delete':
                return !empty($user->rights->appmobtimetouch->timeclock->delete);
            case 'validate':
                return !empty($user->rights->appmobtimetouch->timeclock->validate);
            case 'readall':
                return !empty($user->rights->appmobtimetouch->timeclock->readall);
            case 'export':
                return !empty($user->rights->appmobtimetouch->timeclock->export);
            case 'config':
                return !empty($user->rights->appmobtimetouch->timeclock->config);
            default:
                return false;
        }
    }

    /**
     * Utility method to format time duration for display
     *
     * @param int $minutes Duration in minutes
     * @return string      Formatted duration
     */
    private function formatDuration($minutes)
    {
        if ($minutes <= 0) {
            return '0h00';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%dh%02d', $hours, $mins);
    }

    /**
     * Utility method to check if current time is within working hours
     *
     * @return bool True if within working hours
     */
    private function isWithinWorkingHours()
    {
        global $conf;

        // Default working hours: 7:00 - 19:00
        $start_hour = !empty($conf->global->APPMOBTIMETOUCH_WORKING_HOURS_START) ? $conf->global->APPMOBTIMETOUCH_WORKING_HOURS_START : 7;
        $end_hour = !empty($conf->global->APPMOBTIMETOUCH_WORKING_HOURS_END) ? $conf->global->APPMOBTIMETOUCH_WORKING_HOURS_END : 19;

        $current_hour = date('H');
        
        return ($current_hour >= $start_hour && $current_hour < $end_hour);
    }

    /**
     * Utility method to send notifications
     *
     * @param string $type    Notification type
     * @param array  $data    Notification data
     * @param User   $user    User to notify
     * @return bool           True if sent successfully
     */
    private function sendNotification($type, $data, $user)
    {
        global $conf, $langs;

        // This is a placeholder for notification functionality
        // In a real implementation, this would integrate with Dolibarr's
        // notification system or send emails

        switch ($type) {
            case 'overtime_alert':
                // Send overtime notification
                break;
            case 'validation_request':
                // Send validation request to manager
                break;
            case 'summary_completed':
                // Send weekly summary completion notification
                break;
        }

        return true;
    }

    /**
     * Utility method to log time tracking events
     *
     * @param string $event  Event type
     * @param array  $data   Event data
     * @param User   $user   User who triggered the event
     * @return bool          True if logged successfully
     */
    private function logTimeclockEvent($event, $data, $user)
    {
        global $conf;

        // Log important time tracking events for audit purposes
        $log_data = array(
            'event' => $event,
            'user_id' => $user->id,
            'timestamp' => dol_now(),
            'data' => json_encode($data),
            'ip_address' => getUserRemoteIP()
        );

        // In a real implementation, this would write to a log table or file
        dol_syslog('AppMobTimeTouch: '.$event.' by user '.$user->id.': '.json_encode($data), LOG_INFO);

        return true;
    }
}