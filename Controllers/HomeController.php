<?php
/**
 * Contrôleur page accueil - Responsabilité unique : Logique page home
 * Dépend abstractions : Services injectés
 * 
 * Respecte le principe SRP : Responsabilité unique pour la page d'accueil
 * Respecte le principe OCP : Extensible pour nouvelles actions
 * Respecte le principe DIP : Dépend d'interfaces, pas d'implémentations
 */

class HomeController extends BaseController 
{
    private TimeclockServiceInterface $timeclockService;
    private DataServiceInterface $dataService;
    
    /**
     * Constructor avec injection de dépendances
     * 
     * @param DoliDB $db Base de données
     * @param User $user Utilisateur
     * @param Translate $langs Traductions
     * @param Conf $conf Configuration
     * @param TimeclockServiceInterface $timeclockService Service de pointage
     * @param DataServiceInterface $dataService Service de données
     */
    public function __construct(
        $db, 
        $user, 
        $langs, 
        $conf,
        TimeclockServiceInterface $timeclockService,
        DataServiceInterface $dataService
    ) {
        parent::__construct($db, $user, $langs, $conf);
        $this->timeclockService = $timeclockService;
        $this->dataService = $dataService;
    }
    
    /**
     * Action principale de la page d'accueil
     * 
     * @return array Données pour le template
     */
    public function index(): array 
    {
        // Vérifications sécurité
        $this->checkModuleEnabled();
        $this->checkUserRights('read');
        
        $action = GETPOST('action', 'aZ09');
        $view = GETPOST('view', 'int') ?: 1;
        
        $result = [
            'error' => 0,
            'errors' => [],
            'messages' => []
        ];
        
        // Traitement des actions si permissions suffisantes
        if ($action && $this->isActionAllowed($action)) {
            $actionResult = $this->handleAction($action);
            $result = array_merge($result, $actionResult);
        }
        
        // Messages de succès depuis les redirections
        if (GETPOST('clockin_success', 'int')) {
            $result['messages'][] = $this->langs->trans(TimeclockConstants::MSG_CLOCKIN_SUCCESS);
        }
        if (GETPOST('clockout_success', 'int')) {
            $result['messages'][] = $this->langs->trans(TimeclockConstants::MSG_CLOCKOUT_SUCCESS);
        }
        
        // Préparation des données de la page
        $pageData = $this->preparePageData($view);
        
        return $this->prepareTemplateData(array_merge($result, $pageData));
    }
    
    /**
     * Gestion centralisée des actions - Ouvert à l'extension (OCP)
     * 
     * @param string $action Action à traiter
     * @return array Résultat de l'action
     */
    private function handleAction(string $action): array 
    {
        try {
            return match($action) {
                'clockin' => $this->handleClockIn(),
                'clockout' => $this->handleClockOut(),
                default => ['error' => 1, 'errors' => ["Unknown action: $action"]]
            };
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }
    
    /**
     * Traitement de l'action de pointage d'entrée
     * 
     * @return array Résultat de l'action (redirection ou erreur)
     */
    private function handleClockIn(): array 
    {
        $validation = $this->validatePostParams([
            'timeclock_type_id' => 'int',
            'location' => 'alphanohtml',
            'latitude' => 'float',
            'longitude' => 'float',
            'note' => 'restricthtml'
        ]);
        
        if (!empty($validation['errors'])) {
            return [
                'error' => 1,
                'errors' => $validation['errors']
            ];
        }
        
        // Utiliser le service avec gestion d'exceptions
        $result = $this->timeclockService->clockIn($this->user, $validation['params']);
        
        // Redirection pour éviter la resoumission
        $this->redirectWithSuccess($_SERVER['PHP_SELF'], 'clockin_success');
    }
    
    /**
     * Traitement de l'action de pointage de sortie
     * 
     * @return array Résultat de l'action (redirection ou erreur)
     */
    private function handleClockOut(): array 
    {
        $validation = $this->validatePostParams([
            'location' => 'alphanohtml',
            'latitude' => 'float',
            'longitude' => 'float',
            'note' => 'restricthtml'
        ]);
        
        if (!empty($validation['errors'])) {
            return [
                'error' => 1,
                'errors' => $validation['errors']
            ];
        }
        
        // Utiliser le service avec gestion d'exceptions
        $result = $this->timeclockService->clockOut($this->user, $validation['params']);
        
        // Redirection pour éviter la resoumission
        $this->redirectWithSuccess($_SERVER['PHP_SELF'], 'clockout_success');
    }
    
    /**
     * Préparation des données spécifiques à la page d'accueil
     * 
     * @param int $view Vue sélectionnée (1=aujourd'hui, 2=semaine, 3=tout)
     * @return array Données pour le template
     */
    private function preparePageData(int $view): array 
    {
        // Récupération du statut de pointage de l'utilisateur
        $activeRecord = $this->timeclockService->getActiveRecord($this->user->id);
        $isClocked = !is_null($activeRecord);
        
        // Calcul de la durée actuelle si pointé
        $currentDuration = 0;
        $clockInTime = null;
        
        if ($isClocked && $activeRecord) {
            $clockInTime = $this->extractTimestamp($activeRecord->clock_in_time);
            if ($clockInTime) {
                $currentDuration = TimeHelper::calculateDuration($clockInTime, dol_now());
            }
        }
        
        // Récupération des résumés via le service de données
        $todaySummary = $this->dataService->calculateTodaySummary($this->user->id);
        $weeklySummary = $this->dataService->calculateWeeklySummary($this->user->id);
        
        // Ajout de la durée active au résumé d'aujourd'hui
        $todayTotalHours = ($todaySummary['total_hours'] ?? 0);
        if ($isClocked && $currentDuration > 0) {
            $todayTotalHours += ($currentDuration / 3600); // Convertir secondes en heures
        }
        
        // Configuration du module
        $requireLocation = TimeclockConstants::getValue($this->db, TimeclockConstants::REQUIRE_LOCATION, 0);
        $overtimeThreshold = TimeclockConstants::getValue($this->db, TimeclockConstants::OVERTIME_THRESHOLD, TimeclockConstants::DEFAULT_OVERTIME_THRESHOLD);
        $maxHoursPerDay = TimeclockConstants::getValue($this->db, TimeclockConstants::MAX_HOURS_PER_DAY, TimeclockConstants::DEFAULT_MAX_HOURS);
        
        // Données pour le template
        return [
            'view' => $view,
            'is_clocked_in' => $isClocked,
            'active_record' => $activeRecord,
            'clock_in_time' => $clockInTime,
            'current_duration' => $currentDuration,
            'today_total_hours' => $todayTotalHours,
            'today_total_breaks' => $todaySummary['total_breaks'] ?? 0,
            'today_summary' => $todaySummary,
            'weekly_summary' => $weeklySummary,
            'recent_records' => $this->dataService->getRecentRecords($this->user->id, 5),
            'timeclock_types' => $this->dataService->getActiveTimeclockTypes(),
            'default_type_id' => $this->dataService->getDefaultTimeclockType(),
            'require_location' => $requireLocation,
            'overtime_threshold' => $overtimeThreshold,
            'max_hours_per_day' => $maxHoursPerDay,
            'overtime_alert' => $todayTotalHours > $overtimeThreshold,
            'js_data' => $this->prepareJavaScriptData($isClocked, $clockInTime, $requireLocation, $overtimeThreshold, $maxHoursPerDay)
        ];
    }
    
    /**
     * Extraction intelligente du timestamp depuis différents formats
     * 
     * @param mixed $rawTimestamp Timestamp brut de la base
     * @return int|null Timestamp Unix ou null
     */
    private function extractTimestamp($rawTimestamp): ?int 
    {
        if (empty($rawTimestamp)) {
            return null;
        }
        
        // Méthode 1: Vérifier si c'est déjà un timestamp Unix valide
        if (is_numeric($rawTimestamp) && $rawTimestamp > 946684800 && $rawTimestamp < 4102444800) {
            return (int) $rawTimestamp;
        }
        
        // Méthode 2: Conversion avec jdate pour les formats Dolibarr
        $converted = $this->db->jdate($rawTimestamp);
        if ($converted && is_numeric($converted)) {
            return (int) $converted;
        }
        
        // Méthode 3: Fallback avec strtotime
        $fallback = strtotime($rawTimestamp);
        if ($fallback !== false && $fallback > 0) {
            return $fallback;
        }
        
        return null;
    }
    
    /**
     * Préparation des données JavaScript pour l'interface mobile
     * 
     * @param bool $isClockedIn Statut de pointage
     * @param int|null $clockInTime Heure de pointage
     * @param int $requireLocation Localisation requise
     * @param int $overtimeThreshold Seuil heures supplémentaires
     * @param int $maxHoursPerDay Maximum heures par jour
     * @return array Données JavaScript
     */
    private function prepareJavaScriptData(bool $isClockedIn, ?int $clockInTime, int $requireLocation, int $overtimeThreshold, int $maxHoursPerDay): array 
    {
        return [
            'is_clocked_in' => $isClockedIn,
            'clock_in_time' => $clockInTime,
            'require_location' => $requireLocation,
            'default_type_id' => $this->dataService->getDefaultTimeclockType(),
            'max_hours_per_day' => $maxHoursPerDay,
            'overtime_threshold' => $overtimeThreshold,
            'api_token' => function_exists('newToken') ? newToken() : '',
            'user_id' => $this->user->id,
            'version' => '1.0'
        ];
    }
}