<?php
/* Copyright (C) 2025 SuperAdmin
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
 *   	\file       card.php
 *		\ingroup    appmobtimetouch
 *		\brief      Page to create/edit/view timeclock records
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';

// Load translation files required by the page
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php'));
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');

// Initialize technical objects
$object = new TimeclockRecord($db);
$extrafields = new ExtraFields($db);
$hookmanager->initHooks(array('timeclockrecordcard', 'globalcard'));

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

if (empty($action) && empty($id) && empty($ref)) {
	$action = 'view';
}

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';

// DEBUG: Log object loading and timestamp data
if ($object->id > 0) {
	dol_syslog("DEBUG card.php - Object loaded ID: ".$object->id, LOG_DEBUG);
	dol_syslog("DEBUG card.php - clock_in_time raw: ".$object->clock_in_time, LOG_DEBUG);
	dol_syslog("DEBUG card.php - clock_out_time raw: ".$object->clock_out_time, LOG_DEBUG);
	dol_syslog("DEBUG card.php - clock_in_time jdate: ".($object->clock_in_time ? $db->jdate($object->clock_in_time) : 'NULL'), LOG_DEBUG);
	dol_syslog("DEBUG card.php - clock_out_time jdate: ".($object->clock_out_time ? $db->jdate($object->clock_out_time) : 'NULL'), LOG_DEBUG);
	dol_syslog("DEBUG card.php - Action: ".$action, LOG_DEBUG);
	
	// DEBUG: Add visible debug info for user (disabled in production)
	if ($conf->global->MAIN_MODULE_DOLIBARR_DEBUGBAR && 0) { // Disabled - only show if debugbar is enabled AND manual flag set
		$debug_msg = "<strong>DEBUG TimeClock Card - DATA TYPES</strong><br>";
		$debug_msg .= "Object ID: ".$object->id."<br>";
		$debug_msg .= "Clock In Raw: ".$object->clock_in_time." (".gettype($object->clock_in_time).")<br>";
		$debug_msg .= "Clock Out Raw: ".$object->clock_out_time." (".gettype($object->clock_out_time).")<br>";
		// Test strtotime conversion
		$clock_in_test = is_string($object->clock_in_time) ? strtotime($object->clock_in_time) : $object->clock_in_time;
		$clock_out_test = is_string($object->clock_out_time) ? strtotime($object->clock_out_time) : $object->clock_out_time;
		$debug_msg .= "<strong>Clock In strtotime(): ".$clock_in_test."</strong><br>";
		$debug_msg .= "<strong>Clock Out strtotime(): ".$clock_out_test."</strong><br>";
		$debug_msg .= "Action: ".$action;
		setEventMessages($debug_msg, null, 'mesgs');
	}
}

// Permission checks
$permissiontoread = $user->hasRight('appmobtimetouch', 'timeclock', 'read');
$permissiontoadd = $user->hasRight('appmobtimetouch', 'timeclock', 'write');
$permissiontodelete = $user->hasRight('appmobtimetouch', 'timeclock', 'delete') || ($permissiontoadd && isset($object->status) && $object->status == 0);

// Security check
if (empty($conf->appmobtimetouch->enabled)) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}
if (!$permissiontoread) {
    accessforbidden('NotEnoughPermissions');
}

/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	$error = 0;

	$backurlforlist = dol_buildpath('/custom/appmobtimetouch/list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
				$backtopage = $backurlforlist;
			} else {
				$backtopage = dol_buildpath('/custom/appmobtimetouch/card.php', 1).'?id='.((!empty($id) && $id > 0) ? $id : '__ID__');
			}
		}
	}

	$triggermodname = 'APPMOBTIMETOUCH_TIMECLOCKRECORD_MODIFY';

	// CUSTOM: Handle update action with duration calculation before standard actions
	if ($action == 'update' && $permissiontoadd) {
		$error = 0;

		// Get form data
		$fk_user = GETPOST('fk_user', 'int');
		$clock_in_time = dol_mktime(GETPOST('clock_in_timehour', 'int'), GETPOST('clock_in_timemin', 'int'), 0, GETPOST('clock_in_timemonth', 'int'), GETPOST('clock_in_timeday', 'int'), GETPOST('clock_in_timeyear', 'int'));
		$clock_out_time = dol_mktime(GETPOST('clock_out_timehour', 'int'), GETPOST('clock_out_timemin', 'int'), 0, GETPOST('clock_out_timemonth', 'int'), GETPOST('clock_out_timeday', 'int'), GETPOST('clock_out_timeyear', 'int'));
		$fk_timeclock_type = GETPOST('fk_timeclock_type', 'int');
		$location_in = GETPOST('location_in', 'alpha');
		$location_out = GETPOST('location_out', 'alpha');
		$status = GETPOST('status', 'int');
		$calculated_duration = GETPOST('calculated_duration', 'int'); // Duration from client-side calculation
		
		// DEBUG: Log form data received
		dol_syslog("DEBUG update action - POST data received:", LOG_DEBUG);
		dol_syslog("DEBUG update - fk_user: ".$fk_user, LOG_DEBUG);
		dol_syslog("DEBUG update - clock_in_time: ".$clock_in_time, LOG_DEBUG);
		dol_syslog("DEBUG update - clock_out_time: ".$clock_out_time, LOG_DEBUG);
		dol_syslog("DEBUG update - calculated_duration: ".$calculated_duration, LOG_DEBUG);
		dol_syslog("DEBUG update - Raw POST clock_in: hour=".GETPOST('clock_in_timehour', 'int')." min=".GETPOST('clock_in_timemin', 'int')." day=".GETPOST('clock_in_timeday', 'int')." month=".GETPOST('clock_in_timemonth', 'int')." year=".GETPOST('clock_in_timeyear', 'int'), LOG_DEBUG);
		dol_syslog("DEBUG update - Raw POST clock_out: hour=".GETPOST('clock_out_timehour', 'int')." min=".GETPOST('clock_out_timemin', 'int')." day=".GETPOST('clock_out_timeday', 'int')." month=".GETPOST('clock_out_timemonth', 'int')." year=".GETPOST('clock_out_timeyear', 'int'), LOG_DEBUG);

		// Validation
		if (empty($fk_user)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Employee")), null, 'errors');
		}
		if (empty($clock_in_time)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ClockIn")), null, 'errors');
		}
		if (!empty($clock_out_time) && $clock_out_time <= $clock_in_time) {
			$error++;
			setEventMessages($langs->trans("ErrorClockOutBeforeClockIn"), null, 'errors');
		}

		if (!$error) {
			// DEBUG: Log object state before modification
			dol_syslog("DEBUG update - BEFORE: Object work_duration = ".$object->work_duration, LOG_DEBUG);
			
			$object->fk_user = $fk_user;
			$object->clock_in_time = $db->idate($clock_in_time);
			$object->clock_out_time = !empty($clock_out_time) ? $db->idate($clock_out_time) : null;
			$object->fk_timeclock_type = $fk_timeclock_type;
			$object->location_in = $location_in;
			$object->location_out = $location_out;
			$object->status = !empty($status) ? $status : (!empty($clock_out_time) ? 3 : 2); // Use selected status or auto-determine

			// Calculate work duration - prioritize client-calculated value, fallback to server calculation
			if (!empty($calculated_duration) && $calculated_duration > 0) {
				// Use client-calculated duration (from JavaScript)
				$object->work_duration = (int)$calculated_duration;
				dol_syslog("DEBUG update - Using client-calculated duration: ".$object->work_duration." minutes", LOG_DEBUG);
			} elseif (!empty($clock_out_time)) {
				// Fallback to server-side calculation
				$duration_seconds = $clock_out_time - $clock_in_time;
				$object->work_duration = round($duration_seconds / 60); // Convert to minutes
				dol_syslog("DEBUG update - Server-calculated duration: clock_in=$clock_in_time, clock_out=$clock_out_time, diff=$duration_seconds seconds, duration=".$object->work_duration." minutes", LOG_DEBUG);
			} else {
				$object->work_duration = null;
				dol_syslog("DEBUG update - No clock_out_time, duration set to null", LOG_DEBUG);
			}

			// DEBUG: Log final object state before update call
			dol_syslog("DEBUG update - FINAL: Object work_duration = ".$object->work_duration." minutes before update() call", LOG_DEBUG);
			
			$result = $object->update($user);
			dol_syslog("DEBUG update - Object update result: ".$result, LOG_DEBUG);
			if ($result > 0) {
				dol_syslog("DEBUG update - Update successful", LOG_DEBUG);
				setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
				$action = '';
			} else {
				$error++;
				dol_syslog("DEBUG update - Update failed: ".$object->error, LOG_ERR);
				setEventMessages($object->error, $object->errors, 'errors');
				$action = 'edit';
			}
		} else {
			$action = 'edit';
		}
	}

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Custom actions
	if ($action == 'add' && $permissiontoadd) {
		$error = 0;

		// Get form data
		$fk_user = GETPOST('fk_user', 'int');
		$clock_in_time = dol_mktime(GETPOST('clock_in_timehour', 'int'), GETPOST('clock_in_timemin', 'int'), 0, GETPOST('clock_in_timemonth', 'int'), GETPOST('clock_in_timeday', 'int'), GETPOST('clock_in_timeyear', 'int'));
		$clock_out_time = dol_mktime(GETPOST('clock_out_timehour', 'int'), GETPOST('clock_out_timemin', 'int'), 0, GETPOST('clock_out_timemonth', 'int'), GETPOST('clock_out_timeday', 'int'), GETPOST('clock_out_timeyear', 'int'));
		$fk_timeclock_type = GETPOST('fk_timeclock_type', 'int');
		$location_in = GETPOST('location_in', 'alpha');
		$location_out = GETPOST('location_out', 'alpha');
		$status = GETPOST('status', 'int');
		$calculated_duration = GETPOST('calculated_duration', 'int'); // Duration from client-side calculation

		// Validation
		if (empty($fk_user)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Employee")), null, 'errors');
		}
		if (empty($clock_in_time)) {
			$error++;
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("ClockIn")), null, 'errors');
		}
		if (!empty($clock_out_time) && $clock_out_time <= $clock_in_time) {
			$error++;
			setEventMessages($langs->trans("ErrorClockOutBeforeClockIn"), null, 'errors');
		}

		if (!$error) {
			$object->fk_user = $fk_user;
			$object->clock_in_time = $db->idate($clock_in_time);
			$object->clock_out_time = !empty($clock_out_time) ? $db->idate($clock_out_time) : null;
			$object->fk_timeclock_type = $fk_timeclock_type;
			$object->location_in = $location_in;
			$object->location_out = $location_out;
			$object->status = !empty($status) ? $status : (!empty($clock_out_time) ? 3 : 2); // Use selected status or auto-determine
			$object->fk_user_creat = $user->id;
			$object->datec = dol_now();

			// Calculate work duration - prioritize client-calculated value, fallback to server calculation
			if (!empty($calculated_duration) && $calculated_duration > 0) {
				// Use client-calculated duration (from JavaScript)
				$object->work_duration = (int)$calculated_duration;
				dol_syslog("DEBUG create - Using client-calculated duration: ".$object->work_duration." minutes", LOG_DEBUG);
			} elseif (!empty($clock_out_time)) {
				// Fallback to server-side calculation
				$duration_seconds = $clock_out_time - $clock_in_time;
				$object->work_duration = round($duration_seconds / 60); // Convert to minutes
				dol_syslog("DEBUG create - Server-calculated duration: clock_in=$clock_in_time, clock_out=$clock_out_time, diff=$duration_seconds seconds, duration=".$object->work_duration." minutes", LOG_DEBUG);
			} else {
				$object->work_duration = null;
				dol_syslog("DEBUG create - No clock_out_time, duration set to null", LOG_DEBUG);
			}

			$result = $object->create($user);
			if ($result > 0) {
				setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
				$action = '';
				$id = $result;
			} else {
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
				$action = 'create';
			}
		} else {
			$action = 'create';
		}
	}
}

/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

$title = $langs->trans("TimeclockRecord");
$help_url = '';
llxHeader('', $title, $help_url);

// Build user list for dropdown
$array_users = array();
$sql_users = "SELECT DISTINCT u.rowid, u.login, u.lastname, u.firstname FROM ".MAIN_DB_PREFIX."user as u 
              WHERE u.entity IN (".getEntity('user').") AND u.statut = 1 
              ORDER BY u.lastname, u.firstname, u.login";
$resql_users = $db->query($sql_users);
if ($resql_users) {
    while ($obj_user = $db->fetch_object($resql_users)) {
        $user_name = trim($obj_user->lastname.' '.$obj_user->firstname);
        if (empty($user_name)) $user_name = $obj_user->login;
        $array_users[$obj_user->rowid] = $user_name.' ('.$obj_user->login.')';
    }
    $db->free($resql_users);
}

// Build timeclock type list
$array_types = array();
$sql_types = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."timeclock_types WHERE active = 1 ORDER BY label";
$resql_types = $db->query($sql_types);
if ($resql_types) {
    while ($obj_type = $db->fetch_object($resql_types)) {
        $array_types[$obj_type->rowid] = $obj_type->label;
    }
    $db->free($resql_types);
    dol_syslog("DEBUG card.php - Loaded ".count($array_types)." timeclock types", LOG_DEBUG);
} else {
    dol_syslog("DEBUG card.php - Failed to load timeclock types: ".$db->lasterror(), LOG_ERR);
}
if (empty($array_types)) {
    $array_types[1] = $langs->trans("Standard");
    dol_syslog("DEBUG card.php - Using fallback 'Standard' type", LOG_DEBUG);
}

// Build status array (excluding STATUS_VALIDATED as validation is managed separately)
$array_status = array(
    0 => $langs->trans('Draft'),
    2 => $langs->trans('InProgress'),
    3 => $langs->trans('Completed'),
    9 => $langs->trans('Cancelled')
);

// Part to create
if ($action == 'create') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("NewTimeclockRecord"), '', 'fa-clock-o');

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Employee
	print '<tr><td class="titlefield fieldrequired">'.$langs->trans("Employee").'</td><td>';
	print $form->selectarray('fk_user', $array_users, GETPOST('fk_user', 'int'), 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Clock In Time
	print '<tr><td class="titlefield fieldrequired">'.$langs->trans("ClockInTime").'</td><td>';
	print $form->selectDate(GETPOST('clock_in_time') ? GETPOST('clock_in_time') : dol_now(), "clock_in_time", 1, 1, 0, '', 1, 1);
	print '</td></tr>';

	// Clock Out Time
	print '<tr><td class="titlefield">'.$langs->trans("ClockOutTime").'</td><td>';
	print $form->selectDate(GETPOST('clock_out_time') ? GETPOST('clock_out_time') : -1, "clock_out_time", 1, 1, 1, '', 1, 1);
	print '</td></tr>';

	// Work Type
	print '<tr><td class="titlefield">'.$langs->trans("WorkType").'</td><td>';
	print $form->selectarray('fk_timeclock_type', $array_types, GETPOST('fk_timeclock_type', 'int'), 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Location In
	print '<tr><td class="titlefield">'.$langs->trans("LocationIn").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_in" value="'.dol_escape_htmltag(GETPOST('location_in', 'alpha')).'">';
	print '</td></tr>';

	// Location Out
	print '<tr><td class="titlefield">'.$langs->trans("LocationOut").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_out" value="'.dol_escape_htmltag(GETPOST('location_out', 'alpha')).'">';
	print '</td></tr>';

	// Status
	print '<tr><td class="titlefield">'.$langs->trans("Status").'</td><td>';
	print $form->selectarray('status', $array_status, GETPOST('status', 'int') ? GETPOST('status', 'int') : 2, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Duration (calculated automatically)
	print '<tr id="duration_row"><td class="titlefield">'.$langs->trans("WorkDuration").'</td><td>';
	print '<span id="duration_display" class="badge badge-info">-</span>';
	print '<input type="hidden" id="calculated_duration" name="calculated_duration" value="">';
	print '</td></tr>';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel("Create");

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit') {
	if (empty($permissiontoadd)) {
		accessforbidden($langs->trans('NotEnoughPermissions'), 0, 1);
		exit;
	}

	print load_fiche_titre($langs->trans("EditTimeclockRecord"), '', 'fa-edit');

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) {
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	}
	if ($backtopageforcancel) {
		print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';
	}

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Employee
	print '<tr><td class="titlefield fieldrequired">'.$langs->trans("Employee").'</td><td>';
	print $form->selectarray('fk_user', $array_users, $object->fk_user, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Clock In Time
	print '<tr><td class="titlefield fieldrequired">'.$langs->trans("ClockInTime").'</td><td>';
	// FIX: Use proper Dolibarr method for datetime conversion
	if (is_string($object->clock_in_time)) {
		// Convert MySQL datetime string to timestamp using strtotime (standard PHP)
		$clock_in_jdate = strtotime($object->clock_in_time);
	} else {
		// Already a timestamp
		$clock_in_jdate = $object->clock_in_time ? (int)$object->clock_in_time : '';
	}
	dol_syslog("DEBUG edit form - clock_in_time STANDARD conversion: raw=".$object->clock_in_time." -> timestamp=".$clock_in_jdate, LOG_DEBUG);
	print $form->selectDate($clock_in_jdate, "clock_in_time", 1, 1, 0, '', 1, 1);
	print '</td></tr>';

	// Clock Out Time  
	print '<tr><td class="titlefield">'.$langs->trans("ClockOutTime").'</td><td>';
	// FIX: Use proper Dolibarr method for datetime conversion
	if (is_string($object->clock_out_time)) {
		// Convert MySQL datetime string to timestamp using strtotime (standard PHP)
		$clock_out_jdate = strtotime($object->clock_out_time);
	} else {
		// Already a timestamp or null
		$clock_out_jdate = $object->clock_out_time ? (int)$object->clock_out_time : -1;
	}
	dol_syslog("DEBUG edit form - clock_out_time STANDARD conversion: raw=".$object->clock_out_time." -> timestamp=".$clock_out_jdate, LOG_DEBUG);
	print $form->selectDate($clock_out_jdate, "clock_out_time", 1, 1, 1, '', 1, 1);
	print '</td></tr>';

	// Work Type
	print '<tr><td class="titlefield">'.$langs->trans("WorkType").'</td><td>';
	print $form->selectarray('fk_timeclock_type', $array_types, $object->fk_timeclock_type, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Location In
	print '<tr><td class="titlefield">'.$langs->trans("LocationIn").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_in" value="'.dol_escape_htmltag($object->location_in).'">';
	print '</td></tr>';

	// Location Out
	print '<tr><td class="titlefield">'.$langs->trans("LocationOut").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_out" value="'.dol_escape_htmltag($object->location_out).'">';
	print '</td></tr>';

	// Status
	print '<tr><td class="titlefield">'.$langs->trans("Status").'</td><td>';
	print $form->selectarray('status', $array_status, $object->status, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Duration (calculated automatically)
	print '<tr id="duration_row"><td class="titlefield">'.$langs->trans("WorkDuration").'</td><td>';
	// Show current duration if available
	$current_duration_display = '-';
	if ($object->work_duration && $object->work_duration > 0) {
		$hours = floor($object->work_duration / 60);
		$minutes = $object->work_duration % 60;
		$current_duration_display = sprintf('%dh %02dm (%d minutes)', $hours, $minutes, $object->work_duration);
	}
	print '<span id="duration_display" class="badge badge-info">'.$current_duration_display.'</span>';
	print '<input type="hidden" id="calculated_duration" name="calculated_duration" value="'.$object->work_duration.'">';
	print '</td></tr>';

	print '</table>';

	print dol_get_fiche_end();

	print $form->buttonsSaveCancel();

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	$res = $object->fetch_optionals();

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteTimeclockRecord'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}

	// Print form confirm
	print $formconfirm;

	// Object card
	$linkback = '<a href="'.dol_buildpath('/custom/appmobtimetouch/list.php', 1).'?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);
	
	// Prepare head (tabs)
	$head = array();
	$head[0][0] = dol_buildpath('/custom/appmobtimetouch/card.php', 1).'?id='.$object->id;
	$head[0][1] = $langs->trans("TimeclockRecord");
	$head[0][2] = 'card';
	
	print dol_get_fiche_head($head, 'card', '', -1, 'object_appmobtimetouch', 0, '', '', 0, '', 1);

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Employee
	print '<tr><td class="titlefield">'.$langs->trans("Employee").'</td><td>';
	if ($object->fk_user > 0) {
		$userobj = new User($db);
		$userobj->fetch($object->fk_user);
		print $userobj->getNomUrl(1);
	}
	print '</td></tr>';

	// Clock In Time
	print '<tr><td>'.$langs->trans("ClockInTime").'</td><td>';
	// Clock In avec gestion fuseau utilisateur
	// Utilisation directe de la valeur base sans conversion préalable
	print dol_print_date($object->clock_in_time, 'dayhour', 'tzuser');
	print '</td></tr>';

	// Clock Out Time
	print '<tr><td>'.$langs->trans("ClockOutTime").'</td><td>';
	if ($object->clock_out_time) {
		// Clock Out avec gestion fuseau utilisateur  
		// Utilisation directe de la valeur base sans conversion préalable
		print dol_print_date($object->clock_out_time, 'dayhour', 'tzuser');
	} else {
		print '<span class="opacitymedium">'.$langs->trans("InProgress").'</span>';
	}
	print '</td></tr>';

	// Work Duration
	print '<tr><td>'.$langs->trans("WorkDuration").'</td><td>';
	if ($object->work_duration && $object->work_duration > 0) {
		$hours = floor($object->work_duration / 60);
		$minutes = $object->work_duration % 60;
		print sprintf('%dh %02dm', $hours, $minutes);
	} else {
		print '<span class="opacitymedium">-</span>';
	}
	print '</td></tr>';

	// Work Type
	print '<tr><td>'.$langs->trans("WorkType").'</td><td>';
	if (isset($array_types[$object->fk_timeclock_type])) {
		print $array_types[$object->fk_timeclock_type];
	} else {
		print $langs->trans("Standard");
	}
	print '</td></tr>';

	// Location In
	print '<tr><td>'.$langs->trans("LocationIn").'</td><td>';
	print $object->location_in ? dol_escape_htmltag($object->location_in) : '<span class="opacitymedium">-</span>';
	print '</td></tr>';

	// Location Out
	print '<tr><td>'.$langs->trans("LocationOut").'</td><td>';
	print $object->location_out ? dol_escape_htmltag($object->location_out) : '<span class="opacitymedium">-</span>';
	print '</td></tr>';

	// Status
	print '<tr><td>'.$langs->trans("Status").'</td><td>';
	$status_labels = array(
		0 => 'Draft',
		1 => 'Validated', 
		2 => 'InProgress',
		3 => 'Completed',
		9 => 'Cancelled'
	);
	$status_colors = array(
		0 => 'status0',
		1 => 'status4',
		2 => 'status3',
		3 => 'status6',
		9 => 'status9'
	);
	$status_label = isset($status_labels[$object->status]) ? $langs->trans($status_labels[$object->status]) : $object->status;
	$status_color = isset($status_colors[$object->status]) ? $status_colors[$object->status] : 'status0';
	print dolGetStatus($status_label, '', '', $status_color, 1);
	print '</td></tr>';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();

	// Buttons for actions
	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";

		if (empty($reshook)) {
			// Edit
			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit&token='.newToken(), '', $permissiontoadd);

			// Delete
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete);
		}
		print '</div>'."\n";
	}
}

// JavaScript for automatic duration calculation
if ($action == 'create' || $action == 'edit') {
	print '<script type="text/javascript">
	$(document).ready(function() {
		// Function to calculate duration automatically
		function calculateDuration() {
			// Get clock-in values
			var clockInYear = $("#clock_in_timeyear").val();
			var clockInMonth = $("#clock_in_timemonth").val();
			var clockInDay = $("#clock_in_timeday").val();
			var clockInHour = $("#clock_in_timehour").val();
			var clockInMin = $("#clock_in_timemin").val();
			
			// Get clock-out values  
			var clockOutYear = $("#clock_out_timeyear").val();
			var clockOutMonth = $("#clock_out_timemonth").val();
			var clockOutDay = $("#clock_out_timeday").val();
			var clockOutHour = $("#clock_out_timehour").val();
			var clockOutMin = $("#clock_out_timemin").val();
			
			// Check if all required values are present and not empty/null
			if (clockInYear && clockInMonth && clockInDay && clockInHour !== "" && clockInMin !== "" &&
			    clockOutYear && clockOutMonth && clockOutDay && clockOutHour !== "" && clockOutHour !== "-1" && 
			    clockOutMin !== "" && clockOutMin !== "-1") {
				
				// Create Date objects
				var clockInDate = new Date(clockInYear, clockInMonth-1, clockInDay, clockInHour, clockInMin, 0);
				var clockOutDate = new Date(clockOutYear, clockOutMonth-1, clockOutDay, clockOutHour, clockOutMin, 0);
				
				// Calculate difference in minutes
				var diffMs = clockOutDate.getTime() - clockInDate.getTime();
				var diffMinutes = Math.round(diffMs / (1000 * 60));
				
				// Display result and update hidden field
				if (diffMinutes > 0) {
					var hours = Math.floor(diffMinutes / 60);
					var minutes = diffMinutes % 60;
					var durationText = "<span class=\"badge badge-info\">" + hours + "h " + (minutes < 10 ? "0" : "") + minutes + "m (" + diffMinutes + " minutes)</span>";
					
					$("#duration_display").html(durationText);
					$("#calculated_duration").val(diffMinutes); // Update hidden field for form submission
				} else {
					$("#duration_display").html("<span class=\"badge badge-danger\">Durée invalide</span>");
					$("#calculated_duration").val(""); // Clear hidden field
				}
			} else {
				// Missing data - show current state or empty
				if (clockOutHour === "" || clockOutHour === "-1" || clockOutMin === "" || clockOutMin === "-1") {
					$("#duration_display").html("<span class=\"badge badge-warning\">En cours...</span>");
				} else {
					$("#duration_display").html("-");
				}
				$("#calculated_duration").val(""); // Clear hidden field
			}
		}
		
		// Calculate on load
		calculateDuration();
		
		// Attach change events to all date/time inputs
		$("select[id*=\"clock_in_time\"], select[id*=\"clock_out_time\"]").change(calculateDuration);
		
		// Also trigger when date input changes
		$("#clock_in_time, #clock_out_time").change(calculateDuration);
	});
	</script>';
}

// End of page
llxFooter();
$db->close();