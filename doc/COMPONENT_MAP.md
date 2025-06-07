# Cartographie des composants AppMobTimeTouch

## Vue d'ensemble des responsabilités

### Controllers (3 composants)

#### BaseController.php
**Responsabilités**:
- Authentification et vérification des permissions
- Gestion centralisée des erreurs et messages
- Chargement des dépendances communes (DB, User, Langs)
- Initialisation des variables de session

**Méthodes principales**:
```php
__construct($db, $user, $langs)
checkAuthentication()
checkModuleEnabled()
checkPermissions($permission)
addError($message)
addMessage($message)
getCommonData()
```

#### HomeController.php  
**Responsabilités**:
- Orchestration de la page d'accueil
- Préparation des données pour la vue
- Coordination entre services
- Gestion des paramètres de vue (today/week/all)

**Méthodes principales**:
```php
index($view = 1)
prepareTimeclockData()
prepareSummaryData()
prepareJavaScriptConfig()
```

**Dépendances**:
- TimeclockService
- ConfigService  
- TimeHelper
- DataFormatter

#### TimeclockController.php
**Responsabilités**:
- Actions spécifiques clock-in/clock-out
- Validation des données d'entrée
- Coordination avec les services métier
- Gestion des redirections

**Méthodes principales**:
```php
clockIn($request)
clockOut($request)  
validateClockInData($data)
validateClockOutData($data)
```

**Dépendances**:
- TimeclockService
- LocationService
- ValidationService

### Services (4 composants)

#### TimeclockService.php
**Responsabilités**:
- Logique métier centrale du timetracking
- Gestion des sessions actives
- Calculs de durées et résumés
- Coordination avec les modèles de données

**Méthodes principales**:
```php
getActiveSession($userId)
calculateCurrentDuration($session)
getTodaySummary($userId)
getWeeklySummary($userId, $year, $week)
createClockInRecord($userId, $typeId, $location, $coordinates, $note)
createClockOutRecord($userId, $location, $coordinates, $note)
```

**Dépendances**:
- TimeclockRecord (model)
- WeeklySummary (model)
- TimeHelper
- ConfigService

#### LocationService.php
**Responsabilités**:
- Gestion de la géolocalisation
- Validation des coordonnées GPS
- Interface unifiée pour les services de localisation

**Méthodes principales**:
```php
validateCoordinates($latitude, $longitude)
isLocationRequired()
formatLocationString($location, $coordinates)
calculateDistance($lat1, $lon1, $lat2, $lon2)
```

**Dépendances**:
- ConfigService

#### ValidationService.php
**Responsabilités**:
- Validation des règles métier
- Contrôle des contraintes temporelles
- Validation des données utilisateur

**Méthodes principales**:
```php
canClockIn($userId)
canClockOut($userId)
validateWorkingHours($duration)
validateTimeclockType($typeId)
checkOvertimeRules($totalHours)
```

**Dépendances**:
- TimeclockService
- ConfigService

#### ConfigService.php
**Responsabilités**:
- Centralisation de la configuration
- Cache des paramètres système
- Interface unifiée pour l'accès aux configurations

**Méthodes principales**:
```php
getRequireLocation()
getMaxHoursPerDay()  
getOvertimeThreshold()
getDefaultTimeclockType()
refreshCache()
```

**Dépendances**:
- TimeclockConfig (model)

### Models (2 composants)

#### TimeclockSession.php
**Responsabilités**:
- Modélisation des sessions de travail actives
- Calculs temps réel des durées
- État et métadonnées de session

**Propriétés**:
```php
$userId
$recordId
$clockInTime
$timeclockType
$location
$coordinates
$status
```

**Méthodes**:
```php
getCurrentDuration()
getFormattedDuration()
getType()
getLocation()
isActive()
```

#### TimeclockSummary.php
**Responsabilités**:
- Modélisation des résumés temporels
- Agrégation des données quotidiennes/hebdomadaires
- Calculs d'heures supplémentaires

**Propriétés**:
```php
$period
$totalHours
$workingDays
$overtimeHours
$expectedHours
$status
```

**Méthodes**:
```php
getOvertimePercentage()
getProgressPercentage()
getStatusLabel()
isComplete()
```

### Utils (3 composants)

#### TimeHelper.php
**Responsabilités**:
- Fonctions utilitaires pour le temps
- Conversions et formatage temporel
- Calculs de durées

**Fonctions**:
```php
convertSecondsToReadableTime($seconds)
formatDuration($minutes)
parseTimestamp($value)
getCurrentWeek()
getWeekDates($year, $week)
```

#### DataFormatter.php
**Responsabilités**:
- Formatage des données pour l'affichage
- Nettoyage et validation des entrées
- Préparation des données pour les templates

**Fonctions**:
```php
formatUserName($user)
formatLocation($location, $coordinates)
formatTimeclockType($type)
sanitizeInput($input)
prepareJavaScriptData($data)
```

#### Constants.php
**Responsabilités**:
- Définition des constantes système
- États et codes de statut
- Configuration par défaut

**Constantes**:
```php
TIMECLOCK_STATUS_DRAFT = 0
TIMECLOCK_STATUS_VALIDATED = 1  
TIMECLOCK_STATUS_ACTIVE = 2
TIMECLOCK_STATUS_COMPLETED = 3
TIMECLOCK_STATUS_CANCELLED = 9

DEFAULT_MAX_HOURS_PER_DAY = 12
DEFAULT_OVERTIME_THRESHOLD = 8
DEFAULT_BREAK_DURATION = 30
```

### Assets JavaScript (3 composants)

#### timeclock-app.js
**Responsabilités**:
- Application principale JavaScript
- Orchestration des interactions UI
- Communication avec l'API
- Gestion des états de l'application

**Fonctions principales**:
```javascript
initializeApp()
updateDurationTimer()
showClockInModal()
showClockOutModal()
submitClockIn()
submitClockOut()
```

#### location-manager.js
**Responsabilités**:
- Gestion GPS spécialisée
- Interface géolocalisation  
- Validation des permissions
- Gestion des erreurs de localisation

**Fonctions principales**:
```javascript
getCurrentPosition()
checkLocationPermissions()
updateGPSStatus()
handleLocationError()
```

#### ui-components.js
**Responsabilités**:
- Composants d'interface utilisateur
- Animations et transitions
- Gestion des modals
- Interactions utilisateur

**Fonctions principales**:
```javascript
initializeComponents()
selectTimeclockType()
updateProgressBars()
showNotification()
handleFormValidation()
```

### Templates (8 composants)

#### Layouts

##### mobile-layout.tpl
**Responsabilités**:
- Structure HTML de base
- Inclusion des assets CSS/JS
- Définition des zones de contenu
- Configuration responsive

#### Components

##### status-card.tpl
**Responsabilités**:
- Affichage du statut de pointage actuel
- Informations de session active
- Boutons d'action principaux

##### summary-cards.tpl  
**Responsabilités**:
- Résumés quotidien et hebdomadaire
- Barres de progression
- Alertes heures supplémentaires

##### clockin-modal.tpl
**Responsabilités**:
- Interface de pointage d'entrée
- Sélection du type de travail
- Capture de localisation et notes

##### clockout-modal.tpl
**Responsabilités**:
- Interface de pointage de sortie
- Résumé de session
- Confirmation et notes de fin

##### records-list.tpl
**Responsabilités**:
- Liste des enregistrements récents
- Affichage des détails par enregistrement
- Navigation vers les détails

#### Pages

##### home.tpl
**Responsabilités**:
- Page principale refactorisée
- Orchestration des composants
- Gestion des données dynamiques

## Flux de données

### Initialisation de page
```
HomeController → Services → Models → Templates → Assets
```

### Action Clock-In
```
UI → timeclock-app.js → TimeclockController → TimeclockService → DB → Response
```

### Action Clock-Out  
```
UI → timeclock-app.js → TimeclockController → TimeclockService → WeeklySummary → DB → Response
```

### Mise à jour temps réel
```
Timer → ui-components.js → TimeHelper → DOM Update
```

## Dépendances inter-composants

### Controllers dépendent de:
- Services (injection)
- Utils (helpers statiques)
- Models (via services)

### Services dépendent de:
- Models (manipulation données)
- Utils (fonctions utilitaires)
- Autres Services (coordination)

### Templates dépendent de:
- Assets JS/CSS (fonctionnalités)
- Données Controller (affichage)

### Assets dépendent de:
- Configuration JavaScript (du Controller)
- APIs externes (Dolibarr, géolocalisation)

---

**Version**: 1.0  
**Auteur**: Architecture refactoring  
**Date**: 2025-01-07