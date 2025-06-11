# 📚 Annuaire des Fonctions - AppMobTimeTouch

> **Objectif :** Répertorier toutes les fonctions existantes pour éviter la duplication et favoriser la réutilisation lors des développements futurs.

## 🔍 Guide d'utilisation

**Avant de créer une nouvelle fonction :**
1. ✅ Consulter cet annuaire pour vérifier l'existence d'une fonction similaire
2. ✅ Si une fonction existe → la réutiliser
3. ✅ Si une fonction similaire existe → proposer les options (modification vs nouvelle fonction)
4. ✅ Si aucune fonction n'existe → créer la nouvelle fonction et mettre à jour cet annuaire

---

## 🎯 **NAVIGATION ET INTERFACE (JavaScript)**

### 📁 `js/navigation.js` - Système de navigation mobile

| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`goToCustomApp`** | 5 | `appName: string` | `void` | Navigation vers application personnalisée avec détection URL robuste | `goToCustomApp('appmobtimetouch')` |
| **`detectBaseUrl`** | 50 | aucun | `string` | Détection robuste de l'URL de base Dolibarr | Auto-appelée par goToCustomApp |
| **`navigateAndCloseMenu`** | 119 | `url: string` | `void` | Navigation générique avec fermeture du menu | Navigation interne |
| **`debugAppConfiguration`** | 147 | aucun | `void` | Debug configuration application courante | Mode debug uniquement |
| **`testNavigation`** | 171 | aucun | `void` | Test de navigation (console) | Tests et debug |
| **`loadManagement`** | 187 | aucun | `void` | Navigation vers dashboard validation manager (MVP 3.1) | Bouton Manager |
| **`loadMyRecords`** | 240 | aucun | `void` | Navigation vers enregistrements utilisateur | Bouton Mes Temps |
| **`loadSummary`** | 254 | aucun | `void` | Navigation vers résumés | Bouton Résumés |
| **`loadSettings`** | 268 | aucun | `void` | Navigation vers paramètres | Bouton Paramètres |
| **`initNavigation`** | 281 | aucun | `void` | Initialisation système navigation | `DOMContentLoaded` |

---

## ⏰ **API TIMECLOCK ET COMMUNICATION (JavaScript)**

### 📁 `js/timeclock-api.js` - Module API complet

#### **Utilitaires et Helpers**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`utils.log`** | 32 | `message: string, data?: any` | `void` | Logging conditionnel (debug mode) | `TimeclockAPI.utils.log('message', data)` |
| **`utils.error`** | 38 | `message: string, error?: any` | `void` | Logging des erreurs | Gestion erreurs internes |
| **`utils.formatDuration`** | 42 | `seconds: number` | `string` | Formatage durée en "Xh YY" | Affichage durées |
| **`utils.getCacheKey`** | 48 | `endpoint: string, params?: object` | `string` | Génération clé cache | Cache interne |
| **`utils.isValidResponse`** | 52 | `response: object` | `boolean` | Validation format réponse API | Validation réponses |
| **`utils.showLoading`** | 56 | `message?: string` | `void` | Affichage notification chargement | UX chargement |
| **`utils.showError`** | 62 | `message: string` | `void` | Affichage notification erreur | UX erreurs |
| **`utils.showSuccess`** | 70 | `message: string` | `void` | Affichage notification succès | UX succès |
| **`utils.getToken`** | 77 | aucun | `string\|null` | Récupération token CSRF | Sécurité |
| **`utils.updateToken`** | 85 | `token: string` | `void` | Mise à jour token localStorage | Sécurité |

#### **Communication HTTP**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`http.request`** | 95 | `method, endpoint, data?, options?` | `Promise<object>` | Requête HTTP avec gestion token et erreurs | Base toutes requêtes |
| **`http.get`** | 221 | `endpoint: string, params?: object` | `Promise<object>` | Requête GET | `http.get('/status')` |
| **`http.post`** | 225 | `endpoint: string, data: object` | `Promise<object>` | Requête POST | `http.post('/clockin', data)` |
| **`http.queueRequest`** | 229 | `method, endpoint, data` | `void` | File d'attente requêtes offline | Mode offline |
| **`http.processQueue`** | 239 | aucun | `Promise<void>` | Traitement file d'attente | Mode offline |

#### **Cache et Stockage**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`cache.set`** | 259 | `key: string, data: any` | `void` | Stockage en cache avec timestamp | Performance |
| **`cache.get`** | 266 | `key: string` | `any\|null` | Récupération cache avec expiration | Performance |
| **`cache.clear`** | 279 | aucun | `void` | Nettoyage cache complet | Maintenance |

#### **Géolocalisation**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`geolocation.getCurrentPosition`** | 287 | aucun | `Promise<object>` | Obtention position GPS actuelle | Pointage |
| **`geolocation.watchPosition`** | 330 | `callback: function` | `number\|null` | Surveillance position GPS | Suivi temps réel |

#### **API Métier - Fonctions Principales**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`api.getStatus`** | 348 | `useCache?: boolean` | `Promise<object>` | Statut pointage utilisateur | `await TimeclockAPI.getStatus()` |
| **`api.clockIn`** | 375 | `data: object` | `Promise<object>` | Pointage d'entrée avec géolocalisation | `await TimeclockAPI.clockIn(data)` |
| **`api.clockOut`** | 413 | `data?: object` | `Promise<object>` | Pointage de sortie | `await TimeclockAPI.clockOut()` |
| **`api.getRecords`** | 451 | `params?: object` | `Promise<array>` | Historique enregistrements | `await TimeclockAPI.getRecords()` |
| **`api.getTypes`** | 476 | aucun | `Promise<array>` | Types de pointage disponibles | `await TimeclockAPI.getTypes()` |
| **`api.getTodaySummary`** | 500 | aucun | `Promise<object>` | Résumé journée courante | `await TimeclockAPI.getTodaySummary()` |
| **`api.getWeeklySummary`** | 524 | aucun | `Promise<object>` | Résumé semaine courante | `await TimeclockAPI.getWeeklySummary()` |

#### **Interface Utilisateur**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`ui.clockIn`** | 684 | `formData: object` | `Promise<object>` | Interface pointage entrée avec notifications | Interface utilisateur |
| **`ui.clockOut`** | 720 | `formData: object` | `Promise<object>` | Interface pointage sortie avec notifications | Interface utilisateur |
| **`ui.refreshData`** | 755 | aucun | `Promise<void>` | Actualisation données complète | Rafraîchissement |

#### **Temps Réel**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`realtime.start`** | 550 | aucun | `void` | Démarrage mises à jour temps réel | Activation temps réel |
| **`realtime.stop`** | 568 | aucun | `void` | Arrêt mises à jour temps réel | Désactivation |
| **`realtime.updateStatus`** | 576 | aucun | `Promise<void>` | Mise à jour statut en temps réel | Synchronisation |
| **`realtime.updateDurationDisplay`** | 599 | `status: object` | `void` | Mise à jour affichage durée | Interface temps réel |

#### **Fonctions Globales**
| Fonction | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`submitClockIn`** | 867 | aucun | `Promise<void>` | Soumission formulaire pointage entrée | `onclick="submitClockIn()"` |
| **`submitClockOut`** | 891 | aucun | `Promise<void>` | Soumission formulaire pointage sortie | `onclick="submitClockOut()"` |
| **`refreshTimeclockData`** | 915 | aucun | `void` | Actualisation données timeclock | `onclick="refreshTimeclockData()"` |

---

## 🏗️ **ARCHITECTURE SOLID - CONTRÔLEURS**

### 📁 `Controllers/BaseController.php` - Contrôleur de base (SRP + DIP)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 26 | `$db, $user, $langs, $conf` | `void` | public | Constructor injection dépendances Dolibarr | Héritage contrôleurs |
| **`checkModuleEnabled`** | 39 | aucun | `void` | protected | Vérification module activé (SRP) | Sécurité base |
| **`checkUserRights`** | 52 | `$permission: string` | `void` | protected | Vérification droits utilisateur | `$this->checkUserRights('read')` |
| **`handleError`** | 66 | `$e: Exception` | `array` | protected | Gestion centralisée erreurs | `return $this->handleError($e)` |
| **`prepareTemplateData`** | 81 | `$data: array` | `array` | protected | Préparation données communes templates | Template rendering |
| **`validatePostParams`** | 101 | `$requiredParams: array` | `array` | protected | Validation paramètres POST standardisée | Validation formulaires |
| **`redirectWithSuccess`** | 130 | `$url, $successParam` | `void` | protected | Redirection avec message succès | Actions POST |
| **`isActionAllowed`** | 143 | `$action: string` | `bool` | protected | Validation permissions action | Contrôle accès |

### 📁 `Controllers/HomeController.php` - Page d'accueil (SRP + OCP)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 26 | `$db, $user, $langs, $conf, services...` | `void` | public | Constructor injection services (DIP) | `new HomeController(...)` |
| **`index`** | 44 | aucun | `array` | public | Action principale page accueil | Route principale |
| **`handleAction`** | 85 | `$action: string` | `array` | private | Gestion centralisée actions (OCP) | Dispatch actions |
| **`handleClockIn`** | 103 | aucun | `array` | private | Traitement pointage entrée | Action clockin |
| **`handleClockOut`** | 132 | aucun | `array` | private | Traitement pointage sortie | Action clockout |
| **`preparePageData`** | 161 | `$view: int` | `array` | private | Préparation données spécifiques page | Template data |
| **`extractTimestamp`** | 221 | `$rawTimestamp: mixed` | `int\|null` | private | Extraction intelligente timestamp | Conversion données |
| **`prepareJavaScriptData`** | 257 | `$isClockedIn, $clockInTime, ...` | `array` | private | Préparation données JavaScript | Interface dynamique |

### 📁 `Controllers/ValidationController.php` - Validation Manager (MVP 3.1-3.2)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 35 | `$db, $user, $langs, $conf` | `void` | public | Constructor injection services validation | `new ValidationController(...)` |
| **`dashboard`** | 51 | aucun | `array` | public | Dashboard manager validation (MVP 3.1) | Page validation principale |
| **`calculateBasicStats`** | 94 | `$pendingRecords: array` | `array` | private | Calcul statistiques essentielles | Métriques dashboard |
| **`validateRecord`** | 140 | aucun | `array` | public | Validation individuelle enregistrement (MVP 3.2) | Action AJAX validation |
| **`getRecordDetails`** | 227 | aucun | `array` | public | Récupération détails enregistrement | API détails |
| **`detectRecordAnomalies`** | 308 | `$record: TimeclockRecord` | `array` | private | Détection anomalies enregistrement | Analyse qualité |
| **`batchValidate`** | 348 | aucun | `array` | public | Validation lot (MVP 3.3 placeholder) | Validation multiple |
| **`viewRecord`** | 364 | `$recordId: int` | `array` | public | Affichage détails avec actions validation | Page détail record |
| **`isManager`** | 433 | aucun | `bool` | public | Vérification si utilisateur est manager | Contrôle permissions |

---

## 🔧 **SERVICES MÉTIER (SOLID)**

### 📁 `Services/TimeclockService.php` - Logique métier pointage (SRP + DIP)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 30 | `$db, DataServiceInterface` | `void` | public | Constructor injection dépendances (DIP) | Injection services |
| **`clockIn`** | 39 | `User $user, array $params` | `int` | public | Pointage entrée - logique métier centralisée | `$service->clockIn($user, $params)` |
| **`clockOut`** | 92 | `User $user, array $params` | `int` | public | Pointage sortie - logique métier centralisée | `$service->clockOut($user, $params)` |
| **`getActiveRecord`** | 142 | `int $userId` | `TimeclockRecord\|null` | public | Récupération enregistrement actif utilisateur | Vérification session active |
| **`validateClockInParams`** | 172 | `array $params` | `array` | public | Validation paramètres pointage entrée | Validation côté serveur |
| **`validateClockOutParams`** | 204 | `array $params` | `array` | public | Validation paramètres pointage sortie | Validation côté serveur |
| **`calculateSessionDuration`** | 230 | `TimeclockRecord $record` | `bool` | public | Calcul durée session travail | Calculs temps |
| **`validateActiveSession`** | 284 | `int $userId` | `bool` | public | Validation session active utilisateur | Contrôle intégrité |
| **`convertToTimestamp`** | 293 | `$datetime: mixed` | `int\|false` | private | Conversion sécurisée timestamp | Utilitaire interne |

### 📁 `Services/DataService.php` - Service données (SRP + Repository Pattern)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 24 | `$db: DoliDB` | `void` | public | Constructor base données | Injection DB |
| **`getTodayRecords`** | 32 | `int $userId` | `array` | public | Enregistrements aujourd'hui | `$service->getTodayRecords($userId)` |
| **`getWeeklyRecords`** | 59 | `int $userId, int $year, int $week` | `array` | public | Enregistrements semaine spécifique | Données semaine |
| **`getRecentRecords`** | 85 | `int $userId, int $limit = 5` | `array` | public | Enregistrements récents avec debug | Liste récente |
| **`calculateTodaySummary`** | 123 | `int $userId` | `array` | public | Calcul résumé journalier | Résumé jour |
| **`calculateWeeklySummary`** | 153 | `int $userId` | `WeeklySummary\|null` | public | Calcul résumé hebdomadaire | Résumé semaine |
| **`getActiveTimeclockTypes`** | 211 | aucun | `array` | public | Types pointage actifs | Configuration types |
| **`getDefaultTimeclockType`** | 237 | aucun | `int` | public | Type pointage par défaut | Type défaut |
| **`convertToUnixTimestamp`** | 265 | `$dolibarrTimestamp: mixed` | `int` | private | Conversion sécurisée timestamp Unix | Utilitaire conversion |

### 📁 `Services/ValidationService.php` - Service validation manager (SRP + DIP)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 24 | `DoliDB, DataServiceInterface, NotificationServiceInterface` | `void` | public | Constructor injection dépendances (DIP) | Injection services |
| **`getPendingValidations`** | 37 | `int $managerId` | `array` | public | Temps en attente validation manager | Dashboard validation |
| **`getTodaysRecords`** | 80 | `int $managerId` | `array` | public | Enregistrements aujourd'hui pour manager | Tous records jour |
| **`validateRecord`** | 123 | `int $recordId, int $validatorId, string $action, ?string $comment` | `bool` | public | Validation temps travail | `$service->validateRecord($id, $managerId, 'approve')` |
| **`batchValidate`** | 181 | `array $recordIds, int $validatorId, string $action` | `array` | public | Validation lot enregistrements | Validation multiple |
| **`detectAnomalies`** | 201 | `int $userId, string $period` | `array` | public | Détection anomalies utilisateur | Analyse anomalies |
| **`getValidationStatus`** | 228 | `int $recordId` | `array` | public | Statut validation enregistrement | `$service->getValidationStatus($recordId)` |
| **`canValidate`** | 267 | `int $userId, int $recordId` | `bool` | public | Vérification permissions validation | Contrôle accès |
| **`getValidationStats`** | 295 | `int $managerId, string $period = 'week'` | `array` | public | Statistiques validation manager | Métriques manager |
| **`getTeamMembers`** | 343 | `int $managerId` | `array` | public | Membres équipe manager | Gestion équipe |
| **`canAutoValidate`** | 382 | `int $recordId` | `bool` | public | Validation automatique possible | Auto-validation |
| **`enrichRecordData`** | 420 | `object $record` | `array` | public | Enrichissement données enregistrement | Données complètes |
| **`detectRecordAnomalies`** | 455 | `object $record` | `array` | public | Détection anomalies enregistrement spécifique | Anomalies record |
| **`getRecordData`** | 517 | `int $recordId` | `array\|null` | public | Données enregistrement | Accès données record |

### 📁 `Services/NotificationService.php` - Service notifications (SRP + Observer Pattern)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 20 | `DoliDB $db` | `void` | public | Constructor base données | Injection DB |
| **`notifyPendingValidation`** | 28 | `int $managerId, array $records` | `bool` | public | Notification validations en attente | `$service->notifyPendingValidation($managerId, $records)` |
| **`notifyValidationStatus`** | 54 | `int $userId, int $recordId, string $status` | `bool` | public | Notification statut validation | Notification employé |
| **`alertAnomaly`** | 80 | `int $managerId, string $anomalyType, array $data` | `bool` | public | Alerte anomalie manager | Alerte anomalie |
| **`getUnreadNotifications`** | 104 | `int $userId` | `array` | public | Notifications non lues | `$service->getUnreadNotifications($userId)` |
| **`markAsRead`** | 135 | `int $notificationId` | `bool` | public | Marquer notification lue | Action lecture |
| **`markAllAsRead`** | 157 | `int $userId` | `bool` | public | Marquer toutes notifications lues | Action lecture multiple |
| **`getUnreadCount`** | 180 | `int $userId` | `int` | public | Compter notifications non lues | Badge compteur |
| **`cleanupOldNotifications`** | 200 | `int $daysOld = 30` | `int` | public | Nettoyage anciennes notifications | Maintenance |
| **`createCustomNotification`** | 222 | `int $userId, string $type, string $message, array $data = []` | `int` | public | Création notification personnalisée | Notification custom |
| **`sendValidationReminder`** | 232 | `int $managerId, array $overdueRecords` | `bool` | public | Rappel validations en retard | Rappels |
| **`formatNotificationForDisplay`** | 343 | `array $notification` | `array` | public | Formatage notification affichage | Interface notifications |

---

## 🛠️ **UTILITAIRES ET HELPERS**

### 📁 `Utils/TimeHelper.php` - Utilitaires temps (Statiques)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`convertSecondsToReadableTime`** | 30 | `$seconds: int\|float` | `string` | static | Conversion secondes vers format "Xh YY" | `TimeHelper::convertSecondsToReadableTime(3600)` → "1h 00" |
| **`formatDuration`** | 54 | `$minutes: int\|float` | `string` | static | Formatage durée minutes vers "Xh YY" | `TimeHelper::formatDuration(90)` → "1h 30" |
| **`calculateDuration`** | 79 | `int $start, int $end` | `int` | static | Calcul durée entre timestamps | Calcul durées |
| **`isValidDuration`** | 92 | `int $duration` | `bool` | static | Validation durée raisonnable | Validation temps |
| **`convertDbTimestamp`** | 112 | `$dbTimestamp: mixed, $db: DoliDB` | `int\|null` | static | Conversion timestamp DB vers Unix | Conversion DB |
| **`formatTimestamp`** | 154 | `int $timestamp, string $format = 'dayhour', string $tzoutput = 'tzuser'` | `string` | static | Formatage timestamp avec paramètres Dolibarr | Affichage dates |
| **`convertDecimalHoursToReadable`** | 176 | `float $hours` | `string` | static | Conversion heures décimales vers lisible | Conversion heures |
| **`calculateProgressPercentage`** | 193 | `int $actualSeconds, int $targetSeconds` | `float` | static | Calcul pourcentage progression | Barres progression |
| **`isOvertime`** | 209 | `int $workedSeconds, ?int $thresholdHours = null` | `bool` | static | Détection heures supplémentaires | Détection overtime |

### 📁 `Utils/LocationHelper.php` - Utilitaires géolocalisation (Statiques)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`validateCoordinates`** | 31 | `?float $lat, ?float $lon` | `bool` | static | Validation coordonnées GPS | `LocationHelper::validateCoordinates(48.8566, 2.3522)` |
| **`calculateDistance`** | 58 | `float $lat1, float $lon1, float $lat2, float $lon2` | `float` | static | Calcul distance GPS (Haversine) | Calcul distances GPS |
| **`formatCoordinates`** | 95 | `float $lat, float $lon, int $precision = 6` | `string` | static | Formatage coordonnées affichage | Affichage GPS |
| **`isWithinWorkArea`** | 112 | `float $lat, float $lon, array $workAreas` | `bool` | static | Vérification zone travail autorisée | Contrôle zones |
| **`getApproximateAddress`** | 149 | `float $lat, float $lon` | `string` | static | Adresse approximative (geocoding simple) | Geocoding |
| **`isAccuracyAcceptable`** | 167 | `float $accuracy, ?int $maxAccuracy = null` | `bool` | static | Validation précision GPS | Qualité GPS |
| **`sanitizeLocationName`** | 190 | `string $location` | `string` | static | Nettoyage nom lieu utilisateur | Sécurité input |
| **`getApproximateTimezone`** | 213 | `float $lon` | `string` | static | Fuseau horaire approximatif par longitude | Timezone GPS |
| **`standardizeCoordinates`** | 236 | `float $lat, float $lon` | `array` | static | Standardisation coordonnées stockage | Normalisation GPS |

### 📁 `Utils/Constants.php` - Configuration système (Statiques)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`getValue`** | 66 | `$db: DoliDB, string $key, $default = null` | `mixed` | static | Récupération valeur configuration avec fallback | `Constants::getValue($db, 'MAX_HOURS', 8)` |
| **`getDefaultValues`** | 82 | aucun | `array` | static | Valeurs configuration par défaut | Configuration défaut |
| **`isValidStatus`** | 99 | `int $status` | `bool` | static | Validation statut enregistrement | Validation statuts |
| **`getStatusLabel`** | 116 | `int $status` | `string` | static | Libellé statut enregistrement | Affichage statuts |

### 📁 `Constants/ValidationConstants.php` - Configuration validation (Statiques)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`getValidationStatuses`** | 49 | aucun | `array` | static | Correspondance statuts validation → labels | `ValidationConstants::getValidationStatuses()` |
| **`getAnomalyTypes`** | 64 | aucun | `array` | static | Types anomalies avec seuils et niveaux | Configuration anomalies |
| **`getDefaultWorkflowConfig`** | 99 | aucun | `array` | static | Configuration par défaut workflow validation | Config workflow |
| **`getAlertColors`** | 113 | aucun | `array` | static | Couleurs niveaux alerte (interface) | UI couleurs |
| **`getAnomalyIcons`** | 127 | aucun | `array` | static | Icônes types anomalies (interface) | UI icônes |
| **`getAnomalyLabels`** | 142 | aucun | `array` | static | Labels traduction types anomalies | UI labels |

---

## 📊 **ENTITÉS ET MODÈLES DE DONNÉES**

### 📁 `class/timeclockrecord.class.php` - Entité principale (CRUD)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 152 | `DoliDB $db` | `void` | public | Constructor classe principale enregistrements | `$record = new TimeclockRecord($db)` |
| **`create`** | - | `User $user` | `int` | public | Création nouvel enregistrement (hérité CommonObject) | `$record->create($user)` |
| **`fetch`** | - | `int $id` | `int` | public | Chargement enregistrement par ID (hérité) | `$record->fetch($id)` |
| **`update`** | - | `User $user` | `int` | public | Mise à jour enregistrement (hérité) | `$record->update($user)` |
| **`delete`** | - | `User $user` | `int` | public | Suppression enregistrement (hérité) | `$record->delete($user)` |

### 📁 `class/timeclocktype.class.php` - Types de pointage (CRUD)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | - | `DoliDB $db` | `void` | public | Constructor types de pointage | `$type = new TimeclockType($db)` |
| **`create`** | - | `User $user` | `int` | public | Création type pointage (hérité) | `$type->create($user)` |
| **`fetch`** | - | `int $id` | `int` | public | Chargement type par ID (hérité) | `$type->fetch($id)` |

### 📁 `class/weeklysummary.class.php` - Résumés hebdomadaires (CRUD)

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | - | `DoliDB $db` | `void` | public | Constructor résumés hebdomadaires | `$summary = new WeeklySummary($db)` |
| **`summaryExists`** | - | `int $userId, int $year, int $week` | `WeeklySummary\|null` | public | Vérification existence résumé | Vérification résumé |

---

## 🌐 **API ET POINTS D'ENTRÉE**

### 📁 `api/timeclock.php` - API REST principale

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`TimeclockAPI::__construct`** | 96 | `$db, $user, $langs` | `void` | public | Constructor API avec injection services | Initialisation API |
| **`TimeclockAPI::handleRequest`** | - | `string $action` | `array` | public | Gestionnaire principal requêtes API | Dispatch API |
| **`TimeclockAPI::getStatus`** | - | aucun | `array` | public | Endpoint statut pointage utilisateur | `GET /api/timeclock.php?action=status` |
| **`TimeclockAPI::clockIn`** | - | `array $data` | `array` | public | Endpoint pointage entrée | `POST /api/timeclock.php?action=clockin` |
| **`TimeclockAPI::clockOut`** | - | `array $data` | `array` | public | Endpoint pointage sortie | `POST /api/timeclock.php?action=clockout` |
| **`TimeclockAPI::getRecords`** | - | `array $params` | `array` | public | Endpoint historique enregistrements | `GET /api/timeclock.php?action=records` |
| **`TimeclockAPI::getTypes`** | - | aucun | `array` | public | Endpoint types de pointage | `GET /api/timeclock.php?action=types` |
| **`TimeclockAPI::getTodaySummary`** | - | aucun | `array` | public | Endpoint résumé aujourd'hui | `GET /api/timeclock.php?action=today_summary` |
| **`TimeclockAPI::getWeeklySummary`** | - | aucun | `array` | public | Endpoint résumé semaine | `GET /api/timeclock.php?action=weekly_summary` |

### 📁 `home.php` - Page principale application

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`main execution`** | 87 | - | `void` | global | Point d'entrée principal application mobile | URL : `/custom/appmobtimetouch/home.php` |

### 📁 `validation.php` - Dashboard validation manager

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`main execution`** | 32 | - | `void` | global | Point d'entrée dashboard validation manager | URL : `/custom/appmobtimetouch/validation.php` |

---

## 📚 **UTILITAIRES SYSTÈME**

### 📁 `lib/appmobtimetouch.lib.php` - Fonctions globales Dolibarr

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`appmobtimetouchAdminPrepareHead`** | 29 | aucun | `array` | global | Préparation en-têtes pages admin | Navigation admin |

### 📁 `core/modules/modAppMobTimeTouch.class.php` - Module Dolibarr

| Fonction | Ligne | Paramètres | Retour | Visibilité | Description | Utilisation |
|----------|-------|------------|--------|------------|-------------|-------------|
| **`__construct`** | 41 | `DoliDB $db` | `void` | public | Constructor module Dolibarr | Initialisation module |
| **`init`** | - | aucun | `int` | public | Initialisation module (création tables, permissions) | Installation module |
| **`remove`** | - | aucun | `int` | public | Désinstallation module | Désinstallation |

---

## 🎯 **INTERFACES SOLID (DIP + ISP)**

### 📁 `Services/Interfaces/TimeclockServiceInterface.php` - Contrat service pointage

| Méthode | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`clockIn`** | 21 | `User $user, array $params` | `int` | Contrat pointage entrée | Interface service pointage |
| **`clockOut`** | 30 | `User $user, array $params` | `int` | Contrat pointage sortie | Interface service pointage |
| **`getActiveRecord`** | 38 | `int $userId` | `TimeclockRecord\|null` | Contrat récupération session active | Interface service pointage |
| **`validateClockInParams`** | 46 | `array $params` | `array` | Contrat validation paramètres entrée | Interface service pointage |
| **`validateClockOutParams`** | 54 | `array $params` | `array` | Contrat validation paramètres sortie | Interface service pointage |

### 📁 `Services/Interfaces/DataServiceInterface.php` - Contrat service données

| Méthode | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`getTodayRecords`** | 17 | `int $userId` | `array` | Contrat enregistrements aujourd'hui | Interface données |
| **`getWeeklyRecords`** | 27 | `int $userId, int $year, int $week` | `array` | Contrat enregistrements semaine | Interface données |
| **`getRecentRecords`** | 36 | `int $userId, int $limit = 5` | `array` | Contrat enregistrements récents | Interface données |
| **`calculateTodaySummary`** | 44 | `int $userId` | `array` | Contrat résumé journalier | Interface données |
| **`calculateWeeklySummary`** | 52 | `int $userId` | `WeeklySummary\|null` | Contrat résumé hebdomadaire | Interface données |
| **`getActiveTimeclockTypes`** | 60 | aucun | `array` | Contrat types pointage actifs | Interface données |
| **`getDefaultTimeclockType`** | 66 | aucun | `int` | Contrat type pointage par défaut | Interface données |

### 📁 `Services/Interfaces/ValidationServiceInterface.php` - Contrat service validation

| Méthode | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`getPendingValidations`** | 25 | `int $managerId` | `array` | Contrat temps en attente validation | Interface validation |
| **`validateRecord`** | 36 | `int $recordId, int $validatorId, string $action, ?string $comment` | `bool` | Contrat validation temps travail | Interface validation |
| **`batchValidate`** | 46 | `array $recordIds, int $validatorId, string $action` | `array` | Contrat validation lot | Interface validation |
| **`detectAnomalies`** | 55 | `int $userId, string $period` | `array` | Contrat détection anomalies | Interface validation |
| **`getValidationStatus`** | 63 | `int $recordId` | `array` | Contrat statut validation | Interface validation |
| **`canValidate`** | 72 | `int $userId, int $recordId` | `bool` | Contrat vérification permissions | Interface validation |
| **`getValidationStats`** | 81 | `int $managerId, string $period` | `array` | Contrat statistiques validation | Interface validation |
| **`getTeamMembers`** | 89 | `int $managerId` | `array` | Contrat membres équipe | Interface validation |
| **`canAutoValidate`** | 97 | `int $recordId` | `bool` | Contrat validation automatique | Interface validation |

### 📁 `Services/Interfaces/NotificationServiceInterface.php` - Contrat service notifications

| Méthode | Ligne | Paramètres | Retour | Description | Utilisation |
|----------|-------|------------|--------|-------------|-------------|
| **`notifyPendingValidation`** | 26 | `int $managerId, array $records` | `bool` | Contrat notification validation en attente | Interface notifications |
| **`notifyValidationStatus`** | 36 | `int $userId, int $recordId, string $status` | `bool` | Contrat notification statut validation | Interface notifications |
| **`alertAnomaly`** | 46 | `int $managerId, string $anomalyType, array $data` | `bool` | Contrat alerte anomalie | Interface notifications |
| **`getUnreadNotifications`** | 54 | `int $userId` | `array` | Contrat notifications non lues | Interface notifications |
| **`markAsRead`** | 62 | `int $notificationId` | `bool` | Contrat marquer notification lue | Interface notifications |
| **`markAllAsRead`** | 71 | `int $userId` | `bool` | Contrat marquer toutes notifications lues | Interface notifications |
| **`getUnreadCount`** | 79 | `int $userId` | `int` | Contrat compter notifications non lues | Interface notifications |
| **`cleanupOldNotifications`** | 87 | `int $daysOld = 30` | `int` | Contrat nettoyage notifications | Interface notifications |
| **`createCustomNotification`** | 96 | `int $userId, string $type, string $message, array $data` | `int` | Contrat notification personnalisée | Interface notifications |
| **`sendValidationReminder`** | 105 | `int $managerId, array $overdueRecords` | `bool` | Contrat rappel validation | Interface notifications |

---

## 📈 **STATISTIQUES ET MÉTRIQUES**

| Catégorie | Nombre de fonctions | Complexité | État |
|-----------|-------------------|------------|------|
| **🎯 Navigation JS** | 10 fonctions | Moyenne | ✅ Stable |
| **⏰ API Timeclock JS** | 35+ méthodes | Élevée | ✅ Fonctionnel |
| **🏗️ Contrôleurs SOLID** | 21 méthodes | Élevée | ✅ MVP 3.2 Complet |
| **🔧 Services Métier** | 45+ méthodes | Très élevée | ✅ Architecture SOLID |
| **🛠️ Helpers/Utilitaires** | 25 fonctions statiques | Moyenne | ✅ Réutilisables |
| **📊 Entités/Modèles** | 15+ méthodes CRUD | Faible | ✅ Dolibarr Standard |
| **🌐 API/Points d'entrée** | 12 endpoints | Moyenne | ✅ REST Standard |
| **🎯 Interfaces SOLID** | 30 contrats | Moyenne | ✅ DIP + ISP |
| **📚 Utilitaires Système** | 5 fonctions | Faible | ✅ Dolibarr Intégré |

**📊 Total : 200+ fonctions/méthodes** dans une architecture SOLID complète.

---

## 🔄 **GUIDE DE DÉVELOPPEMENT**

### ✅ **Avant de créer une nouvelle fonction :**

1. **🔍 Rechercher dans cet annuaire** par nom ou fonctionnalité similaire
2. **📋 Vérifier les catégories** correspondant au domaine métier
3. **🎯 Identifier les interfaces** disponibles pour l'extension
4. **⚡ Privilégier la réutilisation** des fonctions existantes

### 🛠️ **Options de développement :**

#### **✅ Fonction existe et correspond**
```php
// Réutiliser directement
$result = TimeHelper::formatDuration($minutes);
$status = ValidationService::getValidationStatus($recordId);
```

#### **🔧 Fonction similaire existe - Modification nécessaire**
**Options proposées :**
1. **Extension** : Ajouter paramètres optionnels à la fonction existante
2. **Surcharge** : Créer nouvelle fonction avec suffixe explicite
3. **Refactoring** : Généraliser la fonction existante pour couvrir les deux cas

#### **🆕 Aucune fonction similaire**
```php
// Créer nouvelle fonction en respectant :
// - Principe SRP (Single Responsibility)
// - Nommage cohérent avec l'existant
// - Interface appropriée si nécessaire
// - Tests unitaires
// - Mise à jour de cet annuaire
```

### 📝 **Template de documentation pour nouvelles fonctions :**

```markdown
| **`nomFonction`** | ligne | `param1: type, param2: type` | `ReturnType` | visibility | Description détaillée | `Exemple::usage()` |
```

---

## 🏆 **ARCHITECTURE SOLID RESPECTÉE**

- **🎯 SRP** : Chaque fonction a une responsabilité unique et bien définie
- **🔓 OCP** : Extensions possibles via interfaces et héritage (contrôleurs, services)
- **🔄 LSP** : Substitution garantie via interfaces et contrats
- **🔧 ISP** : Interfaces spécialisées et ségrégées par domaine métier
- **⬆️ DIP** : Injection de dépendances systématique dans tous les services

Cette architecture garantit la **maintenabilité**, **testabilité** et **évolutivité** du système de pointage mobile avec validation managériale.

---

*📅 Dernière mise à jour : Après implémentation MVP 3.2 "Actions validation individuelles"*  
*🔄 Prochaine mise à jour : Lors d'ajout de nouvelles fonctions ou MVP 3.3*