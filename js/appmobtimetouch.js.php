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
 * \file    js/appmobtimetouch.js.php
 * \ingroup appmobtimetouch
 * \brief   JavaScript file for module AppMobTimeTouch.
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
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res && file_exists("../../../../main.inc.php")) $res = include '../../../../main.inc.php';
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res) die("Include of main fails");

// Define js type
header('Content-type: application/javascript');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) {
    header('Cache-Control: max-age=3600, public, must-revalidate');
} else {
    header('Cache-Control: no-cache');
}

?>

/* JavaScript for AppMobTimeTouch module */

// Module initialization
(function() {
    'use strict';
    
    console.log('AppMobTimeTouch module JavaScript loaded');
    
    // Global module namespace
    window.AppMobTimeTouch = window.AppMobTimeTouch || {};
    
    // Module version
    window.AppMobTimeTouch.version = '<?php echo DOL_VERSION; ?>';
    
    // Utility functions
    window.AppMobTimeTouch.utils = {
        
        /**
         * Format duration in seconds to readable format
         * @param {number} seconds
         * @returns {string}
         */
        formatDuration: function(seconds) {
            if (!seconds || seconds <= 0) return '0h 00m';
            
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            
            return hours + 'h ' + (minutes < 10 ? '0' : '') + minutes + 'm';
        },
        
        /**
         * Show notification toast
         * @param {string} message
         * @param {number} timeout
         */
        showToast: function(message, timeout) {
            timeout = timeout || 2000;
            if (typeof ons !== 'undefined' && ons.notification) {
                ons.notification.toast(message, {timeout: timeout});
            } else {
                console.log('Toast: ' + message);
            }
        },
        
        /**
         * Show confirmation dialog
         * @param {string} message
         * @param {function} callback
         */
        showConfirm: function(message, callback) {
            if (typeof ons !== 'undefined' && ons.notification) {
                ons.notification.confirm({
                    message: message,
                    callback: callback
                });
            } else {
                const result = confirm(message);
                if (callback) callback(result);
            }
        },
        
        /**
         * Show alert dialog
         * @param {string} message
         */
        showAlert: function(message) {
            if (typeof ons !== 'undefined' && ons.notification) {
                ons.notification.alert(message);
            } else {
                alert(message);
            }
        }
    };
    
    // Initialize module when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('AppMobTimeTouch DOM ready');
        
        // Add any initialization code here
        if (typeof ons !== 'undefined') {
            ons.ready(function() {
                console.log('AppMobTimeTouch OnsenUI ready');
            });
        }
    });
    
})();

// Backward compatibility functions
function formatDuration(seconds) {
    return window.AppMobTimeTouch.utils.formatDuration(seconds);
}

function showToast(message, timeout) {
    return window.AppMobTimeTouch.utils.showToast(message, timeout);
}

function showConfirm(message, callback) {
    return window.AppMobTimeTouch.utils.showConfirm(message, callback);
}

function showAlert(message) {
    return window.AppMobTimeTouch.utils.showAlert(message);
}