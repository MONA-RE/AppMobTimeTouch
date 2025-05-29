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
    if (customIndex > 0) {
        baseUrl = window.location.protocol + '//' + window.location.host + pathArray.slice(0, customIndex).join('/');
        console.log('Base URL detected from custom path:', baseUrl);
        return baseUrl;
    }
    
    // Chercher 'htdocs' dans le chemin
    var htdocsIndex = pathArray.indexOf('htdocs');
    if (htdocsIndex >= 0) {
        baseUrl = window.location.protocol + '//' + window.location.host + pathArray.slice(0, htdocsIndex + 1).join('/');
        console.log('Base URL detected from htdocs path:', baseUrl);
        return baseUrl;
    }
    
    // Méthode 3: Analyse des patterns courants Dolibarr
    // Pattern: /dolibarr/htdocs/ ou /htdocs/
    var pathname = window.location.pathname;
    
    // Cas 1: /quelquechose/htdocs/custom/app/
    var htdocsMatch = pathname.match(/^(.*\/htdocs)\//);
    if (htdocsMatch) {
        baseUrl = window.location.protocol + '//' + window.location.host + htdocsMatch[1];
        console.log('Base URL detected from htdocs pattern:', baseUrl);
        return baseUrl;
    }
    
    // Cas 2: /custom/app/ directement sous la racine
    var customMatch = pathname.match(/^(.*)\/custom\//);
    if (customMatch) {
        baseUrl = window.location.protocol + '//' + window.location.host + customMatch[1];
        console.log('Base URL detected from custom pattern:', baseUrl);
        return baseUrl;
    }
    
    // Méthode 4: Fallback - racine du serveur
    baseUrl = window.location.protocol + '//' + window.location.host;
    console.log('Using fallback base URL (server root):', baseUrl);
    
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
 * Initialize navigation system
 */
function initNavigation() {
    console.log('Navigation system initialized');
    
    // Exposer les fonctions de debug en mode développement
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        window.debugAppConfiguration = debugAppConfiguration;
        window.testNavigation = testNavigation;
        console.log('Debug functions exposed: debugAppConfiguration(), testNavigation()');
    }
}