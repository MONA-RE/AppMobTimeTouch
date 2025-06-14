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
 *	\brief      Entry point for AppMobTimeTouch module with device detection
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
    $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
    $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
    $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
    $res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
    $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
    $res = @include "../../../main.inc.php";
}
if (!$res) {
    die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/appmobtimetouch/lib/appmobtimetouch.lib.php');
dol_include_once('/appmobtimetouch/core/modules/modAppMobTimeTouch.class.php');

// Load translation files required by the page
$langs->loadLangs(array("appmobtimetouch@appmobtimetouch"));

// Vérifier si la fonction isModEnabled existe (compatibilité)
if (!function_exists('isModEnabled')) {
    function isModEnabled($module)
    {
        global $conf;
        return !empty($conf->$module->enabled);
    }
}

// Security check - Vérifier que le module est activé
if (!isModEnabled('appmobtimetouch')) {
    accessforbidden('Module AppMobTimeTouch not enabled');
}

// CORRECTION : Initialisation correcte des droits utilisateur
// Vérifier que l'objet user existe et est valide
if (!isset($user) || !is_object($user) || $user->id <= 0) {
    accessforbidden('User not authenticated');
}

// Initialiser la structure des droits si elle n'existe pas
if (!isset($user->rights)) {
    $user->rights = new stdClass();
}

if (!isset($user->rights->appmobtimetouch)) {
    $user->rights->appmobtimetouch = new stdClass();
}

if (!isset($user->rights->appmobtimetouch->timeclock)) {
    $user->rights->appmobtimetouch->timeclock = new stdClass();
    
    // Initialiser tous les droits à false par défaut
    $user->rights->appmobtimetouch->timeclock->read = false;
    $user->rights->appmobtimetouch->timeclock->write = false;
    $user->rights->appmobtimetouch->timeclock->delete = false;
    $user->rights->appmobtimetouch->timeclock->readall = false;
    $user->rights->appmobtimetouch->timeclock->validate = false;
    $user->rights->appmobtimetouch->timeclock->export = false;
    $user->rights->appmobtimetouch->timeclock->config = false;
}

// Vérifier les droits réels de l'utilisateur depuis la base de données
if (is_object($user) && $user->id > 0) {
    // Utiliser la fonction standard de Dolibarr pour vérifier les droits
    $sql = "SELECT COUNT(*) as nb FROM ".MAIN_DB_PREFIX."user_rights as ur";
    $sql .= " WHERE ur.fk_user = ".((int) $user->id);
    $sql .= " AND ur.module = 'appmobtimetouch'";
    
    $resql = $db->query($sql);
    if ($resql) {
        // Si l'utilisateur a des droits spécifiques, les charger
        $obj = $db->fetch_object($resql);
        if ($obj && $obj->nb > 0) {
            // Charger les droits spécifiques depuis la table des droits
            $sql_rights = "SELECT ur.id_rights FROM ".MAIN_DB_PREFIX."user_rights as ur";
            $sql_rights .= " WHERE ur.fk_user = ".((int) $user->id);
            $sql_rights .= " AND ur.module = 'appmobtimetouch'";
            
            $resql_rights = $db->query($sql_rights);
            if ($resql_rights) {
                while ($obj_right = $db->fetch_object($resql_rights)) {
                    $right_id = $obj_right->id_rights;
                    
                    // Mapper les IDs de droits aux propriétés
                    // Ces IDs sont définis dans modAppMobTimeTouch.class.php
                    switch ($right_id) {
                        case 13600701: // Read own records
                            $user->rights->appmobtimetouch->timeclock->read = true;
                            break;
                        case 13600702: // Write own records
                            $user->rights->appmobtimetouch->timeclock->write = true;
                            break;
                        case 13600703: // Delete own records
                            $user->rights->appmobtimetouch->timeclock->delete = true;
                            break;
                        case 13600704: // Read all records
                            $user->rights->appmobtimetouch->timeclock->readall = true;
                            break;
                        case 13600705: // Validate records
                            $user->rights->appmobtimetouch->timeclock->validate = true;
                            break;
                        case 13600706: // Export reports
                            $user->rights->appmobtimetouch->timeclock->export = true;
                            break;
                        case 13600707: // Configuration
                            $user->rights->appmobtimetouch->timeclock->config = true;
                            break;
                    }
                }
                $db->free($resql_rights);
            }
        } else {
            // Si pas de droits spécifiques, vérifier si l'utilisateur est admin
            if (!empty($user->admin)) {
                // Les admins ont tous les droits
                $user->rights->appmobtimetouch->timeclock->read = true;
                $user->rights->appmobtimetouch->timeclock->write = true;
                $user->rights->appmobtimetouch->timeclock->delete = true;
                $user->rights->appmobtimetouch->timeclock->readall = true;
                $user->rights->appmobtimetouch->timeclock->validate = true;
                $user->rights->appmobtimetouch->timeclock->export = true;
                $user->rights->appmobtimetouch->timeclock->config = true;
            }
        }
        $db->free($resql);
    }
}

// Security check final - Au minimum le droit de lecture est requis
if (!$user->rights->appmobtimetouch->timeclock->read) {
    accessforbidden($langs->trans('NotEnoughPermissions'));
}

// Get version number from module class for cache busting
$moduleInstance = new modAppMobTimeTouch($db);
$version = $moduleInstance->version;

// Note: isMobileDevice() function is already declared in lib/appmobtimetouch.lib.php

/**
 * Fonction de détection par taille d'écran (JavaScript sera requis côté client)
 * Cette méthode servira de fallback
 */
function isSmallScreen() {
    // Vérifier si l'utilisateur a explicitement demandé la version mobile
    if (isset($_GET['mobile']) && $_GET['mobile'] == '1') {
        return true;
    }
    
    // Vérifier si l'utilisateur a explicitement demandé la version desktop
    if (isset($_GET['desktop']) && $_GET['desktop'] == '1') {
        return false;
    }
    
    return false;
}

// Variables pour le template desktop
$is_mobile_device = isMobileDevice();
$force_mobile = isSmallScreen();

// Redirection automatique pour les appareils mobiles
if ($is_mobile_device || $force_mobile) {
    // Redirection vers l'application mobile
    header('Location: home.php');
    exit;
}

// Affichage pour les utilisateurs desktop
include 'tpl/index-desktop.tpl';
?>