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
 * \file        class/timeclockbreak.class.php
 * \ingroup     appmobtimetouch
 * \brief       This file is a CRUD class file for TimeclockBreak (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for TimeclockBreak
 */
class TimeclockBreak extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'timeclockbreak';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'timeclock_breaks';

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
     * @var string String with name of icon for timeclockbreak. Must be the part after the 'object_' into object_timeclockbreak.png
     */
    public $picto = 'pause';

    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_COMPLETED = 2;

    const BREAK_TYPE_LUNCH = 'LUNCH';
    const BREAK_TYPE_BREAK = 'BREAK';
    const BREAK_TYPE_PERSONAL = 'PERSONAL';
    const BREAK_TYPE_OTHER = 'OTHER';

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
        'fk_timeclock_record' => array('type'=>'integer:TimeclockRecord:appmobtimetouch/class/timeclockrecord.class.php', 'label'=>'TimeclockRecord', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx', 'help'=>"TimeclockRecordHelp"),
        'break_start' => array('type'=>'datetime', 'label'=>'BreakStart', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'css'=>'minwidth200imp', 'help'=>"BreakStartHelp"),
        'break_end' => array('type'=>'datetime', 'label'=>'BreakEnd', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>1, 'css'=>'minwidth200imp', 'help'=>"BreakEndHelp"),
        'break_type' => array('type'=>'varchar(32)', 'label'=>'BreakType', 'enabled'=>'1', 'position'=>50, 'notnull'=>1, 'visible'=>1, 'default'=>'BREAK', 'css'=>'maxwidth150', 'arrayofkeyval'=>array('LUNCH'=>'Lunch', 'BREAK'=>'Break', 'PERSONAL'=>'Personal', 'OTHER'=>'Other'), 'help'=>"BreakTypeHelp"),
        'duration' => array('type'=>'integer', 'label'=>'Duration', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>1, 'css'=>'maxwidth75imp', 'help'=>"DurationMinutesHelp"),
        'note' => array('type'=>'varchar(255)', 'label'=>'Note', 'enabled'=>'1', 'position'=>70, 'notnull'=>0, 'visible'=>1, 'css'=>'minwidth200', 'help'=>"BreakNoteHelp"),
        'location' => array('type'=>'varchar(255)', 'label'=>'Location', 'enabled'=>'1', 'position'=>80, 'notnull'=>0, 'visible'=>2, 'css'=>'minwidth200', 'help'=>"BreakLocationHelp"),
        'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>90, 'notnull'=>1, 'visible'=>1, 'default'=>'1', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Active', '2'=>'Completed')),
    );

    public $rowid;
    public $entity;
    public $datec;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $fk_timeclock_record;
    public $break_start;
    public $break_end;
    public $break_type;
    public $duration;
    public $note;
    public $location;
    public $status;

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

        // Validate break times
        if (!$this->validateBreakTimes()) {
            return -1;
        }

        // Calculate duration if break_end is provided
        if (!empty($this->break_end)) {
            $this->calculateDuration();
        }

        $resultcreate = $this->createCommon($user, $notrigger);

        if ($resultcreate > 0) {
            // Update the parent timeclock record break duration
            $this->updateParentBreakDuration();
        }

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
        $object->break_start = '';
        $object->break_end = '';
        $object->status = self::STATUS_DRAFT;
        $object->duration = null;

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
        // Validate break times
        if (!$this->validateBreakTimes()) {
            return -1;
        }

        // Calculate duration if break_end is provided
        if (!empty($this->break_end)) {
            $this->calculateDuration();
        }

        $result = $this->updateCommon($user, $notrigger);

        if ($result > 0) {
            // Update the parent timeclock record break duration
            $this->updateParentBreakDuration();
        }

        return $result;
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
        $result = $this->deleteCommon($user, $notrigger);

        if ($result > 0) {
            // Update the parent timeclock record break duration
            $this->updateParentBreakDuration();
        }

        return $result;
    }

    /**
     * Start a break
     *
     * @param User   $user              User object
     * @param int    $timeclock_record_id Parent timeclock record ID
     * @param string $break_type        Type of break
     * @param string $location          Location
     * @param string $note              Note
     * @return int                      <0 if KO, >0 if OK (break ID)
     */
    public function startBreak($user, $timeclock_record_id, $break_type = 'BREAK', $location = '', $note = '')
    {
        global $conf;

        // Check if there's already an active break for this record
        $active_break = $this->getActiveBreak($timeclock_record_id);
        if ($active_break > 0) {
            $this->error = 'BreakAlreadyActive';
            return -1;
        }

        $now = dol_now();

        // Initialize break
        $this->fk_timeclock_record = $timeclock_record_id;
        $this->break_start = $this->db->idate($now);
        $this->break_type = $break_type;
        $this->status = self::STATUS_ACTIVE;
        $this->location = $location;
        $this->note = $note;

        $result = $this->create($user);

        if ($result > 0) {
            return $this->id;
        } else {
            return $result;
        }
    }

    /**
     * End a break
     *
     * @param User   $user     User object
     * @param string $location Location
     * @param string $note     Additional note
     * @return int             <0 if KO, >0 if OK
     */
    public function endBreak($user, $location = '', $note = '')
    {
        if ($this->status != self::STATUS_ACTIVE) {
            $this->error = 'BreakNotActive';
            return -1;
        }

        $now = dol_now();

        // Update break
        $this->break_end = $this->db->idate($now);
        $this->status = self::STATUS_COMPLETED;
        
        if (!empty($location) && empty($this->location)) {
            $this->location = $location;
        }
        
        if (!empty($note)) {
            $this->note .= (!empty($this->note) ? ' | ' : '') . $note;
        }

        // Calculate duration
        $this->calculateDuration();

        return $this->update($user);
    }

    /**
     * Get active break for a timeclock record
     *
     * @param int $timeclock_record_id Timeclock record ID
     * @return int                     Break ID if found, 0 if not found, <0 if error
     */
    public function getActiveBreak($timeclock_record_id)
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_breaks";
        $sql .= " WHERE fk_timeclock_record = ".((int) $timeclock_record_id);
        $sql .= " AND status = ".self::STATUS_ACTIVE;
        $sql .= " AND break_end IS NULL";
        $sql .= " AND entity IN (".getEntity('timeclockbreak').")";
        $sql .= " ORDER BY break_start DESC";
        $sql .= " LIMIT 1";

        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);
                $this->db->free($resql);
                return $obj->rowid;
            }
            $this->db->free($resql);
            return 0;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     * Calculate break duration in minutes
     *
     * @return int Duration in minutes
     */
    public function calculateDuration()
    {
        if (empty($this->break_start) || empty($this->break_end)) {
            $this->duration = null;
            return 0;
        }

        $start = $this->db->jdate($this->break_start);
        $end = $this->db->jdate($this->break_end);

        $duration_minutes = ($end - $start) / 60;

        $this->duration = max(0, $duration_minutes); // Ensure non-negative

        return $this->duration;
    }

    /**
     * Validate break times
     *
     * @return bool True if valid, false otherwise
     */
    public function validateBreakTimes()
    {
        global $conf;

        // Check minimum break duration
        $min_break = !empty($conf->global->APPMOBTIMETOUCH_MINIMUM_BREAK_MINUTES) ? $conf->global->APPMOBTIMETOUCH_MINIMUM_BREAK_MINUTES : 5;

        if (!empty($this->break_start) && !empty($this->break_end)) {
            $start = $this->db->jdate($this->break_start);
            $end = $this->db->jdate($this->break_end);

            // Check if end is after start
            if ($end <= $start) {
                $this->error = 'BreakEndBeforeStart';
                return false;
            }

            // Check minimum duration
            $duration_minutes = ($end - $start) / 60;
            if ($duration_minutes < $min_break) {
                $this->error = 'BreakTooShort';
                return false;
            }
        }

        // If we have a parent timeclock record, validate against it
        if (!empty($this->fk_timeclock_record)) {
            require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
            $timeclock = new TimeclockRecord($this->db);
            if ($timeclock->fetch($this->fk_timeclock_record) > 0) {
                $clock_in = $this->db->jdate($timeclock->clock_in_time);
                $break_start = $this->db->jdate($this->break_start);

                // Break cannot start before clock in
                if ($break_start < $clock_in) {
                    $this->error = 'BreakBeforeClockIn';
                    return false;
                }

                // If timeclock is completed, break cannot end after clock out
                if (!empty($timeclock->clock_out_time) && !empty($this->break_end)) {
                    $clock_out = $this->db->jdate($timeclock->clock_out_time);
                    $break_end = $this->db->jdate($this->break_end);

                    if ($break_end > $clock_out) {
                        $this->error = 'BreakAfterClockOut';
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Update parent timeclock record break duration
     *
     * @return int <0 if KO, >0 if OK
     */
    public function updateParentBreakDuration()
    {
        if (empty($this->fk_timeclock_record)) {
            return 1;
        }

        // Calculate total break duration for the parent record
        $sql = "SELECT SUM(duration) as total_duration FROM ".MAIN_DB_PREFIX."timeclock_breaks";
        $sql .= " WHERE fk_timeclock_record = ".((int) $this->fk_timeclock_record);
        $sql .= " AND status = ".self::STATUS_COMPLETED;
        $sql .= " AND duration IS NOT NULL";
        $sql .= " AND entity IN (".getEntity('timeclockbreak').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $total_duration = $obj->total_duration ? $obj->total_duration : 0;
            $this->db->free($resql);

            // Update parent record
            $sql_update = "UPDATE ".MAIN_DB_PREFIX."timeclock_records";
            $sql_update .= " SET break_duration = ".((int) $total_duration);
            $sql_update .= " WHERE rowid = ".((int) $this->fk_timeclock_record);

            $resql_update = $this->db->query($sql_update);
            if ($resql_update) {
                return 1;
            } else {
                $this->error = $this->db->lasterror();
                return -1;
            }
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     * Get breaks for a timeclock record
     *
     * @param int    $timeclock_record_id Timeclock record ID
     * @param string $status               Status filter
     * @return array                       Array of TimeclockBreak objects
     */
    public function getBreaksByRecord($timeclock_record_id, $status = '')
    {
        $breaks = array();

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_breaks";
        $sql .= " WHERE fk_timeclock_record = ".((int) $timeclock_record_id);
        $sql .= " AND entity IN (".getEntity('timeclockbreak').")";

        if (!empty($status)) {
            $sql .= " AND status = ".((int) $status);
        }

        $sql .= " ORDER BY break_start ASC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $break = new TimeclockBreak($this->db);
                if ($break->fetch($obj->rowid) > 0) {
                    $breaks[] = $break;
                }
            }
            $this->db->free($resql);
        }

        return $breaks;
    }

    /**
     * Get break types array
     *
     * @return array Array of break types
     */
    public static function getBreakTypesArray()
    {
        global $langs;
        
        $langs->load("appmobtimetouch@appmobtimetouch");
        
        return array(
            self::BREAK_TYPE_LUNCH => $langs->trans('Lunch'),
            self::BREAK_TYPE_BREAK => $langs->trans('Break'),
            self::BREAK_TYPE_PERSONAL => $langs->trans('Personal'),
            self::BREAK_TYPE_OTHER => $langs->trans('Other')
        );
    }

    /**
     * Auto-close active breaks that have been left open
     *
     * @param array $params Array of parameters
     * @return int          0 if OK, <>0 if KO
     */
    public function autoCloseActiveBreaks($params = array())
    {
        global $conf, $langs;

        $langs->load("appmobtimetouch@appmobtimetouch");

        $error = 0;
        $this->output = '';
        $this->error = '';

        dol_syslog(__METHOD__, LOG_DEBUG);

        $now = dol_now();
        $auto_close_hours = !empty($conf->global->APPMOBTIMETOUCH_AUTO_CLOCK_OUT_HOURS) ? $conf->global->APPMOBTIMETOUCH_AUTO_CLOCK_OUT_HOURS : 24;
        $cutoff_time = $now - ($auto_close_hours * 3600);

        // Find all active breaks older than cutoff time
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_breaks";
        $sql .= " WHERE status = ".self::STATUS_ACTIVE;
        $sql .= " AND break_end IS NULL";
        $sql .= " AND break_start < '".$this->db->idate($cutoff_time)."'";
        $sql .= " AND entity IN (".getEntity('timeclockbreak').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $nb_closed = 0;
            $i = 0;

            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);
                
                $break = new TimeclockBreak($this->db);
                if ($break->fetch($obj->rowid) > 0) {
                    // Auto-close break after 1 hour by default
                    $auto_break_duration = !empty($conf->global->APPMOBTIMETOUCH_AUTO_BREAK_MINUTES) ? $conf->global->APPMOBTIMETOUCH_AUTO_BREAK_MINUTES : 60;
                    $break_start = $this->db->jdate($break->break_start);
                    $auto_end_time = $break_start + ($auto_break_duration * 60);
                    
                    $break->break_end = $this->db->idate($auto_end_time);
                    $break->status = self::STATUS_COMPLETED;
                    $break->calculateDuration();
                    $break->note .= (!empty($break->note) ? ' | ' : '') . $langs->trans('AutoClosedBySystem');
                    
                    if ($break->update($user, true) > 0) {
                        $nb_closed++;
                    }
                }
                $i++;
            }

            $this->output = $langs->trans('AutoClosedBreaks', $nb_closed);
            $this->db->free($resql);
        } else {
            $this->error = $this->db->lasterror();
            $error++;
        }

        return $error;
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
            $this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
            $this->labelStatus[self::STATUS_ACTIVE] = $langs->trans('Active');
            $this->labelStatus[self::STATUS_COMPLETED] = $langs->trans('Completed');
            $this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
            $this->labelStatusShort[self::STATUS_ACTIVE] = $langs->trans('Active');
            $this->labelStatusShort[self::STATUS_COMPLETED] = $langs->trans('Completed');
        }

        $statusType = 'status'.$status;
        if ($status == self::STATUS_ACTIVE) {
            $statusType = 'status4';
        }
        if ($status == self::STATUS_COMPLETED) {
            $statusType = 'status6';
        }
        if ($status == self::STATUS_DRAFT) {
            $statusType = 'status0';
        }

        return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
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
        
        $this->fk_timeclock_record = 1;
        $this->break_start = dol_now();
        $this->break_end = dol_now() + 1800; // 30 minutes later
        $this->break_type = self::BREAK_TYPE_LUNCH;
        $this->duration = 30;
        $this->note = 'Lunch break';
        $this->location = 'Cafeteria';
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * Get tooltip content array
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
        $datas['picto'] = img_picto('', $this->picto).' <u>'.$langs->trans("TimeclockBreak").'</u>';
        if (isset($this->status)) {
            $datas['picto'] .= ' '.$this->getLibStatut(5);
        }
        
        if (isset($this->break_type)) {
            $types = self::getBreakTypesArray();
            $datas['type'] = '<br><b>'.$langs->trans('BreakType').':</b> '.$types[$this->break_type];
        }
        
        if (isset($this->break_start)) {
            $datas['start'] = '<br><b>'.$langs->trans('BreakStart').':</b> '.dol_print_date($this->db->jdate($this->break_start), 'dayhour');
        }
        
        if (isset($this->break_end) && !empty($this->break_end)) {
            $datas['end'] = '<br><b>'.$langs->trans('BreakEnd').':</b> '.dol_print_date($this->db->jdate($this->break_end), 'dayhour');
        }
        
        if (isset($this->duration) && !empty($this->duration)) {
            $datas['duration'] = '<br><b>'.$langs->trans('Duration').':</b> '.convertSecondToTime($this->duration * 60, 'allhourmin');
        }

        return $datas;
    }

    /**
     * Get URL to the timeclock break card
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

        $label = img_picto('', $this->picto).' <u>'.$langs->trans("TimeclockBreak").'</u>';
        if (isset($this->status)) {
            $label .= ' '.$this->getLibStatut(5);
        }
        $label .= '<br>';
        $label .= '<b>'.$langs->trans('BreakType').':</b> '.$this->break_type;

        $url = dol_buildpath('/appmobtimetouch/timeclockbreak_card.php', 1).'?id='.$this->id;

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
                $label = $langs->trans("ShowTimeclockBreak");
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
            $types = self::getBreakTypesArray();
            $result .= isset($types[$this->break_type]) ? $types[$this->break_type] : $this->break_type;
        }

        $result .= $linkend;

        global $action, $hookmanager;
        $hookmanager->initHooks(array('timeclockbreakdao'));
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
     * Return select list of break types
     *
     * @param string $selected      Selected value
     * @param string $htmlname      Name of HTML field
     * @param int    $showempty     Show empty value (0=No, 1=Yes with generic label, 2=Yes with specific label)
     * @param string $morecss       More CSS classes
     * @param string $moreparams    More parameters
     * @return string               HTML select
     */
    public static function selectBreakTypes($selected = '', $htmlname = 'break_type', $showempty = 1, $morecss = '', $moreparams = '')
    {
        global $langs;

        $langs->load("appmobtimetouch@appmobtimetouch");

        $out = '<select id="'.$htmlname.'" name="'.$htmlname.'" class="flat'.($morecss ? ' '.$morecss : '').'"'.($moreparams ? ' '.$moreparams : '').'>';

        if ($showempty) {
            $textforempty = '';
            if ($showempty == 1) {
                $textforempty = $langs->trans("SelectBreakType");
            } else {
                $textforempty = $showempty;
            }
            $out .= '<option value="">'.$textforempty.'</option>';
        }

        $types = self::getBreakTypesArray();
        foreach ($types as $key => $label) {
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
     * Get total break duration for a user on a specific date
     *
     * @param DoliDB $db      Database handler
     * @param int    $user_id User ID
     * @param string $date    Date (YYYY-MM-DD)
     * @return int            Total duration in minutes
     */
    public static function getTotalBreakDurationByUserAndDate($db, $user_id, $date)
    {
        $sql = "SELECT SUM(tb.duration) as total_duration";
        $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_breaks tb";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."timeclock_records tr ON tr.rowid = tb.fk_timeclock_record";
        $sql .= " WHERE tr.fk_user = ".((int) $user_id);
        $sql .= " AND DATE(tb.break_start) = '".$db->escape($date)."'";
        $sql .= " AND tb.status = ".self::STATUS_COMPLETED;
        $sql .= " AND tb.duration IS NOT NULL";
        $sql .= " AND tb.entity IN (".getEntity('timeclockbreak').")";

        $resql = $db->query($sql);
        if ($resql) {
            $obj = $db->fetch_object($resql);
            $total = $obj->total_duration ? $obj->total_duration : 0;
            $db->free($resql);
            return $total;
        }

        return 0;
    }

    /**
     * Get break statistics for a user within a date range
     *
     * @param int    $user_id    User ID
     * @param string $date_start Start date (YYYY-MM-DD)
     * @param string $date_end   End date (YYYY-MM-DD)
     * @return array             Break statistics
     */
    public function getBreakStats($user_id, $date_start, $date_end)
    {
        $stats = array(
            'total_breaks' => 0,
            'total_duration' => 0,
            'avg_duration' => 0,
            'by_type' => array()
        );

        $sql = "SELECT COUNT(*) as total_breaks, SUM(tb.duration) as total_duration, tb.break_type";
        $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_breaks tb";
        $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."timeclock_records tr ON tr.rowid = tb.fk_timeclock_record";
        $sql .= " WHERE tr.fk_user = ".((int) $user_id);
        $sql .= " AND DATE(tb.break_start) >= '".$this->db->escape($date_start)."'";
        $sql .= " AND DATE(tb.break_start) <= '".$this->db->escape($date_end)."'";
        $sql .= " AND tb.status = ".self::STATUS_COMPLETED;
        $sql .= " AND tb.duration IS NOT NULL";
        $sql .= " AND tb.entity IN (".getEntity('timeclockbreak').")";
        $sql .= " GROUP BY tb.break_type";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $stats['total_breaks'] += $obj->total_breaks;
                $stats['total_duration'] += $obj->total_duration;
                $stats['by_type'][$obj->break_type] = array(
                    'count' => $obj->total_breaks,
                    'duration' => $obj->total_duration
                );
            }
            $this->db->free($resql);
        }

        if ($stats['total_breaks'] > 0) {
            $stats['avg_duration'] = round($stats['total_duration'] / $stats['total_breaks'], 2);
        }

        return $stats;
    }

    /**
     * Check if user can take a break
     *
     * @param int $user_id              User ID
     * @param int $timeclock_record_id  Timeclock record ID
     * @return bool                     True if can take break
     */
    public function canTakeBreak($user_id, $timeclock_record_id)
    {
        global $conf;

        // Check if there's already an active break
        $active_break = $this->getActiveBreak($timeclock_record_id);
        if ($active_break > 0) {
            $this->error = 'BreakAlreadyActive';
            return false;
        }

        // Check if parent timeclock record is active
        require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
        $timeclock = new TimeclockRecord($this->db);
        if ($timeclock->fetch($timeclock_record_id) <= 0) {
            $this->error = 'TimeclockRecordNotFound';
            return false;
        }

        if ($timeclock->status != 2) { // STATUS_IN_PROGRESS
            $this->error = 'TimeclockRecordNotActive';
            return false;
        }

        if ($timeclock->fk_user != $user_id) {
            $this->error = 'NotAuthorized';
            return false;
        }

        return true;
    }

    /**
     * Force end all active breaks for a timeclock record
     *
     * @param int  $timeclock_record_id Timeclock record ID
     * @param User $user                User object
     * @return int                      Number of breaks closed
     */
    public function forceEndActiveBreaks($timeclock_record_id, $user)
    {
        $closed = 0;
        
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_breaks";
        $sql .= " WHERE fk_timeclock_record = ".((int) $timeclock_record_id);
        $sql .= " AND status = ".self::STATUS_ACTIVE;
        $sql .= " AND break_end IS NULL";
        $sql .= " AND entity IN (".getEntity('timeclockbreak').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $break = new TimeclockBreak($this->db);
                if ($break->fetch($obj->rowid) > 0) {
                    $break->break_end = dol_now();
                    $break->status = self::STATUS_COMPLETED;
                    $break->calculateDuration();
                    $break->note .= (!empty($break->note) ? ' | ' : '') . 'Force closed on clock out';
                    
                    if ($break->update($user, true) > 0) {
                        $closed++;
                    }
                }
            }
            $this->db->free($resql);
        }

        return $closed;
    }
}