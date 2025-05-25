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
 * \file        class/weeklysummary.class.php
 * \ingroup     appmobtimetouch
 * \brief       This file is a CRUD class file for WeeklySummary (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for WeeklySummary
 */
class WeeklySummary extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'weeklysummary';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'timeclock_weekly_summary';

    /**
     * @var int  Does this object support multicompany module ?
     * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
     */
    public $ismultientitymanaged = 1;

    /**
     * @var int  Does object support extrafields ? 0=No, 1=Yes
     */
    public $isextrafieldmanaged = 1;

    /**
     * @var string String with name of icon for weeklysummary. Must be the part after the 'object_' into object_weeklysummary.png
     */
    public $picto = 'calendar';

    const STATUS_IN_PROGRESS = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_VALIDATED = 2;
    const STATUS_CANCELLED = 9;

    /**
     * 'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
     * 'label' the translation key.
     * 'picto' is code of a picto to show before value in forms
     * 'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM')
     * 'position' is the sort order of field.
     * 'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
     * 'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list but not create/update/view)
     * 'noteditable' says if field is not editable (1 or 0)
     * 'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
     * 'index' if we want an index in database.
     * 'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
     * 'searchall' is 1 if we want to search in this field when making a search from the quick search button.
     * 'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
     * 'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
     * 'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
     * 'showoncombobox' if value of the field must be visible into the label of the combobox that list record
     * 'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
     * 'arrayofkeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
     * 'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
     * 'comment' is not used. You can store here any text of your choice. It is not used by application.
     *
     * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
     */
    public $fields = array(
        'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
        'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>0,),
        'datec' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>0,),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>0,),
        'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>0, 'foreignkey'=>'user.rowid',),
        'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>0,),
        'fk_user' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'User', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx', 'help'=>"UserHelp"),
        'year' => array('type'=>'integer', 'label'=>'Year', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'css'=>'maxwidth100imp', 'help'=>"WeeklyYearHelp"),
        'week_number' => array('type'=>'integer', 'label'=>'WeekNumber', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1, 'css'=>'maxwidth100imp', 'help'=>"ISOWeekNumberHelp"),
        'week_start_date' => array('type'=>'date', 'label'=>'WeekStartDate', 'enabled'=>'1', 'position'=>50, 'notnull'=>1, 'visible'=>1, 'css'=>'minwidth150imp', 'help'=>"WeekStartDateHelp"),
        'week_end_date' => array('type'=>'date', 'label'=>'WeekEndDate', 'enabled'=>'1', 'position'=>60, 'notnull'=>1, 'visible'=>1, 'css'=>'minwidth150imp', 'help'=>"WeekEndDateHelp"),
        'total_hours' => array('type'=>'double(8,2)', 'label'=>'TotalHours', 'enabled'=>'1', 'position'=>70, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'css'=>'maxwidth100imp', 'help'=>"TotalWorkedHoursHelp", 'isameasure'=>1),
        'total_breaks' => array('type'=>'integer', 'label'=>'TotalBreaks', 'enabled'=>'1', 'position'=>80, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'css'=>'maxwidth100imp', 'help'=>"TotalBreakMinutesHelp"),
        'expected_hours' => array('type'=>'double(8,2)', 'label'=>'ExpectedHours', 'enabled'=>'1', 'position'=>90, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'css'=>'maxwidth100imp', 'help'=>"ExpectedHoursWeekHelp"),
        'overtime_hours' => array('type'=>'double(8,2)', 'label'=>'OvertimeHours', 'enabled'=>'1', 'position'=>100, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'css'=>'maxwidth100imp', 'help'=>"OvertimeHoursHelp"),
        'days_worked' => array('type'=>'integer', 'label'=>'DaysWorked', 'enabled'=>'1', 'position'=>110, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'css'=>'maxwidth100imp', 'help'=>"DaysWorkedHelp"),
        'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>120, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'index'=>1, 'arrayofkeyval'=>array('0'=>'InProgress', '1'=>'Completed', '2'=>'Validated', '9'=>'Cancelled')),
        'validated_by' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'ValidatedBy', 'enabled'=>'1', 'position'=>130, 'notnull'=>0, 'visible'=>2,),
        'validated_date' => array('type'=>'datetime', 'label'=>'ValidatedDate', 'enabled'=>'1', 'position'=>140, 'notnull'=>0, 'visible'=>2,),
        'note' => array('type'=>'text', 'label'=>'Note', 'enabled'=>'1', 'position'=>400, 'notnull'=>0, 'visible'=>3,),
    );

    public $rowid;
    public $entity;
    public $datec;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $fk_user;
    public $year;
    public $week_number;
    public $week_start_date;
    public $week_end_date;
    public $total_hours;
    public $total_breaks;
    public $expected_hours;
    public $overtime_hours;
    public $days_worked;
    public $status;
    public $validated_by;
    public $validated_date;
    public $note;

    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        global $conf, $langs;

        $this->db = $db;

        if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
            $this->fields['rowid']['visible'] = 0;
        }
        if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
            $this->fields['entity']['enabled'] = 0;
        }

        // Unset fields that are disabled
        foreach ($this->fields as $key => $val) {
            if (isset($val['enabled']) && empty($val['enabled'])) {
                unset($this->fields[$key]);
            }
        }

        // Translate some data of arrayofkeyval
        if (is_object($langs)) {
            foreach ($this->fields as $key => $val) {
                if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
                    foreach ($val['arrayofkeyval'] as $key2 => $val2) {
                        $this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
                    }
                }
            }
        }
    }

    /**
     * Create object into database
     *
     * @param  User $user      User that creates
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, Id of created object if OK
     */
    public function create(User $user, $notrigger = false)
    {
        global $conf;

        // Check if summary already exists for this user, year, and week
        if ($this->summaryExists($this->fk_user, $this->year, $this->week_number)) {
            $this->error = 'WeeklySummaryAlreadyExists';
            return -1;
        }

        // Calculate week dates if not provided
        if (empty($this->week_start_date) || empty($this->week_end_date)) {
            $this->calculateWeekDates();
        }

        $resultcreate = $this->createCommon($user, $notrigger);

        return $resultcreate;
    }

    /**
     * Clone an object into another one
     *
     * @param  	User 	$user      	User that creates
     * @param  	int 	$fromid     Id of object to clone
     * @return 	mixed 				New object created, <0 if KO
     */
    public function createFromClone(User $user, $fromid)
    {
        global $langs, $extrafields;
        $error = 0;

        dol_syslog(__METHOD__, LOG_DEBUG);

        $object = new self($this->db);

        $this->db->begin();

        // Load source object
        $object->fetchCommon($fromid);
        // Reset some properties
        unset($object->id);
        unset($object->fk_user_creat);

        // Clear fields that shouldn't be cloned
        $object->status = self::STATUS_IN_PROGRESS;
        $object->validated_by = null;
        $object->validated_date = '';
        $object->total_hours = 0;
        $object->total_breaks = 0;
        $object->overtime_hours = 0;
        $object->days_worked = 0;

        // Create clone
        $object->context['createfromclone'] = 'createfromclone';
        $result = $object->createCommon($user);
        if ($result < 0) {
            $error++;
            $this->error = $object->error;
            $this->errors = $object->errors;
        }

        unset($object->context['createfromclone']);

        if (!$error) {
            $this->db->commit();
            return $object;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     * Load object in memory from the database
     *
     * @param int    $id   Id object
     * @param string $ref  Ref
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = null)
    {
        $result = $this->fetchCommon($id, $ref);
        return $result;
    }

    /**
     * Update object into database
     *
     * @param  User $user      User that modifies
     * @param  bool $notrigger false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function update(User $user, $notrigger = false)
    {
        return $this->updateCommon($user, $notrigger);
    }

    /**
     * Delete object in database
     *
     * @param User $user       User that deletes
     * @param bool $notrigger  false=launch triggers after, true=disable triggers
     * @return int             <0 if KO, >0 if OK
     */
    public function delete(User $user, $notrigger = false)
    {
        return $this->deleteCommon($user, $notrigger);
    }

    /**
     * Check if weekly summary already exists
     *
     * @param int $user_id     User ID
     * @param int $year        Year
     * @param int $week_number Week number
     * @param int $excludeid   ID to exclude from check
     * @return bool            True if exists, false otherwise
     */
    public function summaryExists($user_id, $year, $week_number, $excludeid = 0)
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE fk_user = ".((int) $user_id);
        $sql .= " AND year = ".((int) $year);
        $sql .= " AND week_number = ".((int) $week_number);
        $sql .= " AND entity IN (".getEntity($this->element).")";
        if ($excludeid > 0) {
            $sql .= " AND rowid != ".((int) $excludeid);
        }

        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);
                $summary = new WeeklySummary($this->db);
                if ($summary->fetch($obj->rowid) > 0) {
                    $this->db->free($resql);
                    return $summary;
                }
            }
            $this->db->free($resql);
        }

        return null;
    }

    /**
     * Get weekly statistics for multiple users
     *
     * @param array  $user_ids   Array of user IDs
     * @param string $date_start Start date (YYYY-MM-DD)
     * @param string $date_end   End date (YYYY-MM-DD)
     * @return array             Statistics array
     */
    public function getWeeklyStats($user_ids = array(), $date_start = '', $date_end = '')
    {
        $stats = array(
            'total_summaries' => 0,
            'total_hours' => 0,
            'total_overtime' => 0,
            'avg_hours_per_week' => 0,
            'users_with_overtime' => 0,
            'by_status' => array()
        );

        $sql = "SELECT COUNT(*) as total_summaries,";
        $sql .= " SUM(total_hours) as total_hours,";
        $sql .= " SUM(overtime_hours) as total_overtime,";
        $sql .= " AVG(total_hours) as avg_hours,";
        $sql .= " status";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE entity IN (".getEntity($this->element).")";

        if (!empty($user_ids)) {
            $sql .= " AND fk_user IN (".implode(',', array_map('intval', $user_ids)).")";
        }
        if (!empty($date_start)) {
            $sql .= " AND week_start_date >= '".$this->db->escape($date_start)."'";
        }
        if (!empty($date_end)) {
            $sql .= " AND week_end_date <= '".$this->db->escape($date_end)."'";
        }

        $sql .= " GROUP BY status";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $stats['total_summaries'] += $obj->total_summaries;
                $stats['total_hours'] += $obj->total_hours;
                $stats['total_overtime'] += $obj->total_overtime;
                $stats['by_status'][$obj->status] = array(
                    'count' => $obj->total_summaries,
                    'total_hours' => $obj->total_hours,
                    'avg_hours' => $obj->avg_hours
                );
            }
            $this->db->free($resql);
        }

        if ($stats['total_summaries'] > 0) {
            $stats['avg_hours_per_week'] = round($stats['total_hours'] / $stats['total_summaries'], 2);
        }

        // Count users with overtime
        $sql_overtime = "SELECT COUNT(DISTINCT fk_user) as users_overtime";
        $sql_overtime .= " FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql_overtime .= " WHERE overtime_hours > 0";
        $sql_overtime .= " AND entity IN (".getEntity($this->element).")";

        if (!empty($user_ids)) {
            $sql_overtime .= " AND fk_user IN (".implode(',', array_map('intval', $user_ids)).")";
        }
        if (!empty($date_start)) {
            $sql_overtime .= " AND week_start_date >= '".$this->db->escape($date_start)."'";
        }
        if (!empty($date_end)) {
            $sql_overtime .= " AND week_end_date <= '".$this->db->escape($date_end)."'";
        }

        $resql_overtime = $this->db->query($sql_overtime);
        if ($resql_overtime) {
            $obj = $this->db->fetch_object($resql_overtime);
            $stats['users_with_overtime'] = $obj->users_overtime;
            $this->db->free($resql_overtime);
        }

        return $stats;
    }

    /**
     * Auto-generate weekly summaries for all users (cron job method)
     *
     * @param array $params Parameters for cron job
     * @return int          0 if OK, <>0 if KO
     */
    public function autoGenerateWeeklySummaries($params = array())
    {
        global $conf, $langs, $user;

        $langs->load("appmobtimetouch@appmobtimetouch");

        $error = 0;
        $this->output = '';
        $this->error = '';

        dol_syslog(__METHOD__, LOG_DEBUG);

        // Get current week or specific week from parameters
        $target_date = !empty($params['date']) ? $params['date'] : date('Y-m-d');
        $year = date('Y', strtotime($target_date));
        $week_number = date('W', strtotime($target_date));

        // Get all active users
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."user";
        $sql .= " WHERE statut = 1"; // Active users only
        $sql .= " AND entity IN (".getEntity('user').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $nb_generated = 0;
            $nb_updated = 0;

            while ($obj = $this->db->fetch_object($resql)) {
                $user_id = $obj->rowid;

                // Generate or update weekly summary for this user
                $summary = new WeeklySummary($this->db);
                $result = $summary->generateWeeklySummary($user_id, $year, $week_number, $user);

                if ($result > 0) {
                    if ($summary->summaryExists($user_id, $year, $week_number)) {
                        $nb_updated++;
                    } else {
                        $nb_generated++;
                    }
                }
            }

            $this->output = $langs->trans('WeeklySummariesGenerated', $nb_generated, $nb_updated);
            $this->db->free($resql);
        } else {
            $this->error = $this->db->lasterror();
            $error++;
        }

        return $error;
    }

    /**
     * Get current week number and year
     *
     * @param string $date Date (YYYY-MM-DD), defaults to today
     * @return array       Array with 'year' and 'week_number'
     */
    public static function getCurrentWeek($date = '')
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }

        return array(
            'year' => date('Y', strtotime($date)),
            'week_number' => date('W', strtotime($date))
        );
    }

    /**
     * Get week dates from year and week number
     *
     * @param int $year        Year
     * @param int $week_number Week number
     * @return array           Array with 'start_date' and 'end_date'
     */
    public static function getWeekDates($year, $week_number)
    {
        $first_day = new DateTime($year.'-01-01');
        $first_monday = clone $first_day;
        $first_monday->modify('Monday this week');
        
        if ($first_day->format('N') == 1) {
            $first_monday = $first_day;
        }
        
        $week_start = clone $first_monday;
        $week_start->modify('+'.($week_number - 1).' weeks');
        
        $week_end = clone $week_start;
        $week_end->modify('+6 days');
        
        return array(
            'start_date' => $week_start->format('Y-m-d'),
            'end_date' => $week_end->format('Y-m-d')
        );
    }

    /**
     * Get summaries pending validation
     *
     * @param int $limit Maximum number of records
     * @return array     Array of WeeklySummary objects
     */
    public function getSummariesPendingValidation($limit = 50)
    {
        $summaries = array();

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE status = ".self::STATUS_COMPLETED;
        $sql .= " AND entity IN (".getEntity($this->element).")";
        $sql .= " ORDER BY week_start_date ASC";
        $sql .= " LIMIT ".((int) $limit);

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $summary = new WeeklySummary($this->db);
                if ($summary->fetch($obj->rowid) > 0) {
                    $summaries[] = $summary;
                }
            }
            $this->db->free($resql);
        }

        return $summaries;
    }

    /**
     * Get overtime report for a period
     *
     * @param string $date_start Start date (YYYY-MM-DD)
     * @param string $date_end   End date (YYYY-MM-DD)
     * @param array  $user_ids   Array of user IDs
     * @return array             Overtime report data
     */
    public function getOvertimeReport($date_start, $date_end, $user_ids = array())
    {
        $report = array();

        $sql = "SELECT w.fk_user, u.firstname, u.lastname,";
        $sql .= " SUM(w.overtime_hours) as total_overtime,";
        $sql .= " COUNT(*) as weeks_with_overtime,";
        $sql .= " MAX(w.overtime_hours) as max_weekly_overtime";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." w";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid = w.fk_user";
        $sql .= " WHERE w.overtime_hours > 0";
        $sql .= " AND w.week_start_date >= '".$this->db->escape($date_start)."'";
        $sql .= " AND w.week_end_date <= '".$this->db->escape($date_end)."'";
        $sql .= " AND w.entity IN (".getEntity($this->element).")";

        if (!empty($user_ids)) {
            $sql .= " AND w.fk_user IN (".implode(',', array_map('intval', $user_ids)).")";
        }

        $sql .= " GROUP BY w.fk_user, u.firstname, u.lastname";
        $sql .= " ORDER BY total_overtime DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $report[] = array(
                    'user_id' => $obj->fk_user,
                    'user_name' => $obj->firstname.' '.$obj->lastname,
                    'total_overtime' => $obj->total_overtime,
                    'weeks_with_overtime' => $obj->weeks_with_overtime,
                    'max_weekly_overtime' => $obj->max_weekly_overtime,
                    'avg_weekly_overtime' => round($obj->total_overtime / $obj->weeks_with_overtime, 2)
                );
            }
            $this->db->free($resql);
        }

        return $report;
    }

    /**
     * Export weekly summaries to array
     *
     * @param array  $user_ids   Array of user IDs
     * @param string $date_start Start date (YYYY-MM-DD)
     * @param string $date_end   End date (YYYY-MM-DD)
     * @return array             Export data
     */
    public function exportSummaries($user_ids = array(), $date_start = '', $date_end = '')
    {
        $export = array();

        $sql = "SELECT w.*, u.firstname, u.lastname, u.login";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." w";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user u ON u.rowid = w.fk_user";
        $sql .= " WHERE w.entity IN (".getEntity($this->element).")";

        if (!empty($user_ids)) {
            $sql .= " AND w.fk_user IN (".implode(',', array_map('intval', $user_ids)).")";
        }
        if (!empty($date_start)) {
            $sql .= " AND w.week_start_date >= '".$this->db->escape($date_start)."'";
        }
        if (!empty($date_end)) {
            $sql .= " AND w.week_end_date <= '".$this->db->escape($date_end)."'";
        }

        $sql .= " ORDER BY w.year DESC, w.week_number DESC, u.lastname ASC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $export[] = array(
                    'user_login' => $obj->login,
                    'user_name' => $obj->firstname.' '.$obj->lastname,
                    'year' => $obj->year,
                    'week_number' => $obj->week_number,
                    'week_start' => $obj->week_start_date,
                    'week_end' => $obj->week_end_date,
                    'total_hours' => $obj->total_hours,
                    'expected_hours' => $obj->expected_hours,
                    'overtime_hours' => $obj->overtime_hours,
                    'days_worked' => $obj->days_worked,
                    'total_breaks_minutes' => $obj->total_breaks,
                    'status' => $obj->status
                );
            }
            $this->db->free($resql);
        }

        return $export;
    }

    /**
     *  Return the label of the status
     *
     *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return	string 			       Label of status
     */
    public function getLabelStatus($mode = 0)
    {
        return $this->LibStatut($this->status, $mode);
    }

    /**
     *  Return the label of the status
     *
     *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return	string 			       Label of status
     */
    public function getLibStatut($mode = 0)
    {
        return $this->LibStatut($this->status, $mode);
    }

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
    /**
     *  Return the status
     *
     *  @param	int		$status        Id status
     *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return string 			       Label of status
     */
    public function LibStatut($status, $mode = 0)
    {
        if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
            global $langs;
            $langs->load("appmobtimetouch@appmobtimetouch");
            $this->labelStatus[self::STATUS_IN_PROGRESS] = $langs->trans('InProgress');
            $this->labelStatus[self::STATUS_COMPLETED] = $langs->trans('Completed');
            $this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Validated');
            $this->labelStatus[self::STATUS_CANCELLED] = $langs->trans('Cancelled');
            $this->labelStatusShort[self::STATUS_IN_PROGRESS] = $langs->trans('InProgress');
            $this->labelStatusShort[self::STATUS_COMPLETED] = $langs->trans('Completed');
            $this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Validated');
            $this->labelStatusShort[self::STATUS_CANCELLED] = $langs->trans('Cancelled');
        }

        $statusType = 'status'.$status;
        if ($status == self::STATUS_VALIDATED) {
            $statusType = 'status6';
        }
        if ($status == self::STATUS_COMPLETED) {
            $statusType = 'status4';
        }
        if ($status == self::STATUS_IN_PROGRESS) {
            $statusType = 'status1';
        }
        if ($status == self::STATUS_CANCELLED) {
            $statusType = 'status9';
        }

        return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
    }

    /**
     * getTooltipContentArray
     *
     * @param array $params params to construct tooltip data
     * @since v18
     * @return array
     */
    public function getTooltipContentArray($params)
    {
        global $conf, $langs, $user;

        $langs->load('appmobtimetouch@appmobtimetouch');

        $datas = [];
        $datas['picto'] = img_picto('', $this->picto).' <u>'.$langs->trans("WeeklySummary").'</u>';
        if (isset($this->status)) {
            $datas['picto'] .= ' '.$this->getLibStatut(5);
        }
        
        if (isset($this->fk_user)) {
            require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
            $tmpuser = new User($this->db);
            $tmpuser->fetch($this->fk_user);
            $datas['user'] = '<br><b>'.$langs->trans('User').':</b> '.$tmpuser->getNomUrl(1);
        }
        
        $datas['week'] = '<br><b>'.$langs->trans('Week').':</b> '.$this->year.' - '.$langs->trans('WeekShort').' '.$this->week_number;
        
        if (isset($this->week_start_date) && isset($this->week_end_date)) {
            $datas['period'] = '<br><b>'.$langs->trans('Period').':</b> '.dol_print_date($this->db->jdate($this->week_start_date), 'day').' - '.dol_print_date($this->db->jdate($this->week_end_date), 'day');
        }
        
        if (isset($this->total_hours)) {
            $datas['hours'] = '<br><b>'.$langs->trans('TotalHours').':</b> '.convertSecondToTime($this->total_hours * 3600, 'allhourmin');
        }
        
        if (isset($this->overtime_hours) && $this->overtime_hours > 0) {
            $datas['overtime'] = '<br><b>'.$langs->trans('OvertimeHours').':</b> '.convertSecondToTime($this->overtime_hours * 3600, 'allhourmin');
        }
        
        if (isset($this->days_worked)) {
            $datas['days'] = '<br><b>'.$langs->trans('DaysWorked').':</b> '.$this->days_worked;
        }

        return $datas;
    }

    /**
     *	Load the info information in the object
     *
     *	@param  int		$id       Id of object
     *	@return	void
     */
    public function info($id)
    {
        $sql = "SELECT rowid, datec as datecreation, tms as datem,";
        $sql .= " fk_user_creat, fk_user_modif";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        $sql .= " WHERE t.rowid = ".((int) $id);

        $result = $this->db->query($sql);
        if ($result) {
            if ($this->db->num_rows($result)) {
                $obj = $this->db->fetch_object($result);
                $this->id = $obj->rowid;
                
                if (!empty($obj->fk_user_creat)) {
                    $cuser = new User($this->db);
                    $cuser->fetch($obj->fk_user_creat);
                    $this->user_creation = $cuser;
                }

                if (!empty($obj->fk_user_modif)) {
                    $muser = new User($this->db);
                    $muser->fetch($obj->fk_user_modif);
                    $this->user_modification = $muser;
                }

                $this->date_creation     = $this->db->jdate($obj->datecreation);
                $this->date_modification = $this->db->jdate($obj->datem);
            }

            $this->db->free($result);
        } else {
            dol_print_error($this->db);
        }
    }

    /**
     * Initialise object with example values
     * Id must be 0 if object instance is a specimen
     *
     * @return void
     */
    public function initAsSpecimen()
    {
        $this->initAsSpecimenCommon();
        
        $this->fk_user = 1;
        $this->year = date('Y');
        $this->week_number = date('W');
        $this->week_start_date = date('Y-m-d', strtotime('monday this week'));
        $this->week_end_date = date('Y-m-d', strtotime('sunday this week'));
        $this->total_hours = 42.5;
        $this->total_breaks = 150; // 2.5 hours in minutes
        $this->expected_hours = 40.0;
        $this->overtime_hours = 2.5;
        $this->days_worked = 5;
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * Get URL to the weekly summary card
     *
     * @param int    $withpicto                Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
     * @param string $option                   On what the link point to ('nolink', ...)
     * @param int    $notooltip                1=Disable tooltip
     * @param string $morecss                  Add more css on link
     * @param int    $save_lastsearch_value    -1=Auto, 0=No save of lastsearch_values, 1=Save lastsearch_values
     * @return string                          String with URL
     */
    public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
    {
        global $conf, $langs, $hookmanager;

        if (!empty($conf->dol_no_mouse_hover)) {
            $notooltip = 1; // Force disable tooltips
        }

        $result = '';

        $label = img_picto('', $this->picto).' <u>'.$langs->trans("WeeklySummary").'</u>';
        if (isset($this->status)) {
            $label .= ' '.$this->getLibStatut(5);
        }
        $label .= '<br>';
        $label .= '<b>'.$langs->trans('Week').':</b> '.$this->year.' - '.$langs->trans('WeekShort').' '.$this->week_number;

        $url = dol_buildpath('/appmobtimetouch/weeklysummary_card.php', 1).'?id='.$this->id;

        if ($option != 'nolink') {
            // Add param to save lastsearch_values or not
            $add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
            if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
                $add_save_lastsearch_values = 1;
            }
            if ($add_save_lastsearch_values) {
                $url .= '&save_lastsearch_values=1';
            }
        }

        $linkclose = '';
        if (empty($notooltip)) {
            if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $label = $langs->trans("ShowWeeklySummary");
                $linkclose .= ' alt="'.$label.'"';
            }
            $linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
            $linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
        } else {
            $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
        }

        if ($option == 'nolink') {
            $linkstart = '<span';
        } else {
            $linkstart = '<a href="'.$url.'"';
        }
        $linkstart .= $linkclose.'>';
        if ($option == 'nolink') {
            $linkend = '</span>';
        } else {
            $linkend = '</a>';
        }

        $result .= $linkstart;

        if ($withpicto) {
            $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
        }

        if ($withpicto != 2) {
            $result .= $langs->trans('Week').' '.$this->week_number.'/'.$this->year;
        }

        $result .= $linkend;

        global $action, $hookmanager;
        $hookmanager->initHooks(array('weeklysummarydao'));
        $parameters = array('id'=>$this->id, 'getnomurl'=>$result);
        $reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
        if ($reshook > 0) {
            $result = $hookmanager->resPrint;
        } else {
            $result .= $hookmanager->resPrint;
        }

        return $result;
    }

    /**
     * Return select list of week statuses
     *
     * @param string $selected      Selected value
     * @param string $htmlname      Name of HTML field
     * @param int    $showempty     Show empty value (0=No, 1=Yes with generic label, 2=Yes with specific label)
     * @param string $morecss       More CSS classes
     * @param string $moreparams    More parameters
     * @return string               HTML select
     */
    public static function selectStatuses($selected = '', $htmlname = 'status', $showempty = 1, $morecss = '', $moreparams = '')
    {
        global $langs;

        $langs->load("appmobtimetouch@appmobtimetouch");

        $out = '<select id="'.$htmlname.'" name="'.$htmlname.'" class="flat'.($morecss ? ' '.$morecss : '').'"'.($moreparams ? ' '.$moreparams : '').'>';

        if ($showempty) {
            $textforempty = '';
            if ($showempty == 1) {
                $textforempty = $langs->trans("SelectStatus");
            } else {
                $textforempty = $showempty;
            }
            $out .= '<option value="">'.$textforempty.'</option>';
        }

        $statuses = array(
            self::STATUS_IN_PROGRESS => $langs->trans('InProgress'),
            self::STATUS_COMPLETED => $langs->trans('Completed'),
            self::STATUS_VALIDATED => $langs->trans('Validated'),
            self::STATUS_CANCELLED => $langs->trans('Cancelled')
        );

        foreach ($statuses as $key => $label) {
            $selected_attr = '';
            if ($selected == $key) {
                $selected_attr = ' selected="selected"';
            }

            $out .= '<option value="'.$key.'"'.$selected_attr.'>';
            $out .= dol_escape_htmltag($label);
            $out .= '</option>';
        }

        $out .= '</select>';

        return $out;
    }

    /**
     * Generate summary for current week if not exists
     *
     * @param int  $user_id User ID
     * @param User $user    User object for creation
     * @return int          <0 if KO, >0 if OK (summary ID)
     */
    public function generateCurrentWeekSummary($user_id, $user)
    {
        $current_week = self::getCurrentWeek();
        return $this->generateWeeklySummary($user_id, $current_week['year'], $current_week['week_number'], $user);
    }

    /**
     * Check if user has access to this summary
     *
     * @param User $user User object
     * @return bool      True if access allowed
     */
    public function checkUserAccess($user)
    {
        // Users can access their own summaries
        if ($this->fk_user == $user->id) {
            return true;
        }

        // Managers can access all summaries
        if (!empty($user->rights->appmobtimetouch->timeclock->readall)) {
            return true;
        }

        return false;
    }

    /**
     * Get efficiency percentage for the week
     *
     * @return float Efficiency percentage
     */
    public function getEfficiencyPercentage()
    {
        if ($this->expected_hours <= 0) {
            return 0;
        }

        return round(($this->total_hours / $this->expected_hours) * 100, 1);
    }

    /**
     * Get average hours per day for the week
     *
     * @return float Average hours per day
     */
    public function getAverageHoursPerDay()
    {
        if ($this->days_worked <= 0) {
            return 0;
        }

        return round($this->total_hours / $this->days_worked, 2);
    }

    /**
     * Get break hours for the week
     *
     * @return float Total break hours
     */
    public function getTotalBreakHours()
    {
        return round($this->total_breaks / 60, 2);
    }

    /**
     * Check if week is current week
     *
     * @return bool True if current week
     */
    public function isCurrentWeek()
    {
        $current = self::getCurrentWeek();
        return ($this->year == $current['year'] && $this->week_number == $current['week_number']);
    }

    /**
     * Check if week is past week
     *
     * @return bool True if past week
     */
    public function isPastWeek()
    {
        $current = self::getCurrentWeek();
        
        if ($this->year < $current['year']) {
            return true;
        }
        
        if ($this->year == $current['year'] && $this->week_number < $current['week_number']) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if summary can be validated
     *
     * @return bool True if can be validated
     */
    public function canBeValidated()
    {
        // Must be completed and from a past week
        return ($this->status == self::STATUS_COMPLETED && $this->isPastWeek());
    }

    /**
     * Get week performance summary
     *
     * @return array Performance data
     */
    public function getWeekPerformance()
    {
        $performance = array(
            'efficiency' => $this->getEfficiencyPercentage(),
            'avg_hours_per_day' => $this->getAverageHoursPerDay(),
            'break_hours' => $this->getTotalBreakHours(),
            'is_overtime' => $this->overtime_hours > 0,
            'completion_rate' => 0
        );

        // Calculate completion rate based on expected working days (usually 5)
        $expected_days = 5; // Could be made configurable
        if ($expected_days > 0) {
            $performance['completion_rate'] = round(($this->days_worked / $expected_days) * 100, 1);
        }

        return $performance;
    }

    /**
     * Get comparison with previous week
     *
     * @return array|null Comparison data or null if no previous week
     */
    public function getComparisonWithPreviousWeek()
    {
        // Get previous week
        $prev_week_number = $this->week_number - 1;
        $prev_year = $this->year;
        
        if ($prev_week_number <= 0) {
            $prev_week_number = 52; // Approximate, should use actual last week of previous year
            $prev_year--;
        }

        $prev_summary = $this->getWeeklySummaryByUserAndWeek($this->fk_user, $prev_year, $prev_week_number);
        
        if (!$prev_summary) {
            return null;
        }

        return array(
            'hours_diff' => round($this->total_hours - $prev_summary->total_hours, 2),
            'overtime_diff' => round($this->overtime_hours - $prev_summary->overtime_hours, 2),
            'days_diff' => $this->days_worked - $prev_summary->days_worked,
            'efficiency_diff' => round($this->getEfficiencyPercentage() - $prev_summary->getEfficiencyPercentage(), 1)
        );
    }

    /**
     * Generate summary report as array
     *
     * @return array Summary report data
     */
    public function generateSummaryReport()
    {
        $report = array(
            'basic_info' => array(
                'user_id' => $this->fk_user,
                'year' => $this->year,
                'week_number' => $this->week_number,
                'week_start' => $this->week_start_date,
                'week_end' => $this->week_end_date,
                'status' => $this->status
            ),
            'time_tracking' => array(
                'total_hours' => $this->total_hours,
                'expected_hours' => $this->expected_hours,
                'overtime_hours' => $this->overtime_hours,
                'break_hours' => $this->getTotalBreakHours(),
                'days_worked' => $this->days_worked
            ),
            'performance' => $this->getWeekPerformance(),
            'comparison' => $this->getComparisonWithPreviousWeek()
        );

        return $report;
    }

    /**
     * Send notification about summary status change
     *
     * @param string $action Action that triggered notification
     * @param User   $user   User who performed the action
     * @return int           <0 if KO, >0 if OK
     */
    public function sendNotification($action, $user)
    {
        global $conf, $langs;

        // This method would integrate with Dolibarr's notification system
        // For now, it's a placeholder for future implementation
        
        $langs->load("appmobtimetouch@appmobtimetouch");
        
        // Get the user who owns this summary
        require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
        $summary_user = new User($this->db);
        $summary_user->fetch($this->fk_user);

        $subject = '';
        $message = '';

        switch ($action) {
            case 'validate':
                $subject = $langs->trans('WeeklySummaryValidated');
                $message = $langs->trans('WeeklySummaryValidatedMessage', $this->year, $this->week_number);
                break;
                
            case 'complete':
                $subject = $langs->trans('WeeklySummaryCompleted');
                $message = $langs->trans('WeeklySummaryCompletedMessage', $this->year, $this->week_number);
                break;
                
            default:
                return 1; // No notification needed
        }

        // TODO: Implement actual notification sending
        // This could use Dolibarr's email system or internal notifications
        
        return 1;
    }

    /**
     * Get summaries that need attention (overtime, incomplete, etc.)
     *
     * @param array $criteria Criteria for filtering
     * @return array          Array of summaries needing attention
     */
    public function getSummariesNeedingAttention($criteria = array())
    {
        $summaries = array();

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE entity IN (".getEntity($this->element).")";

        $conditions = array();

        // Default criteria
        if (empty($criteria)) {
            $criteria = array(
                'overtime' => true,
                'incomplete_weeks' => true,
                'pending_validation' => true
            );
        }

        if (!empty($criteria['overtime'])) {
            $conditions[] = "overtime_hours > 0";
        }

        if (!empty($criteria['incomplete_weeks'])) {
            $conditions[] = "(total_hours < expected_hours * 0.8)"; // Less than 80% of expected hours
        }

        if (!empty($criteria['pending_validation'])) {
            $conditions[] = "status = ".self::STATUS_COMPLETED;
        }

        if (!empty($conditions)) {
            $sql .= " AND (".implode(' OR ', $conditions).")";
        }

        // Only recent weeks (last 4 weeks)
        $four_weeks_ago = date('Y-m-d', strtotime('-4 weeks'));
        $sql .= " AND week_start_date >= '".$this->db->escape($four_weeks_ago)."'";

        $sql .= " ORDER BY week_start_date DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $summary = new WeeklySummary($this->db);
                if ($summary->fetch($obj->rowid) > 0) {
                    $summaries[] = $summary;
                }
            }
            $this->db->free($resql);
        }

        return $summaries;
    }

    /**
     * Archive old summaries (change status or move to archive table)
     *
     * @param int $months_old Number of months old to archive
     * @return int            Number of summaries archived
     */
    public function archiveOldSummaries($months_old = 12)
    {
        $archived = 0;
        $cutoff_date = date('Y-m-d', strtotime('-'.$months_old.' months'));

        // For now, we just mark old summaries as "archived" in the note field
        // In a more advanced implementation, we might move them to a separate table
        
        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " SET note = CONCAT(COALESCE(note, ''), ' [ARCHIVED]')";
        $sql .= " WHERE week_end_date < '".$this->db->escape($cutoff_date)."'";
        $sql .= " AND status = ".self::STATUS_VALIDATED;
        $sql .= " AND (note IS NULL OR note NOT LIKE '%[ARCHIVED]%')";
        $sql .= " AND entity IN (".getEntity($this->element).")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $archived = $this->db->affected_rows($resql);
        }

        return $archived;
    }

    /**
     * Recalculate summary from timeclock records
     *
     * @param User $user User object for update
     * @return int       <0 if KO, >0 if OK
     */
    public function recalculateFromRecords($user)
    {
        // Save current status
        $current_status = $this->status;
        
        // Recalculate all values
        $this->calculateTotalsFromRecords();
        $this->calculateOvertimeHours();
        
        // Restore status (don't change it during recalculation)
        $this->status = $current_status;
        
        return $this->update($user);
    }

    /**
     * Get summary for dashboard widget
     *
     * @param int $user_id User ID (0 for current user)
     * @return array       Dashboard data
     */
    public function getDashboardSummary($user_id = 0)
    {
        global $user;
        
        if ($user_id == 0) {
            $user_id = $user->id;
        }

        $dashboard = array(
            'current_week' => null,
            'last_week' => null,
            'this_month_total' => 0,
            'alerts' => array()
        );

        // Get current week summary
        $current = self::getCurrentWeek();
        $current_summary = $this->getWeeklySummaryByUserAndWeek($user_id, $current['year'], $current['week_number']);
        
        if ($current_summary) {
            $dashboard['current_week'] = $current_summary->generateSummaryReport();
        }

        // Get last week summary
        $last_week_num = $current['week_number'] - 1;
        $last_year = $current['year'];
        if ($last_week_num <= 0) {
            $last_week_num = 52;
            $last_year--;
        }
        
        $last_summary = $this->getWeeklySummaryByUserAndWeek($user_id, $last_year, $last_week_num);
        if ($last_summary) {
            $dashboard['last_week'] = $last_summary->generateSummaryReport();
        }

        // Get month total
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');
        
        $sql = "SELECT SUM(total_hours) as month_total FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE fk_user = ".((int) $user_id);
        $sql .= " AND week_start_date >= '".$this->db->escape($month_start)."'";
        $sql .= " AND week_end_date <= '".$this->db->escape($month_end)."'";
        $sql .= " AND entity IN (".getEntity($this->element).")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $dashboard['this_month_total'] = $obj->month_total ? $obj->month_total : 0;
            $this->db->free($resql);
        }

        // Get alerts for summaries needing attention
        $attention_summaries = $this->getSummariesNeedingAttention();
        foreach ($attention_summaries as $summary) {
            if ($summary->fk_user == $user_id) {
                $alert = array(
                    'type' => '',
                    'message' => '',
                    'week' => $summary->year.'-W'.$summary->week_number
                );

                if ($summary->overtime_hours > 0) {
                    $alert['type'] = 'overtime';
                    $alert['message'] = 'Overtime detected: '.convertSecondToTime($summary->overtime_hours * 3600, 'allhourmin');
                } elseif ($summary->status == self::STATUS_COMPLETED) {
                    $alert['type'] = 'validation';
                    $alert['message'] = 'Waiting for validation';
                } elseif ($summary->total_hours < $summary->expected_hours * 0.8) {
                    $alert['type'] = 'incomplete';
                    $alert['message'] = 'Incomplete week (less than 80% of expected hours)';
                }

                if (!empty($alert['type'])) {
                    $dashboard['alerts'][] = $alert;
                }
            }
        }

        return $dashboard;
    }

    /**
     * Create batch summaries for multiple users and weeks
     *
     * @param array $user_ids Array of user IDs
     * @param array $weeks    Array of week data (year, week_number)
     * @param User  $user     User object for creation
     * @return array          Results array with success/error counts
     */
    public static function createBatchSummaries($db, $user_ids, $weeks, $user)
    {
        $results = array(
            'success' => 0,
            'errors' => 0,
            'skipped' => 0
        );

        foreach ($user_ids as $user_id) {
            foreach ($weeks as $week_data) {
                $summary = new WeeklySummary($db);
                
                // Check if already exists
                if ($summary->summaryExists($user_id, $week_data['year'], $week_data['week_number'])) {
                    $results['skipped']++;
                    continue;
                }

                $result = $summary->generateWeeklySummary($user_id, $week_data['year'], $week_data['week_number'], $user);
                
                if ($result > 0) {
                    $results['success']++;
                } else {
                    $results['errors']++;
                }
            }
        }

        return $results;
    }
}
