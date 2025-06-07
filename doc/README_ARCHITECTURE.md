# Architecture AppMobTimeTouch - Vue d'ensemble

## Objectifs de la refactorisation

### Problèmes résolus
- **Fichiers trop volumineux**: home.php (476 lignes) et home.tpl (1464 lignes) décomposés
- **Responsabilités multiples**: Séparation claire des préoccupations
- **Maintenabilité difficile**: Code modulaire et testable
- **Couplage fort**: Interfaces découplées et services indépendants

### Principes architecturaux
- **Single Responsibility Principle**: Chaque classe a une responsabilité unique
- **Dependency Injection**: Services injectés plutôt qu'instanciés
- **Modularité**: Composants réutilisables et indépendants
- **Testabilité**: Code isolé et facilement testable

## Structure de l'architecture

```
appmobtimetouch/
├── Controllers/        # Orchestration des requêtes HTTP
├── Services/          # Logique métier centralisée  
├── Models/            # Modèles de données spécialisés
├── Utils/             # Fonctions utilitaires et constantes
├── Assets/            # Ressources statiques organisées
└── tpl/               # Templates modulaires
```

## Flux de traitement

### 1. Requête entrante
```
HTTP Request → BaseController → HomeController → Services → Models → Response
```

### 2. Actions spécialisées
```
Clock-in/out → TimeclockController → TimeclockService → LocationService → DB
```

### 3. Rendu de vue
```
Controller → Template Principal → Composants → Layout → HTML final
```

## Architecture JavaScript - TimeclockAPI

### Vue d'ensemble
Le module `js/timeclock-api.js` constitue la couche d'abstraction JavaScript pour l'interface mobile. Il orchestre les interactions entre l'interface utilisateur (OnsenUI) et l'API REST backend via une architecture modulaire et robuste.

### Intégration avec le backend

#### 1. Flux de données home.php → home.tpl → timeclock-api.js

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   home.php      │───▶│   home.tpl      │───▶│ timeclock-api.js│
│                 │    │                 │    │                 │
│ • Prépare $js_data│   │ • Expose appConfig│  │ • Utilise config│
│ • Token CSRF    │    │ • Charge module │    │ • Gère état    │
│ • Status user   │    │ • Init localStorage│  │ • API calls    │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

#### 2. Configuration data bridge

**home.php (lignes 419-432):**
```php
$js_data_raw = array(
    'is_clocked_in' => $is_clocked_in,
    'clock_in_time' => $clock_in_time,
    'require_location' => $require_location,
    'api_token' => newToken(),
    'user_id' => $user->id,
    'version' => '1.0'
);

$js_data = DataFormatter::prepareJavaScriptConfig($js_data_raw);
```

**home.tpl (ligne 560):**
```javascript
var appConfig = <?php echo json_encode($js_data); ?>;
```

**timeclock-api.js (lignes 866-871):**
```javascript
if (typeof window.appConfig !== 'undefined') {
    initOptions.apiToken = window.appConfig.api_token;
    initOptions.DEBUG = window.appConfig.debug || false;
}
```

### Architecture modulaire du TimeclockAPI

#### 1. Structure principale (Module Pattern)
```javascript
window.TimeclockAPI = (function() {
    'use strict';
    
    // Configuration centralisée
    const CONFIG = {
        API_BASE_URL: './api/timeclock.php',
        REFRESH_INTERVAL: 60000,
        LOCATION_TIMEOUT: 10000,
        CACHE_DURATION: 300000
    };
    
    // Gestion d'état réactive
    let state = {
        isOnline: navigator.onLine,
        currentStatus: null,
        updateTimer: null,
        requestQueue: [],
        cache: new Map()
    };
    
    // Modules spécialisés
    const utils = { /* Utilitaires */ };
    const http = { /* Requêtes HTTP */ };
    const cache = { /* Gestion cache */ };
    const geolocation = { /* GPS */ };
    const api = { /* API methods */ };
    const realtime = { /* Mises à jour temps réel */ };
    const network = { /* Monitoring réseau */ };
    const ui = { /* Interface utilisateur */ };
    
    return { /* API publique */ };
})();
```

#### 2. Modules spécialisés

**🔧 Utils Module (lignes 31-91)**
- Logging conditionnel (DEBUG mode)
- Formatage de durées
- Gestion token localStorage
- Validation de réponses

**🌐 HTTP Module (lignes 94-255)**
- Requêtes GET/POST avec gestion CSRF
- Gestion automatique des tokens
- File d'attente hors ligne
- Retry automatique

**📍 Geolocation Module (lignes 286-343)**
- Position GPS asynchrone
- Watch position continue
- Gestion d'erreurs détaillée
- Timeout configurables

**🔄 Cache Module (lignes 258-283)**
- Cache Map avec expiration
- Clés composites intelligentes
- Nettoyage automatique

**📡 API Module (lignes 354-546)**
- Endpoints RESTful (/status, /clockin, /clockout)
- Cache intelligent par endpoint
- Gestion d'erreurs spécialisée

**⏱️ Realtime Module (lignes 548-611)**
- Polling automatique (60s)
- Détection de changements d'état
- Mise à jour UI temps réel
- Gestion de la visibilité de page

**🔌 Network Module (lignes 614-680)**
- Détection online/offline
- Indicateurs visuels d'état
- Synchronisation différée

**🎨 UI Module (lignes 683-770)**
- Intégration OnsenUI
- Gestion des modals
- Feedback utilisateur (toasts, alertes)
- Coordination animations

### Gestion des états et synchronisation

#### 1. Token Management Flow
```
home.php generates CSRF token
       ↓
home.tpl stores in appConfig
       ↓
home.tpl saves to localStorage
       ↓
timeclock-api.js reads from localStorage
       ↓
HTTP requests include token
       ↓
API responses may refresh token
       ↓
Updated token saved to localStorage
```

#### 2. State Synchronization
```javascript
// État central dans timeclock-api.js
let state = {
    isOnline: navigator.onLine,      // Connectivité réseau
    currentStatus: null,             // État utilisateur courant
    updateTimer: null,               // Timer mise à jour temps réel
    requestQueue: [],                // File d'attente hors ligne
    cache: new Map()                 // Cache responses API
};
```

#### 3. Offline/Online Handling
```javascript
// Détection automatique réseau
window.addEventListener('online', handleOnline);
window.addEventListener('offline', handleOffline);

// File d'attente hors ligne
if (!state.isOnline) {
    http.queueRequest(method, endpoint, data);
}

// Synchronisation au retour en ligne
handleOnline() → http.processQueue()
```

### Intégration avec OnsenUI

#### 1. Modal Management
```javascript
// home.tpl définit les modals OnsenUI
<ons-modal var="clockInModal" id="clockInModal">
  <form id="clockInForm">
    <!-- Formulaire clock-in -->
  </form>
</ons-modal>

// timeclock-api.js gère l'interaction
window.submitClockIn = async function() {
    const form = document.getElementById('clockInForm');
    const formData = new FormData(form);
    
    await window.TimeclockAPI.clockIn(data);
    
    const modal = document.getElementById('clockInModal');
    if (modal && modal.hide) modal.hide();
};
```

#### 2. Notification System
```javascript
// Feedback utilisateur via OnsenUI
utils.showSuccess = function(message) {
    if (typeof ons !== 'undefined') {
        ons.notification.toast(message, {timeout: 2000});
    }
};

utils.showError = function(message) {
    if (typeof ons !== 'undefined') {
        ons.notification.alert(message);
    }
};
```

### Performance et optimisations

#### 1. Cache Strategy
- **Cache intelligent**: 5 minutes par défaut
- **Invalidation sélective**: Clear sur actions importantes
- **Clés composites**: endpoint + params pour granularité

#### 2. Network Optimization
- **Request batching**: Évite requêtes redondantes
- **Offline queue**: Synchronisation différée
- **Progressive enhancement**: Fallback sur formulaires HTML

#### 3. Memory Management
- **Event listeners cleanup**: Page visibility handling
- **Timer management**: Start/stop selon l'état
- **Cache expiration**: Nettoyage automatique

### Sécurité

#### 1. CSRF Protection
```javascript
// Token inclusion automatique
if (token) {
    formData.append('token', token);
}

// Mise à jour token depuis réponses API
if (responseData.csrf_token) {
    utils.updateToken(responseData.csrf_token);
}
```

#### 2. Input Validation
- Validation côté client avant envoi
- Sanitization des coordonnées GPS
- Gestion erreurs serveur

### Debug et monitoring

#### 1. Development Tools
```javascript
// Debug mode automatique
DEBUG: window.location.hostname === 'localhost'

// API de debug exposée
TimeclockAPI.debug = {
    getState: () => state,
    getConfig: () => CONFIG,
    clearCache: cache.clear,
    testLocation: geolocation.getCurrentPosition
};
```

#### 2. Logging Strategy
```javascript
utils.log('[TimeclockAPI] Message', data);  // Debug only
utils.error('[TimeclockAPI] Error', error); // Always logged
```

### Points d'extension

#### 1. Plugin Architecture
Le TimeclockAPI expose une API publique permettant l'extension :
```javascript
// API publique extensible
return {
    init, api, ui, geolocation, utils, cache, realtime,
    // Accès direct aux fonctions principales
    getStatus: api.getStatus,
    clockIn: ui.clockIn,
    clockOut: ui.clockOut
};
```

#### 2. Configuration Override
```javascript
// Configuration mergeable à l'initialisation
TimeclockAPI.init({
    API_BASE_URL: './custom/api.php',
    REFRESH_INTERVAL: 30000,
    DEBUG: true
});
```

## Services principaux

### TimeclockService
- Gestion des sessions de travail
- Calcul des durées et résumés
- Coordination avec les modèles de données

### LocationService  
- Gestion GPS et validation géographique
- Interface uniforme pour la localisation

### ValidationService
- Règles métier centralisées
- Validation des actions utilisateur

### ConfigService
- Configuration dynamique centralisée
- Cache des paramètres système

## Composants de présentation

### Layout système
- `mobile-layout.tpl`: Structure de base responsive
- Inclusion automatique des assets CSS/JS

### Composants réutilisables
- `status-card.tpl`: Affichage du statut actuel
- `summary-cards.tpl`: Résumés quotidien/hebdomadaire  
- `clockin-modal.tpl`: Interface de pointage d'entrée
- `clockout-modal.tpl`: Interface de pointage de sortie
- `records-list.tpl`: Liste des enregistrements

## Gestion des assets

### JavaScript modulaire
- `timeclock-app.js`: Application principale et orchestration
- `location-manager.js`: Gestion GPS spécialisée
- `ui-components.js`: Interactions et animations UI

### CSS organisé
- `timeclock-base.css`: Styles de base et typography
- `timeclock-components.css`: Styles des composants UI
- `timeclock-responsive.css`: Adaptation mobile/tablette

## Intégration avec Dolibarr

### Respect des conventions
- Utilisation des APIs Dolibarr existantes
- Conservation de la sécurité et des permissions
- Intégration transparente dans l'interface

### Rétrocompatibilité
- API publique inchangée
- Données existantes préservées
- Migration progressive possible

## Performances et optimisation

### Réduction de la charge
- Fichiers plus petits (200-500 lignes max)
- Chargement conditionnel des composants
- Cache intelligent des configurations

### Maintenabilité améliorée
- Tests unitaires facilités
- Debugging simplifié  
- Évolutions modulaires

## Prochaines étapes

1. Migration graduelle par étapes
2. Tests de régression complets
3. Documentation des nouvelles APIs
4. Formation des développeurs

---

**Version**: 1.0  
**Auteur**: Architecture refactoring  
**Date**: 2025-01-07