# Cartographie des Composants - AppMobTimeTouch

## Responsabilités par Composant

### Controllers/ - Orchestration des requêtes

#### BaseController.php
**Responsabilité unique** : Interface commune et gestion d'erreurs
- Chargement environnement Dolibarr
- Vérification permissions
- Gestion erreurs centralisée
- Rendu templates

**Dépendances** :
- AuthServiceInterface
- Dolibarr core classes

**Tests** : Vérification chargement, permissions, gestion erreurs

#### HomeController.php  
**Responsabilité unique** : Logique page d'accueil timeclock
- Traitement actions clock in/out
- Préparation données pour templates
- Gestion redirections
- Validation formulaires

**Dépendances** :
- TimeclockServiceInterface
- DataServiceInterface
- BaseController

**Tests** : Actions, redirections, validation

#### ValidationController.php ✨ **MVP 3.3**
**Responsabilité unique** : Interface validation manager
- Dashboard validation avec statistiques
- Actions validation individuelles et en lot
- Gestion filtres avancés pour liste complète
- Navigation entre vues validation

**Dépendances** :
- ValidationServiceInterface
- NotificationServiceInterface
- DataServiceInterface
- BaseController

**Tests** : Validation actions, filtres, batch operations

#### AuthController.php
**Responsabilité unique** : Authentification et autorisations
- Vérification droits utilisateur
- Gestion session Dolibarr
- Contrôle accès module

**Dépendances** :
- AuthServiceInterface
- User Dolibarr

**Tests** : Droits, accès, session

### Services/ - Logique métier

#### TimeclockService.php
**Responsabilité unique** : Logique métier timetracking
- Clock in/out avec validation
- Calculs durées et heures
- Gestion statuts records
- Validation règles métier

**Dépendances** :
- TimeclockRecord
- TimeclockConfig
- LocationHelper

**Tests** : Clock in/out, calculs, validations

#### DataService.php  
**Responsabilité unique** : Accès et agrégation données
- Récupération records utilisateur
- Calculs résumés journaliers/hebdo
- Gestion cache données
- Optimisation requêtes

**Dépendances** :
- Database
- WeeklySummary
- TimeclockRecord

**Tests** : Requêtes, calculs, cache

#### ValidationService.php ✨ **MVP 3.3**
**Responsabilité unique** : Logique métier validation
- Récupération enregistrements en attente
- Validation individuelle et en lot (approve/reject/partial)
- Détection automatique d'anomalies
- Filtrage avancé avec critères multiples (statut, utilisateur, dates, anomalies)
- Gestion équipes et permissions manager

**Dépendances** :
- DataServiceInterface
- NotificationServiceInterface
- TimeclockRecord
- ValidationConstants

**Tests** : Validations, filtres, anomalies, permissions

#### NotificationService.php ✨ **MVP 3.3**
**Responsabilité unique** : Gestion notifications validation
- Notifications manager (validations en attente, anomalies)
- Notifications employé (statut validation, commentaires)
- Gestion état lu/non-lu
- Système d'alertes par priorité

**Dépendances** :
- Database
- User Dolibarr

**Tests** : Notifications, alertes, états

#### AuthService.php
**Responsabilité unique** : Services authentification
- Validation utilisateur actif
- Vérification permissions module
- Gestion tokens CSRF

**Dépendances** :
- User Dolibarr
- Conf Dolibarr

**Tests** : Validation, permissions, tokens

### Utils/ - Fonctions utilitaires

#### TimeHelper.php
**Responsabilité unique** : Manipulation et formatage temps
```php
- convertSecondsToReadableTime(int $seconds): string
- formatDuration(int $minutes): string  
- calculateWorkDuration(DateTime $start, DateTime $end): int
- isWorkingHours(DateTime $time, array $config): bool
```

**Tests** : Conversions, calculs, validation

#### LocationHelper.php
**Responsabilité unique** : Gestion géolocalisation
```php
- validateCoordinates(float $lat, float $lon): bool
- calculateDistance(array $point1, array $point2): float
- isWithinWorkArea(float $lat, float $lon, array $workAreas): bool
- formatLocationString(float $lat, float $lon): string
```

**Tests** : Validation, calculs, zones

#### Constants.php
**Responsabilité unique** : Configuration centralisée
```php
class TimeclockConstants {
    const REQUIRE_LOCATION = 'REQUIRE_LOCATION';
    const MAX_HOURS_PER_DAY = 'MAX_HOURS_PER_DAY';
    const OVERTIME_THRESHOLD = 'OVERTIME_THRESHOLD';
    const DEFAULT_BREAK_DURATION = 'DEFAULT_BREAK_DURATION';
    
    const STATUS_DRAFT = 0;
    const STATUS_VALIDATED = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 9;
}
```

#### ValidationConstants.php ✨ **MVP 3.3**
**Responsabilité unique** : Configuration workflow validation
```php
class ValidationConstants {
    // Statuts de validation
    const VALIDATION_PENDING = 0;
    const VALIDATION_APPROVED = 1;
    const VALIDATION_REJECTED = 2;
    const VALIDATION_PARTIAL = 3;
    
    // Types d'anomalies
    const ANOMALY_OVERTIME = 'overtime';
    const ANOMALY_MISSING_CLOCKOUT = 'missing_clockout';
    const ANOMALY_LONG_BREAK = 'long_break';
    
    // Niveaux d'alerte
    const ALERT_INFO = 'info';
    const ALERT_WARNING = 'warning';
    const ALERT_CRITICAL = 'critical';
}
```

### Views/ - Présentation

#### layouts/main.tpl
**Responsabilité unique** : Structure page OnsenUI
- Balises OnsenUI principales  
- Chargement CSS/JS
- Structure responsive
- Navigation mobile

#### components/ - Composants atomiques

**StatusCard.tpl** : Affichage statut clock in/out uniquement
- État actuel utilisateur
- Boutons actions principales
- Indicateurs visuels

**SummaryCard.tpl** : Résumés temps uniquement  
- Totaux journaliers
- Totaux hebdomadaires
- Barres de progression

**RecordsList.tpl** : Liste historique uniquement
- Affichage records récents
- Statuts et durées
- Navigation vers détails

**ClockInModal.tpl** : Modal pointage entrée uniquement
- Formulaire clock in
- Sélection type travail
- Géolocalisation

**ClockOutModal.tpl** : Modal pointage sortie uniquement
- Formulaire clock out
- Résumé session
- Confirmation action

#### validation/ - Pages validation manager ✨ **MVP 3.3**

**dashboard.tpl** : Dashboard manager avec batch validation
- Statistiques temps réel (5 colonnes)
- Liste enregistrements en attente avec checkboxes
- Actions de validation en lot (approve/reject all)
- Navigation vers liste complète

**list-all.tpl** : Page liste complète avec filtres avancés
- Interface de filtrage collapsible (statut, utilisateur, dates, anomalies)
- Tri multiple (date, utilisateur, statut)
- Affichage paginé avec statistiques
- Navigation vers détails enregistrements

**record-detail.tpl** : Détails enregistrement avec actions validation
- Informations complètes record
- Actions validation individuelles
- Affichage anomalies détectées
- Historique validation

#### components/ - Composants validation ✨ **MVP 3.3**

**ValidationActions.tpl** : Actions validation individuelles
- Boutons approve/reject/partial
- Modal commentaire
- Feedback utilisateur immédiat

**Messages.tpl** : Système de messages centralisé
- Affichage erreurs et succès
- Support multilingue
- Styles adaptatifs

#### pages/home.tpl
**Responsabilité unique** : Assemblage composants page d'accueil
- Inclusion composants
- Gestion état page
- Scripts spécifiques

### Assets/ - Ressources client

#### js/modules/ - JavaScript modulaire

**TimeclockModule.js** : Gestion timeclock côté client
- API calls clock in/out
- Gestion états
- Synchronisation données

**GPSModule.js** : Géolocalisation uniquement
- Récupération position
- Validation coordonnées  
- Gestion erreurs GPS

**UIModule.js** : Interface utilisateur
- Gestion modales
- Animations
- Interactions tactiles

**TimerModule.js** : Gestion temps temps réel
- Mise à jour durées
- Timers session
- Synchronisation horloge

## Flux de données

### Clock In
1. **UIModule** → Ouvre modal clock in
2. **GPSModule** → Récupère position si requise
3. **TimeclockModule** → Valide données, appel API
4. **HomeController** → Traite action via TimeclockService
5. **TimeclockService** → Logique métier, sauvegarde
6. **DataService** → Mise à jour cache, calculs
7. **Templates** → Affichage nouveau statut

### Clock Out  
1. **UIModule** → Ouvre modal clock out avec résumé
2. **TimerModule** → Affiche durée session temps réel
3. **TimeclockModule** → Validation et soumission
4. **HomeController** → Traite via TimeclockService
5. **TimeclockService** → Finalise record, calculs
6. **DataService** → Mise à jour résumés
7. **Templates** → Affichage statut déconnecté

### Validation Manager Dashboard ✨ **MVP 3.3**
1. **ValidationController** → dashboard() avec vérification permissions
2. **ValidationService** → getPendingValidations() avec enrichissement
3. **NotificationService** → getUnreadNotifications() pour manager
4. **dashboard.tpl** → Affichage statistiques + liste avec checkboxes
5. **JavaScript** → Gestion sélection multiple et batch actions
6. **AJAX** → Actions validation avec feedback immédiat

### Advanced Filtering ✨ **MVP 3.3**
1. **gotoFullList()** → Navigation vers page dédiée
2. **ValidationController** → listAll() avec traitement filtres
3. **ValidationService** → getFilteredRecords() avec SQL avancé
4. **list-all.tpl** → Interface filtres collapsible + résultats
5. **JavaScript** → Gestion filtres et navigation
6. **Backend** → Application filtres SQL avec sécurité

### Individual Validation ✨ **MVP 3.3**
1. **showRecordDetails()** → Navigation vers détail record
2. **ValidationController** → viewRecord() avec permissions
3. **ValidationService** → getRecordData() + detectAnomalies()
4. **record-detail.tpl** → Affichage avec ValidationActions
5. **AJAX** → validateRecord() avec commentaires
6. **Database** → Mise à jour validation_status + notifications

## Points d'extension

### Nouveaux types de pointage
- **TimeclockService** : Ajouter validation spécifique
- **ClockInModal** : Ajouter options interface
- **Constants** : Définir nouvelles constantes

### Nouvelles règles métier
- **TimeclockService** : Implémenter via interfaces
- **Validators/** : Nouveaux validateurs (future extension)
- **Constants** : Configuration paramètres

### Nouveaux affichages
- **components/** : Nouveaux composants atomiques
- **pages/** : Nouvelles compositions
- **css/** : Styles modulaires associés

### Extensions Validation ✨ **MVP 3.3**
- **ValidationService** : Nouveaux types d'anomalies via getAnomalyTypes()
- **NotificationService** : Nouveaux canaux notification (email, SMS)
- **ValidationConstants** : Nouvelles règles métier configurables
- **list-all.tpl** : Nouveaux critères de filtrage
- **dashboard.tpl** : Nouvelles métriques statistiques

### API Extensions ✨ **MVP 3.3**
- **api/validation.php** : Nouveaux endpoints REST
- **ValidationController** : Nouvelles actions manager
- **Filters** : Nouveaux types de filtres complexes
- **Exports** : Génération rapports validation

Cette architecture garantit que chaque modification reste isolée dans son domaine de responsabilité tout en supportant l'évolution vers des fonctionnalités avancées de validation et de gestion d'équipe.