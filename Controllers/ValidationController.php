<?php
/**
 * Contrôleur Validation - Responsabilité unique : Interface validation manager
 * 
 * Respecte le principe SRP : Seule responsabilité gestion interface validation
 * Respecte le principe OCP : Extension de BaseController sans modification
 * Respecte le principe DIP : Dépend d'interfaces de services
 * 
 * MVP 3.1 : Dashboard manager minimal avec statistiques de base
 */

require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Controllers/BaseController.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/ValidationServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/NotificationServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/DataServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/ValidationService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/NotificationService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/DataService.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Constants/ValidationConstants.php';

class ValidationController extends BaseController 
{
    private ValidationServiceInterface $validationService;
    private NotificationServiceInterface $notificationService;
    private DataServiceInterface $dataService;
    
    /**
     * Constructor avec injection de dépendances (DIP)
     * 
     * @param $db Base de données Dolibarr
     * @param $user Utilisateur courant  
     * @param $langs Gestionnaire traductions
     * @param $conf Configuration Dolibarr
     */
    public function __construct($db, $user, $langs, $conf) 
    {
        parent::__construct($db, $user, $langs, $conf);
        
        // Injection de dépendances via interfaces (DIP)
        $this->dataService = new DataService($db);
        $this->notificationService = new NotificationService($db);
        $this->validationService = new ValidationService($db, $this->dataService, $this->notificationService);
    }
    
    /**
     * Dashboard manager - Vue d'ensemble validations (MVP 3.1)
     * Responsabilité unique : Affichage dashboard manager (SRP)
     * 
     * @return array Données pour template dashboard
     */
    public function dashboard(): array 
    {
        // Vérification module et droits
        $this->checkModuleEnabled();
        $this->checkUserRights('validate');
        
        try {
            // Récupérer données de base pour dashboard minimal (MVP 3.1)
            dol_syslog("ValidationController: Starting dashboard for manager " . $this->user->id, LOG_DEBUG);
            
            $pendingRecords = $this->validationService->getPendingValidations($this->user->id);
            dol_syslog("ValidationController: Found " . count($pendingRecords) . " pending records", LOG_INFO);
            
            $notifications = $this->notificationService->getUnreadNotifications($this->user->id);
            dol_syslog("ValidationController: Found " . count($notifications) . " notifications", LOG_DEBUG);
            
            // Calcul statistiques essentielles pour MVP 3.1
            $stats = $this->calculateBasicStats($pendingRecords);
            dol_syslog("ValidationController: Stats calculated - with_anomalies: " . $stats['with_anomalies'], LOG_INFO);
            
            dol_syslog("ValidationController: Dashboard loaded for manager " . $this->user->id, LOG_INFO);
            
            return $this->prepareTemplateData([
                'page_title' => $this->langs->trans('ValidationDashboard'),
                'pending_records' => array_slice($pendingRecords, 0, 10), // CORRIGÉ: Vrais enregistrements en attente
                'notifications' => array_slice($notifications, 0, 3), // Limite pour MVP 3.1  
                'stats' => $stats,
                'is_manager' => true,
                'dashboard_type' => 'validation_manager'
            ]);
            
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    /**
     * Calcul statistiques de base pour MVP 3.1
     * Responsabilité unique : Calcul des métriques essentielles (SRP)
     * 
     * @param array $pendingRecords Enregistrements en attente
     * @return array Statistiques calculées
     */
    private function calculateBasicStats(array $pendingRecords): array 
    {
        $stats = [
            'total_pending' => count($pendingRecords),
            'with_anomalies' => 0,
            'urgent_count' => 0,
            'today_pending' => 0
        ];
        
        $today = date('Y-m-d');
        
        foreach ($pendingRecords as $record) {
            dol_syslog("ValidationController: Processing record ID " . (is_array($record) ? $record['rowid'] : $record->rowid), LOG_DEBUG);
            
            // Compter anomalies
            $anomalies = is_array($record) ? ($record['anomalies'] ?? []) : ($record->anomalies ?? []);
            if (!empty($anomalies)) {
                $stats['with_anomalies']++;
                dol_syslog("ValidationController: Record has " . count($anomalies) . " anomalies", LOG_DEBUG);
            } else {
                dol_syslog("ValidationController: Record has no anomalies", LOG_DEBUG);
            }
            
            // Compter urgents (plus de 2 jours)
            $clockInTime = is_array($record) ? $record['clock_in_time'] : $record->clock_in_time;
            $recordDate = date('Y-m-d', strtotime($clockInTime));
            $daysDiff = (strtotime($today) - strtotime($recordDate)) / (60 * 60 * 24);
            if ($daysDiff > 2) {
                $stats['urgent_count']++;
            }
            
            // Compter today
            if ($recordDate === $today) {
                $stats['today_pending']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Validation individuelle d'un enregistrement (MVP 3.2)
     * Responsabilité unique : Traitement validation individuelle (SRP)
     * 
     * @return array Réponse JSON avec résultat validation
     */
    public function validateRecord(): array 
    {
        // Vérification module et droits
        $this->checkModuleEnabled();
        $this->checkUserRights('validate');
        
        try {
            // Validation des paramètres requis pour MVP 3.2
            $validation = $this->validatePostParams([
                'record_id' => 'int',
                'validation_action' => 'alpha'
            ]);
            
            if (!empty($validation['errors'])) {
                return [
                    'error' => 1,
                    'errors' => $validation['errors']
                ];
            }
            
            $params = $validation['params'];
            $recordId = $params['record_id'];
            $action = $params['validation_action'];
            $comment = GETPOST('comment', 'restricthtml'); // Optionnel
            
            // Validation de l'action
            $allowedActions = ['approve', 'reject', 'partial'];
            if (!in_array($action, $allowedActions)) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('InvalidValidationAction')]
                ];
            }
            
            // Vérification que l'enregistrement existe et peut être validé
            if (!$this->validationService->canValidate($this->user->id, $recordId)) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('InsufficientPermissionsForRecord')]
                ];
            }
            
            // Effectuer la validation via le service (DIP)
            $result = $this->validationService->validateRecord(
                $recordId, 
                $this->user->id, 
                $action, 
                $comment
            );
            
            if ($result) {
                // Succès de la validation
                $actionLabels = [
                    'approve' => $this->langs->trans('RecordApproved'),
                    'reject' => $this->langs->trans('RecordRejected'),
                    'partial' => $this->langs->trans('RecordPartiallyApproved')
                ];
                
                dol_syslog("ValidationController: Record $recordId validated as $action by user " . $this->user->id, LOG_INFO);
                
                return [
                    'error' => 0,
                    'messages' => [$actionLabels[$action] ?? $this->langs->trans('ValidationCompleted')],
                    'action' => $action,
                    'record_id' => $recordId,
                    'validated_by' => $this->user->id,
                    'timestamp' => dol_now()
                ];
            } else {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('ValidationFailed')]
                ];
            }
            
        } catch (Exception $e) {
            dol_syslog("ValidationController: Error in validateRecord - " . $e->getMessage(), LOG_ERROR);
            return $this->handleError($e);
        }
    }
    
    /**
     * Récupérer détails d'un enregistrement pour validation (MVP 3.2)
     * Responsabilité unique : Fournir données enregistrement (SRP)
     * 
     * @return array Données enregistrement pour interface
     */
    public function getRecordDetails(): array 
    {
        // Vérification module et droits
        $this->checkModuleEnabled();
        $this->checkUserRights('validate');
        
        try {
            $recordId = GETPOST('record_id', 'int');
            
            if (!$recordId) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('MissingRecordId')]
                ];
            }
            
            // Vérifier permissions sur cet enregistrement
            if (!$this->validationService->canValidate($this->user->id, $recordId)) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('InsufficientPermissionsForRecord')]
                ];
            }
            
            // Charger l'enregistrement via DataService (DIP)
            $record = new TimeclockRecord($this->db);
            if ($record->fetch($recordId) <= 0) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('RecordNotFound')]
                ];
            }
            
            // Enrichir avec infos validation et anomalies
            $validationStatus = $this->validationService->getValidationStatus($recordId);
            $anomalies = $this->detectRecordAnomalies($record);
            
            // Récupérer infos utilisateur
            $user_obj = new User($this->db);
            $user_obj->fetch($record->fk_user);
            
            // Récupérer type de pointage
            $type = new TimeclockType($this->db);
            $type->fetch($record->fk_timeclock_type);
            
            return [
                'error' => 0,
                'record' => [
                    'id' => $record->rowid,
                    'user_id' => $record->fk_user,
                    'user_name' => $user_obj->getFullName($this->langs),
                    'clock_in_time' => $record->clock_in_time,
                    'clock_out_time' => $record->clock_out_time,
                    'work_duration' => $record->work_duration,
                    'break_duration' => $record->break_duration,
                    'location_in' => $record->location_in,
                    'location_out' => $record->location_out,
                    'note' => $record->note,
                    'type' => [
                        'id' => $type->id,
                        'label' => $type->label,
                        'color' => $type->color
                    ],
                    'validation_status' => $validationStatus,
                    'anomalies' => $anomalies,
                    'can_validate' => true
                ]
            ];
            
        } catch (Exception $e) {
            dol_syslog("ValidationController: Error in getRecordDetails - " . $e->getMessage(), LOG_ERROR);
            return $this->handleError($e);
        }
    }
    
    /**
     * Détecter anomalies pour un enregistrement (Helper MVP 3.2)
     * 
     * @param TimeclockRecord $record Enregistrement à analyser
     * @return array Anomalies détectées
     */
    private function detectRecordAnomalies(TimeclockRecord $record): array 
    {
        $anomalies = [];
        
        // Overtime (plus de 8h)
        if ($record->work_duration > 480) { // 8h en minutes
            $anomalies[] = [
                'type' => 'overtime',
                'level' => 'warning',
                'message' => $this->langs->trans('OvertimeDetected') . ': ' . 
                           TimeHelper::convertSecondsToReadableTime($record->work_duration * 60)
            ];
        }
        
        // Clock-out manquant
        if (empty($record->clock_out_time) && $record->status == 2) { // Status in progress
            $anomalies[] = [
                'type' => 'missing_clockout',
                'level' => 'critical',
                'message' => $this->langs->trans('MissingClockOut')
            ];
        }
        
        // Pause longue (plus de 90 minutes)
        if ($record->break_duration > 90) {
            $anomalies[] = [
                'type' => 'long_break',
                'level' => 'info',
                'message' => $this->langs->trans('ExtendedBreak') . ': ' . $record->break_duration . ' minutes'
            ];
        }
        
        return $anomalies;
    }

    /**
     * Validation en lot (MVP 3.3)
     * Responsabilité unique : Traitement validation groupée (SRP)
     * 
     * @return array Réponse JSON avec résultats validation en lot
     */
    public function batchValidate(): array 
    {
        // Vérification module et droits
        $this->checkModuleEnabled();
        $this->checkUserRights('validate');
        
        try {
            // Validation des paramètres requis pour MVP 3.3
            $recordIds = GETPOST('record_ids', 'array');
            $action = GETPOST('batch_action', 'alpha');
            $comment = GETPOST('batch_comment', 'restricthtml'); // Optionnel
            
            if (empty($recordIds) || empty($action)) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('MissingRequiredParameters')]
                ];
            }
            
            // Validation de l'action
            $allowedActions = ['approve', 'reject', 'partial'];
            if (!in_array($action, $allowedActions)) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('InvalidBatchAction')]
                ];
            }
            
            // Vérifier que l'utilisateur peut valider tous ces enregistrements
            foreach ($recordIds as $recordId) {
                if (!$this->validationService->canValidate($this->user->id, (int)$recordId)) {
                    return [
                        'error' => 1,
                        'errors' => [$this->langs->trans('InsufficientPermissionsForSomeRecords')]
                    ];
                }
            }
            
            // Effectuer la validation en lot via le service (DIP)
            $results = $this->validationService->batchValidate(
                array_map('intval', $recordIds),
                $this->user->id,
                $action,
                $comment
            );
            
            // Analyser les résultats
            $successCount = count(array_filter($results));
            $totalCount = count($results);
            $failedCount = $totalCount - $successCount;
            
            if ($successCount === $totalCount) {
                // Tous les enregistrements validés avec succès
                $actionLabels = [
                    'approve' => $this->langs->trans('BatchApproved'),
                    'reject' => $this->langs->trans('BatchRejected'),
                    'partial' => $this->langs->trans('BatchPartiallyApproved')
                ];
                
                dol_syslog("ValidationController: Batch validation successful - $successCount records validated as $action by user " . $this->user->id, LOG_INFO);
                
                return [
                    'error' => 0,
                    'messages' => [sprintf('%s (%d/%d)', $actionLabels[$action] ?? $this->langs->trans('BatchValidationCompleted'), $successCount, $totalCount)],
                    'action' => $action,
                    'success_count' => $successCount,
                    'total_count' => $totalCount,
                    'results' => $results,
                    'validated_by' => $this->user->id,
                    'timestamp' => dol_now()
                ];
            } else {
                // Validation partielle
                dol_syslog("ValidationController: Batch validation partial - $successCount/$totalCount records validated by user " . $this->user->id, LOG_WARNING);
                
                return [
                    'error' => 1,
                    'errors' => [sprintf($this->langs->trans('BatchValidationPartial'), $successCount, $totalCount, $failedCount)],
                    'success_count' => $successCount,
                    'total_count' => $totalCount,
                    'failed_count' => $failedCount,
                    'results' => $results
                ];
            }
            
        } catch (Exception $e) {
            dol_syslog("ValidationController: Error in batchValidate - " . $e->getMessage(), LOG_ERROR);
            return $this->handleError($e);
        }
    }
    
    /**
     * Afficher détails d'un enregistrement avec actions validation (MVP 3.2)
     * Responsabilité unique : Préparer données pour vue détaillée (SRP)
     * 
     * @param int $recordId ID de l'enregistrement
     * @return array Données pour template record-detail
     */
    public function viewRecord(int $recordId): array 
    {
        // Vérification module et droits
        $this->checkModuleEnabled();
        $this->checkUserRights('validate');
        
        try {
            // Vérifier permissions sur cet enregistrement
            if (!$this->validationService->canValidate($this->user->id, $recordId)) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('InsufficientPermissionsForRecord')]
                ];
            }
            
            // Récupérer détails via le service
            $recordDetails = $this->validationService->getRecordData($recordId);
            
            if (!$recordDetails) {
                return [
                    'error' => 1,
                    'errors' => [$this->langs->trans('RecordNotFound')]
                ];
            }
            
            // Enrichir avec informations utilisateur
            $enrichedRecord = $this->validationService->enrichRecordData((object)$recordDetails);
            
            // Détecter anomalies
            $anomalies = $this->validationService->detectRecordAnomalies((object)$recordDetails);
            
            // Statut de validation
            $validationStatus = $this->validationService->getValidationStatus($recordId);
            
            // Structurer les données pour correspondre aux templates
            $record = [
                'id' => $recordId,
                'rowid' => $recordId,
                'user_name' => $enrichedRecord['user']['fullname'] ?? 'Utilisateur inconnu',
                'clock_in_time' => $enrichedRecord['clock_in_time'],
                'clock_out_time' => $enrichedRecord['clock_out_time'],
                'work_duration' => $enrichedRecord['work_duration'],
                'break_duration' => $enrichedRecord['break_duration'] ?? 0,
                'location_in' => $enrichedRecord['location_in'] ?? '',
                'location_out' => $enrichedRecord['location_out'] ?? '',
                'note' => $enrichedRecord['note'] ?? '',
                'type' => $enrichedRecord['timeclock_type'] ?? ['label' => 'Type inconnu', 'color' => '#6c757d'],
                'validation_status' => $validationStatus ?? ['status' => 0, 'status_label' => 'En attente']
            ];
            
            return $this->prepareTemplateData([
                'page_title' => $this->langs->trans('RecordDetails'),
                'record' => $record,
                'anomalies' => $anomalies,
                'validation_status' => $validationStatus,
                'is_manager' => true,
                'view_type' => 'record_detail'
            ]);
            
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    /**
     * Récupérer tous les enregistrements en attente (MVP 3.3)
     * Responsabilité unique : Fournir liste complète pour vue étendue (SRP)
     * 
     * @return array Données JSON avec tous les enregistrements en attente
     */
    public function getAllPending(): array 
    {
        // Vérification module et droits
        $this->checkModuleEnabled();
        $this->checkUserRights('validate');
        
        try {
            // Récupérer tous les enregistrements en attente (déjà enrichis avec anomalies)
            $pendingRecords = $this->validationService->getPendingValidations($this->user->id);
            
            dol_syslog("ValidationController: getAllPending returned " . count($pendingRecords) . " records for manager " . $this->user->id, LOG_INFO);
            
            return [
                'error' => 0,
                'records' => $pendingRecords, // Already enriched by ValidationService
                'count' => count($pendingRecords),
                'manager_id' => $this->user->id,
                'timestamp' => dol_now()
            ];
            
        } catch (Exception $e) {
            dol_syslog("ValidationController: Error in getAllPending - " . $e->getMessage(), LOG_ERROR);
            return $this->handleError($e);
        }
    }
    
    /**
     * Page liste complète avec filtres (MVP 3.3)
     * Responsabilité unique : Affichage liste filtrée pour managers (SRP)
     * 
     * @return array Données pour template liste complète
     */
    public function listAll(): array 
    {
        // Vérification module et droits
        $this->checkModuleEnabled();
        $this->checkUserRights('validate');
        
        try {
            // Récupérer les filtres
            $filters = $this->getListFilters();
            
            // Récupérer tous les enregistrements selon les filtres
            $records = $this->validationService->getFilteredRecords($this->user->id, $filters);
            
            // Récupérer les utilisateurs d'équipe pour le filtre
            $teamMembers = $this->getTeamMembersForFilter();
            
            // Statistiques pour la liste filtrée
            $stats = $this->calculateFilteredStats($records);
            
            dol_syslog("ValidationController: listAll returned " . count($records) . " records with filters: " . json_encode($filters), LOG_INFO);
            
            return $this->prepareTemplateData([
                'page_title' => $this->langs->trans('AllValidationRecords'),
                'records' => $records,
                'filters' => $filters,
                'team_members' => $teamMembers,
                'stats' => $stats,
                'is_manager' => true,
                'view_type' => 'list_all'
            ]);
            
        } catch (Exception $e) {
            dol_syslog("ValidationController: Error in listAll - " . $e->getMessage(), LOG_ERROR);
            return $this->handleError($e);
        }
    }
    
    /**
     * Récupérer les filtres de la requête
     */
    private function getListFilters(): array 
    {
        return [
            'status' => GETPOST('filter_status', 'alpha'), // all, pending, approved, rejected, partial
            'user_id' => GETPOST('filter_user', 'int'),
            'date_from' => GETPOST('filter_date_from', 'alpha'),
            'date_to' => GETPOST('filter_date_to', 'alpha'),
            'has_anomalies' => GETPOST('filter_anomalies', 'alpha'), // yes, no, all
            'sort_by' => GETPOST('sort_by', 'alpha') ?: 'date_desc', // date_desc, date_asc, user_asc, status
            'limit' => GETPOST('limit', 'int') ?: 50 // pagination
        ];
    }
    
    /**
     * Récupérer membres équipe pour filtre utilisateur
     */
    private function getTeamMembersForFilter(): array 
    {
        $sql = "SELECT u.rowid, u.firstname, u.lastname FROM " . MAIN_DB_PREFIX . "user u";
        $sql .= " WHERE u.statut = 1"; // Utilisateurs actifs
        $sql .= " ORDER BY u.lastname, u.firstname";
        
        $result = $this->db->query($sql);
        $members = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $members[] = [
                    'id' => $obj->rowid,
                    'name' => trim($obj->firstname . ' ' . $obj->lastname)
                ];
            }
            $this->db->free($result);
        }
        
        return $members;
    }
    
    /**
     * Calculer statistiques pour liste filtrée
     */
    private function calculateFilteredStats(array $records): array 
    {
        $stats = [
            'total' => count($records),
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'partial' => 0,
            'with_anomalies' => 0,
            'today' => 0
        ];
        
        $today = date('Y-m-d');
        
        foreach ($records as $record) {
            // Compter par statut de validation
            switch ($record['validation_status']) {
                case 0: $stats['pending']++; break;
                case 1: $stats['approved']++; break;
                case 2: $stats['rejected']++; break;
                case 3: $stats['partial']++; break;
            }
            
            // Compter anomalies
            if (!empty($record['anomalies'])) {
                $stats['with_anomalies']++;
            }
            
            // Compter aujourd'hui
            $recordDate = date('Y-m-d', strtotime($record['clock_in_time']));
            if ($recordDate === $today) {
                $stats['today']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Helper pour vérifier si utilisateur est manager
     * 
     * @return bool True si manager
     */
    public function isManager(): bool 
    {
        return !empty($this->user->rights->appmobtimetouch->timeclock->validate);
    }
}