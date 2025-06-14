<?php
/**
 * Service timeclock métier - Responsabilité unique opérations pointage (SRP)
 * Logique métier centralisée avec injection de dépendances (DIP)
 * 
 * Respecte le principe SRP : Responsabilité unique pour les opérations de pointage
 * Respecte le principe OCP : Ouvert à l'extension via interfaces
 * Respecte le principe DIP : Dépend de DataServiceInterface, pas d'implémentation
 */

dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');

// Import interfaces
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/TimeclockServiceInterface.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Services/Interfaces/DataServiceInterface.php';

// Import helpers
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/LocationHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/TimeHelper.php';
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';

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
     * Effectuer un pointage d'entrée - Logique métier centralisée
     */
    public function clockIn(User $user, array $params): int 
    {
        global $conf;
        
        dol_syslog("TimeclockService::clockIn - User: " . $user->id, LOG_DEBUG);
        
        // Validation via helper
        $errors = $this->validateClockInParams($params);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // Vérifier pas déjà connecté
        if ($this->getActiveRecord($user->id)) {
            throw new RuntimeException("User already clocked in");
        }
        
        // Créer nouvel enregistrement directement
        $timeclockRecord = new TimeclockRecord($this->db);
        
        $now = dol_now();
        $timeclockRecord->fk_user = $user->id;
        $timeclockRecord->fk_user_creat = $user->id;
        $timeclockRecord->entity = $conf->entity;
        $timeclockRecord->datec = $this->db->idate($now);
        $timeclockRecord->clock_in_time = $this->db->idate($now);
        $timeclockRecord->fk_timeclock_type = $params['timeclock_type_id'] ?? 1;
        $timeclockRecord->status = TimeclockRecord::STATUS_IN_PROGRESS;
        $timeclockRecord->location_in = $params['location'] ?? '';
        $timeclockRecord->latitude_in = $params['latitude'] ?? null;
        $timeclockRecord->longitude_in = $params['longitude'] ?? null;
        $timeclockRecord->ip_address_in = getUserRemoteIP();
        $timeclockRecord->note_public = $params['note'] ?? '';
        $timeclockRecord->break_duration = 0;
        $timeclockRecord->ref = '(PROV)';
        
        dol_syslog("TimeclockService::clockIn - Creating record for user " . $user->id, LOG_INFO);
        
        $result = $timeclockRecord->create($user);
        
        if ($result > 0) {
            dol_syslog("TimeclockService::clockIn - Success, record ID: " . $result, LOG_INFO);
            return $result;
        } else {
            dol_syslog("TimeclockService::clockIn - Failed: " . $timeclockRecord->error, LOG_ERROR);
            throw new RuntimeException($timeclockRecord->error ?: "Clock in failed");
        }
    }
    
    /**
     * Effectuer un pointage de sortie - Logique métier centralisée
     */
    public function clockOut(User $user, array $params): int 
    {
        dol_syslog("TimeclockService::clockOut - User: " . $user->id, LOG_DEBUG);
        
        // Validation via helper
        $errors = $this->validateClockOutParams($params);
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        // Récupérer enregistrement actif
        $activeRecord = $this->getActiveRecord($user->id);
        if (!$activeRecord) {
            throw new RuntimeException("User not clocked in");
        }
        
        // Mettre à jour l'enregistrement
        $now = dol_now();
        $activeRecord->fk_user_modif = $user->id;
        $activeRecord->clock_out_time = $this->db->idate($now);
        $activeRecord->status = TimeclockRecord::STATUS_COMPLETED;
        $activeRecord->location_out = $params['location'] ?? '';
        $activeRecord->latitude_out = $params['latitude'] ?? null;
        $activeRecord->longitude_out = $params['longitude'] ?? null;
        $activeRecord->ip_address_out = getUserRemoteIP();
        
        // Gérer note
        $note = $params['note'] ?? '';
        if (!empty($note)) {
            $activeRecord->note_public .= (!empty($activeRecord->note_public) ? "\n" : '') . $note;
        }
        
        // Calculer durée de travail
        $this->calculateSessionDuration($activeRecord);
        
        dol_syslog("TimeclockService::clockOut - Updating record ID: " . $activeRecord->id, LOG_INFO);
        
        $result = $activeRecord->update($user);
        
        if ($result > 0) {
            dol_syslog("TimeclockService::clockOut - Success, work duration: " . $activeRecord->work_duration . " minutes", LOG_INFO);
            return $activeRecord->id;
        } else {
            dol_syslog("TimeclockService::clockOut - Failed: " . $activeRecord->error, LOG_ERROR);
            throw new RuntimeException($activeRecord->error ?: "Clock out failed");
        }
    }
    
    /**
     * Récupérer l'enregistrement actif d'un utilisateur - Logique métier centralisée
     */
    public function getActiveRecord(int $userId): ?TimeclockRecord 
    {
        $sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."timeclock_records";
        $sql .= " WHERE fk_user = ".((int) $userId);
        $sql .= " AND status = ".TimeclockRecord::STATUS_IN_PROGRESS;
        $sql .= " AND clock_out_time IS NULL";
        $sql .= " AND entity IN (".getEntity('user').")";
        $sql .= " ORDER BY clock_in_time DESC";
        $sql .= " LIMIT 1";

        dol_syslog("TimeclockService::getActiveRecord - SQL: " . $sql, LOG_INFO);

        $resql = $this->db->query($sql);
        if ($resql) {
            $numRows = $this->db->num_rows($resql);
            dol_syslog("TimeclockService::getActiveRecord - Rows found: " . $numRows, LOG_DEBUG);
            
            if ($numRows) {
                $obj = $this->db->fetch_object($resql);
                dol_syslog("TimeclockService::getActiveRecord - Found rowid: " . $obj->rowid, LOG_DEBUG);
                $this->db->free($resql);
                
                $activeRecord = new TimeclockRecord($this->db);
                $fetchResult = $activeRecord->fetch($obj->rowid);
                dol_syslog("TimeclockService::getActiveRecord - Fetch result: " . $fetchResult, LOG_DEBUG);
                
                if ($fetchResult > 0) {
                    dol_syslog("TimeclockService::getActiveRecord - Raw clock_in_time: " . $activeRecord->clock_in_time, LOG_INFO);
                    dol_syslog("TimeclockService::getActiveRecord - jdate conversion: " . $this->db->jdate($activeRecord->clock_in_time), LOG_INFO);
                    return $activeRecord;
                }
            }
            $this->db->free($resql);
        }
        
        dol_syslog("TimeclockService::getActiveRecord - No active record found for user " . $userId, LOG_DEBUG);
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
    
    /**
     * Calculer la durée de session de travail - Logique métier centralisée
     */
    public function calculateSessionDuration(TimeclockRecord $record): bool 
    {
        // Vérifier que les heures de début et fin sont définies
        if (empty($record->clock_in_time) || empty($record->clock_out_time)) {
            $record->work_duration = null;
            dol_syslog("TimeclockService::calculateSessionDuration - Missing clock times", LOG_WARNING);
            return false;
        }

        try {
            // Conversion sécurisée des timestamps
            $clockIn = $this->convertToTimestamp($record->clock_in_time);
            $clockOut = $this->convertToTimestamp($record->clock_out_time);
            
            // Validation que les conversions ont réussi
            if ($clockIn === false || $clockOut === false) {
                $record->work_duration = null;
                dol_syslog("TimeclockService::calculateSessionDuration - Failed to convert timestamps", LOG_ERROR);
                return false;
            }
            
            // Validation que clock_out est après clock_in
            if ($clockOut <= $clockIn) {
                $record->work_duration = 0;
                dol_syslog("TimeclockService::calculateSessionDuration - Invalid time sequence", LOG_WARNING);
                return false;
            }
            
            // Calcul de la durée totale en minutes
            $totalMinutes = ($clockOut - $clockIn) / 60;
            
            // Validation et conversion du break_duration
            $breakDuration = 0;
            if (!empty($record->break_duration) && is_numeric($record->break_duration)) {
                $breakDuration = (int) $record->break_duration;
            }
            
            // Calcul de la durée de travail effective
            $workMinutes = $totalMinutes - $breakDuration;
            $record->work_duration = max(0, round($workMinutes));
            
            dol_syslog("TimeclockService::calculateSessionDuration - Calculated: " . $record->work_duration . " minutes", LOG_DEBUG);
            return true;
            
        } catch (Exception $e) {
            dol_syslog("TimeclockService::calculateSessionDuration - Exception: " . $e->getMessage(), LOG_ERROR);
            $record->work_duration = null;
            return false;
        }
    }
    
    /**
     * Valider qu'un utilisateur a une session active - Logique métier centralisée
     */
    public function validateActiveSession(int $userId): bool 
    {
        $activeRecord = $this->getActiveRecord($userId);
        return $activeRecord !== null;
    }
    
    /**
     * Conversion sécurisée timestamp - Utilitaire
     */
    private function convertToTimestamp($datetime)
    {
        if (empty($datetime)) {
            return false;
        }
        
        // Si c'est déjà un timestamp numérique valide
        if (is_numeric($datetime)) {
            $timestamp = (int) $datetime;
            // Vérifier que c'est un timestamp raisonnable (après 2000, avant 2100)
            if ($timestamp > 946684800 && $timestamp < 4102444800) {
                return $timestamp;
            }
        }
        
        // Si c'est une chaîne, essayer la conversion via jdate
        if (is_string($datetime)) {
            try {
                // Utiliser la méthode jdate de Dolibarr
                $timestamp = $this->db->jdate($datetime);
                
                // Validation du résultat
                if (is_numeric($timestamp)) {
                    $timestamp = (int) $timestamp;
                    if ($timestamp > 946684800 && $timestamp < 4102444800) {
                        return $timestamp;
                    }
                }
                
                // Fallback: essayer strtotime si jdate échoue
                $timestamp = strtotime($datetime);
                if ($timestamp !== false && $timestamp > 946684800 && $timestamp < 4102444800) {
                    return $timestamp;
                }
                
            } catch (Exception $e) {
                dol_syslog("TimeclockService::convertToTimestamp - Exception: " . $e->getMessage(), LOG_ERROR);
            }
        }
        
        dol_syslog("TimeclockService::convertToTimestamp - Failed to convert: " . print_r($datetime, true), LOG_ERROR);
        return false;
    }
}