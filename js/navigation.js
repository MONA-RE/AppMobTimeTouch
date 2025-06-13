/**
 * Navigate to any custom application with robust URL detection
 * @param {string} appName - Name of the application
 */
function goToCustomApp(appName) {
    console.log('=== DEBUG goToCustomApp ===');
    console.log('appName parameter:', appName);
    
    // Fermer le menu latéral
    var rightMenu = document.getElementById('rightmenu');
    if (rightMenu) {
        rightMenu.close();
        console.log('Right menu closed');
    } else {
        console.log('Right menu not found');
    }
    
    // Détection robuste de l'URL de base
    var baseUrl = detectBaseUrl();
    console.log('Base URL detected:', baseUrl);
    
    // Construction de l'URL finale
    var finalUrl = baseUrl + '/custom/' + appName + '/';
    console.log('Final URL constructed:', finalUrl);
    
    // Validation et navigation
    try {
        var urlObj = new URL(finalUrl);
        console.log('URL validation successful:', urlObj.href);
        
        // Navigation
        console.log('Navigating to:', finalUrl);
        window.location.href = finalUrl;
        
    } catch (error) {
        console.error('ERROR: Invalid URL constructed:', finalUrl);
        console.error('Error details:', error);
        
        // Fallback d'urgence avec une URL relative
        var emergencyUrl = '../' + appName + '/';
        console.log('Using emergency relative URL:', emergencyUrl);
        window.location.href = emergencyUrl;
    }
}

/**
 * Robust base URL detection function
 * @returns {string} The detected base URL
 */
function detectBaseUrl() {
    var baseUrl = '';
    
    console.log('=== detectBaseUrl DEBUG ===');
    console.log('window.location.href:', window.location.href);
    console.log('window.location.pathname:', window.location.pathname);
    console.log('window.location.protocol:', window.location.protocol);
    console.log('window.location.host:', window.location.host);
    
    // Méthode 1: Depuis les variables globales spécifiques à chaque app
    if (typeof window.appMobTimeTouch !== 'undefined' && window.appMobTimeTouch.DOL_URL_ROOT) {
        baseUrl = window.appMobTimeTouch.DOL_URL_ROOT;
        console.log('Using baseUrl from appMobTimeTouch:', baseUrl);
        return baseUrl;
    }
    
    if (typeof window.appMobSalesOrders !== 'undefined' && window.appMobSalesOrders.DOL_URL_ROOT) {
        baseUrl = window.appMobSalesOrders.DOL_URL_ROOT;
        console.log('Using baseUrl from appMobSalesOrders:', baseUrl);
        return baseUrl;
    }
    
    // Méthode 2: Détection automatique depuis l'URL actuelle
    var currentUrl = window.location.href;
    var pathArray = window.location.pathname.split('/');
    console.log('Current URL:', currentUrl);
    console.log('Path array:', pathArray);
    
    // Chercher 'custom' dans le chemin pour construire la base
    var customIndex = pathArray.indexOf('custom');
    console.log('customIndex:', customIndex);
    if (customIndex > 0) {
        baseUrl = window.location.protocol + '//' + window.location.host + pathArray.slice(0, customIndex).join('/');
        console.log('Base URL detected from custom path:', baseUrl);
        return baseUrl;
    }
    
    // Chercher 'htdocs' dans le chemin
    var htdocsIndex = pathArray.indexOf('htdocs');
    console.log('htdocsIndex:', htdocsIndex);
    if (htdocsIndex >= 0) {
        baseUrl = window.location.protocol + '//' + window.location.host + pathArray.slice(0, htdocsIndex + 1).join('/');
        console.log('Base URL detected from htdocs path:', baseUrl);
        return baseUrl;
    }
    
    // Méthode 3: Analyse des patterns courants Dolibarr
    // Pattern: /dolibarr/htdocs/ ou /htdocs/
    var pathname = window.location.pathname;
    console.log('pathname for pattern matching:', pathname);
    
    // Cas 1: /quelquechose/htdocs/custom/app/
    var htdocsMatch = pathname.match(/^(.*\/htdocs)\//);
    console.log('htdocsMatch:', htdocsMatch);
    if (htdocsMatch) {
        baseUrl = window.location.protocol + '//' + window.location.host + htdocsMatch[1];
        console.log('Base URL detected from htdocs pattern:', baseUrl);
        return baseUrl;
    }
    
    // Cas 2: /custom/app/ directement sous la racine
    var customMatch = pathname.match(/^(.*)\/custom\//);
    console.log('customMatch:', customMatch);
    if (customMatch) {
        baseUrl = window.location.protocol + '//' + window.location.host + customMatch[1];
        console.log('Base URL detected from custom pattern:', baseUrl);
        return baseUrl;
    }
    
    // Méthode 4: Fallback - racine du serveur
    baseUrl = window.location.protocol + '//' + window.location.host;
    console.log('Using fallback base URL (server root):', baseUrl);
    console.log('=== END detectBaseUrl DEBUG ===');
    
    return baseUrl;
}

/**
 * Generic function to close menu and navigate
 * @param {string} url - URL to navigate to
 */
function navigateAndCloseMenu(url) {
    console.log('=== DEBUG navigateAndCloseMenu ===');
    console.log('url parameter:', url);
    
    // Fermer le menu latéral
    var rightMenu = document.getElementById('rightmenu');
    if (rightMenu) {
        rightMenu.close();
        console.log('Right menu closed');
    } else {
        console.log('Right menu not found');
    }
    
    // Navigation
    if (url.startsWith('http')) {
        console.log('Absolute URL detected, navigating directly to:', url);
        window.location.href = url;
    } else {
        var baseUrl = detectBaseUrl();
        var finalUrl = baseUrl + '/' + url.replace(/^\/+/, '');
        console.log('Relative URL converted to:', finalUrl);
        window.location.href = finalUrl;
    }
}

/**
 * Debug function to display current configuration
 */
function debugAppConfiguration() {
    console.log('=== APP CONFIGURATION DEBUG ===');
    console.log('Current URL:', window.location.href);
    console.log('Pathname:', window.location.pathname);
    console.log('Detected base URL:', detectBaseUrl());
    
    // Vérifier les variables globales disponibles
    if (typeof window.appMobTimeTouch !== 'undefined') {
        console.log('AppMobTimeTouch globals:', window.appMobTimeTouch);
    }
    if (typeof window.appMobSalesOrders !== 'undefined') {
        console.log('AppMobSalesOrders globals:', window.appMobSalesOrders);
    }
    
    console.log('Location protocol:', window.location.protocol);
    console.log('Location host:', window.location.host);
    console.log('Location pathname:', window.location.pathname);
    console.log('Location search:', window.location.search);
    console.log('================================');
}

/**
 * Test function for navigation (call from console)
 */
function testNavigation() {
    console.log('=== TESTING NAVIGATION ===');
    debugAppConfiguration();
    
    // Test avec différentes applications
    console.log('Testing navigation to appmobtimetouch...');
    console.log('Would navigate to:', detectBaseUrl() + '/custom/appmobtimetouch/');
    
    console.log('Testing navigation to appmobsalesorders...');
    console.log('Would navigate to:', detectBaseUrl() + '/custom/appmobsalesorders/');
}

/**
 * Navigation vers le dashboard de validation manager (MVP 3.1)
 * Fonction appelée depuis tabbar.tpl pour les managers
 */
function loadManagement() {
    console.log('=== DEBUG loadManagement (MVP 3.1) ===');
    
    try {
        // Construction de l'URL vers validation.php
        var currentUrl = window.location.href;
        var currentPath = window.location.pathname;
        
        // Détecter si on est dans le module appmobtimetouch
        if (currentPath.includes('/appmobtimetouch/')) {
            // URL relative depuis le module actuel
            var validationUrl = './validation.php';
        } else {
            // URL absolue depuis detectBaseUrl
            var baseUrl = detectBaseUrl();
            var validationUrl = baseUrl + '/custom/appmobtimetouch/validation.php';
        }
        
        console.log('Current URL:', currentUrl);
        console.log('Validation URL constructed:', validationUrl);
        
        // Message de chargement
        if (typeof ons !== 'undefined') {
            ons.notification.toast('Loading Validation Manager...', {timeout: 1500});
        }
        
        // Navigation vers le dashboard validation
        setTimeout(function() {
            console.log('Navigating to validation dashboard...');
            window.location.href = validationUrl;
        }, 300);
        
    } catch (error) {
        console.error('ERROR in loadManagement:', error);
        
        // Fallback d'urgence
        var fallbackUrl = './validation.php';
        console.log('Using fallback URL:', fallbackUrl);
        
        if (typeof ons !== 'undefined') {
            ons.notification.alert('Error loading validation manager. Trying fallback...');
        }
        
        setTimeout(function() {
            window.location.href = fallbackUrl;
        }, 1000);
    }
}

/**
 * Navigation vers les rapports mensuels
 * Fonction appelée depuis rightmenu.tpl
 */
function loadReports() {
    console.log('=== DEBUG loadReports ===');
    
    try {
        // Construction de l'URL vers reports.php
        var currentUrl = window.location.href;
        var currentPath = window.location.pathname;
        
        // Détecter si on est dans le module appmobtimetouch
        if (currentPath.includes('/appmobtimetouch/')) {
            // URL relative depuis le module actuel
            var reportsUrl = './reports.php';
        } else {
            // URL absolue depuis detectBaseUrl
            var baseUrl = detectBaseUrl();
            var reportsUrl = baseUrl + '/custom/appmobtimetouch/reports.php';
        }
        
        console.log('Current URL:', currentUrl);
        console.log('Reports URL constructed:', reportsUrl);
        
        // Message de chargement
        if (typeof ons !== 'undefined') {
            ons.notification.toast('Chargement des rapports...', {timeout: 1500});
        }
        
        // Navigation vers la page rapports
        setTimeout(function() {
            console.log('Navigating to reports page...');
            window.location.href = reportsUrl;
        }, 300);
        
    } catch (error) {
        console.error('ERROR in loadReports:', error);
        
        // Fallback d'urgence
        var fallbackUrl = './reports.php';
        console.log('Using fallback URL:', fallbackUrl);
        
        if (typeof ons !== 'undefined') {
            ons.notification.alert('Error loading reports. Trying fallback...');
        }
        
        setTimeout(function() {
            window.location.href = fallbackUrl;
        }, 1000);
    }
}

/**
 * Navigation vers les enregistrements de l'utilisateur
 * Placeholder pour futures implémentations
 */
function loadMyRecords() {
    console.log('=== DEBUG loadMyRecords ===');
    
    if (typeof ons !== 'undefined') {
        ons.notification.alert('My Records feature coming soon!');
    } else {
        alert('My Records feature coming soon!');
    }
}

/**
 * Navigation vers les résumés
 * Placeholder pour futures implémentations
 */
function loadSummary() {
    console.log('=== DEBUG loadSummary ===');
    
    if (typeof ons !== 'undefined') {
        ons.notification.alert('Summary feature coming soon!');
    } else {
        alert('Summary feature coming soon!');
    }
}

/**
 * Navigation vers les paramètres
 * Placeholder pour futures implémentations
 */
function loadSettings() {
    console.log('=== DEBUG loadSettings ===');
    
    if (typeof ons !== 'undefined') {
        ons.notification.alert('Settings feature coming soon!');
    } else {
        alert('Settings feature coming soon!');
    }
}

/**
 * Navigate to specific pages within the application
 * @param {string} pageName - Name of the page to navigate to
 */
function gotoPage(pageName) {
    console.log('=== DEBUG gotoPage ===');
    console.log('pageName parameter:', pageName);
    console.log('Going to page:', pageName);
    console.log('OnsenUI available:', typeof ons !== 'undefined');
    console.log('Current location:', window.location.href);
    
    // Show loading notification
    if (typeof ons !== 'undefined' && ons.notification) {
        ons.notification.toast('Chargement de ' + pageName + '...', {timeout: 1500});
        console.log('Toast notification shown');
    } else {
        console.log('OnsenUI not available, skipping toast');
    }
    
    // Fermer le menu latéral si ouvert
    var splitter = document.getElementById('mySplitter');
    if (splitter && splitter.right) {
        splitter.right.close();
        console.log('Side menu closed');
    }
    
    // Détection de l'URL de base
    var baseUrl = detectBaseUrl();
    console.log('Base URL detected:', baseUrl);
    
    var finalUrl;
    
    // Mapping des pages
    switch(pageName) {
        case 'reports':
            finalUrl = baseUrl + '/custom/appmobtimetouch/reports.php';
            console.log('Reports URL mapping completed:', finalUrl);
            break;
        case 'myTimeclockRecords':
            finalUrl = baseUrl + '/custom/appmobtimetouch/home.php?action=myRecords';
            break;
        case 'weeklySummaries':
            finalUrl = baseUrl + '/custom/appmobtimetouch/home.php?action=summaries';
            break;
        case 'teamManagement':
            finalUrl = baseUrl + '/custom/appmobtimetouch/home.php?action=teamManagement';
            break;
        case 'preferences':
            finalUrl = baseUrl + '/custom/appmobtimetouch/home.php?action=preferences';
            break;
        case 'moncompteApplication':
            finalUrl = baseUrl + '/user/card.php?id=' + (window.userId || '');
            break;
        case 'aproposApplication':
            finalUrl = baseUrl + '/custom/appmobtimetouch/home.php?action=about';
            break;
        default:
            console.error('Unknown page:', pageName);
            if (typeof ons !== 'undefined' && ons.notification) {
                ons.notification.toast('Page non trouvée: ' + pageName, {timeout: 2000});
            }
            return;
    }
    
    console.log('Final URL constructed:', finalUrl);
    
    // Validation et navigation
    try {
        console.log('About to navigate to:', finalUrl);
        
        // Vérification de l'URL
        var testUrl = new URL(finalUrl);
        console.log('URL validation successful:', testUrl.href);
        
        // Navigation avec délai pour permettre au toast de s'afficher
        setTimeout(function() {
            console.log('Executing navigation to:', finalUrl);
            try {
                window.location.href = finalUrl;
                console.log('Navigation call completed');
            } catch (navError) {
                console.error('Navigation execution failed:', navError);
                if (typeof ons !== 'undefined' && ons.notification) {
                    ons.notification.alert('Navigation execution failed: ' + navError.message);
                }
            }
        }, 200);
        
    } catch (error) {
        console.error('ERROR: Navigation failed:', error);
        if (typeof ons !== 'undefined' && ons.notification) {
            ons.notification.alert('Erreur de navigation vers: ' + pageName + '\nErreur: ' + error.message);
        } else {
            alert('Erreur de navigation vers: ' + pageName + '\nErreur: ' + error.message);
        }
    }
}

/**
 * Initialize navigation system
 */
function initNavigation() {
    console.log('Navigation system initialized');
    
    // Exposer les fonctions de navigation globalement
    window.loadManagement = loadManagement;
    window.loadReports = loadReports;
    window.loadMyRecords = loadMyRecords;
    window.loadSummary = loadSummary;
    window.loadSettings = loadSettings;
    window.gotoPage = gotoPage;
    
    console.log('Navigation functions exposed: loadManagement(), loadMyRecords(), loadSummary(), loadSettings(), gotoPage()');
    
    // Exposer les fonctions de debug en mode développement
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        window.debugAppConfiguration = debugAppConfiguration;
        window.testNavigation = testNavigation;
        console.log('Debug functions exposed: debugAppConfiguration(), testNavigation()');
    }
}

// Auto-initialisation
document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
});