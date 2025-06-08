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
     * Action placeholder pour MVP futures (OCP - Ouvert à l'extension)
     * 
     * @return array Réponse JSON
     */
    public function validateRecord(): array 
    {
        // MVP 3.2 : Implémentation validation individuelle
        return [
            'error' => 1,
            'errors' => ['Feature coming in MVP 3.2']
        ];
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