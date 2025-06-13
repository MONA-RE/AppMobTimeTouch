<?php
/* Copyright (C) 2024 AppMobTimeTouch
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
 * \file    css/appmobtimetouch.css.php
 * \ingroup appmobtimetouch
 * \brief   CSS file for module AppMobTimeTouch.
 */

// Chargement de l'environnement Dolibarr
if (!defined('NOREQUIREUSER')) {
    define('NOREQUIREUSER', '1');
}
if (!defined('NOREQUIREDB')) {
    define('NOREQUIREDB', '1');
}
if (!defined('NOREQUIRESOC')) {
    define('NOREQUIRESOC', '1');
}
if (!defined('NOREQUIRETRAN')) {
    define('NOREQUIRETRAN', '1');
}
if (!defined('NOCSRFCHECK')) {
    define('NOCSRFCHECK', 1);
}
if (!defined('NOTOKENRENEWAL')) {
    define('NOTOKENRENEWAL', 1);
}
if (!defined('NOLOGIN')) {
    define('NOLOGIN', 1);
}
if (!defined('NOREQUIREMENU')) {
    define('NOREQUIREMENU', 1);
}
if (!defined('NOREQUIREHTML')) {
    define('NOREQUIREHTML', 1);
}
if (!defined('NOREQUIREAJAX')) {
    define('NOREQUIREAJAX', '1');
}

session_cache_limiter('public');

// Chargement de l'environnement Dolibarr
$res = 0;
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res && file_exists("../../../../main.inc.php")) $res = include '../../../../main.inc.php';
if (!$res) die("Include of main fails");

// Define css type
header('Content-type: text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) {
    header('Cache-Control: max-age=3600, public, must-revalidate');
} else {
    header('Cache-Control: no-cache');
}

?>

/* CSS for AppMobTimeTouch module */

/* Mobile specific styles */
.appmobtimetouch-mobile-container {
    padding: 10px;
    margin: 0;
}

/* Validation styles */
.validation-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}

.validation-priority-indicator {
    width: 6px;
    height: 100%;
    border-radius: 3px;
    margin-right: 10px;
}

.validation-notification-badge {
    background-color: #ffc107;
    color: #856404;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 500;
}

.validation-loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Reports styles */
.reports-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}

.reports-loading {
    opacity: 0.6;
    pointer-events: none;
}

.user-row:nth-child(even) {
    background-color: #f8f9fa;
}

.hours-display {
    font-weight: bold;
    color: #2196f3;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .appmobtimetouch-mobile-container {
        padding: 5px;
    }
    
    .validation-card,
    .reports-card {
        margin-bottom: 10px;
        border-radius: 6px;
    }
}

/* OnsenUI overrides for consistency */
.rightMenuList ons-list-item {
    padding: 10px 15px;
}

.rightMenuList ons-list-item:active {
    background-color: #f5f5f5;
}

/* Time display utilities */
.time-duration {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

.time-status-pending {
    color: #6c757d;
}

.time-status-approved {
    color: #28a745;
}

.time-status-rejected {
    color: #dc3545;
}

.time-status-partial {
    color: #ffc107;
}

/* Loading states */
.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}