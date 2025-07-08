<?php
/* Copyright (C) 2025 SuperAdmin
 * Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *   	\file       list.php
 *		\ingroup    appmobtimetouch
 *		\brief      List page for time records (Dolibarr standard)
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';

// Load translation files required by the page
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch", "other"));

$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');

$action     = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view';
$massaction = GETPOST('massaction', 'alpha');
$show_files = GETPOST('show_files', 'int');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'alpha');
$toselect   = GETPOST('toselect', 'array');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : str_replace('_', '', basename(dirname(__FILE__)).basename(__FILE__, '.php'));
$backtopage = GETPOST('backtopage', 'alpha');
$optioncss  = GETPOST('optioncss', 'aZ');
$mode       = GETPOST('mode', 'aZ');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects
$object = new TimeclockRecord($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->appmobtimetouch->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('timeclockrecordlist'));

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Default sort order
if (!$sortfield) {
	$sortfield = "t.clock_in_time";
}
if (!$sortorder) {
	$sortorder = "DESC";
}

// Initialize array of search criterias
$search_all = GETPOST('search_all', 'alphanohtml');
$search = array();

// Search fields for TimeclockRecord
$search_user = GETPOST('search_user', 'alpha');
$search_status = GETPOST('search_status', 'int');
$search_type = GETPOST('search_type', 'int');
$search_validation_status = GETPOST('search_validation_status', 'int');
$search_clock_in_dtstart = dol_mktime(0, 0, 0, GETPOST('search_clock_in_dtstartmonth', 'int'), GETPOST('search_clock_in_dtstartday', 'int'), GETPOST('search_clock_in_dtstartyear', 'int'));
$search_clock_in_dtend = dol_mktime(23, 59, 59, GETPOST('search_clock_in_dtendmonth', 'int'), GETPOST('search_clock_in_dtendday', 'int'), GETPOST('search_clock_in_dtendyear', 'int'));

// Debug date filtering
dol_syslog("Clock in date filter debug: dtstart=" . ($search_clock_in_dtstart ? dol_print_date($search_clock_in_dtstart, 'dayhour') : 'empty') . ", dtend=" . ($search_clock_in_dtend ? dol_print_date($search_clock_in_dtend, 'dayhour') : 'empty'), LOG_DEBUG);

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	't.location_in' => 'Location',
	'u.login' => 'User',
	'u.lastname' => 'LastName',
	'u.firstname' => 'FirstName',
);

// Definition of array of fields for columns
$arrayfields = array(
	't.rowid' => array('label' => 'ID', 'checked' => -1, 'position' => 1, 'type' => 'integer'),
	't.clock_in_time' => array('label' => 'ClockIn', 'checked' => 1, 'position' => 10, 'type' => 'datetime'),
	't.clock_out_time' => array('label' => 'ClockOut', 'checked' => 1, 'position' => 15, 'type' => 'datetime'),
	'u.login' => array('label' => 'User', 'checked' => 1, 'position' => 20, 'type' => 'varchar'),
	'u.lastname' => array('label' => 'LastName', 'checked' => 1, 'position' => 25, 'type' => 'varchar'),
	'u.firstname' => array('label' => 'FirstName', 'checked' => 1, 'position' => 30, 'type' => 'varchar'),
	't.work_duration' => array('label' => 'Duration', 'checked' => 1, 'position' => 35, 'type' => 'integer'),
	't.location_in' => array('label' => 'Location', 'checked' => 0, 'position' => 40, 'type' => 'varchar'),
	't.status' => array('label' => 'Status', 'checked' => 1, 'position' => 45, 'type' => 'integer'),
	't.validation_status' => array('label' => 'ValidationStatus', 'checked' => 1, 'position' => 50, 'type' => 'integer'),
);

// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_array_fields.tpl.php';

$arrayfields = dol_sort_array($arrayfields, 'position');

// Permission checks
$permissiontoread = 1;
$permissiontoadd = 1;
$permissiontodelete = 1;

// Security check
if (empty($conf->appmobtimetouch->enabled)) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}

if (!$user->admin && empty($user->rights->appmobtimetouch)) {
    accessforbidden('NotEnoughPermissions');
}

/*
 * Actions
 */

if (GETPOST('cancel', 'alpha')) {
	$action = 'list';
	$massaction = '';
}
if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') {
	$massaction = '';
}

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action);
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {
	// Selection of new fields
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
		$search_all = '';
		$search_user = '';
		$search_status = '';
		$search_type = '';
		$search_validation_status = '';
		$search_clock_in_dtstart = '';
		$search_clock_in_dtend = '';
		$toselect = array();
		$search_array_options = array();
	}
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')
		|| GETPOST('button_search_x', 'alpha') || GETPOST('button_search.x', 'alpha') || GETPOST('button_search', 'alpha')) {
		$massaction = '';
	}

	// Mass actions
	$objectclass = 'TimeclockRecord';
	$objectlabel = 'TimeclockRecord';
	$uploaddir = $conf->appmobtimetouch->dir_output;
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}

/*
 * View
 */

$form = new Form($db);

$now = dol_now();

$title = $langs->trans("AllRecords");
$morejs = array();
$morecss = array();

// Build and execute select
$sql = 'SELECT ';
$sql .= 't.rowid,';
$sql .= ' t.clock_in_time,';
$sql .= ' t.clock_out_time,';
$sql .= ' t.work_duration,';
$sql .= ' t.location_in,';
$sql .= ' t.status,';
$sql .= ' t.validation_status,';
$sql .= ' t.fk_user,';
$sql .= ' u.login,';
$sql .= ' u.lastname,';
$sql .= ' u.firstname';

// Add fields from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListSelect', $parameters, $object);
$sql .= preg_replace('/^,/', '', $hookmanager->resPrint);
$sql = preg_replace('/,\s*$/', '', $sql);

$sql .= " FROM ".MAIN_DB_PREFIX."timeclock_records as t";
$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."user as u ON u.rowid = t.fk_user";

// Add table from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListFrom', $parameters, $object);
$sql .= $hookmanager->resPrint;

$sql .= " WHERE 1 = 1";

// Search filters
if ($search_all) {
	$sql .= natural_search(array_keys($fieldstosearchall), $search_all);
}
if ($search_user) {
	$sql .= natural_search(array('u.login', 'u.lastname', 'u.firstname'), $search_user);
}
if ($search_status != '' && $search_status != '-1') {
	$sql .= " AND t.status = ".((int) $search_status);
}
if ($search_type != '' && $search_type != '-1') {
	$sql .= " AND t.fk_timeclock_type = ".((int) $search_type);
}
if ($search_validation_status != '' && $search_validation_status != '-1') {
	$sql .= " AND t.validation_status = ".((int) $search_validation_status);
}
if ($search_clock_in_dtstart) {
	$sql .= " AND t.clock_in_time >= '".$db->idate($search_clock_in_dtstart)."'";
	dol_syslog("Added clock_in_time >= filter: " . $db->idate($search_clock_in_dtstart), LOG_DEBUG);
}
if ($search_clock_in_dtend) {
	$sql .= " AND t.clock_in_time <= '".$db->idate($search_clock_in_dtend)."'";
	dol_syslog("Added clock_in_time <= filter: " . $db->idate($search_clock_in_dtend), LOG_DEBUG);
}

// Add where from hooks
$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldListWhere', $parameters, $object);
$sql .= $hookmanager->resPrint;

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
	$sqlforcount = preg_replace('/^SELECT[a-zA-Z0-9\._\s\(\),=<>\:\-\']+\sFROM/', 'SELECT COUNT(*) as nbtotalofrecords FROM', $sql);
	$resql = $db->query($sqlforcount);
	if ($resql) {
		$objforcount = $db->fetch_object($resql);
		$nbtotalofrecords = $objforcount->nbtotalofrecords;
	} else {
		dol_print_error($db);
	}

	if (($page * $limit) > $nbtotalofrecords) {
		$page = 0;
		$offset = 0;
	}
	$db->free($resql);
}

// Complete request and execute it with limit
$sql .= $db->order($sortfield, $sortorder);
if ($limit) {
	$sql .= $db->plimit($limit + 1, $offset);
}

$resql = $db->query($sql);
if (!$resql) {
	dol_print_error($db);
	exit;
}

$num = $db->num_rows($resql);

// Output page
llxHeader('', $title, '', '', 0, 0, $morejs, $morecss, '', 'bodyforlist');

$arrayofselected = is_array($toselect) ? $toselect : array();

$param = '';
if (!empty($mode)) {
	$param .= '&mode='.urlencode($mode);
}
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
	$param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
	$param .= '&limit='.urlencode($limit);
}
if ($search_all != '') {
	$param .= '&search_all='.urlencode($search_all);
}
if ($search_user != '') {
	$param .= '&search_user='.urlencode($search_user);
}
if ($search_status != '') {
	$param .= '&search_status='.urlencode($search_status);
}
if ($search_type != '') {
	$param .= '&search_type='.urlencode($search_type);
}
if ($search_validation_status != '') {
	$param .= '&search_validation_status='.urlencode($search_validation_status);
}
if ($search_clock_in_dtstart) {
	$param .= '&search_clock_in_dtstartday='.dol_print_date($search_clock_in_dtstart, '%d');
	$param .= '&search_clock_in_dtstartmonth='.dol_print_date($search_clock_in_dtstart, '%m');
	$param .= '&search_clock_in_dtstartyear='.dol_print_date($search_clock_in_dtstart, '%Y');
}
if ($search_clock_in_dtend) {
	$param .= '&search_clock_in_dtendday='.dol_print_date($search_clock_in_dtend, '%d');
	$param .= '&search_clock_in_dtendmonth='.dol_print_date($search_clock_in_dtend, '%m');
	$param .= '&search_clock_in_dtendyear='.dol_print_date($search_clock_in_dtend, '%Y');
}
if ($optioncss != '') {
	$param .= '&optioncss='.urlencode($optioncss);
}

// List of mass actions available
$arrayofmassactions = array();
if ($permissiontodelete) {
	$arrayofmassactions['predelete'] = img_picto('', 'delete', 'class="pictofixedwidth"').$langs->trans("Delete");
}
if (GETPOST('nomassaction', 'int') || in_array($massaction, array('presend', 'predelete'))) {
	$arrayofmassactions = array();
}
$massactionbutton = $form->selectMassAction('', $arrayofmassactions);

print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";
if ($optioncss != '') {
	print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
}
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';
print '<input type="hidden" name="mode" value="'.$mode.'">';

$newcardbutton = '';
$newcardbutton .= dolGetButtonTitle($langs->trans('ViewList'), '', 'fa fa-bars imgforviewmode', $_SERVER["PHP_SELF"].'?mode=common'.preg_replace('/(&|\?)*mode=[^&]+/', '', $param), '', ((empty($mode) || $mode == 'common') ? 2 : 1), array('morecss'=>'reposition'));
$newcardbutton .= dolGetButtonTitle($langs->trans('ViewKanban'), '', 'fa fa-th-list imgforviewmode', $_SERVER["PHP_SELF"].'?mode=kanban'.preg_replace('/(&|\?)*mode=[^&]+/', '', $param), '', ($mode == 'kanban' ? 2 : 1), array('morecss'=>'reposition'));
$newcardbutton .= dolGetButtonTitleSeparator();
if ($permissiontoadd) {
	$newcardbutton .= dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', 'card.php?action=create&backtopage='.urlencode($_SERVER['PHP_SELF']), '', $permissiontoadd);
}

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'object_clock', 0, $newcardbutton, '', $limit, 0, 0, 1);

// Add code for pre mass action (confirmation or email presend form)
$topicmail = "SendTimeclockRecordRef";
$modelmail = "timeclockrecord";
$objecttmp = new TimeclockRecord($db);
$trackid = 'tcr'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

if ($search_all) {
	$setupstring = '';
	foreach ($fieldstosearchall as $key => $val) {
		$fieldstosearchall[$key] = $langs->trans($val);
		$setupstring .= $key."=".$val.";";
	}
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all).join(', ', $fieldstosearchall).'</div>'."\n";
}

$moreforfilter = '';

$parameters = array();
$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object);
if (empty($reshook)) {
	$moreforfilter .= $hookmanager->resPrint;
} else {
	$moreforfilter = $hookmanager->resPrint;
}

if (!empty($moreforfilter)) {
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage, getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN', ''));
$selectedfields .= (count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">';
print '<table class="tagtable nobottomiftotal liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";

// Fields title search
print '<tr class="liste_titre">';
// Action column
if (!empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print '<td class="liste_titre maxwidthsearch">';
	$searchpicto = $form->showFilterButtons('left');
	print $searchpicto;
	print '</td>';
}

// ID
if (!empty($arrayfields['t.rowid']['checked'])) {
	print '<td class="liste_titre">';
	print '<input type="text" class="flat maxwidth75" name="search_id" value="'.dol_escape_htmltag($search_id).'">';
	print '</td>';
}

// Clock In
if (!empty($arrayfields['t.clock_in_time']['checked'])) {
	print '<td class="liste_titre center">';
	print '<div class="nowrap">';
	print $form->selectDate($search_clock_in_dtstart ? $search_clock_in_dtstart : -1, "search_clock_in_dtstart", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('From'));
	print '</div>';
	print '<div class="nowrap">';
	print $form->selectDate($search_clock_in_dtend ? $search_clock_in_dtend : -1, "search_clock_in_dtend", 0, 0, 1, '', 1, 0, 0, '', '', '', '', 1, '', $langs->trans('to'));
	print '</div>';
	print '</td>';
}

// Clock Out
if (!empty($arrayfields['t.clock_out_time']['checked'])) {
	print '<td class="liste_titre">';
	print '</td>';
}

// User
if (!empty($arrayfields['u.login']['checked'])) {
	print '<td class="liste_titre">';
	print '<input type="text" class="flat maxwidth100" name="search_user" value="'.dol_escape_htmltag($search_user).'">';
	print '</td>';
}

// LastName
if (!empty($arrayfields['u.lastname']['checked'])) {
	print '<td class="liste_titre">';
	print '</td>';
}

// FirstName  
if (!empty($arrayfields['u.firstname']['checked'])) {
	print '<td class="liste_titre">';
	print '</td>';
}

// Duration
if (!empty($arrayfields['t.work_duration']['checked'])) {
	print '<td class="liste_titre">';
	print '</td>';
}

// Location
if (!empty($arrayfields['t.location_in']['checked'])) {
	print '<td class="liste_titre">';
	print '</td>';
}

// Status
if (!empty($arrayfields['t.status']['checked'])) {
	print '<td class="liste_titre center">';
	$array_status = array(
		'' => '',
		'0' => 'Draft',
		'1' => 'Validated',
		'2' => 'InProgress',
		'3' => 'Completed',
		'9' => 'Cancelled'
	);
	print $form->selectarray('search_status', $array_status, $search_status, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth100', 1);
	print '</td>';
}

// Validation Status
if (!empty($arrayfields['t.validation_status']['checked'])) {
	print '<td class="liste_titre center">';
	$array_validation = array(
		'' => '',
		'0' => 'Pending',
		'1' => 'Approved',
		'2' => 'Rejected',
		'3' => 'Partial'
	);
	print $form->selectarray('search_validation_status', $array_validation, $search_validation_status, 1, 0, 0, '', 1, 0, 0, '', 'maxwidth100', 1);
	print '</td>';
}

// Action column
if (empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print '<td class="liste_titre maxwidthsearch">';
	$searchpicto = $form->showFilterButtons();
	print $searchpicto;
	print '</td>';
}
print '</tr>'."\n";

$totalarray = array();
$totalarray['nbfield'] = 0;

// Fields title label
print '<tr class="liste_titre">';
if (!empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print getTitleFieldOfList(($mode != 'kanban' ? $selectedfields : ''), 0, $_SERVER["PHP_SELF"], '', '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ')."\n";
}

foreach ($arrayfields as $key => $val) {
	if (!empty($val['checked'])) {
		$cssforfield = (empty($val['csslist']) ? (empty($val['css']) ? '' : $val['css']) : $val['csslist']);
		if ($key == 't.status' || $key == 't.validation_status') {
			$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif (!empty($val['type']) && in_array($val['type'], array('date', 'datetime', 'timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '').'center';
		} elseif (!empty($val['type']) && in_array($val['type'], array('timestamp'))) {
			$cssforfield .= ($cssforfield ? ' ' : '').'nowrap';
		} elseif (!empty($val['type']) && in_array($val['type'], array('double(24,8)', 'double(6,3)', 'integer', 'real', 'price')) && $key != 't.rowid') {
			$cssforfield .= ($cssforfield ? ' ' : '').'right';
		}
		$cssforfield = preg_replace('/small\s*/', '', $cssforfield);
		print getTitleFieldOfList($val['label'], 0, $_SERVER['PHP_SELF'], $key, '', $param, ($cssforfield ? 'class="'.$cssforfield.'"' : ''), $sortfield, $sortorder, ($cssforfield ? $cssforfield.' ' : ''))."\n";
		$totalarray['nbfield']++;
	}
}

if (empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
	print getTitleFieldOfList(($mode != 'kanban' ? $selectedfields : ''), 0, $_SERVER["PHP_SELF"], '', '', '', '', $sortfield, $sortorder, 'center maxwidthsearch ')."\n";
}
$totalarray['nbfield']++;
print '</tr>'."\n";

// Loop on record
$i = 0;
$savnbfield = $totalarray['nbfield'];
$totalarray = array();
$totalarray['nbfield'] = 0;
$imaxinloop = ($limit ? min($num, $limit) : $num);
while ($i < $imaxinloop) {
	$obj = $db->fetch_object($resql);
	if (empty($obj)) {
		break;
	}

	// Show here line of result
	print '<tr data-rowid="'.$obj->rowid.'" class="oddeven">';
	
	// Action column
	if (!empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
		print '<td class="nowrap center">';
		if ($massactionbutton || $massaction) {
			$selected = 0;
			if (in_array($obj->rowid, $arrayofselected)) {
				$selected = 1;
			}
			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		print '</td>';
	}

	// ID
	if (!empty($arrayfields['t.rowid']['checked'])) {
		print '<td class="nowrap">';
		print $obj->rowid;
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// Clock In
	if (!empty($arrayfields['t.clock_in_time']['checked'])) {
		print '<td class="center">';
		print $obj->clock_in_time ? dol_print_date($db->jdate($obj->clock_in_time), 'dayhour') : '';
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// Clock Out
	if (!empty($arrayfields['t.clock_out_time']['checked'])) {
		print '<td class="center">';
		print $obj->clock_out_time ? dol_print_date($db->jdate($obj->clock_out_time), 'dayhour') : '<span class="opacitymedium">'.$langs->trans("InProgress").'</span>';
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// User Login
	if (!empty($arrayfields['u.login']['checked'])) {
		print '<td class="nowrap">';
		print $obj->login;
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// LastName
	if (!empty($arrayfields['u.lastname']['checked'])) {
		print '<td class="nowrap">';
		print $obj->lastname;
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// FirstName
	if (!empty($arrayfields['u.firstname']['checked'])) {
		print '<td class="nowrap">';
		print $obj->firstname;
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// Duration
	if (!empty($arrayfields['t.work_duration']['checked'])) {
		print '<td class="right">';
		if ($obj->work_duration && $obj->work_duration > 0) {
			$hours = floor($obj->work_duration / 3600);
			$minutes = floor(($obj->work_duration % 3600) / 60);
			print sprintf('%dh %02dm', $hours, $minutes);
		} else {
			print '<span class="opacitymedium">-</span>';
		}
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// Location
	if (!empty($arrayfields['t.location_in']['checked'])) {
		print '<td class="nowrap">';
		print $obj->location_in ? $obj->location_in : '<span class="opacitymedium">-</span>';
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// Status
	if (!empty($arrayfields['t.status']['checked'])) {
		print '<td class="center">';
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
		$status_label = isset($status_labels[$obj->status]) ? $langs->trans($status_labels[$obj->status]) : $obj->status;
		$status_color = isset($status_colors[$obj->status]) ? $status_colors[$obj->status] : 'status0';
		print dolGetStatus($status_label, '', '', $status_color, 1);
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// Validation Status
	if (!empty($arrayfields['t.validation_status']['checked'])) {
		print '<td class="center">';
		$validation_labels = array(
			0 => 'Pending',
			1 => 'Approved',
			2 => 'Rejected', 
			3 => 'Partial'
		);
		$validation_colors = array(
			0 => 'status3',
			1 => 'status4',
			2 => 'status8',
			3 => 'status7'
		);
		$validation_label = isset($validation_labels[$obj->validation_status]) ? $langs->trans($validation_labels[$obj->validation_status]) : $obj->validation_status;
		$validation_color = isset($validation_colors[$obj->validation_status]) ? $validation_colors[$obj->validation_status] : 'status0';
		print dolGetStatus($validation_label, '', '', $validation_color, 1);
		print '</td>';
		if (!$i) $totalarray['nbfield']++;
	}

	// Action column
	if (empty($conf->global->MAIN_CHECKBOX_LEFT_COLUMN)) {
		print '<td class="nowrap center">';
		if ($massactionbutton || $massaction) {
			$selected = 0;
			if (in_array($obj->rowid, $arrayofselected)) {
				$selected = 1;
			}
			print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected ? ' checked="checked"' : '').'>';
		}
		print '</td>';
	}
	if (!$i) {
		$totalarray['nbfield']++;
	}

	print '</tr>'."\n";

	$i++;
}

// Show total line
include DOL_DOCUMENT_ROOT.'/core/tpl/list_print_total.tpl.php';

// If no record found
if ($num == 0) {
	$colspan = 1;
	foreach ($arrayfields as $key => $val) {
		if (!empty($val['checked'])) {
			$colspan++;
		}
	}
	print '<tr><td colspan="'.$colspan.'"><span class="opacitymedium">'.$langs->trans("NoRecordFound").'</span></td></tr>';
}

$db->free($resql);

$parameters = array('arrayfields'=>$arrayfields, 'sql'=>$sql);
$reshook = $hookmanager->executeHooks('printFieldListFooter', $parameters, $object, $action);
print $hookmanager->resPrint;

print '</table>'."\n";
print '</div>'."\n";

print '</form>'."\n";

// End of page
llxFooter();
$db->close();