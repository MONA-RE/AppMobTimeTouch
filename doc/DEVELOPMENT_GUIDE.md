# Guide de Développement - Architecture SOLID

## Principes de Développement Obligatoires

Toute nouvelle fonctionnalité DOIT respecter strictement les principes SOLID et suivre l'architecture modulaire établie.

## Points d'Entrée Multiples - CRITIQUE

⚠️ **ATTENTION** : Le module a **DEUX points d'entrée** qui partagent les templates :
- `index.php` : Interface mobile OnsenUI (templates avec valeurs par défaut)
- `home.php` : Logique métier (templates avec données réelles)

**Règle OBLIGATOIRE** : Tout helper/variable utilisé dans les templates DOIT être disponible dans **les DEUX fichiers**.

📖 **Voir documentation complète** : [INDEX_HOME_COMPATIBILITY.md](INDEX_HOME_COMPATIBILITY.md)

## Structure de Développement

### Ajout de Nouvelles Fonctionnalités

#### 1. Analyse SOLID Préalable

Avant tout développement, répondre à ces questions :

**Single Responsibility Principle (SRP)**
- Cette fonctionnalité a-t-elle une seule responsabilité claire ?
- Peut-elle être isolée dans un module spécifique ?
- Évite-t-elle le mélange de préoccupations ?

**Open/Closed Principle (OCP)**
- Puis-je l'ajouter sans modifier le code existant ?
- Utilise-t-elle les interfaces existantes ?
- Est-elle extensible pour futures évolutions ?

**Liskov Substitution Principle (LSP)**
- Les nouvelles implémentations respectent-elles les contrats ?
- Sont-elles substituables sans casser l'existant ?

**Interface Segregation Principle (ISP)**
- Ai-je besoin de nouvelles interfaces spécialisées ?
- Les interfaces existantes sont-elles trop larges ?
- Chaque dépendance est-elle minimale ?

**Dependency Inversion Principle (DIP)**
- Dépend-elle d'abstractions plutôt que de concrétions ?
- Les dépendances sont-elles injectées ?
- Le couplage est-il minimal ?

#### 2. Processus de Développement Incrémental

**Étape 1** : Création interface (si nécessaire)
```php
// Services/Interfaces/NewFeatureServiceInterface.php
interface NewFeatureServiceInterface 
{
    public function specificMethod(array $params): mixed;
}
```

**Étape 2** : Implémentation service
```php
// Services/NewFeatureService.php  
class NewFeatureService implements NewFeatureServiceInterface 
{
    // Responsabilité unique : logique métier de la nouvelle fonctionnalité
}
```

**Étape 3** : Extension contrôleur (si nécessaire)
```php
// Controllers/NewFeatureController.php extends BaseController
// OU extension HomeController avec nouvelle action
```

**Étape 4** : Composants UI atomiques
```php
// Views/components/NewFeatureComponent.tpl
// Responsabilité unique : affichage de la nouvelle fonctionnalité
```

**Étape 5** : Tests et validation
- Tests unitaires service
- Tests intégration contrôleur  
- Tests UI composants

### Exemples Concrets

#### Ajout Gestion des Pauses

**Analyse SOLID** :
- **SRP** : Service dédié gestion pauses
- **OCP** : Extension via interface BreakServiceInterface
- **LSP** : Implémentation substituable
- **ISP** : Interface spécifique aux pauses
- **DIP** : Injection dans contrôleurs existants

**Implémentation** :

```php
// 1. Interface spécialisée (ISP)
interface BreakServiceInterface 
{
    public function startBreak(int $recordId, string $reason): int;
    public function endBreak(int $breakId): int;
    public function getActiveBreak(int $recordId): ?TimeclockBreak;
}

// 2. Service métier (SRP + DIP)
class BreakService implements BreakServiceInterface 
{
    private $db;
    
    public function __construct($db) 
    {
        $this->db = $db;
    }
    
    public function startBreak(int $recordId, string $reason): int 
    {
        // Logique unique : démarrage pause
        $break = new TimeclockBreak($this->db);
        return $break->create([
            'fk_timeclock_record' => $recordId,
            'start_time' => dol_now(),
            'reason' => $reason,
            'status' => TimeclockConstants::STATUS_IN_PROGRESS
        ]);
    }
    
    // ... autres méthodes
}

// 3. Extension contrôleur (OCP)
class HomeController extends BaseController 
{
    private BreakServiceInterface $breakService;
    
    public function __construct(
        // ... paramètres existants
        BreakServiceInterface $breakService
    ) {
        // ... initialisation existante
        $this->breakService = $breakService;
    }
    
    // Extension actions sans modification existant
    private function handleAction(string $action): array 
    {
        return match($action) {
            'clockin' => $this->handleClockIn(),
            'clockout' => $this->handleClockOut(),
            'startbreak' => $this->handleStartBreak(), // NOUVEAU
            'endbreak' => $this->handleEndBreak(),     // NOUVEAU
            default => ['error' => 1, 'errors' => ["Unknown action: $action"]]
        };
    }
    
    private function handleStartBreak(): array 
    {
        $activeRecord = $this->timeclockService->getActiveRecord($this->user->id);
        if (!$activeRecord) {
            throw new RuntimeException("No active record for break");
        }
        
        $reason = GETPOST('break_reason', 'alphanohtml');
        $this->breakService->startBreak($activeRecord->id, $reason);
        
        header('Location: '.$_SERVER['PHP_SELF'].'?break_start_success=1');
        exit;
    }
}

// 4. Composant UI atomique (SRP)
// Views/components/BreakControl.tpl
<div class="break-control" style="margin-top: 15px;">
    <?php if ($active_break): ?>
        <!-- Pause en cours -->
        <ons-button onclick="showEndBreakModal()" style="background-color: #FF9800;">
            <ons-icon icon="md-play-arrow"></ons-icon>
            <?php echo $langs->trans("EndBreak"); ?>
        </ons-button>
    <?php elseif ($is_clocked_in): ?>
        <!-- Possibilité de prendre pause -->
        <ons-button onclick="showStartBreakModal()" style="background-color: #2196F3;">
            <ons-icon icon="md-pause"></ons-icon>
            <?php echo $langs->trans("StartBreak"); ?>
        </ons-button>
    <?php endif; ?>
</div>

// 5. Integration dans StatusCard (composition)
// Views/components/StatusCard.tpl
<div class="content">
    <!-- ... contenu existant ... -->
    <?php include 'components/BreakControl.tpl'; ?>
</div>
```

#### Ajout Système de Notifications

**Analyse SOLID** :
- **SRP** : Service notifications isolé
- **OCP** : Types notifications extensibles
- **LSP** : Différents canaux substituables
- **ISP** : Interfaces par type notification
- **DIP** : Injection service notification

```php
// 1. Interfaces spécialisées (ISP)
interface NotificationServiceInterface 
{
    public function send(string $type, array $data, User $user): bool;
}

interface NotificationChannelInterface 
{
    public function deliver(string $message, User $user): bool;
}

// 2. Service principal (SRP + DIP)
class NotificationService implements NotificationServiceInterface 
{
    private array $channels;
    
    public function __construct(array $channels = []) 
    {
        $this->channels = $channels; // Injection dépendances
    }
    
    public function send(string $type, array $data, User $user): bool 
    {
        $message = $this->buildMessage($type, $data);
        
        foreach ($this->channels as $channel) {
            $channel->deliver($message, $user);
        }
        
        return true;
    }
    
    private function buildMessage(string $type, array $data): string 
    {
        return match($type) {
            'overtime_alert' => "Attention: vous dépassez {$data['hours']}h de travail",
            'clockout_reminder' => "N'oubliez pas de pointer votre sortie",
            default => "Notification: $type"
        };
    }
}

// 3. Canaux notification (LSP)
class EmailNotificationChannel implements NotificationChannelInterface 
{
    public function deliver(string $message, User $user): bool 
    {
        // Logique envoi email
        return mail($user->email, "Timeclock Alert", $message);
    }
}

class ToastNotificationChannel implements NotificationChannelInterface 
{
    public function deliver(string $message, User $user): bool 
    {
        // Stockage pour affichage toast
        $_SESSION['timeclock_notifications'][] = $message;
        return true;
    }
}

// 4. Integration service timeclock (OCP)
class TimeclockService implements TimeclockServiceInterface 
{
    private NotificationServiceInterface $notificationService;
    
    public function __construct(
        $db, 
        DataServiceInterface $dataService,
        NotificationServiceInterface $notificationService
    ) {
        // ... initialisation existante
        $this->notificationService = $notificationService;
    }
    
    public function clockIn(User $user, array $params): int 
    {
        $result = /* ... logique existante ... */;
        
        // Extension : notification sans modification logique existante
        $this->notificationService->send('clockin_success', [
            'time' => date('H:i'),
            'location' => $params['location'] ?? ''
        ], $user);
        
        return $result;
    }
}
```

### Règles de Modification

#### Modification Code Existant (INTERDIT)

**❌ Ne JAMAIS** :
- Modifier les interfaces existantes
- Ajouter responsabilités aux classes existantes
- Créer dépendances directes vers nouvelles classes
- Mélanger logique métier et présentation

#### Extension Code Existant (AUTORISÉ)

**✅ TOUJOURS** :
- Créer nouvelles interfaces pour nouvelles fonctionnalités
- Étendre par héritage ou composition
- Injecter nouvelles dépendances via constructeur
- Ajouter nouveaux composants UI atomiques

### Tests Obligatoires

#### Tests Unitaires Services

```php
// test/phpunit/Services/NewFeatureServiceTest.php
class NewFeatureServiceTest extends PHPUnit\Framework\TestCase 
{
    private $mockDb;
    private NewFeatureService $service;
    
    protected function setUp(): void 
    {
        $this->mockDb = $this->createMock(DoliDB::class);
        $this->service = new NewFeatureService($this->mockDb);
    }
    
    public function testSpecificMethodWithValidParams(): void 
    {
        $params = ['valid' => 'data'];
        $result = $this->service->specificMethod($params);
        
        $this->assertGreaterThan(0, $result);
    }
    
    public function testSpecificMethodWithInvalidParams(): void 
    {
        $this->expectException(InvalidArgumentException::class);
        $this->service->specificMethod([]);
    }
}
```

#### Tests Intégration Contrôleurs

```php
// test/phpunit/Controllers/HomeControllerTest.php  
class HomeControllerTest extends PHPUnit\Framework\TestCase 
{
    public function testNewActionWithMockedServices(): void 
    {
        $mockServices = $this->createMockServices();
        $controller = new HomeController(
            $this->mockDb,
            $this->mockUser, 
            $this->mockLangs,
            $this->mockConf,
            ...$mockServices
        );
        
        $_POST['action'] = 'newaction';
        $result = $controller->index();
        
        $this->assertArrayHasKey('messages', $result);
    }
}
```

#### Tests UI Composants

```bash
# Test rendu template
php -l Views/components/NewComponent.tpl

# Test intégration  
curl -s http://localhost/appmobtimetouch/home.php | grep "new-component"

# Test JavaScript si applicable
npm test -- components/NewComponent.test.js
```

### Documentation Obligatoire

#### Pour Chaque Nouveau Service

```php
/**
 * Service [NOM] - Responsabilité unique : [DESCRIPTION]
 * 
 * Principes SOLID respectés :
 * - SRP : [Comment le SRP est respecté]
 * - OCP : [Comment l'extension est possible]  
 * - LSP : [Comment les substitutions fonctionnent]
 * - ISP : [Interfaces spécialisées utilisées]
 * - DIP : [Dépendances injectées]
 * 
 * @example
 * $service = new [NOM]Service($db, $dependency);
 * $result = $service->method($params);
 */
```

#### Pour Chaque Nouveau Composant

```php
<?php
/**
 * Composant [NOM] - Responsabilité unique : [DESCRIPTION]
 * 
 * Props attendues :
 * - $variable1 : Description et type
 * - $variable2 : Description et type
 * 
 * Dépendances :
 * - Composant1.tpl : Pour [raison]
 * - Service X : Pour [données]
 * 
 * Tests :
 * - Affichage avec données valides
 * - Gestion cas erreur
 * - Responsive design
 */
?>
```

### Checklist Validation

Avant chaque commit, vérifier :

#### ✅ Compatibilité Points d'Entrée
- [ ] Nouveaux helpers inclus dans index.php ET home.php
- [ ] Variables template initialisées dans les DEUX fichiers  
- [ ] Tests sur `/index.php` ET `/home.php` sans erreur
- [ ] Template fonctionne depuis les deux points d'entrée

#### ✅ Conformité SOLID
- [ ] Chaque classe a une responsabilité unique
- [ ] Extensions possibles sans modification
- [ ] Substitutions respectent les contrats  
- [ ] Interfaces spécialisées et minimales
- [ ] Dépendances vers abstractions

#### ✅ Tests Complets
- [ ] Tests unitaires services (>80% couverture)
- [ ] Tests intégration contrôleurs
- [ ] Tests UI composants
- [ ] Tests non-régression existant

#### ✅ Documentation
- [ ] Interfaces documentées
- [ ] Services avec exemples usage
- [ ] Composants avec props et dépendances
- [ ] COMPONENT_MAP.md mis à jour

#### ✅ Performance
- [ ] Pas de requêtes N+1
- [ ] Cache utilisé si approprié
- [ ] Ressources minimales chargées
- [ ] Pas de duplication logique

Cette approche garantit une base de code maintenir, évolutive et robuste selon les principes SOLID.