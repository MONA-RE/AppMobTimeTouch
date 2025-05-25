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
 * \file        class/timeclocktype.class.php
 * \ingroup     appmobtimetouch
 * \brief       This file is a CRUD class file for TimeclockType (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for TimeclockType
 */
class TimeclockType extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'timeclocktype';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'timeclock_types';

    /**
     * @var int  Does this object support multicompany module ?
     * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
     */
    public $ismultientitymanaged = 1;

    /**
     * @var int  Does object support extrafields ? 0=No, 1=Yes
     */
    public $isextrafieldmanaged = 0;

    /**
     * @var string String with name of icon for timeclocktype. Must be the part after the 'object_' into object_timeclocktype.png
     */
    public $picto = 'category';

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
        'code' => array('type'=>'varchar(32)', 'label'=>'Code', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'css'=>'minwidth100 maxwidth200', 'help'=>"TimeclockTypeCodeHelp", 'autofocusoncreate'=>1),
        'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'showoncombobox'=>'2', 'css'=>'minwidth200', 'help'=>"TimeclockTypeLabelHelp"),
        'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>'1', 'position'=>40, 'notnull'=>0, 'visible'=>3, 'css'=>'minwidth300', 'help'=>"TimeclockTypeDescriptionHelp"),
        'color' => array('type'=>'varchar(7)', 'label'=>'Color', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1, 'default'=>'#4CAF50', 'css'=>'maxwidth100', 'help'=>"TimeclockTypeColorHelp"),
        'position' => array('type'=>'integer', 'label'=>'Position', 'enabled'=>'1', 'position'=>60, 'notnull'=>1, 'visible'=>2, 'default'=>'0', 'css'=>'maxwidth75imp', 'help'=>"TimeclockTypePositionHelp"),
        'active' => array('type'=>'boolean', 'label'=>'Active', 'enabled'=>'1', 'position'=>70, 'notnull'=>1, 'visible'=>1, 'default'=>'1', 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes')),
        'module' => array('type'=>'varchar(32)', 'label'=>'Module', 'enabled'=>'1', 'position'=>80, 'notnull'=>0, 'visible'=>2, 'css'=>'minwidth100', 'help'=>"TimeclockTypeModuleHelp"),
    );

    public $rowid;
    public $entity;
    public $datec;
    public $tms;
    public $code;
    public $label;
    public $description;
    public $color;
    public $position;
    public $active;
    public $module;

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

        // Check if code already exists
        if ($this->checkCodeExists($this->code)) {
            $this->error = 'TimeclockTypeCodeAlreadyExists';
            return -1;
        }

        // Set position if not provided
        if (empty($this->position)) {
            $this->position = $this->getNextPosition();
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

        // Clear fields that shouldn't be cloned
        $object->code = '';
        $object->label = $langs->trans('CopyOf').' '.$object->label;

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
     * @param string $code Code
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = null, $code = null)
    {
        $result = $this->fetchCommon($id, $ref, $code);
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
        // Check if code already exists for another record
        if ($this->checkCodeExists($this->code, $this->id)) {
            $this->error = 'TimeclockTypeCodeAlreadyExists';
            return -1;
        }

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
        // Check if type is used in timeclock records
        if ($this->isUsed()) {
            $this->error = 'TimeclockTypeCannotDeleteUsed';
            return -1;
        }

        return $this->deleteCommon($user, $notrigger);
    }

    /**
     * Check if code already exists
     *
     * @param string $code Code to check
     * @param int    $excludeid ID to exclude from check
     * @return bool  True if exists, false otherwise
     */
    public function checkCodeExists($code, $excludeid = 0)
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE code = '".$this->db->escape($code)."'";
        $sql .= " AND entity IN (".getEntity($this->element).")";
        if ($excludeid > 0) {
            $sql .= " AND rowid != ".((int) $excludeid);
        }

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $this->db->free($resql);
            return ($num > 0);
        }
        return false;
    }

    /**
     * Get next position value
     *
     * @return int Next position
     */
    public function getNextPosition()
    {
        $sql = "SELECT MAX(position) as maxpos FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE entity IN (".getEntity($this->element).")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $this->db->free($resql);
            return ($obj->maxpos + 10);
        }
        return 10;
    }

    /**
     * Check if timeclock type is used in records
     *
     * @return bool True if used, false otherwise
     */
    public function isUsed()
    {
        $sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_timeclock_type = ".((int) $this->id);
        $sql .= " AND entity IN (".getEntity('timeclockrecord').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $this->db->free($resql);
            return ($obj->nb > 0);
        }
        return false;
    }

    /**
     * Get active timeclock types
     *
     * @param string $sortfield  Sort field
     * @param string $sortorder  Sort order
     * @return array             Array of TimeclockType objects
     */
    public static function getActiveTypes($db, $sortfield = 'position', $sortorder = 'ASC')
    {
        $types = array();

        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_types";
        $sql .= " WHERE active = 1";
        $sql .= " AND entity IN (".getEntity('timeclocktype').")";
        $sql .= " ORDER BY ".$db->escape($sortfield)." ".$db->escape($sortorder);

        $resql = $db->query($sql);
        if ($resql) {
            while ($obj = $db->fetch_object($resql)) {
                $type = new TimeclockType($db);
                if ($type->fetch($obj->rowid) > 0) {
                    $types[] = $type;
                }
            }
            $db->free($resql);
        }

        return $types;
    }

    /**
     * Get timeclock type by code
     *
     * @param DoliDB $db   Database handler
     * @param string $code Code to search
     * @return TimeclockType|null Object if found, null otherwise
     */
    public static function getByCode($db, $code)
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_types";
        $sql .= " WHERE code = '".$db->escape($code)."'";
        $sql .= " AND entity IN (".getEntity('timeclocktype').")";

        $resql = $db->query($sql);
        if ($resql) {
            if ($db->num_rows($resql)) {
                $obj = $db->fetch_object($resql);
                $type = new TimeclockType($db);
                if ($type->fetch($obj->rowid) > 0) {
                    $db->free($resql);
                    return $type;
                }
            }
            $db->free($resql);
        }

        return null;
    }

    /**
     * Get colored label for display
     *
     * @param int $mode 0=Label only, 1=Label with color background, 2=Color dot + label
     * @return string   Formatted label
     */
    public function getColoredLabel($mode = 1)
    {
        $out = '';
        
        if ($mode == 1) {
            $out = '<span style="background-color: '.$this->color.'; color: white; padding: 2px 6px; border-radius: 3px;">';
            $out .= dol_escape_htmltag($this->label);
            $out .= '</span>';
        } elseif ($mode == 2) {
            $out = '<span style="display: inline-block; width: 12px; height: 12px; background-color: '.$this->color.'; border-radius: 50%; margin-right: 5px; vertical-align: middle;"></span>';
            $out .= dol_escape_htmltag($this->label);
        } else {
            $out = dol_escape_htmltag($this->label);
        }

        return $out;
    }

    /**
     * Reorder positions
     *
     * @param array $new_positions Array of id => position
     * @return int                 <0 if KO, >0 if OK
     */
    public function reorderPositions($new_positions)
    {
        $error = 0;

        $this->db->begin();

        foreach ($new_positions as $id => $position) {
            $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
            $sql .= " SET position = ".((int) $position);
            $sql .= " WHERE rowid = ".((int) $id);
            $sql .= " AND entity IN (".getEntity($this->element).")";

            $resql = $this->db->query($sql);
            if (!$resql) {
                $error++;
                $this->error = $this->db->lasterror();
                break;
            }
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
     *  Return the label of the status
     *
     *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return	string 			       Label of status
     */
    public function getLabelStatus($mode = 0)
    {
        return $this->LibStatut($this->active, $mode);
    }

    /**
     *  Return the label of the status
     *
     *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
     *  @return	string 			       Label of status
     */
    public function getLibStatut($mode = 0)
    {
        return $this->LibStatut($this->active, $mode);
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
            $this->labelStatus[0] = $langs->trans('Disabled');
            $this->labelStatus[1] = $langs->trans('Enabled');
            $this->labelStatusShort[0] = $langs->trans('Disabled');
            $this->labelStatusShort[1] = $langs->trans('Enabled');
        }

        $statusType = 'status'.$status;
        if ($status == 1) {
            $statusType = 'status4';
        }
        if ($status == 0) {
            $statusType = 'status5';
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
        $sql = "SELECT rowid, datec as datecreation, tms as datem";
        $sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        $sql .= " WHERE t.rowid = ".((int) $id);

        $result = $this->db->query($sql);
        if ($result) {
            if ($this->db->num_rows($result)) {
                $obj = $this->db->fetch_object($result);
                $this->id = $obj->rowid;
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
        
        $this->code = 'OFFICE';
        $this->label = 'Office Work';
        $this->description = 'Regular office work at company premises';
        $this->color = '#4CAF50';
        $this->position = 10;
        $this->active = 1;
        $this->module = 'appmobtimetouch';
    }

    /**
     * Get URL to the timeclock type card
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

        $label = img_picto('', $this->picto).' <u>'.$langs->trans("TimeclockType").'</u>';
        $label .= '<br>';
        $label .= '<b>'.$langs->trans('Code').':</b> '.$this->code;
        $label .= '<br>';
        $label .= '<b>'.$langs->trans('Label').':</b> '.$this->label;

        $url = dol_buildpath('/appmobtimetouch/timeclocktype_card.php', 1).'?id='.$this->id;

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
                $label = $langs->trans("ShowTimeclockType");
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
            $result .= $this->label;
        }

        $result .= $linkend;

        global $action, $hookmanager;
        $hookmanager->initHooks(array('timeclocktypedao'));
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
     * Return select list of timeclock types
     *
     * @param DoliDB $db            Database handler
     * @param string $selected      Selected value
     * @param string $htmlname      Name of HTML field
     * @param int    $showempty     Show empty value (0=No, 1=Yes with generic label, 2=Yes with specific label)
     * @param string $morecss       More CSS classes
     * @param string $moreparams    More parameters
     * @param int    $activeonly    Show active types only (1=Yes, 0=No)
     * @return string               HTML select
     */
    public static function selectTimeclockTypes($db, $selected = '', $htmlname = 'fk_timeclock_type', $showempty = 1, $morecss = '', $moreparams = '', $activeonly = 1)
    {
        global $langs;

        $langs->load("appmobtimetouch@appmobtimetouch");

        $out = '';

        $sql = "SELECT rowid, code, label, color FROM ".MAIN_DB_PREFIX."timeclock_types";
        $sql .= " WHERE entity IN (".getEntity('timeclocktype').")";
        if ($activeonly) {
            $sql .= " AND active = 1";
        }
        $sql .= " ORDER BY position ASC, label ASC";

        $resql = $db->query($sql);
        if ($resql) {
            $out = '<select id="'.$htmlname.'" name="'.$htmlname.'" class="flat'.($morecss ? ' '.$morecss : '').'"'.($moreparams ? ' '.$moreparams : '').'>';

            if ($showempty) {
                $textforempty = '';
                if ($showempty == 1) {
                    $textforempty = $langs->trans("SelectTimeclockType");
                } else {
                    $textforempty = $showempty;
                }
                $out .= '<option value="">'.$textforempty.'</option>';
            }

            while ($obj = $db->fetch_object($resql)) {
                $selected_attr = '';
                if ($selected == $obj->rowid) {
                    $selected_attr = ' selected="selected"';
                }

                $out .= '<option value="'.$obj->rowid.'"'.$selected_attr;
                if (!empty($obj->color)) {
                    $out .= ' data-color="'.$obj->color.'"';
                }
                $out .= '>';
                $out .= dol_escape_htmltag($obj->label);
                $out .= '</option>';
            }

            $out .= '</select>';
            $db->free($resql);
        } else {
            dol_print_error($db);
        }

        return $out;
    }

    /**
     * Return array of timeclock types for use in forms
     *
     * @param DoliDB $db        Database handler
     * @param int    $activeonly Show active types only (1=Yes, 0=No)
     * @return array            Array of id => label
     */
    public static function getTypesArray($db, $activeonly = 1)
    {
        $types = array();

        $sql = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."timeclock_types";
        $sql .= " WHERE entity IN (".getEntity('timeclocktype').")";
        if ($activeonly) {
            $sql .= " AND active = 1";
        }
        $sql .= " ORDER BY position ASC, label ASC";

        $resql = $db->query($sql);
        if ($resql) {
            while ($obj = $db->fetch_object($resql)) {
                $types[$obj->rowid] = $obj->label;
            }
            $db->free($resql);
        }

        return $types;
    }

    /**
     * Get default timeclock type ID
     *
     * @param DoliDB $db Database handler
     * @return int       Default type ID, 0 if none found
     */
    public static function getDefaultType($db)
    {
        global $conf;

        // Check if default type is configured
        if (!empty($conf->global->APPMOBTIMETOUCH_DEFAULT_TIMECLOCK_TYPE)) {
            $default_id = $conf->global->APPMOBTIMETOUCH_DEFAULT_TIMECLOCK_TYPE;
            
            // Verify the type exists and is active
            $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_types";
            $sql .= " WHERE rowid = ".((int) $default_id);
            $sql .= " AND active = 1";
            $sql .= " AND entity IN (".getEntity('timeclocktype').")";

            $resql = $db->query($sql);
            if ($resql) {
                if ($db->num_rows($resql)) {
                    $db->free($resql);
                    return $default_id;
                }
                $db->free($resql);
            }
        }

        // Get first active type
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_types";
        $sql .= " WHERE active = 1";
        $sql .= " AND entity IN (".getEntity('timeclocktype').")";
        $sql .= " ORDER BY position ASC, label ASC";
        $sql .= " LIMIT 1";

        $resql = $db->query($sql);
        if ($resql) {
            if ($db->num_rows($resql)) {
                $obj = $db->fetch_object($resql);
                $db->free($resql);
                return $obj->rowid;
            }
            $db->free($resql);
        }

        return 0;
    }

    /**
     * Export data for dictionary synchronization
     *
     * @return array Array of type data
     */
    public function exportForDictionary()
    {
        return array(
            'code' => $this->code,
            'label' => $this->label,
            'color' => $this->color,
            'position' => $this->position,
            'active' => $this->active
        );
    }

    /**
     * Import data from dictionary
     *
     * @param array $data Dictionary data
     * @param User  $user User object
     * @return int        <0 if KO, >0 if OK
     */
    public function importFromDictionary($data, $user)
    {
        // Check if type with this code already exists
        $existing = self::getByCode($this->db, $data['code']);
        
        if ($existing) {
            // Update existing type
            $existing->label = $data['label'];
            $existing->color = $data['color'];
            $existing->position = $data['position'];
            $existing->active = $data['active'];
            return $existing->update($user, true);
        } else {
            // Create new type
            $this->code = $data['code'];
            $this->label = $data['label'];
            $this->color = $data['color'];
            $this->position = $data['position'];
            $this->active = $data['active'];
            $this->module = 'appmobtimetouch';
            return $this->create($user, true);
        }
    }

    /**
     * Synchronize with dictionary data
     *
     * @param DoliDB $db   Database handler
     * @param User   $user User object
     * @return int         Number of types synchronized
     */
    public static function synchronizeWithDictionary($db, $user)
    {
        $synchronized = 0;
        
        // This method would be called to sync with the dictionary
        // when dictionary is updated through admin interface
        
        return $synchronized;
    }

    /**
     * Get statistics about timeclock type usage
     *
     * @return array Array with usage statistics
     */
    public function getUsageStats()
    {
        $stats = array(
            'total_records' => 0,
            'active_records' => 0,
            'completed_records' => 0,
            'total_hours' => 0
        );

        // Total records using this type
        $sql = "SELECT COUNT(*) as total FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_timeclock_type = ".((int) $this->id);
        $sql .= " AND entity IN (".getEntity('timeclockrecord').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $stats['total_records'] = $obj->total;
            $this->db->free($resql);
        }

        // Active records (in progress)
        $sql = "SELECT COUNT(*) as active FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_timeclock_type = ".((int) $this->id);
        $sql .= " AND status = 2"; // STATUS_IN_PROGRESS
        $sql .= " AND entity IN (".getEntity('timeclockrecord').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $stats['active_records'] = $obj->active;
            $this->db->free($resql);
        }

        // Completed records and total hours
        $sql = "SELECT COUNT(*) as completed, SUM(work_duration) as total_minutes FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_timeclock_type = ".((int) $this->id);
        $sql .= " AND status = 3"; // STATUS_COMPLETED
        $sql .= " AND work_duration IS NOT NULL";
        $sql .= " AND entity IN (".getEntity('timeclockrecord').")";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $stats['completed_records'] = $obj->completed;
            $stats['total_hours'] = round($obj->total_minutes / 60, 2);
            $this->db->free($resql);
        }

        return $stats;
    }
}