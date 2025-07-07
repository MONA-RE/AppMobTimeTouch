<?php
/* Copyright (C) 2025 SuperAdmin
 * Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 */

/**
 *	\file       appmobtimetouch/index.php
 *	\ingroup    appmobtimetouch
 *	\brief      Home page of time tracking area with device detection
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/class/timeclockrecord.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
dol_include_once('/appmobtimetouch/lib/appmobtimetouch.lib.php');
dol_include_once('/appmobtimetouch/core/modules/modAppMobTimeTouch.class.php');

// Security check
restrictedArea($user, 'appmobtimetouch');

// Load translation files required by the page
$langs->loadLangs(array('appmobtimetouch@appmobtimetouch', 'other'));

// Filter to show only result of one user (if user is not admin)
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
    $socid = $user->socid;
}

$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

// Maximum elements of the tables
$maxDraftCount = empty($conf->global->MAIN_MAXLIST_OVERLOAD) ? $max : $conf->global->MAIN_MAXLIST_OVERLOAD;
$maxOpenCount = empty($conf->global->MAIN_MAXLIST_OVERLOAD) ? $max : $conf->global->MAIN_MAXLIST_OVERLOAD;

// Device detection - redirect mobile users to mobile interface
if (function_exists('isMobileDevice') && isMobileDevice()) {
    // Force mobile users to use mobile interface
    header('Location: home.php');
    exit;
}

// Check for explicit mobile/desktop parameter
if (GETPOST('mobile', 'int') == 1) {
    header('Location: home.php');
    exit;
}

/*
 * View - Desktop Dashboard
 */

llxHeader("", $langs->trans("TimeClockManagement"), "EN:TimeClockManagement|FR:Gestion_du_temps");

print load_fiche_titre($langs->trans("TimeClockManagement"), '', 'clock');

print '<div class="fichecenter">';

print '<div class="fichethirdleft">';

// Get number of time records pie chart
// $tmp = getTimeRecordsPieChart('employees');
// if ($tmp) {
//     print $tmp;
//     print '<br>';
// }

// Get draft time records table  
// $tmp = getTimeRecordsDraftTable($maxDraftCount, $socid);
// if ($tmp) {
//     print $tmp;
//     print '<br>';
// }

// For now, show simple statistics
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder nohover centpercent">';
print '<tr class="liste_titre"><th colspan="2">'.$langs->trans("Statistics").'</th></tr>';

// Count total records
$sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."timeclock_records";
$resql = $db->query($sql);
if ($resql) {
    $obj = $db->fetch_object($resql);
    print '<tr><td>'.$langs->trans("TotalTimeRecords").'</td><td class="right">'.$obj->nb.'</td></tr>';
    $db->free($resql);
}

// Count today's records
$sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."timeclock_records WHERE DATE(clock_in) = CURDATE()";
$resql = $db->query($sql);
if ($resql) {
    $obj = $db->fetch_object($resql);
    print '<tr><td>'.$langs->trans("TodayTimeRecords").'</td><td class="right">'.$obj->nb.'</td></tr>';
    $db->free($resql);
}

print '</table>';
print '</div>';

print '</div>';

print '<div class="fichetwothirdright">';

// Get latest time records table
// $tmp = getTimeRecordsLatestEditTable($max, $socid);
// if ($tmp) {
//     print $tmp;
//     print '<br>';
// }

// Get pending validation records
// $tmp = getTimeRecordsPendingValidationTable($maxOpenCount, $socid);
// if ($tmp) {
//     print $tmp;
//     print '<br>';
// }

// Show quick access links
print '<div class="div-table-responsive-no-min">';
print '<table class="noborder nohover centpercent">';
print '<tr class="liste_titre"><th>'.$langs->trans("QuickAccess").'</th></tr>';

print '<tr><td>';
print '<a href="list.php" class="butAction">'.$langs->trans("ViewAllRecords").'</a>';
print '</td></tr>';

print '<tr><td>';
print '<a href="home.php" class="butAction">'.$langs->trans("MobileInterface").'</a>';
print '</td></tr>';

if (!empty($user->rights->appmobtimetouch->timeclock->validate)) {
    print '<tr><td>';
    print '<a href="validation.php" class="butAction">'.$langs->trans("ValidationInterface").'</a>';
    print '</td></tr>';
}

print '</table>';
print '</div>';

print '</div>';

print '</div>';

// End of page
llxFooter();
$db->close();