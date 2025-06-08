<?php
/**
 * Service timeclock métier - Inversion Dépendance (DIP)
 * Dépend d'abstractions, pas de concrétions
 * 
 * Respecte le principe SRP : Responsabilité unique pour les opérations de pointage
 * Respecte le principe OCP : Ouvert à l'extension via interfaces
 * Respecte le principe DIP : Dépend de DataServiceInterface, pas d'implémentation
 */

dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');

class TimeclockService implements TimeclockServiceInterface 
{
    private $db;
    private DataServiceInterface $dataService;
    
    /**
     * Constructor avec injection de dépendances
     * 
     * @param DoliDB $db Base de données Dolibarr
     * @param DataServiceInterface $dataService Service d'accès aux données
     */
    public function __construct($db, DataServiceInterface $dataService) 
    {
        $this->db = $db;
        $this->dataService = $dataService; // Injection dépendance
    }
    
    /**
     * Effectuer un pointage d'entrée
     */
    public function clockIn(User $user, array $params): int 
    {
        dol_syslog("TimeclockService::clockIn - User: " . $user->id, LOG_DEBUG);
        
        // Validation via helper
        $errors = $this->validateClockInParams($params);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // Vérifier pas déjà connecté
        $activeRecord = $this->getActiveRecord($user->id);
        if ($activeRecord) {
            throw new RuntimeException("User already clocked in");
        }
        
        // Créer record via classe métier existante
        $timeclockrecord = new TimeclockRecord($this->db);
        $result = $timeclockrecord->clockIn(
            $user, 
            $params['timeclock_type_id'] ?? 1,
            $params['location'] ?? '',
            $params['latitude'] ?? null,
            $params['longitude'] ?? null,
            $params['note'] ?? ''
        );
        
        if ($result <= 0) {
            throw new RuntimeException($timeclockrecord->error ?: "Clock in failed");
        }
        
        dol_syslog("TimeclockService::clockIn - Success, record ID: " . $result, LOG_INFO);
        return $result;
    }
    
    /**
     * Effectuer un pointage de sortie
     */
    public function clockOut(User $user, array $params): int 
    {
        dol_syslog("TimeclockService::clockOut - User: " . $user->id, LOG_DEBUG);
        
        // Validation via helper
        $errors = $this->validateClockOutParams($params);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // Vérifier qu'il est bien connecté
        $activeRecord = $this->getActiveRecord($user->id);
        if (!$activeRecord) {
            throw new RuntimeException("User not clocked in");
        }
        
        // Effectuer clock out via classe métier existante
        $timeclockrecord = new TimeclockRecord($this->db);
        $result = $timeclockrecord->clockOut(
            $user,
            $params['location'] ?? '',
            $params['latitude'] ?? null, 
            $params['longitude'] ?? null,
            $params['note'] ?? ''
        );
        
        if ($result <= 0) {
            throw new RuntimeException($timeclockrecord->error ?: "Clock out failed");
        }
        
        dol_syslog("TimeclockService::clockOut - Success, record ID: " . $result, LOG_INFO);
        return $result;
    }
    
    /**
     * Récupérer l'enregistrement actif d'un utilisateur
     */
    public function getActiveRecord(int $userId): ?TimeclockRecord 
    {
        $timeclockrecord = new TimeclockRecord($this->db);
        $activeId = $timeclockrecord->getActiveRecord($userId);
        
        if ($activeId > 0) {
            $active = new TimeclockRecord($this->db);
            if ($active->fetch($activeId) > 0) {
                return $active;
            }
        }
        
        return null;
    }
    
    /**
     * Valider les paramètres de pointage d'entrée
     */
    public function validateClockInParams(array $params): array 
    {
        $errors = [];
        
        // Validation type de pointage
        $typeId = $params['timeclock_type_id'] ?? null;
        if (!$typeId || !is_numeric($typeId) || $typeId <= 0) {
            $errors[] = "Invalid timeclock type";
        }
        
        // Validation localisation si requise
        $requireLocation = TimeclockConstants::getValue(
            $this->db, 
            TimeclockConstants::REQUIRE_LOCATION, 
            0
        );
        
        if ($requireLocation) {
            $lat = $params['latitude'] ?? null;
            $lon = $params['longitude'] ?? null;
            
            if (!LocationHelper::validateCoordinates($lat, $lon)) {
                $errors[] = TimeclockConstants::MSG_LOCATION_REQUIRED;
            }
        }
        
        return $errors;
    }
    
    /**
     * Valider les paramètres de pointage de sortie
     */
    public function validateClockOutParams(array $params): array 
    {
        $errors = [];
        
        // Validation localisation si requise
        $requireLocation = TimeclockConstants::getValue(
            $this->db, 
            TimeclockConstants::REQUIRE_LOCATION, 
            0
        );
        
        if ($requireLocation) {
            $lat = $params['latitude'] ?? null;
            $lon = $params['longitude'] ?? null;
            
            if (!LocationHelper::validateCoordinates($lat, $lon)) {
                $errors[] = TimeclockConstants::MSG_LOCATION_REQUIRED;
            }
        }
        
        return $errors;
    }
}