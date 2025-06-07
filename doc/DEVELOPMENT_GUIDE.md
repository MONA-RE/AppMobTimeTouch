# Guide de développement AppMobTimeTouch

## Introduction

Ce guide décrit les bonnes pratiques pour développer et étendre le module AppMobTimeTouch après sa refactorisation modulaire.

## Architecture overview

### Principe de base
L'architecture suit le pattern **MVC + Services** avec séparation claire des responsabilités:

- **Controllers**: Orchestration et gestion des requêtes HTTP
- **Services**: Logique métier centralisée et réutilisable  
- **Models**: Couche d'accès aux données (classes Dolibarr existantes)
- **Utils**: Fonctions utilitaires et constantes
- **Assets**: Ressources statiques organisées
- **Templates**: Vue avec composants modulaires

### Flow de développement
```
HTTP Request → Controller → Service → Model → Database
                    ↓
               Template ← Utils ← Assets
```

## Conventions de développement

### 1. Naming conventions

#### Classes
```php
// Controllers: Suffixe "Controller"
class TimeclockController extends BaseController

// Services: Suffixe "Service"  
class ValidationService

// Utils: Pas de suffixe
class TimeHelper

// Constants: Suffixe "Constants"
class TimeclockConstants
```

#### Méthodes
```php
// Action controllers: verbes d'action
public function clockIn()
public function clockOut()

// Services: logique métier descriptive
public function validateClockInData()
public function calculateDuration()

// Utils: fonctions statiques descriptives
public static function formatDuration()
public static function parseTimestamp()
```

#### Variables
```php
// Variables: camelCase
$timeclockService
$activeSession
$todaySummary

// Constantes: UPPER_SNAKE_CASE
TIMECLOCK_STATUS_ACTIVE
TIMECLOCK_DEFAULT_MAX_HOURS
```

### 2. Structure des fichiers

#### Taille maximum
- **Controllers**: 200-300 lignes max
- **Services**: 250-400 lignes max
- **Utils**: 150-200 lignes max
- **Templates**: 100-200 lignes max

#### Organisation des méthodes
```php
class ExampleService 
{
    // 1. Propriétés privées
    private $db;
    private $configService;
    
    // 2. Constructeur
    public function __construct($db, $configService) 
    
    // 3. Méthodes publiques (API)
    public function mainMethod()
    public function secondaryMethod()
    
    // 4. Méthodes privées (helpers)
    private function helperMethod()
    private function anotherHelper()
}
```

### 3. Documentation du code

#### Docblocks obligatoires
```php
/**
 * Get active timeclock session for user
 * 
 * @param int $userId User ID from Dolibarr
 * @return array|null Session data or null if no active session
 * @throws Exception If database error occurs
 */
public function getActiveSession($userId) 
{
    // Implementation
}
```

#### Commentaires inline
```php
// CORRECTION: Handle timezone conversion for user preferences
$clockInTime = TimeHelper::parseTimestamp($this->db, $record->clock_in_time);

// TODO: Add caching layer for frequently accessed configurations
$config = $this->configService->getRequireLocation();

// WARNING: This method has side effects - updates database
$result = $timeclockrecord->clockOut($user, $location, $lat, $lon, $note);
```

## Ajouter une nouvelle fonctionnalité

### Étape 1: Analyser le besoin

1. **Identifier la couche concernée**:
   - Interface utilisateur → Template/Assets
   - Logique métier → Service
   - Manipulation de données → Model
   - Orchestration → Controller

2. **Vérifier l'impact sur l'existant**:
   - Modifications de base de données
   - Nouvelles permissions Dolibarr
   - Configuration additionnelle

### Étape 2: Développement backend

#### Créer un nouveau service
```php
<?php
// Services/BreakManagementService.php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
dol_include_once('/appmobtimetouch/class/timeclockbreak.class.php');

class BreakManagementService 
{
    private $db;
    private $configService;
    
    public function __construct($db, $configService) 
    {
        $this->db = $db;
        $this->configService = $configService;
    }
    
    /**
     * Start a break for active session
     * 
     * @param int $userId User ID
     * @param string $breakType Type of break (lunch, coffee, etc.)
     * @return int Break ID or error code
     */
    public function startBreak($userId, $breakType) 
    {
        // 1. Validate user has active session
        $timeclockService = new TimeclockService($this->db, $this->configService);
        $activeSession = $timeclockService->getActiveSession($userId);
        
        if (!$activeSession) {
            return -1; // No active session
        }
        
        // 2. Create break record
        $breakRecord = new TimeclockBreak($this->db);
        $result = $breakRecord->createBreak($activeSession['record']->id, $breakType);
        
        return $result;
    }
    
    /**
     * End current break
     * 
     * @param int $userId User ID
     * @return int Success/error code
     */
    public function endBreak($userId) 
    {
        // Implementation
    }
}
```

#### Étendre un contrôleur existant
```php
<?php
// Controllers/TimeclockController.php - Ajouter nouvelles méthodes

/**
 * Start break action
 */
public function startBreak() 
{
    if (!$this->user->rights->appmobtimetouch->timeclock->write) {
        accessforbidden();
    }
    
    $breakType = GETPOST('break_type', 'alpha');
    
    // Inject break service
    $breakService = new BreakManagementService($this->db, $this->configService);
    $result = $breakService->startBreak($this->user->id, $breakType);
    
    if ($result > 0) {
        $this->addMessage($this->langs->trans("BreakStarted"));
    } else {
        $this->addError($this->langs->trans("BreakStartError"));
    }
    
    // Redirect to avoid resubmission
    header('Location: ' . $_SERVER['PHP_SELF'] . '?break_started=1');
    exit;
}
```

#### Ajouter des constantes
```php
<?php
// Utils/Constants.php - Ajouter nouvelles constantes

// Break types
define('TIMECLOCK_BREAK_LUNCH', 'lunch');
define('TIMECLOCK_BREAK_COFFEE', 'coffee');
define('TIMECLOCK_BREAK_PERSONAL', 'personal');

// Break durations (minutes)
define('TIMECLOCK_DEFAULT_LUNCH_DURATION', 60);
define('TIMECLOCK_DEFAULT_COFFEE_DURATION', 15);
```

### Étape 3: Développement frontend

#### Créer un nouveau composant template
```html
<!-- tpl/components/break-manager.tpl -->
<?php if ($is_clocked_in && !$is_on_break): ?>
<div style="padding: 15px;">
  <ons-card>
    <div class="title">
      <h3><?php echo $langs->trans("BreakManagement"); ?></h3>
    </div>
    <div class="content">
      <ons-list>
        <ons-list-item tappable onclick="startBreak('lunch')">
          <div class="left">
            <ons-icon icon="md-restaurant" style="color: #FF9800;"></ons-icon>
          </div>
          <div class="center">
            <?php echo $langs->trans("LunchBreak"); ?>
          </div>
        </ons-list-item>
        
        <ons-list-item tappable onclick="startBreak('coffee')">
          <div class="left">
            <ons-icon icon="md-local-cafe" style="color: #8BC34A;"></ons-icon>
          </div>
          <div class="center">
            <?php echo $langs->trans("CoffeeBreak"); ?>
          </div>
        </ons-list-item>
      </ons-list>
    </div>
  </ons-card>
</div>
<?php endif; ?>
```

#### Ajouter JavaScript modulaire
```javascript
// Assets/js/break-manager.js

class BreakManager {
    constructor(apiConfig) {
        this.apiConfig = apiConfig;
        this.currentBreak = null;
    }
    
    /**
     * Start a break session
     * @param {string} breakType Type of break
     */
    async startBreak(breakType) {
        try {
            const response = await fetch('/api/timeclock.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.apiConfig.api_token
                },
                body: JSON.stringify({
                    action: 'start_break',
                    break_type: breakType,
                    user_id: this.apiConfig.user_id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.handleBreakStarted(result.break_id, breakType);
                ons.notification.toast('Break started', {timeout: 2000});
            } else {
                ons.notification.alert('Error starting break: ' + result.error);
            }
        } catch (error) {
            console.error('Break start error:', error);
            ons.notification.alert('Connection error');
        }
    }
    
    /**
     * End current break
     */
    async endBreak() {
        // Implementation
    }
    
    /**
     * Handle break started state
     */
    handleBreakStarted(breakId, breakType) {
        this.currentBreak = {
            id: breakId,
            type: breakType,
            startTime: Date.now()
        };
        
        // Update UI
        this.updateBreakStatus();
        
        // Start break timer
        this.startBreakTimer();
    }
}

// Global function for template usage
function startBreak(breakType) {
    if (window.breakManager) {
        window.breakManager.startBreak(breakType);
    }
}

// Initialize when DOM ready
ons.ready(() => {
    if (typeof appConfig !== 'undefined') {
        window.breakManager = new BreakManager(appConfig);
    }
});
```

#### Ajouter styles CSS
```css
/* Assets/css/timeclock-components.css - Ajouter styles break */

/* Break status indicators */
.break-status-active {
    background: linear-gradient(135deg, #fff3e0, #ffcc80);
    border-left: 4px solid #FF9800;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
}

.break-timer {
    font-size: 18px;
    font-weight: bold;
    color: #FF9800;
    text-align: center;
    animation: pulse 2s infinite;
}

/* Break type icons */
.break-lunch-icon {
    color: #FF9800;
}

.break-coffee-icon {
    color: #8BC34A;
}

.break-personal-icon {
    color: #2196F3;
}
```

### Étape 4: Configuration et données

#### Ajouter configuration
```php
<?php
// Services/ConfigService.php - Ajouter méthodes

/**
 * Get break management settings
 */
public function getBreakSettings() 
{
    return [
        'enabled' => $this->getValue('BREAK_MANAGEMENT_ENABLED', 1),
        'max_break_duration' => $this->getValue('MAX_BREAK_DURATION', 120),
        'auto_end_breaks' => $this->getValue('AUTO_END_BREAKS', 0),
        'break_types' => $this->getBreakTypes()
    ];
}

/**
 * Get configured break types
 */
private function getBreakTypes() 
{
    // Could be stored in database or configuration
    return [
        TIMECLOCK_BREAK_LUNCH => [
            'label' => 'Lunch',
            'default_duration' => TIMECLOCK_DEFAULT_LUNCH_DURATION,
            'icon' => 'md-restaurant'
        ],
        TIMECLOCK_BREAK_COFFEE => [
            'label' => 'Coffee',
            'default_duration' => TIMECLOCK_DEFAULT_COFFEE_DURATION,
            'icon' => 'md-local-cafe'
        ]
    ];
}
```

#### Migration de base de données
```sql
-- sql/llx_appmobtimetouch_timeclockbreak_v2.sql
-- Add break management enhancements

ALTER TABLE llx_timeclock_breaks 
ADD COLUMN break_category varchar(50) DEFAULT 'general',
ADD COLUMN auto_ended tinyint(1) DEFAULT 0,
ADD COLUMN expected_duration int(11) DEFAULT NULL;

-- Add indexes for performance
CREATE INDEX idx_timeclock_breaks_category ON llx_timeclock_breaks(break_category);
CREATE INDEX idx_timeclock_breaks_date ON llx_timeclock_breaks(break_start_time);
```

### Étape 5: Tests

#### Test unitaire du service
```php
<?php
// test/phpunit/BreakManagementServiceTest.php

class BreakManagementServiceTest extends PHPUnit\Framework\TestCase 
{
    private $db;
    private $configService;
    private $breakService;
    
    protected function setUp(): void 
    {
        // Setup test database and services
        $this->db = $this->createMock(DoliDB::class);
        $this->configService = $this->createMock(ConfigService::class);
        $this->breakService = new BreakManagementService($this->db, $this->configService);
    }
    
    public function testStartBreakWithActiveSession() 
    {
        // Mock active session
        $this->configService
            ->method('getBreakSettings')
            ->willReturn(['enabled' => 1]);
        
        // Test break start
        $result = $this->breakService->startBreak(1, TIMECLOCK_BREAK_LUNCH);
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testStartBreakWithoutActiveSession() 
    {
        // Test should fail without active session
        $result = $this->breakService->startBreak(999, TIMECLOCK_BREAK_LUNCH);
        
        $this->assertEquals(-1, $result);
    }
}
```

#### Test fonctionnel
```bash
# test/functional/break_management_test.php
<?php
// Test break management functionality

require_once '../../Controllers/TimeclockController.php';
require_once '../../Services/BreakManagementService.php';

// Test 1: Start break via controller
echo "Testing break start...\n";
$_POST['action'] = 'start_break';
$_POST['break_type'] = 'lunch';

// Simulate controller call
$controller = new TimeclockController($db, $user, $langs);
ob_start();
$controller->startBreak();
$output = ob_get_clean();

echo "Break start test completed\n";
```

### Étape 6: Documentation

#### Documenter la nouvelle API
```php
/**
 * Break Management API
 * 
 * Endpoints:
 * POST /api/timeclock.php?action=start_break
 * POST /api/timeclock.php?action=end_break
 * GET  /api/timeclock.php?action=break_status
 * 
 * Parameters:
 * - break_type: lunch|coffee|personal
 * - user_id: Current user ID
 * 
 * Returns:
 * {
 *   "success": true,
 *   "break_id": 123,
 *   "start_time": "2025-01-07 14:30:00"
 * }
 */
```

#### Mettre à jour la documentation utilisateur
```markdown
# doc/BREAK_MANAGEMENT.md

## Gestion des pauses

### Fonctionnalités
- Démarrage/arrêt des pauses
- Types de pauses configurables  
- Suivi automatique des durées
- Intégration aux résumés temporels

### Configuration
Les types de pauses sont configurables via le module de configuration:
- Administration → Modules → AppMobTimeTouch → Setup → Break Management

### API
Voir documentation technique pour intégration externe.
```

## Bonnes pratiques de maintenance

### 1. Gestion des erreurs

#### Logging standardisé
```php
// Toujours logger les erreurs importantes
dol_syslog("BreakManagementService::startBreak - Failed for user " . $userId . ": " . $error, LOG_ERR);

// Logger les informations de debug
dol_syslog("BreakManagementService::startBreak - Starting " . $breakType . " break for user " . $userId, LOG_DEBUG);
```

#### Gestion des exceptions
```php
public function startBreak($userId, $breakType) 
{
    try {
        // Validation
        if (!$this->validateBreakType($breakType)) {
            throw new InvalidArgumentException("Invalid break type: " . $breakType);
        }
        
        // Business logic
        $result = $this->createBreakRecord($userId, $breakType);
        
        return $result;
        
    } catch (Exception $e) {
        dol_syslog("BreakManagementService::startBreak - Exception: " . $e->getMessage(), LOG_ERR);
        return -1;
    }
}
```

### 2. Performance

#### Utiliser les caches
```php
// Cache pour les configurations fréquemment accédées
private function getBreakTypes() 
{
    static $breakTypesCache = null;
    
    if ($breakTypesCache === null) {
        $breakTypesCache = $this->loadBreakTypesFromDB();
    }
    
    return $breakTypesCache;
}
```

#### Optimiser les requêtes
```php
// Éviter les requêtes N+1
public function getBreaksForPeriod($userId, $startDate, $endDate) 
{
    // Une seule requête avec JOIN plutôt que des requêtes multiples
    $sql = "SELECT b.*, t.label as type_label 
            FROM " . MAIN_DB_PREFIX . "timeclock_breaks b
            LEFT JOIN " . MAIN_DB_PREFIX . "timeclock_types t ON b.break_type = t.code
            WHERE b.fk_user = " . (int) $userId . "
            AND b.break_start_time BETWEEN '" . $this->db->escape($startDate) . "' 
            AND '" . $this->db->escape($endDate) . "'";
    
    return $this->db->query($sql);
}
```

### 3. Sécurité

#### Validation des entrées
```php
/**
 * Validate break type
 */
private function validateBreakType($breakType) 
{
    $allowedTypes = [
        TIMECLOCK_BREAK_LUNCH,
        TIMECLOCK_BREAK_COFFEE,
        TIMECLOCK_BREAK_PERSONAL
    ];
    
    return in_array($breakType, $allowedTypes);
}

/**
 * Validate user permissions
 */
private function canUserManageBreaks($user) 
{
    return !empty($user->rights->appmobtimetouch->timeclock->write);
}
```

#### Échapper les données
```php
// Toujours échapper les données utilisateur
$location = $this->db->escape(GETPOST('location', 'alphanohtml'));
$note = $this->db->escape(GETPOST('note', 'restricthtml'));
```

### 4. Tests automatisés

#### Structure des tests
```
test/
├── unit/                  # Tests unitaires
│   ├── Services/
│   ├── Utils/
│   └── Controllers/
├── integration/           # Tests d'intégration
│   ├── API/
│   └── Database/
├── functional/            # Tests fonctionnels
│   ├── UserJourneys/
│   └── Scenarios/
└── fixtures/              # Données de test
    ├── users.json
    └── timeclock_data.json
```

#### Tests de régression
```bash
#!/bin/bash
# test/regression_suite.sh

echo "Running regression tests..."

# Test API endpoints
php test/api_timeclock_test.php

# Test core functionality  
cd test/phpunit
phpunit TimeclockServicesTest.php
phpunit BreakManagementTest.php

# Test UI (if Selenium available)
# ./selenium_tests.sh

echo "Regression tests completed"
```

## Débogage et monitoring

### 1. Outils de debug

#### Debug dans les services
```php
// Activer le debug pour un service spécifique
class TimeclockService 
{
    private $debug = false;
    
    public function __construct($db, $configService, $debug = false) 
    {
        $this->debug = $debug;
        // ...
    }
    
    private function debugLog($message) 
    {
        if ($this->debug) {
            dol_syslog("TimeclockService DEBUG: " . $message, LOG_DEBUG);
        }
    }
}
```

#### Profiling JavaScript
```javascript
// Assets/js/timeclock-app.js - Debug mode
class TimeclockApp {
    constructor(config) {
        this.debug = config.debug || false;
        this.performanceStart = Date.now();
    }
    
    debugLog(message, data = null) {
        if (this.debug) {
            console.log(`[TimeclockApp] ${message}`, data);
        }
    }
    
    profileMethod(methodName, callback) {
        if (this.debug) {
            const start = performance.now();
            const result = callback();
            const end = performance.now();
            console.log(`[Profile] ${methodName}: ${end - start}ms`);
            return result;
        }
        return callback();
    }
}
```

### 2. Monitoring de production

#### Métriques personnalisées
```php
// Utils/MetricsCollector.php
class MetricsCollector 
{
    public static function trackTimeclockAction($action, $userId, $duration = null) 
    {
        $metrics = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'user_id' => $userId,
            'duration' => $duration,
            'memory_usage' => memory_get_usage(true),
            'response_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        ];
        
        // Log to metrics file or external service
        file_put_contents(
            '/var/log/timeclock_metrics.json', 
            json_encode($metrics) . "\n", 
            FILE_APPEND | LOCK_EX
        );
    }
}

// Usage dans les contrôleurs
public function clockIn() 
{
    $start = microtime(true);
    
    // ... logic ...
    
    MetricsCollector::trackTimeclockAction(
        'clock_in', 
        $this->user->id, 
        microtime(true) - $start
    );
}
```

#### Health checks
```php
// api/health.php
<?php
header('Content-Type: application/json');

$health = [
    'status' => 'OK',
    'timestamp' => date('c'),
    'version' => '1.0',
    'checks' => []
];

// Check database
try {
    $db = new DoliDB();
    $db->query("SELECT 1");
    $health['checks']['database'] = 'OK';
} catch (Exception $e) {
    $health['checks']['database'] = 'ERROR';
    $health['status'] = 'ERROR';
}

// Check timeclock tables
try {
    $sql = "SELECT COUNT(*) FROM " . MAIN_DB_PREFIX . "timeclock_records";
    $result = $db->query($sql);
    $health['checks']['timeclock_tables'] = 'OK';
} catch (Exception $e) {
    $health['checks']['timeclock_tables'] = 'ERROR';
    $health['status'] = 'ERROR';
}

http_response_code($health['status'] === 'OK' ? 200 : 500);
echo json_encode($health, JSON_PRETTY_PRINT);
```

## Évolutions futures

### Roadmap technique
1. **Cache layer**: Redis/Memcached pour les configurations
2. **API versioning**: Support de multiples versions d'API
3. **WebSocket**: Mises à jour temps réel
4. **PWA**: Application mobile progressive
5. **Microservices**: Découpage en services indépendants

### Migration patterns
```php
// Pattern pour les futures migrations
interface MigrationInterface 
{
    public function up();
    public function down();
    public function getVersion();
}

class AddBreakCategoriesMigration implements MigrationInterface 
{
    public function up() 
    {
        // Apply migration
    }
    
    public function down() 
    {
        // Rollback migration  
    }
    
    public function getVersion() 
    {
        return '1.1.0';
    }
}
```

---

**Version**: 1.0  
**Auteur**: Architecture refactoring  
**Date**: 2025-01-07