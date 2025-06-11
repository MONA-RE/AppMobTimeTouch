<?php
/**
 * Service Validation - Responsabilité unique : Logique métier validation
 * 
 * Respecte le principe SRP : Seule responsabilité la gestion des validations
 * Respecte le principe DIP : Dépend d'abstractions (DataServiceInterface)
 * Respecte le principe OCP : Extensible pour nouveaux types de validation
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Constants/ValidationConstants.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';

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
     * Récupérer les temps en attente de validation pour un manager
     */
    public function getPendingValidations(int $managerId): array 
    {
        dol_syslog("ValidationService: Getting pending validations for manager $managerId", LOG_DEBUG);
        
        // 1. Récupérer les équipes du manager
        $teamMembers = $this->getTeamMembers($managerId);
        
        if (empty($teamMembers)) {
            dol_syslog("ValidationService: No team members found for manager $managerId", LOG_DEBUG);
            return [];
        }
        
        // 2. Récupérer les enregistrements non validés (inclut ceux en cours avec anomalies)
        $sql = "SELECT r.* FROM " . MAIN_DB_PREFIX . "timeclock_records r";
        $sql .= " WHERE r.fk_user IN (" . implode(',', array_map('intval', $teamMembers)) . ")";
        $sql .= " AND r.validated_by IS NULL"; // Enregistrements non validés
        $sql .= " AND (r.status = " . TimeclockConstants::STATUS_COMPLETED;
        $sql .= " OR r.status = " . TimeclockConstants::STATUS_IN_PROGRESS . ")"; // Inclure sessions en cours
        $sql .= " ORDER BY r.clock_in_time DESC";
        
        $result = $this->db->query($sql);
        $pendingRecords = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                // Enrichir avec informations utilisateur et anomalies
                $record = $this->enrichRecordData($obj);
                
                // Détecter anomalies pour cet enregistrement
                $record['anomalies'] = $this->detectRecordAnomalies($obj);
                
                $pendingRecords[] = $record;
            }
            $this->db->free($result);
        }
        
        dol_syslog("ValidationService: Found " . count($pendingRecords) . " pending validations", LOG_INFO);
        return $pendingRecords;
    }
    
    /**
     * Récupérer les enregistrements d'aujourd'hui pour un manager
     */
    public function getTodaysRecords(int $managerId): array 
    {
        dol_syslog("ValidationService: Getting today's records for manager $managerId", LOG_DEBUG);
        
        // 1. Récupérer les équipes du manager
        $teamMembers = $this->getTeamMembers($managerId);
        
        if (empty($teamMembers)) {
            dol_syslog("ValidationService: No team members found for manager $managerId", LOG_DEBUG);
            return [];
        }
        
        // 2. Récupérer tous les enregistrements d'aujourd'hui (validés et non validés)
        $sql = "SELECT r.* FROM " . MAIN_DB_PREFIX . "timeclock_records r";
        $sql .= " WHERE r.fk_user IN (" . implode(',', array_map('intval', $teamMembers)) . ")";
        $sql .= " AND DATE(r.clock_in_time) = CURDATE()"; // Enregistrements d'aujourd'hui
        $sql .= " AND (r.status = " . TimeclockConstants::STATUS_COMPLETED;
        $sql .= " OR r.status = " . TimeclockConstants::STATUS_IN_PROGRESS . ")"; // Sessions terminées ou en cours
        $sql .= " ORDER BY r.clock_in_time DESC";
        
        $result = $this->db->query($sql);
        $todaysRecords = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                // Enrichir avec informations utilisateur et anomalies
                $record = $this->enrichRecordData($obj);
                
                // Détecter anomalies pour cet enregistrement
                $record['anomalies'] = $this->detectRecordAnomalies($obj);
                
                $todaysRecords[] = $record;
            }
            $this->db->free($result);
        }
        
        dol_syslog("ValidationService: Found " . count($todaysRecords) . " today's records", LOG_INFO);
        return $todaysRecords;
    }
    
    /**
     * Valider un temps de travail
     */
    public function validateRecord(int $recordId, int $validatorId, string $action, ?string $comment = null): bool 
    {
        dol_syslog("ValidationService: Validating record $recordId with action $action by validator $validatorId", LOG_DEBUG);
        
        // 1. Vérifier les permissions
        if (!$this->canValidate($validatorId, $recordId)) {
            $this->setError("Insufficient permissions to validate this record");
            return false;
        }
        
        // 2. Déterminer le nouveau statut
        $newStatus = match($action) {
            'approve' => ValidationConstants::VALIDATION_APPROVED,
            'reject' => ValidationConstants::VALIDATION_REJECTED,
            'partial' => ValidationConstants::VALIDATION_PARTIAL,
            default => null
        };
        
        if ($newStatus === null) {
            $this->setError("Invalid validation action: $action");
            return false;
        }
        
        // 3. Mettre à jour l'enregistrement (adapter au schéma existant)
        $sql = "UPDATE " . MAIN_DB_PREFIX . "timeclock_records SET";
        $sql .= " validated_by = " . ((int) $validatorId);
        $sql .= ", validated_date = NOW()";
        
        if ($comment) {
            $sql .= ", note_private = '" . $this->db->escape($comment) . "'";
        }
        
        $sql .= " WHERE rowid = " . ((int) $recordId);
        
        $result = $this->db->query($sql);
        
        if ($result) {
            // 4. Notifier l'employé du résultat
            $recordData = $this->getRecordData($recordId);
            if ($recordData && isset($recordData['fk_user'])) {
                $this->notificationService->notifyValidationStatus(
                    $recordData['fk_user'], 
                    $recordId, 
                    $action
                );
            }
            
            dol_syslog("ValidationService: Record $recordId validated successfully with status $newStatus", LOG_DEBUG);
            return true;
        }
        
        $this->setError("Database error while validating record");
        return false;
    }
    
    /**
     * Valider en lot plusieurs enregistrements
     */
    public function batchValidate(array $recordIds, int $validatorId, string $action): array 
    {
        dol_syslog("ValidationService: Batch validating " . count($recordIds) . " records", LOG_DEBUG);
        
        $results = [];
        
        // Valider chaque enregistrement individuellement
        foreach ($recordIds as $recordId) {
            $results[$recordId] = $this->validateRecord((int) $recordId, $validatorId, $action);
        }
        
        $successCount = count(array_filter($results));
        dol_syslog("ValidationService: Batch validation completed: $successCount/" . count($recordIds) . " successful", LOG_DEBUG);
        
        return $results;
    }
    
    /**
     * Détecter les anomalies dans les temps d'un utilisateur
     */
    public function detectAnomalies(int $userId, string $period): array 
    {
        dol_syslog("ValidationService: Detecting anomalies for user $userId in period $period", LOG_DEBUG);
        
        $anomalies = [];
        
        // Récupérer les enregistrements selon la période
        $records = $this->getRecordsByPeriod($userId, $period);
        
        foreach ($records as $record) {
            $recordAnomalies = $this->detectRecordAnomalies($record);
            if (!empty($recordAnomalies)) {
                $anomalies[] = [
                    'record_id' => $record->rowid,
                    'anomalies' => $recordAnomalies,
                    'date' => $record->clock_in_time
                ];
            }
        }
        
        dol_syslog("ValidationService: Found " . count($anomalies) . " anomalies", LOG_DEBUG);
        return $anomalies;
    }
    
    /**
     * Récupérer le statut de validation d'un enregistrement
     */
    public function getValidationStatus(int $recordId): array 
    {
        $sql = "SELECT validated_by, validated_date, note_private";
        $sql .= " FROM " . MAIN_DB_PREFIX . "timeclock_records";
        $sql .= " WHERE rowid = " . ((int) $recordId);
        
        $result = $this->db->query($sql);
        
        if ($result && $obj = $this->db->fetch_object($result)) {
            $this->db->free($result);
            
            // Déterminer le statut basé sur validated_by
            $status = $obj->validated_by ? ValidationConstants::VALIDATION_APPROVED : ValidationConstants::VALIDATION_PENDING;
            
            return [
                'status' => $status,
                'validated_by' => (int) $obj->validated_by,
                'validated_at' => $obj->validated_date,
                'comment' => $obj->note_private,
                'status_label' => $this->getValidationStatusLabel($status)
            ];
        }
        
        return [];
    }
    
    /**
     * Vérifier si un utilisateur peut valider un enregistrement
     */
    public function canValidate(int $userId, int $recordId): bool 
    {
        // 1. Récupérer les données de l'enregistrement
        $recordData = $this->getRecordData($recordId);
        
        if (!$recordData) {
            return false;
        }
        
        // 2. Vérifier que l'utilisateur n'est pas le propriétaire de l'enregistrement
        if ($recordData['fk_user'] == $userId) {
            return false;
        }
        
        // 3. Vérifier que l'utilisateur a des droits de validation
        global $user;
        if (empty($user->rights->appmobtimetouch->timeclock->validate)) {
            return false;
        }
        
        // 4. Vérifier que l'utilisateur est manager de l'employé
        $teamMembers = $this->getTeamMembers($userId);
        return in_array($recordData['fk_user'], $teamMembers);
    }
    
    /**
     * Récupérer les statistiques de validation pour un manager
     */
    public function getValidationStats(int $managerId, string $period = 'week'): array 
    {
        $teamMembers = $this->getTeamMembers($managerId);
        
        if (empty($teamMembers)) {
            return [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'total' => 0
            ];
        }
        
        // Construire la condition de période
        $periodCondition = $this->buildPeriodCondition($period);
        
        // Compter les enregistrements validés et non validés
        $sql = "SELECT";
        $sql .= " SUM(CASE WHEN validated_by IS NULL THEN 1 ELSE 0 END) as pending,";
        $sql .= " SUM(CASE WHEN validated_by IS NOT NULL THEN 1 ELSE 0 END) as approved,";
        $sql .= " COUNT(*) as total";
        $sql .= " FROM " . MAIN_DB_PREFIX . "timeclock_records";
        $sql .= " WHERE fk_user IN (" . implode(',', array_map('intval', $teamMembers)) . ")";
        $sql .= " AND status = " . TimeclockConstants::STATUS_COMPLETED;
        $sql .= $periodCondition;
        
        $result = $this->db->query($sql);
        $stats = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'partial' => 0,
            'total' => 0
        ];
        
        if ($result && $obj = $this->db->fetch_object($result)) {
            $stats['pending'] = (int) $obj->pending;
            $stats['approved'] = (int) $obj->approved;
            $stats['total'] = (int) $obj->total;
            $this->db->free($result);
        }
        
        return $stats;
    }
    
    /**
     * Obtenir les membres d'équipe d'un manager
     */
    public function getTeamMembers(int $managerId): array 
    {
        // Pour cette implémentation simplifiée, un manager avec droits validate peut valider tous les employés
        // Dans une version avancée, on pourrait avoir une table dédiée aux équipes
        
        global $user;
        $teamMembers = [];
        
        // Vérifier que l'utilisateur actuel a les droits de validation
        if (empty($user->rights->appmobtimetouch->timeclock->validate)) {
            dol_syslog("ValidationService: User $managerId has no validation rights", LOG_DEBUG);
            return [];
        }
        dol_syslog("ValidationService: User $managerId has validation rights, proceeding", LOG_DEBUG);
        
        // Si admin, peut valider tous les utilisateurs
        if ($user->admin) {
            $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "user WHERE statut = 1 AND rowid != " . ((int) $managerId);
        } else {
            // Si manager non-admin, peut valider tous les employés (non-admin)
            $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "user WHERE statut = 1 AND admin = 0 AND rowid != " . ((int) $managerId);
        }
        
        $result = $this->db->query($sql);
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $teamMembers[] = (int) $obj->rowid;
            }
            $this->db->free($result);
        }
        
        dol_syslog("ValidationService: Manager $managerId can validate " . count($teamMembers) . " team members", LOG_DEBUG);
        return $teamMembers;
    }
    
    /**
     * Vérifier si une validation automatique est possible
     */
    public function canAutoValidate(int $recordId): bool 
    {
        $recordData = $this->getRecordData($recordId);
        
        if (!$recordData) {
            return false;
        }
        
        // Récupérer le seuil d'auto-validation
        $autoApproveThreshold = TimeclockConstants::getValue(
            $this->db, 
            ValidationConstants::AUTO_APPROVE_THRESHOLD, 
            8
        );
        
        // Calculer les heures travaillées
        if (!empty($recordData['work_duration'])) {
            $hoursWorked = $recordData['work_duration'] / 60; // Convertir minutes en heures
            
            // Auto-valider si sous le seuil et pas d'anomalies critiques
            if ($hoursWorked <= $autoApproveThreshold) {
                $anomalies = $this->detectRecordAnomalies((object) $recordData);
                $hasCriticalAnomalies = array_filter($anomalies, function($anomaly) {
                    return $anomaly['level'] === ValidationConstants::ALERT_CRITICAL;
                });
                
                return empty($hasCriticalAnomalies);
            }
        }
        
        return false;
    }
    
    // === MÉTHODES PRIVÉES UTILITAIRES ===
    
    /**
     * Enrichir un enregistrement avec des données supplémentaires (public pour controller)
     */
    public function enrichRecordData(object $record): array 
    {
        // Convertir l'objet en tableau
        $enriched = (array) $record;
        
        // Ajouter informations utilisateur
        $userInfo = $this->getUserInfo((int) $record->fk_user);
        $enriched['user'] = $userInfo;
        
        // Calculer work_duration si manquant et que clock_out existe
        if (empty($record->work_duration) && !empty($record->clock_out_time) && !empty($record->clock_in_time)) {
            $clockIn = strtotime($record->clock_in_time);
            $clockOut = strtotime($record->clock_out_time);
            if ($clockIn && $clockOut && $clockOut > $clockIn) {
                $durationMinutes = round(($clockOut - $clockIn) / 60);
                $enriched['work_duration'] = $durationMinutes;
            }
        }
        
        // Ajouter durée formatée
        if (!empty($enriched['work_duration'])) {
            $enriched['formatted_duration'] = TimeHelper::formatDuration((int) $enriched['work_duration']);
        }
        
        // Ajouter type de pointage
        if (!empty($record->fk_timeclock_type)) {
            $enriched['timeclock_type'] = $this->getTimeclockTypeInfo((int) $record->fk_timeclock_type);
        }
        
        return $enriched;
    }
    
    /**
     * Détecter les anomalies d'un enregistrement spécifique (public pour controller)
     */
    public function detectRecordAnomalies(object $record): array 
    {
        $anomalies = [];
        $anomalyTypes = ValidationConstants::getAnomalyTypes();
        
        // 1. Vérifier les heures supplémentaires
        if (!empty($record->work_duration)) {
            $hoursWorked = $record->work_duration / 60;
            $overtimeThreshold = $anomalyTypes[ValidationConstants::ANOMALY_OVERTIME]['threshold'];
            
            if ($hoursWorked > $overtimeThreshold) {
                $anomalies[] = [
                    'type' => ValidationConstants::ANOMALY_OVERTIME,
                    'level' => $anomalyTypes[ValidationConstants::ANOMALY_OVERTIME]['level'],
                    'message' => "Overtime detected: {$hoursWorked}h (threshold: {$overtimeThreshold}h)",
                    'data' => ['hours' => $hoursWorked, 'threshold' => $overtimeThreshold]
                ];
            }
        }
        
        // 2. Vérifier clock-out manquant (seulement si plus d'une journée)
        if (empty($record->clock_out_time) && $record->status == TimeclockConstants::STATUS_IN_PROGRESS) {
            // Vérifier si le clock-in est d'un jour précédent
            $clockInDate = date('Y-m-d', strtotime($record->clock_in_time));
            $currentDate = date('Y-m-d');
            
            if ($clockInDate < $currentDate) {
                $daysDiff = (strtotime($currentDate) - strtotime($clockInDate)) / (60 * 60 * 24);
                $anomalies[] = [
                    'type' => ValidationConstants::ANOMALY_MISSING_CLOCKOUT,
                    'level' => ValidationConstants::ALERT_CRITICAL,
                    'message' => "Missing clock-out time ({$daysDiff} day(s) ago)",
                    'data' => [
                        'clock_in_time' => $record->clock_in_time,
                        'days_ago' => $daysDiff,
                        'clock_in_date' => $clockInDate
                    ]
                ];
            }
        }
        
        // 3. Vérifier pause trop longue
        if (!empty($record->break_duration)) {
            $breakMinutes = $record->break_duration;
            $longBreakThreshold = $anomalyTypes[ValidationConstants::ANOMALY_LONG_BREAK]['threshold'];
            
            if ($breakMinutes > $longBreakThreshold) {
                $anomalies[] = [
                    'type' => ValidationConstants::ANOMALY_LONG_BREAK,
                    'level' => $anomalyTypes[ValidationConstants::ANOMALY_LONG_BREAK]['level'],
                    'message' => "Long break detected: {$breakMinutes}min (threshold: {$longBreakThreshold}min)",
                    'data' => ['break_duration' => $breakMinutes, 'threshold' => $longBreakThreshold]
                ];
            }
        }
        
        return $anomalies;
    }
    
    /**
     * Récupérer les données d'un enregistrement (public pour controller)
     */
    public function getRecordData(int $recordId): ?array 
    {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_records WHERE rowid = " . ((int) $recordId);
        $result = $this->db->query($sql);
        
        if ($result && $obj = $this->db->fetch_object($result)) {
            $this->db->free($result);
            return (array) $obj;
        }
        
        return null;
    }
    
    /**
     * Récupérer les enregistrements d'un utilisateur selon une période
     */
    private function getRecordsByPeriod(int $userId, string $period): array 
    {
        $periodCondition = $this->buildPeriodCondition($period);
        
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_records";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= $periodCondition;
        $sql .= " ORDER BY clock_in_time DESC";
        
        $result = $this->db->query($sql);
        $records = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $records[] = $obj;
            }
            $this->db->free($result);
        }
        
        return $records;
    }
    
    /**
     * Construire condition SQL pour une période
     */
    private function buildPeriodCondition(string $period): string 
    {
        return match($period) {
            'day' => " AND DATE(clock_in_time) = CURDATE()",
            'week' => " AND YEAR(clock_in_time) = YEAR(NOW()) AND WEEK(clock_in_time) = WEEK(NOW())",
            'month' => " AND YEAR(clock_in_time) = YEAR(NOW()) AND MONTH(clock_in_time) = MONTH(NOW())",
            default => " AND DATE(clock_in_time) >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        };
    }
    
    /**
     * Récupérer informations utilisateur
     */
    private function getUserInfo(int $userId): array 
    {
        $sql = "SELECT firstname, lastname, email FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . ((int) $userId);
        $result = $this->db->query($sql);
        
        if ($result && $obj = $this->db->fetch_object($result)) {
            $this->db->free($result);
            return [
                'firstname' => $obj->firstname,
                'lastname' => $obj->lastname,
                'email' => $obj->email,
                'fullname' => trim($obj->firstname . ' ' . $obj->lastname)
            ];
        }
        
        return ['fullname' => 'Unknown User'];
    }
    
    /**
     * Récupérer informations type de pointage
     */
    private function getTimeclockTypeInfo(int $typeId): array 
    {
        $sql = "SELECT code, label, color FROM " . MAIN_DB_PREFIX . "timeclock_types WHERE rowid = " . ((int) $typeId);
        $result = $this->db->query($sql);
        
        if ($result && $obj = $this->db->fetch_object($result)) {
            $this->db->free($result);
            return [
                'code' => $obj->code,
                'label' => $obj->label,
                'color' => $obj->color
            ];
        }
        
        return ['label' => 'Type inconnu', 'color' => '#6c757d', 'code' => 'unknown'];
    }
    
    /**
     * Obtenir le libellé d'un statut de validation
     */
    private function getValidationStatusLabel(int $status): string 
    {
        $statuses = ValidationConstants::getValidationStatuses();
        return $statuses[$status] ?? 'Unknown';
    }
    
    /**
     * Gérer les erreurs
     */
    private function setError(string $message): void 
    {
        dol_syslog("ValidationService Error: $message", LOG_ERR);
        $this->error = $message;
    }
    
    public $error = '';
}