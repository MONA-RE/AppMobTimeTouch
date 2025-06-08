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
            $pendingRecords = $this->validationService->getPendingValidations($this->user->id);
            $notifications = $this->notificationService->getUnreadNotifications($this->user->id);
            
            // Calcul statistiques essentielles pour MVP 3.1
            $stats = $this->calculateBasicStats($pendingRecords);
            
            dol_syslog("ValidationController: Dashboard loaded for manager " . $this->user->id, LOG_INFO);
            
            return $this->prepareTemplateData([
                'page_title' => $this->langs->trans('ValidationDashboard'),
                'pending_records' => array_slice($pendingRecords, 0, 5), // Limite pour MVP 3.1
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
            // Compter anomalies
            if (!empty($record->anomalies)) {
                $stats['with_anomalies']++;
            }
            
            // Compter urgents (plus de 2 jours)
            $recordDate = date('Y-m-d', strtotime($record->clock_in_time));
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
     * Action placeholder pour MVP futures (OCP - Ouvert à l'extension)
     * 
     * @return array Réponse JSON  
     */
    public function batchValidate(): array 
    {
        // MVP 3.3 : Implémentation validation en lot
        return [
            'error' => 1,
            'errors' => ['Feature coming in MVP 3.3']
        ];
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