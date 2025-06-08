<?php
/**
 * Helpers manipulation temps - AppMobTimeTouch
 * Responsabilité unique : Formatage et calculs temporels
 * Ouvert extension : Nouvelles fonctions sans modification existantes
 * 
 * Principes SOLID appliqués :
 * - SRP : Responsabilité unique pour les opérations temporelles
 * - OCP : Ouvert à l'extension par ajout de nouvelles méthodes
 * - LSP : Méthodes statiques substituables
 * - ISP : Interface spécialisée pour le temps uniquement
 * - DIP : Pas de dépendances externes, fonctions pures
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

/**
 * Classe helper pour les opérations temporelles
 */
class TimeHelper 
{
    /**
     * Convertit secondes en format lisible h:mm
     * 
     * @param int|float $seconds Nombre de secondes
     * @return string Format "Xh YY" (ex: "2h30")
     */
    public static function convertSecondsToReadableTime($seconds): string 
    {
        // Validation et nettoyage de l'entrée
        if (!is_numeric($seconds) || $seconds <= 0) {
            dol_syslog("TimeHelper::convertSecondsToReadableTime - Invalid input: " . print_r($seconds, true), LOG_DEBUG);
            return '0h00';
        }
        
        $seconds = (int) $seconds; // Cast explicite en entier
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $result = sprintf('%dh%02d', $hours, $minutes);
        dol_syslog("TimeHelper::convertSecondsToReadableTime - Input: " . $seconds . ", Output: " . $result, LOG_DEBUG);
        
        return $result;
    }
    
    /**
     * Formate durée en minutes vers h:mm
     * 
     * @param int|float $minutes Nombre de minutes
     * @return string Format "Xh YY" (ex: "1h30")
     */
    public static function formatDuration($minutes): string 
    {
        // Validation et nettoyage de l'entrée
        if (!is_numeric($minutes) || $minutes <= 0) {
            dol_syslog("TimeHelper::formatDuration - Invalid input: " . print_r($minutes, true), LOG_DEBUG);
            return '0h00';
        }
        
        $minutes = (int) $minutes; // Cast explicite en entier
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        $result = sprintf('%dh%02d', $hours, $mins);
        dol_syslog("TimeHelper::formatDuration - Input: " . $minutes . ", Output: " . $result, LOG_DEBUG);
        
        return $result;
    }
    
    /**
     * Calcule durée entre deux timestamps
     * 
     * @param int $start Timestamp de début
     * @param int $end Timestamp de fin
     * @return int Durée en secondes (toujours >= 0)
     */
    public static function calculateDuration(int $start, int $end): int 
    {
        $duration = max(0, $end - $start);
        dol_syslog("TimeHelper::calculateDuration - Start: $start, End: $end, Duration: $duration", LOG_DEBUG);
        return $duration;
    }
    
    /**
     * Valide qu'un timestamp est raisonnable (pas plus de 24h de durée)
     * 
     * @param int $duration Durée en secondes
     * @return bool True si la durée est valide
     */
    public static function isValidDuration(int $duration): bool 
    {
        $maxDuration = TimeclockConstants::MAX_SESSION_DURATION; // 24h
        $isValid = $duration >= 0 && $duration <= $maxDuration;
        
        if (!$isValid) {
            dol_syslog("TimeHelper::isValidDuration - Invalid duration: $duration (max: $maxDuration)", LOG_WARNING);
        }
        
        return $isValid;
    }
    
    /**
     * Convertit timestamp de base de données vers timestamp Unix
     * Gère les différents formats de Dolibarr (jdate, timestamp direct)
     * 
     * @param mixed $dbTimestamp Timestamp depuis base de données
     * @param DoliDB $db Instance de base de données pour conversion jdate
     * @return int|null Timestamp Unix ou null si conversion échoue
     */
    public static function convertDbTimestamp($dbTimestamp, $db): ?int 
    {
        if (empty($dbTimestamp)) {
            return null;
        }
        
        // Méthode 1: Vérifier si c'est déjà un timestamp Unix valide
        if (is_numeric($dbTimestamp) && $dbTimestamp > 946684800 && $dbTimestamp < 4102444800) {
            // C'est déjà un timestamp Unix valide (entre 2000 et 2100)
            dol_syslog("TimeHelper::convertDbTimestamp - Already valid Unix timestamp: " . $dbTimestamp, LOG_DEBUG);
            return (int) $dbTimestamp;
        }
        
        // Méthode 2: Essayer la conversion jdate pour les formats de date Dolibarr
        if ($db && method_exists($db, 'jdate')) {
            $unixTime = $db->jdate($dbTimestamp);
            if (!empty($unixTime) && is_numeric($unixTime)) {
                dol_syslog("TimeHelper::convertDbTimestamp - Converted with jdate: " . $unixTime, LOG_DEBUG);
                return (int) $unixTime;
            }
        }
        
        // Méthode 3: Fallback avec strtotime si jdate échoue
        $unixTime = strtotime($dbTimestamp);
        if ($unixTime !== false && $unixTime > 0) {
            dol_syslog("TimeHelper::convertDbTimestamp - Converted with strtotime: " . $unixTime, LOG_DEBUG);
            return $unixTime;
        }
        
        // Toutes les méthodes ont échoué
        dol_syslog("TimeHelper::convertDbTimestamp - All conversion methods failed for: " . print_r($dbTimestamp, true), LOG_ERROR);
        return null;
    }
    
    /**
     * Formate un timestamp en utilisant les paramètres utilisateur Dolibarr
     * 
     * @param int $timestamp Timestamp Unix
     * @param string $format Format Dolibarr ('day', 'hour', 'dayhour', etc.)
     * @param string $tzoutput Timezone de sortie ('tzuser', 'tzserver', etc.)
     * @return string Date formatée
     */
    public static function formatTimestamp(int $timestamp, string $format = 'dayhour', string $tzoutput = 'tzuser'): string 
    {
        if ($timestamp <= 0) {
            return '';
        }
        
        // Utiliser la fonction Dolibarr si disponible
        if (function_exists('dol_print_date')) {
            return dol_print_date($timestamp, $format, $tzoutput);
        }
        
        // Fallback simple
        return date('Y-m-d H:i', $timestamp);
    }
    
    /**
     * Convertit heures décimales en format lisible
     * Exemple: 1.5 -> "1h30"
     * 
     * @param float $hours Heures décimales
     * @return string Format "Xh YY"
     */
    public static function convertDecimalHoursToReadable(float $hours): string 
    {
        if ($hours <= 0) {
            return '0h00';
        }
        
        $totalMinutes = round($hours * 60);
        return self::formatDuration($totalMinutes);
    }
    
    /**
     * Calcule le pourcentage de progression d'une durée par rapport à un objectif
     * 
     * @param int $actualSeconds Durée réelle en secondes
     * @param int $targetSeconds Durée objectif en secondes
     * @return float Pourcentage (0-100+)
     */
    public static function calculateProgressPercentage(int $actualSeconds, int $targetSeconds): float 
    {
        if ($targetSeconds <= 0) {
            return 0.0;
        }
        
        return round(($actualSeconds / $targetSeconds) * 100, 1);
    }
    
    /**
     * Détermine si une durée dépasse le seuil d'heures supplémentaires
     * 
     * @param int $workedSeconds Secondes travaillées
     * @param int $thresholdHours Seuil en heures (défaut depuis constantes)
     * @return bool True si heures supplémentaires
     */
    public static function isOvertime(int $workedSeconds, ?int $thresholdHours = null): bool 
    {
        if ($thresholdHours === null) {
            $thresholdHours = TimeclockConstants::DEFAULT_OVERTIME_THRESHOLD;
        }
        
        $thresholdSeconds = $thresholdHours * 3600;
        return $workedSeconds > $thresholdSeconds;
    }
}