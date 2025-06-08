# SPRINT 2 - Validation et Workflow Manager

**Durée estimée :** 2 semaines  
**Objectif :** Implémentation du processus de validation manager avec architecture SOLID

## 📋 Vue d'ensemble

### User Stories Principales
- **US-1** : En tant que manager, je veux valider les temps de mes équipes
- **US-2** : En tant qu'employé, je veux voir le statut de validation de mes temps  
- **US-3** : En tant que manager, je veux être alerté des anomalies

### Prérequis
- ✅ Sprint 1 complété (architecture SOLID de base)
- ✅ Architecture Controllers/Services/Views en place
- ✅ Interfaces et injection de dépendances fonctionnelles

## 🏗️ Architecture SOLID pour Sprint 2

### Nouveaux Composants à Créer

```
Sprint2/
├── Controllers/
│   └── ValidationController.php     # Logique validation manager (SRP)
├── Services/
│   ├── ValidationService.php        # Business logic validation (SRP)
│   ├── NotificationService.php      # Gestion notifications (SRP) 
│   └── Interfaces/
│       ├── ValidationServiceInterface.php  # Contrat validation (ISP)
│       └── NotificationServiceInterface.php # Contrat notifications (ISP)
├── Views/
│   ├── validation/                   # Pages validation manager
│   │   ├── dashboard.tpl            # Dashboard manager
│   │   ├── pending-list.tpl         # Liste temps à valider
│   │   └── validation-form.tpl      # Formulaire validation
│   └── components/                   # Composants modulaires (SRP)
│       ├── ValidationStatus.tpl     # Statut validation employé
│       ├── ManagerAlert.tpl         # Alertes manager
│       ├── ValidationActions.tpl    # Actions validation
│       └── AnomalyCard.tpl         # Carte anomalie
├── Constants/
│   └── ValidationConstants.php      # Constantes workflow (SRP)
└── api/
    └── validation.php               # API REST validation
```

---

## 🎯 ÉTAPE 1 : Architecture Foundation (Jour 1-2)

### 1.1 Constants et Configuration

**Fichier :** `Constants/ValidationConstants.php`

```php
<?php
/**
 * Constants de validation - Responsabilité unique : Configuration workflow
 * 
 * Respecte le principe SRP : Seule responsabilité la configuration des validations
 */

class ValidationConstants extends TimeclockConstants 
{
    // Statuts de validation
    const VALIDATION_PENDING = 0;
    const VALIDATION_APPROVED = 1; 
    const VALIDATION_REJECTED = 2;
    const VALIDATION_PARTIAL = 3;
    
    // Types d'anomalies
    const ANOMALY_OVERTIME = 'overtime';
    const ANOMALY_MISSING_CLOCKOUT = 'missing_clockout';
    const ANOMALY_LONG_BREAK = 'long_break';
    const ANOMALY_LOCATION_MISMATCH = 'location_mismatch';
    
    // Niveaux d'alerte
    const ALERT_INFO = 'info';
    const ALERT_WARNING = 'warning';
    const ALERT_CRITICAL = 'critical';
    
    // Configuration workflow
    const AUTO_APPROVE_THRESHOLD = 'VALIDATION_AUTO_APPROVE_HOURS'; // 8h
    const VALIDATION_DEADLINE_DAYS = 'VALIDATION_DEADLINE_DAYS';     // 3 jours
    const MANAGER_NOTIFICATION_ENABLED = 'VALIDATION_MANAGER_NOTIFY'; // 1
    
    /**
     * Correspondance statuts → labels
     */
    public static function getValidationStatuses(): array 
    {
        return [
            self::VALIDATION_PENDING => 'ValidationPending',
            self::VALIDATION_APPROVED => 'ValidationApproved', 
            self::VALIDATION_REJECTED => 'ValidationRejected',
            self::VALIDATION_PARTIAL => 'ValidationPartial'
        ];
    }
    
    /**
     * Types d'anomalies avec seuils
     */
    public static function getAnomalyTypes(): array 
    {
        return [
            self::ANOMALY_OVERTIME => ['threshold' => 8, 'level' => self::ALERT_WARNING],
            self::ANOMALY_MISSING_CLOCKOUT => ['threshold' => 0, 'level' => self::ALERT_CRITICAL],
            self::ANOMALY_LONG_BREAK => ['threshold' => 90, 'level' => self::ALERT_INFO],
            self::ANOMALY_LOCATION_MISMATCH => ['threshold' => 0, 'level' => self::ALERT_WARNING]
        ];
    }
}
```

### 1.2 Interfaces Services (ISP)

**Fichier :** `Services/Interfaces/ValidationServiceInterface.php`

```php
<?php
/**
 * Interface ValidationService - Ségrégation Interface (ISP)
 * Contrat spécifique aux opérations de validation uniquement
 */

interface ValidationServiceInterface 
{
    /**
     * Récupérer les temps en attente de validation pour un manager
     */
    public function getPendingValidations(int $managerId): array;
    
    /**
     * Valider un temps de travail
     */
    public function validateRecord(int $recordId, int $validatorId, string $action, ?string $comment = null): bool;
    
    /**
     * Valider en lot plusieurs enregistrements
     */
    public function batchValidate(array $recordIds, int $validatorId, string $action): array;
    
    /**
     * Détecter les anomalies dans les temps
     */
    public function detectAnomalies(int $userId, string $period): array;
    
    /**
     * Récupérer le statut de validation d'un enregistrement
     */
    public function getValidationStatus(int $recordId): array;
    
    /**
     * Vérifier si un utilisateur peut valider un enregistrement
     */
    public function canValidate(int $userId, int $recordId): bool;
}
```

**Fichier :** `Services/Interfaces/NotificationServiceInterface.php`

```php
<?php
/**
 * Interface NotificationService - Ségrégation Interface (ISP)
 * Contrat spécifique aux notifications uniquement
 */

interface NotificationServiceInterface 
{
    /**
     * Envoyer notification de validation en attente
     */
    public function notifyPendingValidation(int $managerId, array $records): bool;
    
    /**
     * Notifier employé du statut de validation
     */
    public function notifyValidationStatus(int $userId, int $recordId, string $status): bool;
    
    /**
     * Alerter manager d'une anomalie
     */
    public function alertAnomaly(int $managerId, string $anomalyType, array $data): bool;
    
    /**
     * Récupérer les notifications non lues
     */
    public function getUnreadNotifications(int $userId): array;
    
    /**
     * Marquer notification comme lue
     */
    public function markAsRead(int $notificationId): bool;
}
```

---

## 🎯 ÉTAPE 2 : Services Implementation (Jour 3-4)

### 2.1 ValidationService (DIP + SRP)

**Fichier :** `Services/ValidationService.php`

```php
<?php
/**
 * Service Validation - Responsabilité unique : Logique métier validation
 * 
 * Respecte le principe SRP : Seule responsabilité la gestion des validations
 * Respecte le principe DIP : Dépend d'abstractions (DataServiceInterface)
 */

class ValidationService implements ValidationServiceInterface 
{
    private DoliDB $db;
    private DataServiceInterface $dataService;
    private NotificationServiceInterface $notificationService;
    
    public function __construct(
        DoliDB $db,
        DataServiceInterface $dataService,
        NotificationServiceInterface $notificationService
    ) {
        $this->db = $db;
        $this->dataService = $dataService;
        $this->notificationService = $notificationService;
    }
    
    /**
     * Récupérer les temps en attente de validation
     */
    public function getPendingValidations(int $managerId): array 
    {
        // 1. Récupérer les équipes du manager
        $teamMembers = $this->getTeamMembers($managerId);
        
        if (empty($teamMembers)) {
            return [];
        }
        
        // 2. Récupérer les enregistrements non validés
        $sql = "SELECT r.* FROM " . MAIN_DB_PREFIX . "timeclock_records r";
        $sql .= " WHERE r.fk_user IN (" . implode(',', array_map('intval', $teamMembers)) . ")";
        $sql .= " AND r.validation_status = " . ValidationConstants::VALIDATION_PENDING;
        $sql .= " AND r.status = " . TimeclockConstants::STATUS_COMPLETED;
        $sql .= " ORDER BY r.clock_in_time DESC";
        
        $result = $this->db->query($sql);
        $records = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $record = new TimeclockRecord($this->db);
                $record->fetch($obj->rowid);
                
                // 3. Enrichir avec infos validation
                $record->validation_info = $this->getValidationInfo($record->id);
                $record->anomalies = $this->detectRecordAnomalies($record);
                
                $records[] = $record;
            }
            $this->db->free($result);
        }
        
        return $records;
    }
    
    /**
     * Valider un enregistrement de temps
     */
    public function validateRecord(int $recordId, int $validatorId, string $action, ?string $comment = null): bool 
    {
        // 1. Vérifier permissions
        if (!$this->canValidate($validatorId, $recordId)) {
            throw new InvalidArgumentException("Insufficient permissions to validate record");
        }
        
        // 2. Charger l'enregistrement
        $record = new TimeclockRecord($this->db);
        if ($record->fetch($recordId) <= 0) {
            throw new InvalidArgumentException("Record not found");
        }
        
        // 3. Déterminer nouveau statut
        $newStatus = match($action) {
            'approve' => ValidationConstants::VALIDATION_APPROVED,
            'reject' => ValidationConstants::VALIDATION_REJECTED,
            'partial' => ValidationConstants::VALIDATION_PARTIAL,
            default => throw new InvalidArgumentException("Invalid validation action")
        };
        
        // 4. Mettre à jour l'enregistrement
        $sql = "UPDATE " . MAIN_DB_PREFIX . "timeclock_records SET";
        $sql .= " validation_status = " . ((int) $newStatus);
        $sql .= ", validated_by = " . ((int) $validatorId);
        $sql .= ", validated_date = '" . $this->db->escape(dol_now('Y-m-d H:i:s')) . "'";
        if ($comment) {
            $sql .= ", validation_comment = '" . $this->db->escape($comment) . "'";
        }
        $sql .= " WHERE rowid = " . ((int) $recordId);
        
        $result = $this->db->query($sql);
        
        if ($result) {
            // 5. Notifier l'employé
            $this->notificationService->notifyValidationStatus(
                $record->fk_user, 
                $recordId, 
                $action
            );
            
            // 6. Log de l'action
            dol_syslog("ValidationService: Record $recordId validated as $action by user $validatorId", LOG_INFO);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Validation en lot
     */
    public function batchValidate(array $recordIds, int $validatorId, string $action): array 
    {
        $results = [];
        
        foreach ($recordIds as $recordId) {
            try {
                $results[$recordId] = $this->validateRecord($recordId, $validatorId, $action);
            } catch (Exception $e) {
                $results[$recordId] = false;
                dol_syslog("ValidationService: Batch validation failed for record $recordId: " . $e->getMessage(), LOG_ERR);
            }
        }
        
        return $results;
    }
    
    /**
     * Détecter les anomalies dans une période
     */
    public function detectAnomalies(int $userId, string $period = 'week'): array 
    {
        $anomalies = [];
        $records = $this->dataService->getUserRecordsByPeriod($userId, $period);
        
        foreach ($records as $record) {
            $recordAnomalies = $this->detectRecordAnomalies($record);
            if (!empty($recordAnomalies)) {
                $anomalies[] = [
                    'record_id' => $record->id,
                    'date' => $record->clock_in_time,
                    'anomalies' => $recordAnomalies
                ];
            }
        }
        
        return $anomalies;
    }
    
    /**
     * Détecter anomalies pour un enregistrement spécifique
     */
    private function detectRecordAnomalies(TimeclockRecord $record): array 
    {
        $anomalies = [];
        $anomalyTypes = ValidationConstants::getAnomalyTypes();
        
        // Overtime
        if ($record->work_duration > ($anomalyTypes[ValidationConstants::ANOMALY_OVERTIME]['threshold'] * 60)) {
            $anomalies[] = [
                'type' => ValidationConstants::ANOMALY_OVERTIME,
                'level' => ValidationConstants::ALERT_WARNING,
                'message' => 'Overtime detected: ' . TimeHelper::convertSecondsToReadableTime($record->work_duration * 60)
            ];
        }
        
        // Missing clock out
        if (empty($record->clock_out_time) && $record->status == TimeclockConstants::STATUS_IN_PROGRESS) {
            $anomalies[] = [
                'type' => ValidationConstants::ANOMALY_MISSING_CLOCKOUT,
                'level' => ValidationConstants::ALERT_CRITICAL,
                'message' => 'Missing clock out'
            ];
        }
        
        // Long break
        if ($record->break_duration > $anomalyTypes[ValidationConstants::ANOMALY_LONG_BREAK]['threshold']) {
            $anomalies[] = [
                'type' => ValidationConstants::ANOMALY_LONG_BREAK,
                'level' => ValidationConstants::ALERT_INFO,
                'message' => 'Extended break: ' . $record->break_duration . ' minutes'
            ];
        }
        
        return $anomalies;
    }
    
    /**
     * Vérifier permissions de validation
     */
    public function canValidate(int $userId, int $recordId): bool 
    {
        global $user;
        
        // 1. Vérifier permission générale
        if (!$user->rights->timeclock->validate) {
            return false;
        }
        
        // 2. Récupérer l'enregistrement
        $record = new TimeclockRecord($this->db);
        if ($record->fetch($recordId) <= 0) {
            return false;
        }
        
        // 3. Vérifier si c'est le manager de l'utilisateur
        return $this->isManager($userId, $record->fk_user);
    }
    
    /**
     * Vérifier relation manager-employé
     */
    private function isManager(int $managerId, int $employeeId): bool 
    {
        // Logique selon structure organisationnelle Dolibarr
        // À adapter selon configuration client
        $sql = "SELECT COUNT(*) as count FROM " . MAIN_DB_PREFIX . "user";
        $sql .= " WHERE rowid = " . ((int) $employeeId);
        $sql .= " AND fk_user = " . ((int) $managerId); // ou autre champ selon structure
        
        $result = $this->db->query($sql);
        if ($result) {
            $obj = $this->db->fetch_object($result);
            $this->db->free($result);
            return $obj->count > 0;
        }
        
        return false;
    }
    
    /**
     * Récupérer membres équipe d'un manager
     */
    private function getTeamMembers(int $managerId): array 
    {
        $teamIds = [];
        
        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "user";
        $sql .= " WHERE fk_user = " . ((int) $managerId);
        $sql .= " AND statut = 1"; // Actif
        
        $result = $this->db->query($sql);
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $teamIds[] = $obj->rowid;
            }
            $this->db->free($result);
        }
        
        return $teamIds;
    }
    
    /**
     * Informations de validation d'un enregistrement
     */
    public function getValidationStatus(int $recordId): array 
    {
        $sql = "SELECT validation_status, validated_by, validated_date, validation_comment";
        $sql .= " FROM " . MAIN_DB_PREFIX . "timeclock_records";
        $sql .= " WHERE rowid = " . ((int) $recordId);
        
        $result = $this->db->query($sql);
        if ($result) {
            $obj = $this->db->fetch_object($result);
            $this->db->free($result);
            
            if ($obj) {
                return [
                    'status' => $obj->validation_status,
                    'validated_by' => $obj->validated_by,
                    'validated_date' => $obj->validated_date,
                    'comment' => $obj->validation_comment,
                    'status_label' => ValidationConstants::getValidationStatuses()[$obj->validation_status] ?? 'Unknown'
                ];
            }
        }
        
        return [];
    }
}
```

### 2.2 NotificationService (SRP)

**Fichier :** `Services/NotificationService.php`

```php
<?php
/**
 * Service Notifications - Responsabilité unique : Gestion notifications
 * 
 * Respecte le principe SRP : Seule responsabilité les notifications
 * Respecte le principe OCP : Extensible pour nouveaux types notifications
 */

class NotificationService implements NotificationServiceInterface 
{
    private DoliDB $db;
    
    public function __construct(DoliDB $db) 
    {
        $this->db = $db;
    }
    
    /**
     * Notifier manager des validations en attente
     */
    public function notifyPendingValidation(int $managerId, array $records): bool 
    {
        $count = count($records);
        if ($count === 0) {
            return true;
        }
        
        $message = sprintf(
            "You have %d time record(s) pending validation", 
            $count
        );
        
        return $this->createNotification(
            $managerId,
            'pending_validation',
            $message,
            ['count' => $count, 'records' => array_keys($records)]
        );
    }
    
    /**
     * Notifier employé du statut de validation
     */
    public function notifyValidationStatus(int $userId, int $recordId, string $status): bool 
    {
        $statusLabels = [
            'approve' => 'approved',
            'reject' => 'rejected', 
            'partial' => 'partially approved'
        ];
        
        $message = sprintf(
            "Your time record has been %s", 
            $statusLabels[$status] ?? $status
        );
        
        return $this->createNotification(
            $userId,
            'validation_status',
            $message,
            ['record_id' => $recordId, 'status' => $status]
        );
    }
    
    /**
     * Alerter manager d'une anomalie
     */
    public function alertAnomaly(int $managerId, string $anomalyType, array $data): bool 
    {
        $messages = [
            ValidationConstants::ANOMALY_OVERTIME => 'Overtime detected for employee',
            ValidationConstants::ANOMALY_MISSING_CLOCKOUT => 'Missing clock-out detected',
            ValidationConstants::ANOMALY_LONG_BREAK => 'Extended break detected',
            ValidationConstants::ANOMALY_LOCATION_MISMATCH => 'Location mismatch detected'
        ];
        
        $message = $messages[$anomalyType] ?? 'Anomaly detected';
        
        return $this->createNotification(
            $managerId,
            'anomaly_alert',
            $message,
            array_merge(['anomaly_type' => $anomalyType], $data)
        );
    }
    
    /**
     * Récupérer notifications non lues
     */
    public function getUnreadNotifications(int $userId): array 
    {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= " AND is_read = 0";
        $sql .= " ORDER BY created_date DESC";
        
        $result = $this->db->query($sql);
        $notifications = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $notifications[] = [
                    'id' => $obj->rowid,
                    'type' => $obj->notification_type,
                    'message' => $obj->message,
                    'data' => json_decode($obj->notification_data, true),
                    'created_date' => $obj->created_date
                ];
            }
            $this->db->free($result);
        }
        
        return $notifications;
    }
    
    /**
     * Marquer notification comme lue
     */
    public function markAsRead(int $notificationId): bool 
    {
        $sql = "UPDATE " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " SET is_read = 1, read_date = '" . $this->db->escape(dol_now('Y-m-d H:i:s')) . "'";
        $sql .= " WHERE rowid = " . ((int) $notificationId);
        
        return $this->db->query($sql);
    }
    
    /**
     * Créer une notification
     */
    private function createNotification(int $userId, string $type, string $message, array $data = []): bool 
    {
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " (fk_user, notification_type, message, notification_data, created_date)";
        $sql .= " VALUES (";
        $sql .= ((int) $userId) . ",";
        $sql .= "'" . $this->db->escape($type) . "',";
        $sql .= "'" . $this->db->escape($message) . "',";
        $sql .= "'" . $this->db->escape(json_encode($data)) . "',";
        $sql .= "'" . $this->db->escape(dol_now('Y-m-d H:i:s')) . "'";
        $sql .= ")";
        
        return $this->db->query($sql);
    }
}
```

---

## 🎯 ÉTAPE 3 : Controllers Implementation (Jour 5-6)

### 3.1 ValidationController (SRP + DIP)

**Fichier :** `Controllers/ValidationController.php`

```php
<?php
/**
 * Contrôleur Validation - Responsabilité unique : Interface validation manager
 * 
 * Respecte le principe SRP : Seule responsabilité gestion interface validation
 * Respecte le principe DIP : Dépend d'interfaces de services
 */

class ValidationController extends BaseController 
{
    private ValidationServiceInterface $validationService;
    private NotificationServiceInterface $notificationService;
    private DataServiceInterface $dataService;
    
    public function __construct(
        $db, $user, $langs, $conf,
        ValidationServiceInterface $validationService,
        NotificationServiceInterface $notificationService,
        DataServiceInterface $dataService
    ) {
        parent::__construct($db, $user, $langs, $conf);
        $this->validationService = $validationService;
        $this->notificationService = $notificationService;
        $this->dataService = $dataService;
    }
    
    /**
     * Dashboard manager - Vue d'ensemble validations
     */
    public function dashboard(): array 
    {
        $this->checkUserRights('validate');
        
        // Récupérer données dashboard
        $pendingRecords = $this->validationService->getPendingValidations($this->user->id);
        $anomalies = $this->getTeamAnomalies();
        $notifications = $this->notificationService->getUnreadNotifications($this->user->id);
        
        // Statistiques
        $stats = $this->calculateValidationStats($pendingRecords);
        
        return $this->prepareTemplateData([
            'pending_records' => $pendingRecords,
            'anomalies' => $anomalies,
            'notifications' => $notifications,
            'stats' => $stats,
            'page_title' => $this->langs->trans('ValidationDashboard')
        ]);
    }
    
    /**
     * Liste des temps à valider
     */
    public function pendingList(): array 
    {
        $this->checkUserRights('validate');
        
        // Filtres optionnels
        $userId = GETPOST('user_id', 'int');
        $dateFrom = GETPOST('date_from', 'alpha');
        $dateTo = GETPOST('date_to', 'alpha');
        
        // Récupérer enregistrements
        $records = $this->validationService->getPendingValidations($this->user->id);
        
        // Appliquer filtres
        if ($userId) {
            $records = array_filter($records, fn($r) => $r->fk_user == $userId);
        }
        
        if ($dateFrom || $dateTo) {
            $records = $this->filterByDateRange($records, $dateFrom, $dateTo);
        }
        
        // Trier par priorité (anomalies en premier)
        usort($records, function($a, $b) {
            $aHasAnomalies = !empty($a->anomalies);
            $bHasAnomalies = !empty($b->anomalies);
            
            if ($aHasAnomalies && !$bHasAnomalies) return -1;
            if (!$aHasAnomalies && $bHasAnomalies) return 1;
            
            return strtotime($a->clock_in_time) <=> strtotime($b->clock_in_time);
        });
        
        return $this->prepareTemplateData([
            'records' => $records,
            'filters' => [
                'user_id' => $userId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ],
            'team_members' => $this->getTeamMembers(),
            'page_title' => $this->langs->trans('PendingValidations')
        ]);
    }
    
    /**
     * Action de validation individuelle
     */
    public function validateRecord(): array 
    {
        $this->checkUserRights('validate');
        
        $recordId = GETPOST('record_id', 'int');
        $action = GETPOST('validation_action', 'alpha');
        $comment = GETPOST('comment', 'restricthtml');
        
        if (empty($recordId) || empty($action)) {
            return [
                'error' => 1,
                'errors' => ['Missing required parameters']
            ];
        }
        
        try {
            $result = $this->validationService->validateRecord(
                $recordId, 
                $this->user->id, 
                $action, 
                $comment
            );
            
            if ($result) {
                return [
                    'error' => 0,
                    'messages' => [$this->langs->trans('ValidationCompleted')]
                ];
            } else {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('ValidationFailed')]
                ];
            }
            
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    /**
     * Validation en lot
     */
    public function batchValidate(): array 
    {
        $this->checkUserRights('validate');
        
        $recordIds = GETPOST('record_ids', 'array');
        $action = GETPOST('batch_action', 'alpha');
        
        if (empty($recordIds) || empty($action)) {
            return [
                'error' => 1,
                'errors' => ['Missing required parameters']
            ];
        }
        
        try {
            $results = $this->validationService->batchValidate(
                $recordIds, 
                $this->user->id, 
                $action
            );
            
            $successCount = count(array_filter($results));
            $totalCount = count($results);
            
            if ($successCount === $totalCount) {
                return [
                    'error' => 0,
                    'messages' => [$this->langs->trans('BatchValidationComplete', $successCount)]
                ];
            } else {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('BatchValidationPartial', $successCount, $totalCount)]
                ];
            }
            
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    /**
     * Récupérer anomalies équipe
     */
    private function getTeamAnomalies(): array 
    {
        $anomalies = [];
        $teamMembers = $this->getTeamMembers();
        
        foreach ($teamMembers as $member) {
            $memberAnomalies = $this->validationService->detectAnomalies($member['id'], 'week');
            if (!empty($memberAnomalies)) {
                $anomalies[$member['id']] = [
                    'user' => $member,
                    'anomalies' => $memberAnomalies
                ];
            }
        }
        
        return $anomalies;
    }
    
    /**
     * Calculer statistiques validation
     */
    private function calculateValidationStats(array $records): array 
    {
        $stats = [
            'total_pending' => count($records),
            'with_anomalies' => 0,
            'overtime_count' => 0,
            'missing_clockout' => 0,
            'average_hours' => 0
        ];
        
        $totalHours = 0;
        
        foreach ($records as $record) {
            if (!empty($record->anomalies)) {
                $stats['with_anomalies']++;
                
                foreach ($record->anomalies as $anomaly) {
                    switch ($anomaly['type']) {
                        case ValidationConstants::ANOMALY_OVERTIME:
                            $stats['overtime_count']++;
                            break;
                        case ValidationConstants::ANOMALY_MISSING_CLOCKOUT:
                            $stats['missing_clockout']++;
                            break;
                    }
                }
            }
            
            if ($record->work_duration) {
                $totalHours += ($record->work_duration / 60); // minutes to hours
            }
        }
        
        if ($stats['total_pending'] > 0) {
            $stats['average_hours'] = round($totalHours / $stats['total_pending'], 2);
        }
        
        return $stats;
    }
    
    /**
     * Récupérer membres équipe
     */
    private function getTeamMembers(): array 
    {
        // Simplifiée - à adapter selon structure organisationnelle
        $sql = "SELECT rowid, firstname, lastname FROM " . MAIN_DB_PREFIX . "user";
        $sql .= " WHERE fk_user = " . ((int) $this->user->id);
        $sql .= " AND statut = 1";
        
        $result = $this->db->query($sql);
        $members = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $members[] = [
                    'id' => $obj->rowid,
                    'name' => $obj->firstname . ' ' . $obj->lastname
                ];
            }
            $this->db->free($result);
        }
        
        return $members;
    }
    
    /**
     * Filtrer enregistrements par plage de dates
     */
    private function filterByDateRange(array $records, ?string $dateFrom, ?string $dateTo): array 
    {
        if (!$dateFrom && !$dateTo) {
            return $records;
        }
        
        return array_filter($records, function($record) use ($dateFrom, $dateTo) {
            $recordDate = date('Y-m-d', strtotime($record->clock_in_time));
            
            if ($dateFrom && $recordDate < $dateFrom) {
                return false;
            }
            
            if ($dateTo && $recordDate > $dateTo) {
                return false;
            }
            
            return true;
        });
    }
}
```

---

## 🎯 ÉTAPE 4 : View Components (Jour 7-8)

### 4.1 Composants Interface Manager (SRP)

**Fichier :** `Views/components/ValidationStatus.tpl`

```php
<?php
/**
 * Composant ValidationStatus - Responsabilité unique : Affichage statut validation
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage du statut de validation employé
 */
?>

<?php if (!empty($validation_status)): ?>
<div class="validation-status-card" style="padding: 10px; margin: 10px 0;">
  <ons-card>
    <div class="content" style="padding: 15px;">
      <div style="display: flex; align-items: center; margin-bottom: 10px;">
        <ons-icon 
          icon="<?php echo $this->getStatusIcon($validation_status['status']); ?>" 
          style="color: <?php echo $this->getStatusColor($validation_status['status']); ?>; margin-right: 10px; font-size: 20px;">
        </ons-icon>
        <h4 style="margin: 0; color: #333;">
          <?php echo $langs->trans($validation_status['status_label']); ?>
        </h4>
      </div>
      
      <?php if (!empty($validation_status['validated_by'])): ?>
      <div style="font-size: 14px; color: #666; margin-bottom: 5px;">
        <strong><?php echo $langs->trans('ValidatedBy'); ?>:</strong>
        <?php 
        $validator = new User($db);
        $validator->fetch($validation_status['validated_by']);
        echo $validator->getFullName($langs);
        ?>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($validation_status['validated_date'])): ?>
      <div style="font-size: 14px; color: #666; margin-bottom: 5px;">
        <strong><?php echo $langs->trans('ValidatedDate'); ?>:</strong>
        <?php echo dol_print_date($validation_status['validated_date'], 'dayhour', 'tzuser'); ?>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($validation_status['comment'])): ?>
      <div style="margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
        <strong><?php echo $langs->trans('Comment'); ?>:</strong>
        <div style="margin-top: 5px;"><?php echo dol_escape_htmltag($validation_status['comment']); ?></div>
      </div>
      <?php endif; ?>
    </div>
  </ons-card>
</div>

<?php
// Helper methods pour les styles
function getStatusIcon($status) {
    return match($status) {
        ValidationConstants::VALIDATION_APPROVED => 'md-check-circle',
        ValidationConstants::VALIDATION_REJECTED => 'md-cancel',
        ValidationConstants::VALIDATION_PARTIAL => 'md-warning',
        default => 'md-schedule'
    };
}

function getStatusColor($status) {
    return match($status) {
        ValidationConstants::VALIDATION_APPROVED => '#4CAF50',
        ValidationConstants::VALIDATION_REJECTED => '#f44336', 
        ValidationConstants::VALIDATION_PARTIAL => '#FF9800',
        default => '#999'
    };
}
?>
<?php endif; ?>
```

**Fichier :** `Views/components/ValidationActions.tpl`

```php
<?php
/**
 * Composant ValidationActions - Responsabilité unique : Actions de validation
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage des actions de validation
 */
?>

<div class="validation-actions" style="padding: 15px; border-top: 1px solid #eee;">
  <div style="display: flex; gap: 10px; align-items: center;">
    
    <!-- Action Approuver -->
    <ons-button 
      onclick="validateRecord(<?php echo $record_id; ?>, 'approve')"
      style="background-color: #4CAF50; color: white; flex: 1; border-radius: 20px;">
      <ons-icon icon="md-check" style="margin-right: 5px;"></ons-icon>
      <?php echo $langs->trans('Approve'); ?>
    </ons-button>
    
    <!-- Action Rejeter -->
    <ons-button 
      onclick="validateRecord(<?php echo $record_id; ?>, 'reject')"
      style="background-color: #f44336; color: white; flex: 1; border-radius: 20px;">
      <ons-icon icon="md-close" style="margin-right: 5px;"></ons-icon>
      <?php echo $langs->trans('Reject'); ?>
    </ons-button>
    
    <!-- Action Commentaire -->
    <ons-button 
      onclick="showCommentModal(<?php echo $record_id; ?>)"
      style="background-color: #2196F3; color: white; border-radius: 20px;">
      <ons-icon icon="md-comment"></ons-icon>
    </ons-button>
    
  </div>
  
  <!-- Section commentaire (cachée par défaut) -->
  <div id="comment-section-<?php echo $record_id; ?>" style="display: none; margin-top: 15px;">
    <textarea 
      id="comment-<?php echo $record_id; ?>"
      placeholder="<?php echo $langs->trans('ValidationComment'); ?>"
      style="width: 100%; height: 80px; border: 1px solid #ddd; border-radius: 5px; padding: 10px;">
    </textarea>
    <div style="margin-top: 10px; text-align: right;">
      <ons-button onclick="hideCommentModal(<?php echo $record_id; ?>)" modifier="quiet">
        <?php echo $langs->trans('Cancel'); ?>
      </ons-button>
      <ons-button onclick="submitWithComment(<?php echo $record_id; ?>)" style="background-color: #4CAF50; color: white;">
        <?php echo $langs->trans('Submit'); ?>
      </ons-button>
    </div>
  </div>
</div>

<script>
function validateRecord(recordId, action) {
    // Validation simple sans commentaire
    fetch('<?php echo $_SERVER["PHP_SELF"]; ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=validate_record&record_id=${recordId}&validation_action=${action}&token=<?php echo newToken(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error === 0) {
            ons.notification.toast(data.messages[0], {timeout: 2000});
            location.reload(); // Refresh pour voir les changements
        } else {
            ons.notification.alert(data.errors[0]);
        }
    })
    .catch(error => {
        console.error('Validation error:', error);
        ons.notification.alert('<?php echo $langs->trans("ValidationError"); ?>');
    });
}

function showCommentModal(recordId) {
    document.getElementById(`comment-section-${recordId}`).style.display = 'block';
}

function hideCommentModal(recordId) {
    document.getElementById(`comment-section-${recordId}`).style.display = 'none';
    document.getElementById(`comment-${recordId}`).value = '';
}

function submitWithComment(recordId) {
    const comment = document.getElementById(`comment-${recordId}`).value;
    
    fetch('<?php echo $_SERVER["PHP_SELF"]; ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=validate_record&record_id=${recordId}&validation_action=approve&comment=${encodeURIComponent(comment)}&token=<?php echo newToken(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error === 0) {
            ons.notification.toast(data.messages[0], {timeout: 2000});
            location.reload();
        } else {
            ons.notification.alert(data.errors[0]);
        }
    })
    .catch(error => {
        console.error('Validation error:', error);
        ons.notification.alert('<?php echo $langs->trans("ValidationError"); ?>');
    });
}
</script>
```

**Fichier :** `Views/components/AnomalyCard.tpl`

```php
<?php
/**
 * Composant AnomalyCard - Responsabilité unique : Affichage anomalies
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage des anomalies détectées
 */
?>

<?php if (!empty($anomalies)): ?>
<div class="anomaly-alerts" style="margin: 10px 0;">
  <?php foreach ($anomalies as $anomaly): ?>
  <ons-card style="margin-bottom: 8px; border-left: 4px solid <?php echo $this->getAnomalyColor($anomaly['level']); ?>;">
    <div class="content" style="padding: 10px;">
      <div style="display: flex; align-items: center;">
        <ons-icon 
          icon="<?php echo $this->getAnomalyIcon($anomaly['type']); ?>" 
          style="color: <?php echo $this->getAnomalyColor($anomaly['level']); ?>; margin-right: 10px; font-size: 18px;">
        </ons-icon>
        
        <div style="flex: 1;">
          <div style="font-weight: 500; color: #333; margin-bottom: 2px;">
            <?php echo $langs->trans($this->getAnomalyTypeLabel($anomaly['type'])); ?>
          </div>
          <div style="font-size: 14px; color: #666;">
            <?php echo dol_escape_htmltag($anomaly['message']); ?>
          </div>
        </div>
        
        <div style="font-size: 12px; color: <?php echo $this->getAnomalyColor($anomaly['level']); ?>; font-weight: 500;">
          <?php echo strtoupper($anomaly['level']); ?>
        </div>
      </div>
    </div>
  </ons-card>
  <?php endforeach; ?>
</div>

<?php
// Helper methods pour les anomalies
function getAnomalyColor($level) {
    return match($level) {
        ValidationConstants::ALERT_CRITICAL => '#f44336',
        ValidationConstants::ALERT_WARNING => '#FF9800',
        ValidationConstants::ALERT_INFO => '#2196F3',
        default => '#999'
    };
}

function getAnomalyIcon($type) {
    return match($type) {
        ValidationConstants::ANOMALY_OVERTIME => 'md-schedule',
        ValidationConstants::ANOMALY_MISSING_CLOCKOUT => 'md-error',
        ValidationConstants::ANOMALY_LONG_BREAK => 'md-pause',
        ValidationConstants::ANOMALY_LOCATION_MISMATCH => 'md-place',
        default => 'md-warning'
    };
}

function getAnomalyTypeLabel($type) {
    return match($type) {
        ValidationConstants::ANOMALY_OVERTIME => 'AnomalyOvertime',
        ValidationConstants::ANOMALY_MISSING_CLOCKOUT => 'AnomalyMissingClockout',
        ValidationConstants::ANOMALY_LONG_BREAK => 'AnomalyLongBreak',
        ValidationConstants::ANOMALY_LOCATION_MISMATCH => 'AnomalyLocationMismatch',
        default => 'Anomaly'
    };
}
?>
<?php endif; ?>
```

**Fichier :** `Views/components/ManagerAlert.tpl`

```php
<?php
/**
 * Composant ManagerAlert - Responsabilité unique : Alertes manager
 * 
 * Respecte le principe SRP : Seule responsabilité l'affichage des alertes pour managers
 */
?>

<?php if (!empty($notifications)): ?>
<div class="manager-alerts" style="padding: 15px;">
  <h4 style="margin: 0 0 15px 0; color: #333;">
    <ons-icon icon="md-notifications" style="color: #FF9800; margin-right: 8px;"></ons-icon>
    <?php echo $langs->trans('Notifications'); ?>
    <span style="background-color: #FF9800; color: white; border-radius: 10px; padding: 2px 8px; font-size: 12px; margin-left: 5px;">
      <?php echo count($notifications); ?>
    </span>
  </h4>
  
  <div class="notifications-list">
    <?php foreach ($notifications as $notification): ?>
    <ons-card style="margin-bottom: 10px; border-left: 4px solid <?php echo $this->getNotificationColor($notification['type']); ?>;">
      <div class="content" style="padding: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div style="flex: 1;">
            <div style="font-weight: 500; color: #333; margin-bottom: 5px;">
              <?php echo dol_escape_htmltag($notification['message']); ?>
            </div>
            <div style="font-size: 12px; color: #666;">
              <?php echo dol_print_date($notification['created_date'], 'dayhour', 'tzuser'); ?>
            </div>
          </div>
          <ons-button 
            onclick="markNotificationRead(<?php echo $notification['id']; ?>)"
            modifier="quiet"
            style="min-width: auto; padding: 5px;">
            <ons-icon icon="md-check" style="color: #4CAF50;"></ons-icon>
          </ons-button>
        </div>
        
        <!-- Données additionnelles selon le type -->
        <?php if ($notification['type'] === 'pending_validation' && !empty($notification['data']['count'])): ?>
        <div style="margin-top: 10px; padding: 8px; background-color: #f8f9fa; border-radius: 5px;">
          <strong><?php echo $notification['data']['count']; ?></strong> 
          <?php echo $langs->trans('RecordsPendingValidation'); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($notification['type'] === 'anomaly_alert' && !empty($notification['data']['anomaly_type'])): ?>
        <div style="margin-top: 10px;">
          <span style="background-color: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 12px; font-size: 12px;">
            <?php echo $langs->trans($this->getAnomalyTypeLabel($notification['data']['anomaly_type'])); ?>
          </span>
        </div>
        <?php endif; ?>
      </div>
    </ons-card>
    <?php endforeach; ?>
  </div>
  
  <?php if (count($notifications) > 3): ?>
  <div style="text-align: center; margin-top: 15px;">
    <ons-button onclick="viewAllNotifications()" modifier="quiet">
      <?php echo $langs->trans('ViewAllNotifications'); ?>
    </ons-button>
  </div>
  <?php endif; ?>
</div>

<script>
function markNotificationRead(notificationId) {
    fetch('<?php echo $_SERVER["PHP_SELF"]; ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_notification_read&notification_id=${notificationId}&token=<?php echo newToken(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.error === 0) {
            // Masquer la notification avec animation
            event.target.closest('ons-card').style.opacity = '0.5';
            setTimeout(() => {
                event.target.closest('ons-card').style.display = 'none';
            }, 300);
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function viewAllNotifications() {
    // Navigation vers page complète des notifications
    window.location.href = '<?php echo $_SERVER["PHP_SELF"]; ?>?action=notifications';
}
</script>

<?php
// Helper method pour couleurs notifications
function getNotificationColor($type) {
    return match($type) {
        'pending_validation' => '#2196F3',
        'anomaly_alert' => '#FF9800',
        'validation_status' => '#4CAF50',
        default => '#999'
    };
}
?>
<?php endif; ?>
```

---

## 🎯 ÉTAPE 5 : Templates Pages Manager (Jour 9-10)

### 5.1 Pages Principales Manager

**Fichier :** `Views/validation/dashboard.tpl`

```php
<?php
/**
 * Dashboard Manager - Page principale validation
 * Architecture modulaire avec composants SOLID
 */
?>

<ons-page id="ValidationDashboard">
  <!-- TopBar Manager -->
  <ons-toolbar>
    <div class="center"><?php echo $page_title; ?></div>
    <div class="right">
      <ons-toolbar-button onclick="refreshDashboard()">
        <ons-icon icon="md-refresh"></ons-icon>
      </ons-toolbar-button>
    </div>
  </ons-toolbar>
  
  <!-- Pull to refresh -->
  <ons-pull-hook id="pull-hook">
    <?php echo $langs->trans("PullToRefresh"); ?>
  </ons-pull-hook>
  
  <!-- Messages Component -->
  <?php include 'Views/components/Messages.tpl'; ?>
  
  <!-- Manager Alerts Component -->
  <?php include 'Views/components/ManagerAlert.tpl'; ?>
  
  <!-- Statistiques Rapides -->
  <div style="padding: 15px;">
    <ons-card>
      <div class="title" style="padding: 10px;">
        <h3><?php echo $langs->trans('ValidationStatistics'); ?></h3>
      </div>
      <div class="content" style="padding: 0 15px 15px 15px;">
        <ons-row>
          <ons-col width="25%">
            <div style="text-align: center; padding: 10px;">
              <div style="font-size: 24px; font-weight: bold; color: #2196F3;">
                <?php echo $stats['total_pending']; ?>
              </div>
              <div style="font-size: 12px; color: #666;">
                <?php echo $langs->trans('PendingValidation'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="25%">
            <div style="text-align: center; padding: 10px;">
              <div style="font-size: 24px; font-weight: bold; color: #FF9800;">
                <?php echo $stats['with_anomalies']; ?>
              </div>
              <div style="font-size: 12px; color: #666;">
                <?php echo $langs->trans('WithAnomalies'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="25%">
            <div style="text-align: center; padding: 10px;">
              <div style="font-size: 24px; font-weight: bold; color: #f44336;">
                <?php echo $stats['overtime_count']; ?>
              </div>
              <div style="font-size: 12px; color: #666;">
                <?php echo $langs->trans('Overtime'); ?>
              </div>
            </div>
          </ons-col>
          <ons-col width="25%">
            <div style="text-align: center; padding: 10px;">
              <div style="font-size: 24px; font-weight: bold; color: #4CAF50;">
                <?php echo $stats['average_hours']; ?>h
              </div>
              <div style="font-size: 12px; color: #666;">
                <?php echo $langs->trans('AverageHours'); ?>
              </div>
            </div>
          </ons-col>
        </ons-row>
      </div>
    </ons-card>
  </div>
  
  <!-- Actions Rapides -->
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 10px;">
        <h3><?php echo $langs->trans('QuickActions'); ?></h3>
      </div>
      <div class="content" style="padding: 15px;">
        <ons-row>
          <ons-col width="50%">
            <ons-button 
              onclick="gotoPage('pending_list')"
              style="width: 100%; background-color: #2196F3; color: white; border-radius: 15px;">
              <ons-icon icon="md-list" style="margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans('ViewPendingList'); ?>
            </ons-button>
          </ons-col>
          <ons-col width="50%">
            <ons-button 
              onclick="showBatchValidationModal()"
              style="width: 100%; background-color: #4CAF50; color: white; border-radius: 15px;">
              <ons-icon icon="md-done-all" style="margin-right: 8px;"></ons-icon>
              <?php echo $langs->trans('BatchValidation'); ?>
            </ons-button>
          </ons-col>
        </ons-row>
      </div>
    </ons-card>
  </div>
  
  <!-- Enregistrements Récents avec Priorité -->
  <?php if (!empty($pending_records)): ?>
  <div style="padding: 0 15px 15px 15px;">
    <ons-card>
      <div class="title" style="padding: 10px;">
        <h3><?php echo $langs->trans('RecentPendingRecords'); ?></h3>
      </div>
      <ons-list style="margin: 0;">
        <?php 
        $displayCount = 0;
        foreach ($pending_records as $record): 
          if ($displayCount >= 5) break;
          $displayCount++;
          
          // Récupérer infos utilisateur
          $user_obj = new User($db);
          $user_obj->fetch($record->fk_user);
          
          // Récupérer type
          $type = new TimeclockType($db);
          $type->fetch($record->fk_timeclock_type);
          
          $hasAnomalies = !empty($record->anomalies);
          $priorityColor = $hasAnomalies ? '#FF9800' : '#4CAF50';
        ?>
        <ons-list-item tappable onclick="viewRecordDetail(<?php echo $record->rowid; ?>)">
          <div class="left">
            <div style="width: 8px; height: 40px; background-color: <?php echo $priorityColor; ?>; border-radius: 4px;"></div>
          </div>
          <div class="center">
            <div style="font-weight: bold; margin-bottom: 2px;">
              <?php echo $user_obj->getFullName($langs); ?>
            </div>
            <div style="font-size: 14px; color: #666; margin-bottom: 2px;">
              <?php echo dol_print_date($record->clock_in_time, 'day'); ?> - 
              <span style="background-color: <?php echo $type->color; ?>; color: white; padding: 2px 6px; border-radius: 8px; font-size: 11px;">
                <?php echo $type->label; ?>
              </span>
            </div>
            <?php if ($hasAnomalies): ?>
            <div style="font-size: 12px; color: #FF9800;">
              <ons-icon icon="md-warning" style="color: #FF9800;"></ons-icon>
              <?php echo count($record->anomalies); ?> <?php echo $langs->trans('AnomaliesDetected'); ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="right">
            <div style="text-align: right;">
              <div style="font-weight: bold; color: #2196F3;">
                <?php echo TimeHelper::convertSecondsToReadableTime($record->work_duration * 60); ?>
              </div>
              <div style="font-size: 12px; color: #666;">
                <?php echo dol_print_date($record->clock_in_time, 'hour'); ?>
              </div>
            </div>
          </div>
        </ons-list-item>
        <?php endforeach; ?>
      </ons-list>
      
      <?php if (count($pending_records) > 5): ?>
      <div style="text-align: center; padding: 10px;">
        <ons-button modifier="quiet" onclick="gotoPage('pending_list')">
          <?php echo $langs->trans('ViewAll'); ?> (<?php echo count($pending_records); ?>)
        </ons-button>
      </div>
      <?php endif; ?>
    </ons-card>
  </div>
  <?php endif; ?>
  
  <!-- Navigation Bottom -->
  <div style="position: fixed; bottom: 0; left: 0; right: 0; background: white; border-top: 1px solid #eee; padding: 10px;">
    <ons-row>
      <ons-col width="33%">
        <ons-button onclick="gotoPage('dashboard')" modifier="quiet" style="width: 100%;">
          <ons-icon icon="md-home"></ons-icon><br>
          <span style="font-size: 11px;"><?php echo $langs->trans('Dashboard'); ?></span>
        </ons-button>
      </ons-col>
      <ons-col width="33%">
        <ons-button onclick="gotoPage('pending_list')" modifier="quiet" style="width: 100%;">
          <ons-icon icon="md-list"></ons-icon><br>
          <span style="font-size: 11px;"><?php echo $langs->trans('Pending'); ?></span>
        </ons-button>
      </ons-col>
      <ons-col width="33%">
        <ons-button onclick="gotoPage('reports')" modifier="quiet" style="width: 100%;">
          <ons-icon icon="md-assessment"></ons-icon><br>
          <span style="font-size: 11px;"><?php echo $langs->trans('Reports'); ?></span>
        </ons-button>
      </ons-col>
    </ons-row>
  </div>
  
  <script>
  function refreshDashboard() {
    location.reload();
  }
  
  function gotoPage(page) {
    window.location.href = `<?php echo $_SERVER['PHP_SELF']; ?>?action=${page}`;
  }
  
  function viewRecordDetail(recordId) {
    window.location.href = `<?php echo $_SERVER['PHP_SELF']; ?>?action=record_detail&id=${recordId}`;
  }
  
  function showBatchValidationModal() {
    // Implementation de la validation en lot
    ons.notification.alert('<?php echo $langs->trans("BatchValidationFeatureComing"); ?>');
  }
  
  // Pull to refresh
  document.addEventListener('DOMContentLoaded', function() {
    var pullHook = document.getElementById('pull-hook');
    pullHook.addEventListener('changestate', function(event) {
      var message = '';
      switch (event.state) {
        case 'initial':
          message = '<?php echo $langs->trans("PullToRefresh"); ?>';
          break;
        case 'preaction':
          message = '<?php echo $langs->trans("Release"); ?>';
          break;
        case 'action':
          message = '<?php echo $langs->trans("Loading"); ?>...';
          break;
      }
      pullHook.innerHTML = message;
    });

    pullHook.onAction = function(done) {
      setTimeout(function() {
        location.reload();
        done();
      }, 1000);
    };
  });
  </script>
</ons-page>
```

---

## 🎯 ÉTAPE 6 : API REST et Integration (Jour 11-12)

### 6.1 API Validation

**Fichier :** `api/validation.php`

```php
<?php
/**
 * API REST Validation - Responsabilité unique : Endpoints validation
 * 
 * Respecte le principe SRP : Seule responsabilité les endpoints de validation
 * Respecte le principe OCP : Extensible pour nouveaux endpoints
 */

// Headers CORS et sécurité
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Chargement Dolibarr
$res = 0;
if (!$res && file_exists("../main.inc.php")) $res = include '../main.inc.php';
if (!$res && file_exists("../../main.inc.php")) $res = include '../../main.inc.php';
if (!$res && file_exists("../../../main.inc.php")) $res = include '../../../main.inc.php';
if (!$res) die("Include of main fails");

// Chargement classes SOLID
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Constants/ValidationConstants.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/ValidationServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/NotificationServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/ValidationService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/NotificationService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/DataService.php';

/**
 * Classe API Validation
 */
class ValidationAPI 
{
    private ValidationServiceInterface $validationService;
    private NotificationServiceInterface $notificationService;
    private User $user;
    
    public function __construct() 
    {
        global $db, $user;
        
        $this->user = $user;
        
        // Injection de dépendances (DIP)
        $dataService = new DataService($db);
        $this->notificationService = new NotificationService($db);
        $this->validationService = new ValidationService($db, $dataService, $this->notificationService);
    }
    
    /**
     * Point d'entrée principal
     */
    public function handleRequest(): void 
    {
        try {
            // Vérification authentication
            if (!$this->isAuthenticated()) {
                $this->sendError(401, 'Authentication required');
                return;
            }
            
            $method = $_SERVER['REQUEST_METHOD'];
            $action = $_GET['action'] ?? '';
            
            // Routing des endpoints
            match([$method, $action]) {
                ['GET', 'pending'] => $this->getPendingValidations(),
                ['GET', 'status'] => $this->getValidationStatus(),
                ['POST', 'validate'] => $this->validateRecord(),
                ['POST', 'batch_validate'] => $this->batchValidate(),
                ['GET', 'anomalies'] => $this->getAnomalies(),
                ['GET', 'notifications'] => $this->getNotifications(),
                ['POST', 'mark_read'] => $this->markNotificationRead(),
                default => $this->sendError(404, 'Endpoint not found')
            };
            
        } catch (Exception $e) {
            $this->sendError(500, $e->getMessage());
        }
    }
    
    /**
     * GET /api/validation.php?action=pending
     * Récupérer validations en attente
     */
    private function getPendingValidations(): void 
    {
        if (!$this->hasPermission('validate')) {
            $this->sendError(403, 'Insufficient permissions');
            return;
        }
        
        $records = $this->validationService->getPendingValidations($this->user->id);
        
        // Formater pour API
        $formattedRecords = array_map(function($record) {
            return [
                'id' => $record->rowid,
                'user_id' => $record->fk_user,
                'user_name' => $this->getUserName($record->fk_user),
                'clock_in_time' => $record->clock_in_time,
                'clock_out_time' => $record->clock_out_time,
                'work_duration' => $record->work_duration,
                'type_id' => $record->fk_timeclock_type,
                'anomalies' => $record->anomalies ?? [],
                'validation_info' => $record->validation_info ?? []
            ];
        }, $records);
        
        $this->sendSuccess([
            'records' => $formattedRecords,
            'count' => count($formattedRecords)
        ]);
    }
    
    /**
     * GET /api/validation.php?action=status&record_id=123
     * Statut validation d'un enregistrement
     */
    private function getValidationStatus(): void 
    {
        $recordId = (int) ($_GET['record_id'] ?? 0);
        
        if (!$recordId) {
            $this->sendError(400, 'Missing record_id parameter');
            return;
        }
        
        $status = $this->validationService->getValidationStatus($recordId);
        $this->sendSuccess($status);
    }
    
    /**
     * POST /api/validation.php?action=validate
     * Valider un enregistrement
     */
    private function validateRecord(): void 
    {
        if (!$this->hasPermission('validate')) {
            $this->sendError(403, 'Insufficient permissions');
            return;
        }
        
        $input = $this->getJsonInput();
        $recordId = (int) ($input['record_id'] ?? 0);
        $action = $input['action'] ?? '';
        $comment = $input['comment'] ?? null;
        
        if (!$recordId || !$action) {
            $this->sendError(400, 'Missing required parameters');
            return;
        }
        
        $result = $this->validationService->validateRecord(
            $recordId, 
            $this->user->id, 
            $action, 
            $comment
        );
        
        if ($result) {
            $this->sendSuccess(['message' => 'Validation completed successfully']);
        } else {
            $this->sendError(500, 'Validation failed');
        }
    }
    
    /**
     * POST /api/validation.php?action=batch_validate
     * Validation en lot
     */
    private function batchValidate(): void 
    {
        if (!$this->hasPermission('validate')) {
            $this->sendError(403, 'Insufficient permissions');
            return;
        }
        
        $input = $this->getJsonInput();
        $recordIds = $input['record_ids'] ?? [];
        $action = $input['action'] ?? '';
        
        if (empty($recordIds) || !$action) {
            $this->sendError(400, 'Missing required parameters');
            return;
        }
        
        $results = $this->validationService->batchValidate($recordIds, $this->user->id, $action);
        
        $successCount = count(array_filter($results));
        $totalCount = count($results);
        
        $this->sendSuccess([
            'message' => "Batch validation completed: $successCount/$totalCount successful",
            'results' => $results,
            'success_count' => $successCount,
            'total_count' => $totalCount
        ]);
    }
    
    /**
     * GET /api/validation.php?action=anomalies&user_id=123&period=week
     * Anomalies détectées
     */
    private function getAnomalies(): void 
    {
        $userId = (int) ($_GET['user_id'] ?? $this->user->id);
        $period = $_GET['period'] ?? 'week';
        
        // Vérification permissions (manager peut voir équipe)
        if ($userId !== $this->user->id && !$this->hasPermission('readall')) {
            $this->sendError(403, 'Cannot view other user anomalies');
            return;
        }
        
        $anomalies = $this->validationService->detectAnomalies($userId, $period);
        
        $this->sendSuccess([
            'anomalies' => $anomalies,
            'user_id' => $userId,
            'period' => $period,
            'count' => count($anomalies)
        ]);
    }
    
    /**
     * GET /api/validation.php?action=notifications
     * Notifications non lues
     */
    private function getNotifications(): void 
    {
        $notifications = $this->notificationService->getUnreadNotifications($this->user->id);
        
        $this->sendSuccess([
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }
    
    /**
     * POST /api/validation.php?action=mark_read
     * Marquer notification comme lue
     */
    private function markNotificationRead(): void 
    {
        $input = $this->getJsonInput();
        $notificationId = (int) ($input['notification_id'] ?? 0);
        
        if (!$notificationId) {
            $this->sendError(400, 'Missing notification_id parameter');
            return;
        }
        
        $result = $this->notificationService->markAsRead($notificationId);
        
        if ($result) {
            $this->sendSuccess(['message' => 'Notification marked as read']);
        } else {
            $this->sendError(500, 'Failed to mark notification as read');
        }
    }
    
    /**
     * Helpers
     */
    private function isAuthenticated(): bool 
    {
        global $user;
        return isset($user) && $user->id > 0;
    }
    
    private function hasPermission(string $permission): bool 
    {
        return $this->user->rights->timeclock->{$permission} ?? false;
    }
    
    private function getJsonInput(): array 
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?? [];
    }
    
    private function getUserName(int $userId): string 
    {
        $user = new User($GLOBALS['db']);
        if ($user->fetch($userId) > 0) {
            return $user->getFullName($GLOBALS['langs']);
        }
        return 'Unknown User';
    }
    
    private function sendSuccess(array $data): void 
    {
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => time()
        ]);
    }
    
    private function sendError(int $code, string $message): void 
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message
            ],
            'timestamp' => time()
        ]);
    }
}

// Exécution API
$api = new ValidationAPI();
$api->handleRequest();
```

---

## 🎯 ÉTAPE 7 : Tests et Documentation (Jour 13-14)

### 7.1 Tests Unitaires

**Fichier :** `test/phpunit/ValidationServiceTest.php`

```php
<?php
/**
 * Tests unitaires ValidationService
 */

use PHPUnit\Framework\TestCase;

class ValidationServiceTest extends TestCase 
{
    private ValidationService $validationService;
    private $mockDb;
    private $mockDataService;
    private $mockNotificationService;
    
    protected function setUp(): void 
    {
        // Mock dependencies
        $this->mockDb = $this->createMock(DoliDB::class);
        $this->mockDataService = $this->createMock(DataServiceInterface::class);
        $this->mockNotificationService = $this->createMock(NotificationServiceInterface::class);
        
        // Create service with mocked dependencies
        $this->validationService = new ValidationService(
            $this->mockDb,
            $this->mockDataService,
            $this->mockNotificationService
        );
    }
    
    public function testValidateRecordSuccess(): void 
    {
        // Test validation réussie
        $recordId = 123;
        $validatorId = 456;
        $action = 'approve';
        
        // Mock database operations
        $this->mockDb->expects($this->once())
                     ->method('query')
                     ->willReturn(true);
        
        $result = $this->validationService->validateRecord($recordId, $validatorId, $action);
        
        $this->assertTrue($result);
    }
    
    public function testDetectOvertimeAnomaly(): void 
    {
        // Test détection anomalie overtime
        $record = new TimeclockRecord($this->mockDb);
        $record->work_duration = 600; // 10 heures en minutes
        
        $method = new ReflectionMethod($this->validationService, 'detectRecordAnomalies');
        $method->setAccessible(true);
        
        $anomalies = $method->invoke($this->validationService, $record);
        
        $this->assertNotEmpty($anomalies);
        $this->assertEquals(ValidationConstants::ANOMALY_OVERTIME, $anomalies[0]['type']);
    }
}
```

### 7.2 Documentation Sprint 2

**Mise à jour du README avec Sprint 2 :**

```markdown
## Sprint 2 - Validation Manager ✅

### Fonctionnalités Implémentées
- ✅ **Dashboard Manager** : Vue d'ensemble validations et anomalies
- ✅ **Validation Workflow** : Approve/Reject/Partial avec commentaires 
- ✅ **Détection Anomalies** : Overtime, clock-out manquant, pauses longues
- ✅ **Notifications** : Alertes manager et statuts employés
- ✅ **API REST** : Endpoints validation complets
- ✅ **Validation en lot** : Actions groupées sur plusieurs enregistrements

### Architecture SOLID Respectée
- **SRP** : Services séparés (Validation, Notification, Data)
- **OCP** : Extensible pour nouveaux types validation/anomalies
- **LSP** : BaseController utilisé par ValidationController
- **ISP** : Interfaces ségrégées par responsabilité
- **DIP** : Injection dépendances dans tous les services
```

---

## 📋 Checklist Sprint 2

### ✅ Architecture & Code
- [ ] **Constants/ValidationConstants.php** - Configuration centralisée
- [ ] **Services/Interfaces/** - Contrats ValidationService & NotificationService
- [ ] **Services/ValidationService.php** - Logique métier validation
- [ ] **Services/NotificationService.php** - Gestion notifications
- [ ] **Controllers/ValidationController.php** - Interface manager
- [ ] **Views/components/** - 4 composants modulaires (ValidationStatus, ValidationActions, AnomalyCard, ManagerAlert)
- [ ] **Views/validation/dashboard.tpl** - Page dashboard manager
- [ ] **api/validation.php** - API REST complète

### ✅ Features Fonctionnelles
- [ ] **Dashboard manager** avec statistiques temps réel
- [ ] **Liste validations** avec filtres et tri par priorité
- [ ] **Actions validation** : Approve/Reject/Partial + commentaires
- [ ] **Validation en lot** pour actions groupées
- [ ] **Détection anomalies** automatique (4 types)
- [ ] **Notifications** manager et employés
- [ ] **API REST** avec 7 endpoints validation

### ✅ Tests & Qualité
- [ ] **Tests unitaires** ValidationService & NotificationService
- [ ] **Tests API** tous endpoints validation
- [ ] **Tests interface** composants validation
- [ ] **Documentation** mise à jour avec Sprint 2

### ✅ Base de Données
- [ ] **Migration script** pour champs validation (validation_status, validated_by, validated_date, validation_comment)
- [ ] **Table notifications** pour système alertes
- [ ] **Index performance** sur champs fréquemment utilisés

---

## 🚀 Points d'Entry Implementation

### Pour Claude Code :

1. **Commencer par les Constants** (jour 1)
2. **Créer les Interfaces** avant les Services (ISP)
3. **Implémenter Services** avec injection dépendances (DIP)
4. **Développer Controller** en étendant BaseController (OCP)
5. **Créer composants Views** modulaires (SRP)
6. **Assembler pages** avec composants
7. **Développer API REST** avec routing propre
8. **Écrire tests** pour validation qualité

### Principes SOLID à Respecter :
- **Un service = une responsabilité** métier
- **Interfaces avant implémentations**
- **Injection dépendances** systématique
- **Composants UI** modulaires et réutilisables
- **Extension** sans modification du code existant

Ce guide garantit une implémentation **SOLID complète** du Sprint 2 ! 🎯