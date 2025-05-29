

/**
 * Navigate to any custom application
 * @param {string} appName - Name of the application
 */
function goToCustomApp(appName) {
    // Fermer le menu latéral
    var rightMenu = document.getElementById('rightmenu');
    if (rightMenu) {
        rightMenu.close();
    }
    
    // Rediriger vers l'application custom
    var baseUrl = window.appMobTimeTouch && window.appMobTimeTouch.DOL_URL_ROOT ? window.appMobTimeTouch.DOL_URL_ROOT : '';
    window.location.href = baseUrl + '/custom/' + appName + '/';
}

/**
 * Generic function to close menu and navigate
 * @param {string} url - URL to navigate to
 */
function navigateAndCloseMenu(url) {
    // Fermer le menu latéral
    var rightMenu = document.getElementById('rightmenu');
    if (rightMenu) {
        rightMenu.close();
    }
    
    // Navigation
    if (url.startsWith('http')) {
        window.location.href = url;
    } else {
        var baseUrl = window.appMobTimeTouch && window.appMobTimeTouch.DOL_URL_ROOT ? window.appMobTimeTouch.DOL_URL_ROOT : '';
        window.location.href = baseUrl + '/' + url.replace(/^\/+/, '');
    }
}