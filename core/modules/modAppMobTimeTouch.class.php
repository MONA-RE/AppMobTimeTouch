<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2025 SuperAdmin
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * 	\defgroup   appmobtimetouch     Module AppMobTimeTouch
 *  \brief      AppMobTimeTouch module descriptor - Time tracking and presence management.
 *
 *  \file       htdocs/appmobtimetouch/core/modules/modAppMobTimeTouch.class.php
 *  \ingroup    appmobtimetouch
 *  \brief      Description and activation file for module AppMobTimeTouch - Employee time tracking
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module AppMobTimeTouch - Employee Time Tracking
 */
class modAppMobTimeTouch extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		$this->numero = 136006; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module

		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'appmobtimetouch';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// Module is HR related for time tracking
		$this->family = "hr";

		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '85';

		// Module label (no space allowed), used if translation string 'ModuleAppMobTimeTouchName' not found
		$this->name = preg_replace('/^mod/i', '', get_class($this));

		// Module description, used if translation string 'ModuleAppMobTimeTouchDesc' not found
		$this->description = "Mobile time tracking and employee presence management";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "Complete mobile solution for employee time tracking, presence management, and work hours reporting. Allows employees to clock in/out with mobile interface and provides management tools for time validation and reporting.";

		// Author
		$this->editor_name = 'MONA';
		$this->editor_url = '';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.1';

		// Key used in llx_const table to save module status enabled/disabled
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		// Name of image file used for this module.
		$this->picto = 'clock'; // Use clock icon for time tracking

		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				'/appmobtimetouch/css/appmobtimetouch.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				'/appmobtimetouch/js/appmobtimetouch.js.php',
			),
			// Set here all hooks context managed by module
			'hooks' => array(
				'data' => array(
					'usercard',
					'globalcard',
					'leftblock',
					'rightblock',
					'timeclockcard',
				),
				'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);

		// Data directories to create when module is enabled.
		$this->dirs = array("/appmobtimetouch/temp");

		// Config pages. Put here list of php page, stored into appmobtimetouch/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@appmobtimetouch");

		// Dependencies
		$this->hidden = false;
		// List of module class names that must be enabled if this module is enabled
		$this->depends = array('modUser'); // Requires user module
		$this->requiredby = array();
		$this->conflictwith = array();

		// The language file dedicated to your module
		$this->langfiles = array("appmobtimetouch@appmobtimetouch");

		// Prerequisites
		$this->phpmin = array(5, 6);
		$this->need_dolibarr_version = array(11, -3);

		// Messages at activation
		$this->warnings_activation = array();
		$this->warnings_activation_ext = array();

		// Constants
		// Default configuration constants for time tracking
		$this->const = array(
			1 => array('APPMOBTIMETOUCH_AUTO_BREAK_MINUTES', 'chaine', '60', 'Default break duration in minutes', 1),
			2 => array('APPMOBTIMETOUCH_MAX_HOURS_DAY', 'chaine', '12', 'Maximum hours per day', 1),
			3 => array('APPMOBTIMETOUCH_REQUIRE_LOCATION', 'chaine', '0', 'Require GPS location for clock in/out', 1),
			4 => array('APPMOBTIMETOUCH_ALLOW_MANUAL_EDIT', 'chaine', '1', 'Allow manual time editing', 1),
			5 => array('APPMOBTIMETOUCH_DEFAULT_TIMECLOCK_TYPE', 'chaine', '1', 'Default time clock type', 1),
			6 => array('APPMOBTIMETOUCH_VALIDATION_REQUIRED', 'chaine', '1', 'Require manager validation', 1),
		);

		if (!isset($conf->appmobtimetouch) || !isset($conf->appmobtimetouch->enabled)) {
			$conf->appmobtimetouch = new stdClass();
			$conf->appmobtimetouch->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		// Add time tracking tab to user card
		$this->tabs[] = array('data'=>'user:+timeclock:TimeTracking:appmobtimetouch@appmobtimetouch:$user->rights->appmobtimetouch->read:/appmobtimetouch/timeclock_user.php?id=__ID__');

		// Dictionaries - Time clock types
		$this->dictionaries = array(
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'tabname'=>array("llx_timeclock_types"),
			'tablib'=>array("TimeclockTypes"),
			'tabsql'=>array('SELECT t.rowid, t.code, t.label, t.color, t.active FROM '.MAIN_DB_PREFIX.'timeclock_types as t'),
			'tabsqlsort'=>array("position ASC, label ASC"),
			'tabfield'=>array("code,label,color"),
			'tabfieldvalue'=>array("code,label,color"),
			'tabfieldinsert'=>array("code,label,color"),
			'tabrowid'=>array("rowid"),
			'tabcond'=>array($conf->appmobtimetouch->enabled),
			'tabhelp'=>array(array('code'=>$langs->trans('TimeclockTypeCodeTooltip'), 'label'=>$langs->trans('TimeclockTypeLabelTooltip'), 'color'=>$langs->trans('TimeclockTypeColorTooltip'))),
		);

		// Boxes/Widgets for dashboard
		$this->boxes = array(
			0 => array(
				'file' => 'timeclockwidget.php@appmobtimetouch',
				'note' => 'Current time tracking status widget',
				'enabledbydefaulton' => 'Home',
			),
			1 => array(
				'file' => 'weeklysummarywidget.php@appmobtimetouch',
				'note' => 'Weekly time summary widget',
				'enabledbydefaulton' => 'Home',
			),
		);

		// Cronjobs for automated tasks
		$this->cronjobs = array(
			0 => array(
				'label' => 'Auto close open timeclock records',
				'jobtype' => 'method',
				'class' => '/appmobtimetouch/class/timeclockrecord.class.php',
				'objectname' => 'TimeclockRecord',
				'method' => 'autoCloseOpenRecords',
				'parameters' => '',
				'comment' => 'Automatically close timeclock records left open overnight',
				'frequency' => 1,
				'unitfrequency' => 86400, // Daily
				'status' => 1,
				'test' => '$conf->appmobtimetouch->enabled',
				'priority' => 50,
			),
			1 => array(
				'label' => 'Generate weekly summaries',
				'jobtype' => 'method',
				'class' => '/appmobtimetouch/class/weeklysummary.class.php',
				'objectname' => 'WeeklySummary',
				'method' => 'generateWeeklySummaries',
				'parameters' => '',
				'comment' => 'Generate weekly time summaries for all users',
				'frequency' => 1,
				'unitfrequency' => 604800, // Weekly
				'status' => 1,
				'test' => '$conf->appmobtimetouch->enabled',
				'priority' => 40,
			),
		);

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;

		// Time tracking permissions
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Read own time tracking records';
		$this->rights[$r][4] = 'timeclock';
		$this->rights[$r][5] = 'read';
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Create/Update own time tracking records';
		$this->rights[$r][4] = 'timeclock';
		$this->rights[$r][5] = 'write';
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Delete own time tracking records';
		$this->rights[$r][4] = 'timeclock';
		$this->rights[$r][5] = 'delete';
		$r++;

		// Management permissions
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Read all users time tracking records';
		$this->rights[$r][4] = 'timeclock';
		$this->rights[$r][5] = 'readall';
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Validate time tracking records';
		$this->rights[$r][4] = 'timeclock';
		$this->rights[$r][5] = 'validate';
		$r++;

		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Export time tracking reports';
		$this->rights[$r][4] = 'timeclock';
		$this->rights[$r][5] = 'export';
		$r++;

		// Configuration permissions
		$this->rights[$r][0] = $this->numero . sprintf("%02d", $r + 1);
		$this->rights[$r][1] = 'Configure time tracking module';
		$this->rights[$r][4] = 'timeclock';
		$this->rights[$r][5] = 'config';
		$r++;

		// Main menu entries
		$this->menu = array();
		$r = 0;

		// Top menu for time tracking
		$this->menu[$r++] = array(
			'fk_menu'=>'',
			'type'=>'top',
			'titre'=>'TimeTracking',
			'prefix' => img_picto('', $this->picto, 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'',
			'url'=>'/appmobtimetouch/index.php',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000 + $r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->read',
			'target'=>'',
			'user'=>2,
		);

		// Left menu - My Time Tracking
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=appmobtimetouch',
			'type'=>'left',
			'titre'=>'MyTimeTracking',
			'prefix' => img_picto('', 'clock', 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'mytimeclock',
			'url'=>'/appmobtimetouch/timeclock_card.php',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000+$r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->read',
			'target'=>'',
			'user'=>2,
		);

		// Left menu - Clock In/Out
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=appmobtimetouch,fk_leftmenu=mytimeclock',
			'type'=>'left',
			'titre'=>'ClockInOut',
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'clockinout',
			'url'=>'/appmobtimetouch/clockinout.php',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000+$r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->write',
			'target'=>'',
			'user'=>2,
		);

		// Left menu - My Records
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=appmobtimetouch,fk_leftmenu=mytimeclock',
			'type'=>'left',
			'titre'=>'MyRecords',
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'myrecords',
			'url'=>'/appmobtimetouch/timeclock_list.php',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000+$r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->read',
			'target'=>'',
			'user'=>2,
		);

		// Left menu - Management (for managers)
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=appmobtimetouch',
			'type'=>'left',
			'titre'=>'TimeManagement',
			'prefix' => img_picto('', 'group', 'class="paddingright pictofixedwidth valignmiddle"'),
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'timemanagement',
			'url'=>'/appmobtimetouch/management.php',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000+$r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->readall',
			'target'=>'',
			'user'=>2,
		);

		// Left menu - All Records (for managers)
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=appmobtimetouch,fk_leftmenu=timemanagement',
			'type'=>'left',
			'titre'=>'AllRecords',
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'allrecords',
			'url'=>'/appmobtimetouch/timeclock_list.php?mode=all',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000+$r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->readall',
			'target'=>'',
			'user'=>2,
		);

		// Left menu - Validation
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=appmobtimetouch,fk_leftmenu=timemanagement',
			'type'=>'left',
			'titre'=>'Validation',
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'validation',
			'url'=>'/appmobtimetouch/validation.php',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000+$r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->validate',
			'target'=>'',
			'user'=>2,
		);

		// Left menu - Reports
		$this->menu[$r++] = array(
			'fk_menu'=>'fk_mainmenu=appmobtimetouch,fk_leftmenu=timemanagement',
			'type'=>'left',
			'titre'=>'Reports',
			'mainmenu'=>'appmobtimetouch',
			'leftmenu'=>'reports',
			'url'=>'/appmobtimetouch/reports.php',
			'langs'=>'appmobtimetouch@appmobtimetouch',
			'position'=>1000+$r,
			'enabled'=>'$conf->appmobtimetouch->enabled',
			'perms'=>'$user->rights->appmobtimetouch->timeclock->export',
			'target'=>'',
			'user'=>2,
		);

		// Exports profiles
		$r = 1;
		$this->export_code[$r] = $this->rights_class.'_'.$r;
		$this->export_label[$r] = 'TimeclockRecords';
		$this->export_icon[$r] = 'timeclock@appmobtimetouch';
		$this->export_sql_start[$r] = 'SELECT DISTINCT ';
		$this->export_sql_end[$r] = ' FROM '.MAIN_DB_PREFIX.'timeclock_records as t';
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'user as u ON u.rowid = t.fk_user';
		$this->export_sql_end[$r] .= ' LEFT JOIN '.MAIN_DB_PREFIX.'timeclock_types as tt ON tt.rowid = t.fk_timeclock_type';
		$this->export_sql_end[$r] .= ' WHERE 1 = 1';
		$this->export_sql_end[$r] .= ' AND t.entity IN ('.getEntity('user').')';

		// No imports for security reasons - time tracking should be done through the interface
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories and loads SQL tables
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		// Load SQL tables for time tracking
		$result = $this->_load_tables('/appmobtimetouch/sql/');
		if ($result < 0) {
			return -1;
		}

		// Insert default time clock types
		$sql = array();
		$sql[] = "INSERT IGNORE INTO ".MAIN_DB_PREFIX."timeclock_types (code, label, color, position, active, module) VALUES";
		$sql[] = "('OFFICE', 'Office Work', '#4CAF50', 1, 1, 'appmobtimetouch'),";
		$sql[] = "('REMOTE', 'Remote Work', '#2196F3', 2, 1, 'appmobtimetouch'),";
		$sql[] = "('MISSION', 'External Mission', '#FF9800', 3, 1, 'appmobtimetouch'),";
		$sql[] = "('TRAINING', 'Training', '#9C27B0', 4, 1, 'appmobtimetouch')";

		// Execute SQL to insert default data
		foreach ($sql as $query) {
			$resql = $this->db->query($query);
			if (!$resql) {
				dol_print_error($this->db);
				return -1;
			}
		}

		// Permissions
		$this->remove($options);

		return $this->_init(array(), $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}