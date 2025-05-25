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
 * \file        class/timeclockrecord.class.php
 * \ingroup     appmobtimetouch
 * \brief       This file is a CRUD class file for TimeclockRecord (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for TimeclockRecord
 */
class TimeclockRecord extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'timeclockrecord';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'timeclock_records';

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
     * @var string String with name of icon for timeclockrecord. Must be the part after the 'object_' into object_timeclockrecord.png
     */
    public $picto = 'clock';

    const STATUS_DRAFT = 0;
    const STATUS_VALIDATED = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_COMPLETED = 3;
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
        'ref' => array('type'=>'varchar(30)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
        'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>0,),
        'datec' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>0,),
        'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>0,),
        'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>0, 'foreignkey'=>'user.rowid',),
        'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>0,),
        'fk_user' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'User', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'css'=>'maxwidth500 widthcentpercentminusxx', 'help'=>"UserHelp"),
        'clock_in_time' => array('type'=>'datetime', 'label'=>'ClockInTime', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1, 'css'=>'minwidth200imp', 'help'=>"ClockInTimeHelp"),
        'clock_out_time' => array('type'=>'datetime', 'label'=>'ClockOutTime', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1, 'css'=>'minwidth200imp', 'help'=>"ClockOutTimeHelp"),
        'break_duration' => array('type'=>'integer', 'label'=>'BreakDuration', 'enabled'=>'1', 'position'=>60, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'css'=>'maxwidth75imp', 'help'=>"BreakDurationMinutesHelp"),
        'work_duration' => array('type'=>'integer', 'label'=>'WorkDuration', 'enabled'=>'1', 'position'=>70, 'notnull'=>0, 'visible'=>1, 'css'=>'maxwidth75imp', 'help'=>"WorkDurationMinutesHelp"),
        'location_in' => array('type'=>'varchar(255)', 'label'=>'LocationIn', 'enabled'=>'1', 'position'=>80, 'notnull'=>0, 'visible'=>2, 'css'=>'minwidth200', 'help'=>"LocationInHelp"),
        'location_out' => array('type'=>'varchar(255)', 'label'=>'LocationOut', 'enabled'=>'1', 'position'=>90, 'notnull'=>0, 'visible'=>2, 'css'=>'minwidth200', 'help'=>"LocationOutHelp"),
        'latitude_in' => array('type'=>'double(10,8)', 'label'=>'LatitudeIn', 'enabled'=>'1', 'position'=>100, 'notnull'=>0, 'visible'=>0,),
        'longitude_in' => array('type'=>'double(11,8)', 'label'=>'LongitudeIn', 'enabled'=>'1', 'position'=>110, 'notnull'=>0, 'visible'=>0,),
        'latitude_out' => array('type'=>'double(10,8)', 'label'=>'LatitudeOut', 'enabled'=>'1', 'position'=>120, 'notnull'=>0, 'visible'=>0,),
        'longitude_out' => array('type'=>'double(11,8)', 'label'=>'LongitudeOut', 'enabled'=>'1', 'position'=>130, 'notnull'=>0, 'visible'=>0,),
        'ip_address_in' => array('type'=>'varchar(45)', 'label'=>'IpAddressIn', 'enabled'=>'1', 'position'=>140, 'notnull'=>0, 'visible'=>0,),
        'ip_address_out' => array('type'=>'varchar(45)', 'label'=>'IpAddressOut', 'enabled'=>'1', 'position'=>150, 'notnull'=>0, 'visible'=>0,),
        'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>160, 'notnull'=>1, 'visible'=>1, 'default'=>'2', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Validated', '2'=>'InProgress', '3'=>'Completed', '9'=>'Cancelled')),
        'fk_timeclock_type' => array('type'=>'integer:TimeclockType:appmobtimetouch/class/timeclocktype.class.php', 'label'=>'TimeclockType', 'enabled'=>'1', 'position'=>170, 'notnull'=>1, 'visible'=>1, 'default'=>'1', 'css'=>'maxwidth200', 'help'=>"TimeclockTypeHelp"),
        'validated_by' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'ValidatedBy', 'enabled'=>'1', 'position'=>180, 'notnull'=>0, 'visible'=>2,),
        'validated_date' => array('type'=>'datetime', 'label'=>'ValidatedDate', 'enabled'=>'1', 'position'=>190, 'notnull'=>0, 'visible'=>2,),
        'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>400, 'notnull'=>0, 'visible'=>0,),
        'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>401, 'notnull'=>0, 'visible'=>0,),
        'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
        'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0,),
    );

    public $rowid;
    public $ref;
    public $entity;
    public $datec;
    public $tms;
    public $fk_user_creat;
    public $fk_user_modif;
    public $fk_user;
    public $clock_in_time;
    public $clock_out_time;
    public $break_duration;
    public $work_duration;
    public $location_in;
    public $location_out;
    public $latitude_in;
    public $longitude_in;
    public $latitude_out;
    public $longitude_out;
    public $ip_address_in;
    public $ip_address_out;
    public $status;
    public $fk_timeclock_type;
    public $validated_by;
    public $validated_date;
    public $note_private;
    public $note_public;
    public $import_key;
    public $model_pdf;

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

        // Example to show how to set values of fields definition dynamically
        /*if ($user->rights->appmobtimetouch->timeclock->read) {
            $this->fields['myfield']['visible'] = 1;
            $this->fields['myfield']['noteditable'] = 0;
        }*/

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

        $resultcreate = $this->createCommon($user, $notrigger);

        if ($resultcreate > 0) {
            // Auto-generate reference if needed
            if ($this->ref == '(PROV)') {
                $this->ref = $this->getNextNumRef();
                $this->update($user, true);
            }
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
        unset($object->import_key);

        // Clear fields that shouldn't be cloned
        $object->ref = '';
        $object->clock_in_time = '';
        $object->clock_out_time = '';
        $object->status = self::STATUS_DRAFT;
        $object->validated_by = null;
        $object->validated_date = '';

        // Create clone
        $object->context['createfromclone'] = 'createfromclone';
        $result = $object->createCommon($user);
        if ($result < 0) {
            $error++;
            $this->error = $object->error;
            $this->errors = $object->errors;
        }

        if (!$error) {
            // copy internal contacts
            if ($this->copy_linked_contact($object, 'internal') < 0) {
                $error++;
            }
        }

        if (!$error) {
            // copy external contacts if same company
            if (property_exists($this, 'socid') && $this->socid == $object->socid) {
                if ($this->copy_linked_contact($object, 'external') < 0) {
                    $error++;
                }
            }
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
        if ($result > 0 && !empty($this->table_element_line)) {
            $this->fetchLines();
        }
        return $result;
    }

    /**
     * Load object lines in memory from the database
     *
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetchLines()
    {
        $this->lines = array();

        $result = $this->fetchLinesCommon();
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
     *  Delete a line of object in database
     *
     *	@param  User	$user       User that delete
     *  @param	int		$idline		Id of line to delete
     *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
     *  @return int         		>0 if OK, <0 if KO
     */
    public function deleteLine(User $user, $idline, $notrigger = false)
    {
        if ($this->status < 0) {
            $this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
            return -2;
        }

        return $this->deleteLineCommon($user, $idline, $notrigger);
    }

    /**
     *	Validate object
     *
     *	@param		User	$user     		User making the validation
     *	@param		bool	$notrigger		1=Does not execute triggers, 0= execute triggers
     *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
     */
    public function validate($user, $notrigger = 0)
    {
        global $conf, $langs;

        require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

        $error = 0;

        // Protection
        if ($this->status == self::STATUS_VALIDATED) {
            dol_syslog(get_class($this)."::validate action abandoned: already validated", LOG_WARNING);
            return 0;
        }

        $now = dol_now();

        $this->db->begin();

        // Define new ref
        if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) {
            $num = $this->getNextNumRef();
        } else {
            $num = $this->ref;
        }
        $this->newref = $num;

        if (!empty($num)) {
            // Validate
            $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
            $sql .= " SET ref = '".$this->db->escape($num)."',";
            $sql .= " status = ".self::STATUS_VALIDATED.",";
            $sql .= " validated_by = ".((int) $user->id).",";
            $sql .= " validated_date = '".$this->db->idate($now)."'";
            $sql .= " WHERE rowid = ".((int) $this->id);

            dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
            $resql = $this->db->query($sql);
            if (!$resql) {
                dol_print_error($this->db);
                $this->error = $this->db->lasterror();
                $error++;
            }

            if (!$error && !$notrigger) {
                // Call trigger
                $result = $this->call_trigger('TIMECLOCKRECORD_VALIDATE', $user);
                if ($result < 0) {
                    $error++;
                }
            }
        }

        if (!$error) {
            $this->oldref = $this->ref;

            // Rename directory if dir was a temporary ref
            if (preg_match('/^[\(]?PROV/i', $this->ref)) {
                // Now we rename also files into index
                $sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'timeclockrecord/".$this->db->escape($this->newref)."'";
                $sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'timeclockrecord/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
                $resql = $this->db->query($sql);
                if (!$resql) {
                    $error++; $this->error = $this->db->lasterror();
                }

                // We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
                $oldref = dol_sanitizeFileName($this->ref);
                $newref = dol_sanitizeFileName($num);
                $dirsource = $conf->appmobtimetouch->dir_output.'/timeclockrecord/'.$oldref;
                $dirdest = $conf->appmobtimetouch->dir_output.'/timeclockrecord/'.$newref;
                if (!$error && file_exists($dirsource)) {
                    dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

                    if (@rename($dirsource, $dirdest)) {
                        dol_syslog("Rename ok");
                        // Rename docs starting with $oldref with $newref
                        $listoffiles = dol_dir_list($conf->appmobtimetouch->dir_output.'/timeclockrecord/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
                        foreach ($listoffiles as $fileentry) {
                            $dirsource = $fileentry['name'];
                            $dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
                            $dirsource = $fileentry['path'].'/'.$dirsource;
                            $dirdest = $fileentry['path'].'/'.$dirdest;
                            @rename($dirsource, $dirdest);
                        }
                    }
                }
            }
        }

        // Set new ref and current status
        if (!$error) {
            $this->ref = $num;
            $this->status = self::STATUS_VALIDATED;
            $this->validated_by = $user->id;
            $this->validated_date = $now;
        }

        if (!$error) {
            $this->db->commit();
            return 1;
        } else {
            $this->db->rollback();
            return -1;
        }
    }

    /**
     *	Set draft status
     *
     *	@param	User	$user			Object user that modify
     *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
     *	@return	int						<0 if KO, >0 if OK
     */
    public function setDraft($user, $notrigger = 0)
    {
        // Protection
        if ($this->status <= self::STATUS_DRAFT) {
            return 0;
        }

        return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'TIMECLOCKRECORD_UNVALIDATE');
    }

    /**
     *	Set cancel status
     *
     *	@param	User	$user			Object user that modify
     *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
     *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
     */
    public function cancel($user, $notrigger = 0)
    {
        // Protection
        if ($this->status != self::STATUS_VALIDATED && $this->status != self::STATUS_IN_PROGRESS) {
            return 0;
        }

        return $this->setStatusCommon($user, self::STATUS_CANCELLED, $notrigger, 'TIMECLOCKRECORD_CANCEL');
    }

    /**
     *	Set back to validated status
     *
     *	@param	User	$user			Object user that modify
     *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
     *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
     */
    public function reopen($user, $notrigger = 0)
    {
        // Protection
        if ($this->status != self::STATUS_CANCELLED) {
            return 0;
        }

        return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'TIMECLOCKRECORD_REOPEN');
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
        $datas['picto'] = img_picto('', $this->picto).' <u>'.$langs->trans("TimeclockRecord").'</u>';
        if (isset($this->status)) {
            $datas['picto'] .= ' '.$this->getLibStatut(5);
        }
        $datas['ref'] = '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
        if (isset($this->fk_user)) {
            require_once DOL_DOCUMENT_ROOT.'/user/class/user.class.php';
            $tmpuser = new User($this->db);
            $tmpuser->fetch($this->fk_user);
            $datas['user'] = '<br><b>'.$langs->trans('User').':</b> '.$tmpuser->getNomUrl(1);
        }
        if (isset($this->clock_in_time)) {
            $datas['clockin'] = '<br><b>'.$langs->trans('ClockInTime').':</b> '.dol_print_date($this->db->jdate($this->clock_in_time), 'dayhour');
        }
        if (isset($this->clock_out_time) && !empty($this->clock_out_time)) {
            $datas['clockout'] = '<br><b>'.$langs->trans('ClockOutTime').':</b> '.dol_print_date($this->db->jdate($this->clock_out_time), 'dayhour');
        }
        if (isset($this->work_duration)) {
            $datas['duration'] = '<br><b>'.$langs->trans('WorkDuration').':</b> '.convertSecondToTime($this->work_duration * 60, 'allhourmin');
        }

        return $datas;
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
            $this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Validated');
            $this->labelStatus[self::STATUS_IN_PROGRESS] = $langs->trans('InProgress');
            $this->labelStatus[self::STATUS_COMPLETED] = $langs->trans('Completed');
            $this->labelStatus[self::STATUS_CANCELLED] = $langs->trans('Cancelled');
            $this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
            $this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Validated');
            $this->labelStatusShort[self::STATUS_IN_PROGRESS] = $langs->trans('InProgress');
            $this->labelStatusShort[self::STATUS_COMPLETED] = $langs->trans('Completed');
            $this->labelStatusShort[self::STATUS_CANCELLED] = $langs->trans('Cancelled');
        }

        $statusType = 'status'.$status;
        if ($status == self::STATUS_VALIDATED) {
            $statusType = 'status1';
        }
        if ($status == self::STATUS_IN_PROGRESS) {
            $statusType = 'status4';
        }
        if ($status == self::STATUS_COMPLETED) {
            $statusType = 'status6';
        }
        if ($status == self::STATUS_CANCELLED) {
            $statusType = 'status9';
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
                if (!empty($obj->fk_user_author)) {
                    $cuser = new User($this->db);
                    $cuser->fetch($obj->fk_user_author);
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
    }

    /**
     * 	Create an array of lines
     *
     * 	@return array|int		array of lines if OK, <0 if KO
     */
    public function getLinesArray()
    {
        $this->lines = array();

        $objectline = new TimeclockRecordLine($this->db);
        $result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_timeclockrecord = '.((int) $this->id)));

        if (is_numeric($result)) {
            $this->error = $objectline->error;
            $this->errors = $objectline->errors;
            return $result;
        } else {
            $this->lines = $result;
            return $this->lines;
        }
    }

    /**
     *  Returns the reference to the following non used object depending on the active numbering module.
     *
     *  @return string      		Object free reference
     */
    public function getNextNumRef()
    {
        global $langs, $conf;
        $langs->load("appmobtimetouch@appmobtimetouch");

        if (empty($conf->global->APPMOBTIMETOUCH_TIMECLOCKRECORD_ADDON)) {
            $conf->global->APPMOBTIMETOUCH_TIMECLOCKRECORD_ADDON = 'mod_timeclockrecord_standard';
        }

        if (!empty($conf->global->APPMOBTIMETOUCH_TIMECLOCKRECORD_ADDON)) {
            $mybool = false;

            $file = $conf->global->APPMOBTIMETOUCH_TIMECLOCKRECORD_ADDON.".php";
            $classname = $conf->global->APPMOBTIMETOUCH_TIMECLOCKRECORD_ADDON;

            // Include file with class
            $dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
            foreach ($dirmodels as $reldir) {
                $dir = dol_buildpath($reldir."core/modules/appmobtimetouch/");

                // Load file with numbering class (if found)
                $mybool |= @include_once $dir.$file;
            }

            if ($mybool === false) {
                dol_print_error('', "Failed to include file ".$file);
                return '';
            }

            if (class_exists($classname)) {
                $obj = new $classname();
                $numref = $obj->getNextValue($this);

                if ($numref != '' && $numref != '-1') {
                    return $numref;
                } else {
                    $this->error = $obj->error;
                    return "";
                }
            } else {
                print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
                return "";
            }
        } else {
            print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
            return "";
        }
    }

    /**
     *  Create a document onto disk according to template module.
     *
     *  @param	    string		$modele			Force template to use ('' for automatic choice)
     *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
     *  @param      int			$hidedetails    Hide details of lines
     *  @param      int			$hidedesc       Hide description
     *  @param      int			$hideref        Hide ref
     *  @param      null|array  $moreparams     Array to provide more information
     *  @return     int         				0 if KO, 1 if OK
     */
    public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
    {
        global $conf, $langs;

        $result = 0;
        $includedocgeneration = 1;

        $langs->load("appmobtimetouch@appmobtimetouch");

        if (!dol_strlen($modele)) {
            $modele = 'standard_timeclockrecord';

            if (!empty($this->model_pdf)) {
                $modele = $this->model_pdf;
            } elseif (!empty($conf->global->TIMECLOCKRECORD_ADDON_PDF)) {
                $modele = $conf->global->TIMECLOCKRECORD_ADDON_PDF;
            }
        }

        $modelpath = "core/modules/appmobtimetouch/doc/";

        if ($includedocgeneration && !empty($modele)) {
            $result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
        }

        return $result;
    }

    /**
     * Action executed by scheduler
     * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
     * Use this method to auto-close open timeclock records that have been left open overnight
     *
     * @param	array	$params		Array of parameters
     * @return	int					0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
     */
    public function autoCloseOpenRecords($params = array())
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

        // Find all open records older than cutoff time
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE status = ".self::STATUS_IN_PROGRESS;
        $sql .= " AND clock_out_time IS NULL";
        $sql .= " AND clock_in_time < '".$this->db->idate($cutoff_time)."'";
        $sql .= " AND entity IN (".getEntity('user').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $nb_closed = 0;

            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);
                
                $timeclockrecord = new TimeclockRecord($this->db);
                if ($timeclockrecord->fetch($obj->rowid) > 0) {
                    // Auto clock out at the end of the day (23:59)
                    $clock_in_date = dol_print_date($this->db->jdate($timeclockrecord->clock_in_time), '%Y-%m-%d');
                    $auto_clock_out_time = $clock_in_date.' 23:59:00';
                    
                    $timeclockrecord->clock_out_time = $auto_clock_out_time;
                    $timeclockrecord->status = self::STATUS_COMPLETED;
                    $timeclockrecord->calculateWorkDuration();
                    
                    if ($timeclockrecord->update($user, true) > 0) {
                        $nb_closed++;
                    }
                }
                $i++;
            }

            $this->output = $langs->trans('AutoClosedRecords', $nb_closed);
            $this->db->free($resql);
        } else {
            $this->error = $this->db->lasterror();
            $error++;
        }

        return $error;
    }

    /**
     * Clock in a user
     *
     * @param User   $user              User object
     * @param int    $timeclock_type_id Type of timeclock
     * @param string $location          Location
     * @param float  $latitude          Latitude
     * @param float  $longitude         Longitude
     * @param string $note              Note
     * @return int                      <0 if KO, >0 if OK (record ID)
     */
    public function clockIn($user, $timeclock_type_id = 1, $location = '', $latitude = null, $longitude = null, $note = '')
    {
        global $conf;

        // Check if user already has an active record
        $active_record = $this->getActiveRecord($user->id);
        if ($active_record > 0) {
            $this->error = 'UserAlreadyClockedIn';
            return -1;
        }

        $now = dol_now();

        // Initialize record
        $this->fk_user = $user->id;
        $this->clock_in_time = $this->db->idate($now);
        $this->fk_timeclock_type = $timeclock_type_id;
        $this->status = self::STATUS_IN_PROGRESS;
        $this->location_in = $location;
        $this->latitude_in = $latitude;
        $this->longitude_in = $longitude;
        $this->ip_address_in = getUserRemoteIP();
        $this->note_public = $note;
        $this->break_duration = 0;

        $result = $this->create($user);

        if ($result > 0) {
            return $this->id;
        } else {
            return $result;
        }
    }

    /**
     * Clock out a user
     *
     * @param User   $user      User object
     * @param string $location  Location
     * @param float  $latitude  Latitude
     * @param float  $longitude Longitude
     * @param string $note      Note
     * @return int              <0 if KO, >0 if OK
     */
    public function clockOut($user, $location = '', $latitude = null, $longitude = null, $note = '')
    {
        // Get active record
        $active_record_id = $this->getActiveRecord($user->id);
        if ($active_record_id <= 0) {
            $this->error = 'NoActiveClock';
            return -1;
        }

        if ($this->fetch($active_record_id) <= 0) {
            return -1;
        }

        $now = dol_now();

        // Update record
        $this->clock_out_time = $this->db->idate($now);
        $this->status = self::STATUS_COMPLETED;
        $this->location_out = $location;
        $this->latitude_out = $latitude;
        $this->longitude_out = $longitude;
        $this->ip_address_out = getUserRemoteIP();
        if (!empty($note)) {
            $this->note_public .= (!empty($this->note_public) ? '\n' : '') . $note;
        }

        // Calculate work duration
        $this->calculateWorkDuration();

        return $this->update($user);
    }

    /**
     * Get active timeclock record for a user
     *
     * @param int $user_id User ID
     * @return int         Record ID if found, 0 if not found, <0 if error
     */
    public function getActiveRecord($user_id)
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_user = ".((int) $user_id);
        $sql .= " AND status = ".self::STATUS_IN_PROGRESS;
        $sql .= " AND clock_out_time IS NULL";
        $sql .= " AND entity IN (".getEntity('user').")";
        $sql .= " ORDER BY clock_in_time DESC";
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
     * Calculate work duration based on clock in/out times and breaks
     *
     * @return int Work duration in minutes
     */
    public function calculateWorkDuration()
    {
        if (empty($this->clock_in_time) || empty($this->clock_out_time)) {
            $this->work_duration = null;
            return 0;
        }

        $clock_in = $this->db->jdate($this->clock_in_time);
        $clock_out = $this->db->jdate($this->clock_out_time);

        $total_minutes = ($clock_out - $clock_in) / 60;
        $work_minutes = $total_minutes - $this->break_duration;

        $this->work_duration = max(0, $work_minutes); // Ensure non-negative

        return $this->work_duration;
    }

    /**
     * Add a break to the timeclock record
     *
     * @param int    $break_duration Duration in minutes
     * @param string $break_type     Type of break (LUNCH, BREAK, PERSONAL, OTHER)
     * @param string $note           Note about the break
     * @return int                   <0 if KO, >0 if OK
     */
    public function addBreak($break_duration, $break_type = 'BREAK', $note = '')
    {
        if ($break_duration <= 0) {
            $this->error = 'InvalidBreakDuration';
            return -1;
        }

        $this->break_duration += $break_duration;

        // Recalculate work duration if clock out time exists
        if (!empty($this->clock_out_time)) {
            $this->calculateWorkDuration();
        }

        return 1;
    }

    /**
     * Get timeclock records for a user within a date range
     *
     * @param int    $user_id    User ID
     * @param string $date_start Start date (YYYY-MM-DD)
     * @param string $date_end   End date (YYYY-MM-DD)
     * @param string $status     Status filter
     * @return array             Array of records
     */
    public function getRecordsByUserAndDate($user_id, $date_start = '', $date_end = '', $status = '')
    {
        $records = array();

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_user = ".((int) $user_id);
        $sql .= " AND entity IN (".getEntity('user').")";

        if (!empty($date_start)) {
            $sql .= " AND DATE(clock_in_time) >= '".$this->db->escape($date_start)."'";
        }
        if (!empty($date_end)) {
            $sql .= " AND DATE(clock_in_time) <= '".$this->db->escape($date_end)."'";
        }
        if (!empty($status)) {
            $sql .= " AND status = ".((int) $status);
        }

        $sql .= " ORDER BY clock_in_time DESC";

        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $record = new TimeclockRecord($this->db);
                if ($record->fetch($obj->rowid) > 0) {
                    $records[] = $record;
                }
            }
            $this->db->free($resql);
        }

        return $records;
    }

    /**
     * Get work hours summary for a user
     *
     * @param int    $user_id    User ID
     * @param string $date_start Start date (YYYY-MM-DD)
     * @param string $date_end   End date (YYYY-MM-DD)
     * @return array             Summary data
     */
    public function getWorkSummary($user_id, $date_start, $date_end)
    {
        $summary = array(
            'total_days' => 0,
            'total_hours' => 0,
            'total_breaks' => 0,
            'average_hours_per_day' => 0,
            'records' => array()
        );

        $sql = "SELECT COUNT(*) as total_days,";
        $sql .= " SUM(work_duration) as total_minutes,";
        $sql .= " SUM(break_duration) as total_break_minutes";
        $sql .= " FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_user = ".((int) $user_id);
        $sql .= " AND DATE(clock_in_time) >= '".$this->db->escape($date_start)."'";
        $sql .= " AND DATE(clock_in_time) <= '".$this->db->escape($date_end)."'";
        $sql .= " AND status = ".self::STATUS_COMPLETED;
        $sql .= " AND entity IN (".getEntity('user').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $summary['total_days'] = $obj->total_days;
            $summary['total_hours'] = round($obj->total_minutes / 60, 2);
            $summary['total_breaks'] = round($obj->total_break_minutes / 60, 2);
            
            if ($obj->total_days > 0) {
                $summary['average_hours_per_day'] = round($summary['total_hours'] / $obj->total_days, 2);
            }
            
            $this->db->free($resql);
        }

        return $summary;
    }

    /**
     * Check if user can clock in/out at current location
     *
     * @param float $latitude  Current latitude
     * @param float $longitude Current longitude
     * @return bool            True if location is allowed
     */
    public function checkLocationPermission($latitude, $longitude)
    {
        global $conf;

        // If location checking is disabled, always allow
        if (empty($conf->global->APPMOBTIMETOUCH_REQUIRE_LOCATION)) {
            return true;
        }

        // For now, always allow - location checking can be implemented later
        // based on predefined work locations stored in database
        return true;
    }

    /**
     * Get URL to the timeclock record card
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

        $label = img_picto('', $this->picto).' <u>'.$langs->trans("TimeclockRecord").'</u>';
        if (isset($this->status)) {
            $label .= ' '.$this->getLibStatut(5);
        }
        $label .= '<br>';
        $label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

        $url = dol_buildpath('/appmobtimetouch/timeclock_card.php', 1).'?id='.$this->id;

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
                $label = $langs->trans("ShowTimeclockRecord");
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

        if (empty($this->showphoto_on_popup)) {
            if ($withpicto) {
                $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
            }
        } else {
            if ($withpicto) {
                require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

                list($class, $module) = explode('@', $this->picto);
                $upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
                $filearray = dol_dir_list($upload_dir, "files", 0, '', '(\.meta|_preview.*\.png), $conf->global->GED_SORT_ORDER ? $conf->global->GED_SORT_ORDER : 'name', SORT_ASC, 1);
                if (count($filearray)) {
                    $filename = $filearray[0]['name'];
                    $result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($class.'/'.$this->ref.'/'.$filename).'"></div></div>';
                } else {
                    $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
                }
            }
        }

        if ($withpicto != 2) {
            $result .= $this->ref;
        }

        $result .= $linkend;
        //if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

        global $action, $hookmanager;
        $hookmanager->initHooks(array('timeclockrecorddao'));
        $parameters = array('id'=>$this->id, 'getnomurl'=>$result);
        $reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
        if ($reshook > 0) {
            $result = $hookmanager->resPrint;
        } else {
            $result .= $hookmanager->resPrint;
        }

        return $result;
    }
}