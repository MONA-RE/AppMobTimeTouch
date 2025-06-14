<?php
/**
 * Service d'accès aux données - Responsabilité Unique (SRP)
 * Responsabilité unique : Accès et manipulation des données
 * 
 * Respecte le principe SRP : Seule responsabilité l'accès aux données
 * Respecte le principe OCP : Extensible pour nouvelles requêtes de données
 * Respecte le principe DIP : Peut être injecté via interface
 */

dol_include_once('/appmobtimetouch/class/timeclockrecord.class.php');
dol_include_once('/appmobtimetouch/class/timeclocktype.class.php');
dol_include_once('/appmobtimetouch/class/weeklysummary.class.php');

class DataService implements DataServiceInterface 
{
    private $db;
    
    /**
     * Constructor
     * 
     * @param DoliDB $db Base de données Dolibarr
     */
    public function __construct($db) 
    {
        $this->db = $db;
    }
    
    /**
     * Récupérer les enregistrements d'aujourd'hui pour un utilisateur
     */
    public function getTodayRecords(int $userId): array 
    {
        $timeclockRecord = new TimeclockRecord($this->db);
        
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_records";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= " AND DATE(clock_in_time) = CURDATE()";
        $sql .= " ORDER BY clock_in_time DESC";
        
        $result = $this->db->query($sql);
        $records = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $record = new TimeclockRecord($this->db);
                $record->fetch($obj->rowid);
                $records[] = $record;
            }
            $this->db->free($result);
        }
        
        return $records;
    }
    
    /**
     * Récupérer les enregistrements d'une semaine spécifique
     */
    public function getWeeklyRecords(int $userId, int $year, int $week): array 
    {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_records";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= " AND YEAR(clock_in_time) = " . ((int) $year);
        $sql .= " AND WEEK(clock_in_time, 1) = " . ((int) $week);
        $sql .= " ORDER BY clock_in_time DESC";
        
        $result = $this->db->query($sql);
        $records = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $record = new TimeclockRecord($this->db);
                $record->fetch($obj->rowid);
                $records[] = $record;
            }
            $this->db->free($result);
        }
        
        return $records;
    }
    
    /**
     * Récupérer les enregistrements récents (tous statuts)
     */
    public function getRecentRecords(int $userId, int $limit = 5): array 
    {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_records";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= " ORDER BY clock_in_time DESC";
        $sql .= " LIMIT " . ((int) $limit);
        
        $result = $this->db->query($sql);
        $records = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $record = new TimeclockRecord($this->db);
                $fetchResult = $record->fetch($obj->rowid);
                
                // Debug: vérifier que le fetch a réussi
                if ($fetchResult <= 0) {
                    dol_syslog("DataService: Failed to fetch TimeclockRecord with ID " . $obj->rowid, LOG_WARNING);
                    continue;
                }
                
                // Vérifier que rowid est bien défini
                if (empty($record->rowid)) {
                    dol_syslog("DataService: TimeclockRecord fetched but rowid is empty for ID " . $obj->rowid, LOG_WARNING);
                    $record->rowid = $obj->rowid; // Force l'ID si nécessaire
                }
                
                // Debug spécifique pour l'enregistrement 43
                if ($obj->rowid == 43) {
                    dol_syslog("DataService: Record 43 debug - work_duration in object: " . ($record->work_duration ?? 'NULL'), LOG_INFO);
                    dol_syslog("DataService: Record 43 debug - clock_out_time in object: " . ($record->clock_out_time ?? 'NULL'), LOG_INFO);
                    dol_syslog("DataService: Record 43 debug - status in object: " . ($record->status ?? 'NULL'), LOG_INFO);
                }
                
                $records[] = $record;
            }
            $this->db->free($result);
        }
        
        return $records;
    }
    
    /**
     * Calculer le résumé journalier d'un utilisateur
     */
    public function calculateTodaySummary(int $userId): array 
    {
        $todayRecords = $this->getTodayRecords($userId);
        
        $totalHours = 0;
        $totalBreaks = 0;
        $completedRecords = 0;
        
        foreach ($todayRecords as $record) {
            if ($record->status == TimeclockConstants::STATUS_COMPLETED && !empty($record->work_duration)) {
                $totalHours += ($record->work_duration / 60); // Convertir minutes en heures
                $completedRecords++;
            }
            
            if (!empty($record->break_duration)) {
                $totalBreaks += $record->break_duration;
            }
        }
        
        return [
            'total_hours' => $totalHours,
            'total_breaks' => $totalBreaks,
            'completed_records' => $completedRecords,
            'records_count' => count($todayRecords)
        ];
    }
    
    /**
     * Calculer le résumé hebdomadaire d'un utilisateur
     */
    public function calculateWeeklySummary(int $userId): ?WeeklySummary 
    {
        $currentYear = (int) date('Y');
        $currentWeek = (int) date('W');
        
        // Vérifier si un résumé existe déjà
        $weeklySummary = new WeeklySummary($this->db);
        $existingSummary = $weeklySummary->summaryExists($userId, $currentYear, $currentWeek);
        
        if ($existingSummary) {
            return $existingSummary;
        }
        
        // Calculer le résumé à partir des enregistrements
        $weeklyRecords = $this->getWeeklyRecords($userId, $currentYear, $currentWeek);
        
        if (empty($weeklyRecords)) {
            return null;
        }
        
        $totalHours = 0;
        $daysWorked = [];
        $overtimeHours = 0;
        $expectedHours = TimeclockConstants::getValue($this->db, TimeclockConstants::DEFAULT_OVERTIME_THRESHOLD, 40);
        
        foreach ($weeklyRecords as $record) {
            if ($record->status == TimeclockConstants::STATUS_COMPLETED && !empty($record->work_duration)) {
                $totalHours += ($record->work_duration / 60);
                // Conversion sécurisée du timestamp
                $rawTimestamp = $this->db->jdate($record->clock_in_time);
                if ($rawTimestamp === false || $rawTimestamp === null) {
                    continue; // Skip ce record si timestamp invalide
                }
                $timestamp = $this->convertToUnixTimestamp($rawTimestamp);
                $workDay = date('Y-m-d', $timestamp);
                $daysWorked[$workDay] = true;
            }
        }
        
        $overtimeHours = max(0, $totalHours - $expectedHours);
        
        // Créer un objet WeeklySummary temporaire pour affichage
        $summary = new WeeklySummary($this->db);
        $summary->fk_user = $userId;
        $summary->year = $currentYear;
        $summary->week_number = $currentWeek;
        $summary->total_hours = $totalHours;
        $summary->days_worked = count($daysWorked);
        $summary->overtime_hours = $overtimeHours;
        $summary->expected_hours = $expectedHours;
        $summary->status = TimeclockConstants::STATUS_DRAFT;
        
        return $summary;
    }
    
    /**
     * Récupérer les types de pointage actifs
     */
    public function getActiveTimeclockTypes(): array 
    {
        $timeclockType = new TimeclockType($this->db);
        
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_types";
        $sql .= " WHERE active = 1"; // Actif
        $sql .= " ORDER BY position ASC, label ASC";
        
        $result = $this->db->query($sql);
        $types = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $type = new TimeclockType($this->db);
                $type->fetch($obj->rowid);
                $types[] = $type;
            }
            $this->db->free($result);
        }
        
        return $types;
    }
    
    /**
     * Récupérer le type de pointage par défaut
     */
    public function getDefaultTimeclockType(): int 
    {
        // Fallback : premier type actif (pas de colonne is_default dans cette table)
        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "timeclock_types";
        $sql .= " WHERE active = 1";
        $sql .= " ORDER BY position ASC";
        $sql .= " LIMIT 1";
        
        $result = $this->db->query($sql);
        
        if ($result) {
            $obj = $this->db->fetch_object($result);
            if ($obj) {
                $this->db->free($result);
                return (int) $obj->rowid;
            }
            $this->db->free($result);
        }
        
        return 1; // Fallback ultime
    }
    
    /**
     * Conversion sécurisée d'un timestamp Dolibarr vers timestamp Unix
     * 
     * @param mixed $dolibarrTimestamp Timestamp depuis jdate()
     * @return int Timestamp Unix valide
     */
    private function convertToUnixTimestamp($dolibarrTimestamp): int 
    {
        // Vérification des valeurs nulles/false
        if ($dolibarrTimestamp === null || $dolibarrTimestamp === false || $dolibarrTimestamp === '') {
            return time();
        }
        
        if (is_int($dolibarrTimestamp) && $dolibarrTimestamp > 0) {
            return $dolibarrTimestamp;
        }
        
        if (is_string($dolibarrTimestamp)) {
            $converted = strtotime($dolibarrTimestamp);
            return ($converted !== false && $converted > 0) ? $converted : time();
        }
        
        // Vérification si c'est un float (parfois retourné par jdate)
        if (is_float($dolibarrTimestamp)) {
            return (int) $dolibarrTimestamp;
        }
        
        return time(); // Fallback sur timestamp actuel
    }
}