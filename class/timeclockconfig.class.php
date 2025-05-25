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
 * \file        class/timeclockconfig.class.php
 * \ingroup     appmobtimetouch
 * \brief       This file is a CRUD class file for TimeclockConfig (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

/**
 * Class for TimeclockConfig
 */
class TimeclockConfig extends CommonObject
{
    /**
     * @var string ID to identify managed object
     */
    public $element = 'timeclockconfig';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'timeclock_config';

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
     * @var string String with name of icon for timeclockconfig. Must be the part after the 'object_' into object_timeclockconfig.png
     */
    public $picto = 'setup';

    /**
     * @var array Cache for configuration values
     */
    private static $config_cache = array();

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
        'name' => array('type'=>'varchar(180)', 'label'=>'ConfigName', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'css'=>'minwidth200', 'help'=>"ConfigNameHelp", 'autofocusoncreate'=>1),
        'value' => array('type'=>'text', 'label'=>'ConfigValue', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'css'=>'minwidth300', 'help'=>"ConfigValueHelp"),
        'type' => array('type'=>'varchar(64)', 'label'=>'ConfigType', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1, 'default'=>'string', 'css'=>'maxwidth150', 'arrayofkeyval'=>array('string'=>'String', 'int'=>'Integer', 'float'=>'Float', 'boolean'=>'Boolean', 'json'=>'JSON'), 'help'=>"ConfigTypeHelp"),
        'note' => array('type'=>'varchar(255)', 'label'=>'Note', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1, 'css'=>'minwidth300', 'help'=>"ConfigNoteHelp"),
        'active' => array('type'=>'boolean', 'label'=>'Active', 'enabled'=>'1', 'position'=>60, 'notnull'=>1, 'visible'=>1, 'default'=>'1', 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes')),
    );

    public $rowid;
    public $entity;
    public $datec;
    public $tms;
    public $name;
    public $value;
    public $type;
    public $note;
    public $active;

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

        // Check if config name already exists
        if ($this->configExists($this->name)) {
            $this->error = 'ConfigNameAlreadyExists';
            return -1;
        }

        // Validate value according to type
        if (!$this->validateValue()) {
            return -1;
        }

        $resultcreate = $this->createCommon($user, $notrigger);

        if ($resultcreate > 0) {
            // Clear cache
            self::clearCache();
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

        // Clear fields that shouldn't be cloned
        $object->name = '';

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
     * @param string $name Config name
     * @return int         <0 if KO, 0 if not found, >0 if OK
     */
    public function fetch($id, $ref = null, $name = null)
    {
        if (!empty($name)) {
            $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
            $sql .= " WHERE name = '".$this->db->escape($name)."'";
            $sql .= " AND entity IN (".getEntity($this->element).")";
            
            $resql = $this->db->query($sql);
            if ($resql) {
                if ($this->db->num_rows($resql)) {
                    $obj = $this->db->fetch_object($resql);
                    $id = $obj->rowid;
                }
                $this->db->free($resql);
            }
        }

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
        // Check if config name already exists for another record
        if ($this->configExists($this->name, $this->id)) {
            $this->error = 'ConfigNameAlreadyExists';
            return -1;
        }

        // Validate value according to type
        if (!$this->validateValue()) {
            return -1;
        }

        $result = $this->updateCommon($user, $notrigger);

        if ($result > 0) {
            // Clear cache
            self::clearCache();
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
            // Clear cache
            self::clearCache();
        }

        return $result;
    }

    /**
     * Check if config name already exists
     *
     * @param string $name      Config name to check
     * @param int    $excludeid ID to exclude from check
     * @return bool             True if exists, false otherwise
     */
    public function configExists($name, $excludeid = 0)
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX.$this->table_element;
        $sql .= " WHERE name = '".$this->db->escape($name)."'";
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
     * Validate configuration value according to its type
     *
     * @return bool True if valid, false otherwise
     */
    public function validateValue()
    {
        if (empty($this->type)) {
            $this->type = 'string';
        }

        switch ($this->type) {
            case 'int':
                if (!is_numeric($this->value) || (string)(int)$this->value !== $this->value) {
                    $this->error = 'InvalidIntegerValue';
                    return false;
                }
                break;

            case 'float':
                if (!is_numeric($this->value)) {
                    $this->error = 'InvalidFloatValue';
                    return false;
                }
                break;

            case 'boolean':
                if (!in_array($this->value, array('0', '1', 0, 1, true, false, 'true', 'false'))) {
                    $this->error = 'InvalidBooleanValue';
                    return false;
                }
                // Normalize boolean values
                $this->value = in_array($this->value, array('1', 1, true, 'true')) ? '1' : '0';
                break;

            case 'json':
                if (!empty($this->value)) {
                    $decoded = json_decode($this->value, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->error = 'InvalidJSONValue';
                        return false;
                    }
                }
                break;

            case 'string':
            default:
                // No specific validation for string
                break;
        }

        return true;
    }

    /**
     * Get configuration value by name
     *
     * @param DoliDB $db        Database handler
     * @param string $name      Configuration name
     * @param mixed  $default   Default value if not found
     * @param int    $entity    Entity (0 for current entity)
     * @return mixed            Configuration value
     */
    public static function getValue($db, $name, $default = null, $entity = 0)
    {
        if ($entity == 0) {
            $entity = getEntity('timeclockconfig');
        }

        $cache_key = $entity.'_'.$name;

        // Check cache first
        if (isset(self::$config_cache[$cache_key])) {
            return self::$config_cache[$cache_key];
        }

        $sql = "SELECT value, type FROM ".MAIN_DB_PREFIX."timeclock_config";
        $sql .= " WHERE name = '".$db->escape($name)."'";
        $sql .= " AND entity IN (".$entity.")";
        $sql .= " AND active = 1";

        $resql = $db->query($sql);
        if ($resql) {
            if ($db->num_rows($resql)) {
                $obj = $db->fetch_object($resql);
                $value = self::convertValue($obj->value, $obj->type);
                
                // Cache the value
                self::$config_cache[$cache_key] = $value;
                
                $db->free($resql);
                return $value;
            }
            $db->free($resql);
        }

        // Cache the default value
        self::$config_cache[$cache_key] = $default;
        return $default;
    }

    /**
     * Set configuration value by name
     *
     * @param DoliDB $db     Database handler
     * @param string $name   Configuration name
     * @param mixed  $value  Configuration value
     * @param string $type   Value type (string, int, float, boolean, json)
     * @param string $note   Optional note
     * @param User   $user   User object
     * @return int           <0 if KO, >0 if OK
     */
    public static function setValue($db, $name, $value, $type = 'string', $note = '', $user = null)
    {
        $config = new TimeclockConfig($db);
        
        // Try to fetch existing config
        if ($config->fetch(0, null, $name) > 0) {
            // Update existing
            $config->value = $value;
            $config->type = $type;
            if (!empty($note)) {
                $config->note = $note;
            }
            $result = $config->update($user ? $user : new User($db));
        } else {
            // Create new
            $config->name = $name;
            $config->value = $value;
            $config->type = $type;
            $config->note = $note;
            $config->active = 1;
            $result = $config->create($user ? $user : new User($db));
        }

        if ($result > 0) {
            // Clear cache
            self::clearCache();
        }

        return $result;
    }

    /**
     * Convert value according to its type
     *
     * @param mixed  $value Raw value from database
     * @param string $type  Value type
     * @return mixed        Converted value
     */
    private static function convertValue($value, $type)
    {
        switch ($type) {
            case 'int':
                return (int)$value;

            case 'float':
                return (float)$value;

            case 'boolean':
                return (bool)$value;

            case 'json':
                return !empty($value) ? json_decode($value, true) : array();

            case 'string':
            default:
                return (string)$value;
        }
    }

    /**
     * Get all configurations as array
     *
     * @param DoliDB $db     Database handler
     * @param bool   $active Only active configurations
     * @return array         Array of name => value
     */
    public static function getAllConfigs($db, $active = true)
    {
        $configs = array();

        $sql = "SELECT name, value, type FROM ".MAIN_DB_PREFIX."timeclock_config";
        $sql .= " WHERE entity IN (".getEntity('timeclockconfig').")";
        if ($active) {
            $sql .= " AND active = 1";
        }
        $sql .= " ORDER BY name ASC";

        $resql = $db->query($sql);
        if ($resql) {
            while ($obj = $db->fetch_object($resql)) {
                $configs[$obj->name] = self::convertValue($obj->value, $obj->type);
            }
            $db->free($resql);
        }

        return $configs;
    }

    /**
     * Clear configuration cache
     */
    public static function clearCache()
    {
        self::$config_cache = array();
    }

    /**
     * Import configurations from array
     *
     * @param DoliDB $db      Database handler
     * @param array  $configs Array of configurations
     * @param User   $user    User object
     * @return int            Number of configs imported
     */
    public static function importConfigs($db, $configs, $user)
    {
        $imported = 0;

        foreach ($configs as $name => $config_data) {
            $value = isset($config_data['value']) ? $config_data['value'] : '';
            $type = isset($config_data['type']) ? $config_data['type'] : 'string';
            $note = isset($config_data['note']) ? $config_data['note'] : '';

            if (self::setValue($db, $name, $value, $type, $note, $user) > 0) {
                $imported++;
            }
        }

        return $imported;
    }

    /**
     * Export configurations to array
     *
     * @param DoliDB $db Database handler
     * @return array     Exportable configuration data
     */
    public static function exportConfigs($db)
    {
        $export = array();

        $sql = "SELECT name, value, type, note FROM ".MAIN_DB_PREFIX."timeclock_config";
        $sql .= " WHERE entity IN (".getEntity('timeclockconfig').")";
        $sql .= " AND active = 1";
        $sql .= " ORDER BY name ASC";

        $resql = $db->query($sql);
        if ($resql) {
            while ($obj = $db->fetch_object($resql)) {
                $export[$obj->name] = array(
                    'value' => $obj->value,
                    'type' => $obj->type,
                    'note' => $obj->note
                );
            }
            $db->free($resql);
        }

        return $export;
    }

    /**
     * Load default configurations into database
     *
     * @param DoliDB $db   Database handler
     * @param User   $user User object
     * @return int         Number of configs loaded
     */
    public static function loadDefaultConfigs($db, $user)
    {
        $defaults = array(
            'AUTO_BREAK_MINUTES' => array('value' => '60', 'type' => 'int', 'note' => 'Automatic break duration in minutes after continuous work'),
            'MAX_HOURS_PER_DAY' => array('value' => '12', 'type' => 'int', 'note' => 'Maximum allowed working hours per day'),
            'REQUIRE_LOCATION' => array('value' => '0', 'type' => 'boolean', 'note' => 'Require GPS location for clock in/out'),
            'ALLOW_MANUAL_EDIT' => array('value' => '1', 'type' => 'boolean', 'note' => 'Allow users to manually edit their time records'),
            'VALIDATION_REQUIRED' => array('value' => '1', 'type' => 'boolean', 'note' => 'Require manager validation for time records'),
            'OVERTIME_THRESHOLD' => array('value' => '8', 'type' => 'float', 'note' => 'Daily hours threshold before overtime calculation'),
            'WEEKLY_HOURS_THRESHOLD' => array('value' => '40', 'type' => 'float', 'note' => 'Weekly hours threshold before overtime calculation'),
            'AUTO_CLOCK_OUT_HOURS' => array('value' => '24', 'type' => 'int', 'note' => 'Auto clock out after X hours if not manually clocked out'),
            'BREAK_REMINDER_MINUTES' => array('value' => '240', 'type' => 'int', 'note' => 'Remind user to take break after X minutes of continuous work'),
            'ALLOW_FUTURE_CLOCKIN' => array('value' => '0', 'type' => 'boolean', 'note' => 'Allow clock in for future dates'),
            'MINIMUM_BREAK_MINUTES' => array('value' => '5', 'type' => 'int', 'note' => 'Minimum break duration in minutes'),
            'LOCATION_RADIUS_METERS' => array('value' => '100', 'type' => 'int', 'note' => 'Allowed radius in meters from registered work locations'),
        );

        $loaded = 0;

        foreach ($defaults as $name => $config) {
            // Check if config already exists
            $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_config";
            $sql .= " WHERE name = '".$db->escape($name)."'";
            $sql .= " AND entity IN (".getEntity('timeclockconfig').")";

            $resql = $db->query($sql);
            if ($resql) {
                if ($db->num_rows($resql) == 0) {
                    // Config doesn't exist, create it
                    if (self::setValue($db, $name, $config['value'], $config['type'], $config['note'], $user) > 0) {
                        $loaded++;
                    }
                }
                $db->free($resql);
            }
        }

        return $loaded;
    }

    /**
     * Get configurations for setup form
     *
     * @param DoliDB $db Database handler
     * @return array     Array of configurations grouped by category
     */
    public static function getConfigsForSetup($db)
    {
        $configs = array(
            'general' => array(),
            'validation' => array(),
            'automation' => array(),
            'location' => array()
        );

        $all_configs = self::getAllConfigs($db);

        // Categorize configurations
        foreach ($all_configs as $name => $value) {
            $config_obj = new TimeclockConfig($db);
            if ($config_obj->fetch(0, null, $name) > 0) {
                $config_data = array(
                    'name' => $name,
                    'value' => $value,
                    'type' => $config_obj->type,
                    'note' => $config_obj->note
                );

                // Categorize based on name patterns
                if (in_array($name, array('VALIDATION_REQUIRED', 'ALLOW_MANUAL_EDIT'))) {
                    $configs['validation'][] = $config_data;
                } elseif (in_array($name, array('AUTO_BREAK_MINUTES', 'AUTO_CLOCK_OUT_HOURS', 'BREAK_REMINDER_MINUTES'))) {
                    $configs['automation'][] = $config_data;
                } elseif (in_array($name, array('REQUIRE_LOCATION', 'LOCATION_RADIUS_METERS'))) {
                    $configs['location'][] = $config_data;
                } else {
                    $configs['general'][] = $config_data;
                }
            }
        }

        return $configs;
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
        
        $this->name = 'EXAMPLE_CONFIG';
        $this->value = '30';
        $this->type = 'int';
        $this->note = 'Example configuration parameter';
        $this->active = 1;
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
        $datas['picto'] = img_picto('', $this->picto).' <u>'.$langs->trans("TimeclockConfig").'</u>';
        if (isset($this->active)) {
            $datas['picto'] .= ' '.$this->getLibStatut(5);
        }
        
        $datas['name'] = '<br><b>'.$langs->trans('ConfigName').':</b> '.$this->name;
        
        if (isset($this->type)) {
            $types = array('string' => 'String', 'int' => 'Integer', 'float' => 'Float', 'boolean' => 'Boolean', 'json' => 'JSON');
            $datas['type'] = '<br><b>'.$langs->trans('ConfigType').':</b> '.$types[$this->type];
        }
        
        if (isset($this->value)) {
            $display_value = $this->value;
            if ($this->type == 'boolean') {
                $display_value = $this->value ? $langs->trans('Yes') : $langs->trans('No');
            } elseif (strlen($this->value) > 50) {
                $display_value = substr($this->value, 0, 50) . '...';
            }
            $datas['value'] = '<br><b>'.$langs->trans('ConfigValue').':</b> '.$display_value;
        }
        
        if (isset($this->note) && !empty($this->note)) {
            $datas['note'] = '<br><b>'.$langs->trans('Note').':</b> '.$this->note;
        }

        return $datas;
    }

    /**
     * Get URL to the timeclock config card
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

        $label = img_picto('', $this->picto).' <u>'.$langs->trans("TimeclockConfig").'</u>';
        if (isset($this->active)) {
            $label .= ' '.$this->getLibStatut(5);
        }
        $label .= '<br>';
        $label .= '<b>'.$langs->trans('ConfigName').':</b> '.$this->name;

        $url = dol_buildpath('/appmobtimetouch/timeclockconfig_card.php', 1).'?id='.$this->id;

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
                $label = $langs->trans("ShowTimeclockConfig");
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
            $result .= $this->name;
        }

        $result .= $linkend;

        global $action, $hookmanager;
        $hookmanager->initHooks(array('timeclockconfigdao'));
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
     * Return select list of config types
     *
     * @param string $selected      Selected value
     * @param string $htmlname      Name of HTML field
     * @param int    $showempty     Show empty value (0=No, 1=Yes with generic label, 2=Yes with specific label)
     * @param string $morecss       More CSS classes
     * @param string $moreparams    More parameters
     * @return string               HTML select
     */
    public static function selectConfigTypes($selected = '', $htmlname = 'type', $showempty = 1, $morecss = '', $moreparams = '')
    {
        global $langs;

        $langs->load("appmobtimetouch@appmobtimetouch");

        $out = '<select id="'.$htmlname.'" name="'.$htmlname.'" class="flat'.($morecss ? ' '.$morecss : '').'"'.($moreparams ? ' '.$moreparams : '').'>';

        if ($showempty) {
            $textforempty = '';
            if ($showempty == 1) {
                $textforempty = $langs->trans("SelectConfigType");
            } else {
                $textforempty = $showempty;
            }
            $out .= '<option value="">'.$textforempty.'</option>';
        }

        $types = array(
            'string' => $langs->trans('String'),
            'int' => $langs->trans('Integer'),
            'float' => $langs->trans('Float'),
            'boolean' => $langs->trans('Boolean'),
            'json' => $langs->trans('JSON')
        );

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
     * Get configuration value with fallback to global constants
     *
     * @param DoliDB $db      Database handler
     * @param string $name    Configuration name
     * @param mixed  $default Default value
     * @return mixed          Configuration value
     */
    public static function getValueWithFallback($db, $name, $default = null)
    {
        // First try to get from timeclock_config table
        $value = self::getValue($db, $name, null);
        
        if ($value !== null) {
            return $value;
        }

        // Fallback to global constants with APPMOBTIMETOUCH_ prefix
        global $conf;
        $const_name = 'APPMOBTIMETOUCH_' . $name;
        
        if (isset($conf->global->$const_name)) {
            return $conf->global->$const_name;
        }

        return $default;
    }

    /**
     * Synchronize with global constants
     *
     * @param DoliDB $db   Database handler
     * @param User   $user User object
     * @return int         Number of synchronized configs
     */
    public static function syncWithGlobalConstants($db, $user)
    {
        global $conf;
        
        $synced = 0;
        $prefix = 'APPMOBTIMETOUCH_';
        
        // Get all global constants starting with our prefix
        foreach ($conf->global as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $config_name = substr($key, strlen($prefix));
                
                // Check if this config already exists in our table
                $existing_value = self::getValue($db, $config_name, null);
                
                if ($existing_value === null) {
                    // Create new config entry
                    $type = 'string';
                    if (is_numeric($value)) {
                        $type = (strpos($value, '.') !== false) ? 'float' : 'int';
                    } elseif (in_array(strtolower($value), array('0', '1', 'true', 'false'))) {
                        $type = 'boolean';
                    }
                    
                    if (self::setValue($db, $config_name, $value, $type, 'Synchronized from global constant', $user) > 0) {
                        $synced++;
                    }
                }
            }
        }
        
        return $synced;
    }

    /**
     * Get configuration schema for validation
     *
     * @return array Configuration schema
     */
    public static function getConfigSchema()
    {
        return array(
            'AUTO_BREAK_MINUTES' => array(
                'type' => 'int',
                'min' => 0,
                'max' => 480,
                'default' => 60,
                'description' => 'Automatic break duration in minutes after continuous work'
            ),
            'MAX_HOURS_PER_DAY' => array(
                'type' => 'int',
                'min' => 1,
                'max' => 24,
                'default' => 12,
                'description' => 'Maximum allowed working hours per day'
            ),
            'REQUIRE_LOCATION' => array(
                'type' => 'boolean',
                'default' => false,
                'description' => 'Require GPS location for clock in/out'
            ),
            'ALLOW_MANUAL_EDIT' => array(
                'type' => 'boolean',
                'default' => true,
                'description' => 'Allow users to manually edit their time records'
            ),
            'VALIDATION_REQUIRED' => array(
                'type' => 'boolean',
                'default' => true,
                'description' => 'Require manager validation for time records'
            ),
            'OVERTIME_THRESHOLD' => array(
                'type' => 'float',
                'min' => 0,
                'max' => 24,
                'default' => 8.0,
                'description' => 'Daily hours threshold before overtime calculation'
            ),
            'WEEKLY_HOURS_THRESHOLD' => array(
                'type' => 'float',
                'min' => 0,
                'max' => 168,
                'default' => 40.0,
                'description' => 'Weekly hours threshold before overtime calculation'
            ),
            'AUTO_CLOCK_OUT_HOURS' => array(
                'type' => 'int',
                'min' => 1,
                'max' => 72,
                'default' => 24,
                'description' => 'Auto clock out after X hours if not manually clocked out'
            ),
            'BREAK_REMINDER_MINUTES' => array(
                'type' => 'int',
                'min' => 0,
                'max' => 480,
                'default' => 240,
                'description' => 'Remind user to take break after X minutes of continuous work'
            ),
            'ALLOW_FUTURE_CLOCKIN' => array(
                'type' => 'boolean',
                'default' => false,
                'description' => 'Allow clock in for future dates'
            ),
            'MINIMUM_BREAK_MINUTES' => array(
                'type' => 'int',
                'min' => 1,
                'max' => 60,
                'default' => 5,
                'description' => 'Minimum break duration in minutes'
            ),
            'LOCATION_RADIUS_METERS' => array(
                'type' => 'int',
                'min' => 10,
                'max' => 5000,
                'default' => 100,
                'description' => 'Allowed radius in meters from registered work locations'
            )
        );
    }

    /**
     * Validate configuration against schema
     *
     * @param string $name  Configuration name
     * @param mixed  $value Configuration value
     * @return bool         True if valid
     */
    public static function validateAgainstSchema($name, $value)
    {
        $schema = self::getConfigSchema();
        
        if (!isset($schema[$name])) {
            return true; // Unknown config, allow it
        }
        
        $config_schema = $schema[$name];
        
        switch ($config_schema['type']) {
            case 'int':
                if (!is_numeric($value) || (string)(int)$value !== (string)$value) {
                    return false;
                }
                $int_value = (int)$value;
                if (isset($config_schema['min']) && $int_value < $config_schema['min']) {
                    return false;
                }
                if (isset($config_schema['max']) && $int_value > $config_schema['max']) {
                    return false;
                }
                break;
                
            case 'float':
                if (!is_numeric($value)) {
                    return false;
                }
                $float_value = (float)$value;
                if (isset($config_schema['min']) && $float_value < $config_schema['min']) {
                    return false;
                }
                if (isset($config_schema['max']) && $float_value > $config_schema['max']) {
                    return false;
                }
                break;
                
            case 'boolean':
                if (!in_array($value, array('0', '1', 0, 1, true, false, 'true', 'false'))) {
                    return false;
                }
                break;
        }
        
        return true;
    }

    /**
     * Reset configurations to default values
     *
     * @param DoliDB $db   Database handler
     * @param User   $user User object
     * @return int         Number of configs reset
     */
    public static function resetToDefaults($db, $user)
    {
        $schema = self::getConfigSchema();
        $reset = 0;
        
        foreach ($schema as $name => $config_schema) {
            $default_value = $config_schema['default'];
            $type = $config_schema['type'];
            $note = $config_schema['description'];
            
            if (self::setValue($db, $name, $default_value, $type, $note, $user) > 0) {
                $reset++;
            }
        }
        
        return $reset;
    }
}