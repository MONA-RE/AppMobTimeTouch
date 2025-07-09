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

			// Calculate work duration if both times are set
			if (!empty($clock_out_time)) {
				$duration_seconds = $clock_out_time - $clock_in_time;
				$object->work_duration = round($duration_seconds / 60); // Convert to minutes
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

			// Calculate work duration if both times are set
			if (!empty($clock_out_time)) {
				$duration_seconds = $clock_out_time - $clock_in_time;
				$object->work_duration = round($duration_seconds / 60); // Convert to minutes
			} else {
				$object->work_duration = null;
			}

			$result = $object->update($user);
			if ($result > 0) {
				setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
				$action = '';
			} else {
				$error++;
				setEventMessages($object->error, $object->errors, 'errors');
				$action = 'edit';
			}
		} else {
			$action = 'edit';
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
$sql_types = "SELECT rowid, label FROM ".MAIN_DB_PREFIX."appmobtimetouch_timeclocktype WHERE active = 1 ORDER BY label";
$resql_types = $db->query($sql_types);
if ($resql_types) {
    while ($obj_type = $db->fetch_object($resql_types)) {
        $array_types[$obj_type->rowid] = $obj_type->label;
    }
    $db->free($resql_types);
}
if (empty($array_types)) {
    $array_types[1] = $langs->trans("Standard");
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

	print load_fiche_titre($langs->trans("NewTimeclockRecord"), '', 'clock');

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
	print '<tr><td class="fieldrequired">'.$langs->trans("Employee").'</td><td>';
	print $form->selectarray('fk_user', $array_users, GETPOST('fk_user', 'int'), 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Clock In Time
	print '<tr><td class="fieldrequired">'.$langs->trans("ClockInTime").'</td><td>';
	print $form->selectDate(GETPOST('clock_in_time') ? GETPOST('clock_in_time') : dol_now(), "clock_in_time", 1, 1, 0, '', 1, 1);
	print '</td></tr>';

	// Clock Out Time
	print '<tr><td>'.$langs->trans("ClockOutTime").'</td><td>';
	print $form->selectDate(GETPOST('clock_out_time') ? GETPOST('clock_out_time') : -1, "clock_out_time", 1, 1, 1, '', 1, 1);
	print '</td></tr>';

	// Work Type
	print '<tr><td>'.$langs->trans("WorkType").'</td><td>';
	print $form->selectarray('fk_timeclock_type', $array_types, GETPOST('fk_timeclock_type', 'int'), 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Location In
	print '<tr><td>'.$langs->trans("LocationIn").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_in" value="'.dol_escape_htmltag(GETPOST('location_in', 'alpha')).'">';
	print '</td></tr>';

	// Location Out
	print '<tr><td>'.$langs->trans("LocationOut").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_out" value="'.dol_escape_htmltag(GETPOST('location_out', 'alpha')).'">';
	print '</td></tr>';

	// Status
	print '<tr><td>'.$langs->trans("Status").'</td><td>';
	print $form->selectarray('status', $array_status, GETPOST('status', 'int') ? GETPOST('status', 'int') : 2, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
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

	print load_fiche_titre($langs->trans("EditTimeclockRecord"), '', 'clock');

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
	print '<tr><td class="fieldrequired">'.$langs->trans("Employee").'</td><td>';
	print $form->selectarray('fk_user', $array_users, $object->fk_user, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Clock In Time
	print '<tr><td class="fieldrequired">'.$langs->trans("ClockInTime").'</td><td>';
	print $form->selectDate($db->jdate($object->clock_in_time), "clock_in_time", 1, 1, 0, '', 1, 1);
	print '</td></tr>';

	// Clock Out Time
	print '<tr><td>'.$langs->trans("ClockOutTime").'</td><td>';
	print $form->selectDate($object->clock_out_time ? $db->jdate($object->clock_out_time) : -1, "clock_out_time", 1, 1, 1, '', 1, 1);
	print '</td></tr>';

	// Work Type
	print '<tr><td>'.$langs->trans("WorkType").'</td><td>';
	print $form->selectarray('fk_timeclock_type', $array_types, $object->fk_timeclock_type, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
	print '</td></tr>';

	// Location In
	print '<tr><td>'.$langs->trans("LocationIn").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_in" value="'.dol_escape_htmltag($object->location_in).'">';
	print '</td></tr>';

	// Location Out
	print '<tr><td>'.$langs->trans("LocationOut").'</td><td>';
	print '<input type="text" class="flat maxwidth300" name="location_out" value="'.dol_escape_htmltag($object->location_out).'">';
	print '</td></tr>';

	// Status
	print '<tr><td>'.$langs->trans("Status").'</td><td>';
	print $form->selectarray('status', $array_status, $object->status, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth300', 1);
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
	print dol_print_date($db->jdate($object->clock_in_time), 'dayhour');
	print '</td></tr>';

	// Clock Out Time
	print '<tr><td>'.$langs->trans("ClockOutTime").'</td><td>';
	if ($object->clock_out_time) {
		print dol_print_date($db->jdate($object->clock_out_time), 'dayhour');
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

// End of page
llxFooter();
$db->close();