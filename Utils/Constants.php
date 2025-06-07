<?php
/**
 * Configuration centralisée AppMobTimeTouch
 * Responsabilité unique : Constantes et paramètres
 * 
 * Principe SOLID appliqué :
 * - SRP : Responsabilité unique pour la configuration
 * - OCP : Ouvert à l'extension par ajout de nouvelles constantes
 * - DIP : Prépare l'inversion de dépendance pour les services futurs
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

/**
 * Classe de configuration centralisée pour AppMobTimeTouch
 */
class TimeclockConstants 
{
    // Configuration timeclock
    const REQUIRE_LOCATION = 'REQUIRE_LOCATION';
    const MAX_HOURS_PER_DAY = 'MAX_HOURS_PER_DAY';
    const OVERTIME_THRESHOLD = 'OVERTIME_THRESHOLD';
    const AUTO_BREAK_MINUTES = 'AUTO_BREAK_MINUTES';
    const VALIDATION_REQUIRED = 'VALIDATION_REQUIRED';
    
    // Status workflow
    const STATUS_DRAFT = 0;
    const STATUS_VALIDATED = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELLED = 9;
    
    // Valeurs par défaut
    const DEFAULT_MAX_HOURS = 12;
    const DEFAULT_OVERTIME_THRESHOLD = 8;
    const DEFAULT_BREAK_DURATION = 30;
    const DEFAULT_EXPECTED_WEEKLY_HOURS = 40;
    
    // Types de messages
    const MSG_CLOCKIN_SUCCESS = 'ClockInSuccess';
    const MSG_CLOCKOUT_SUCCESS = 'ClockOutSuccess';
    const MSG_LOCATION_REQUIRED = 'LocationRequiredForClockIn';
    const MSG_CLOCKIN_ERROR = 'ClockInError';
    const MSG_CLOCKOUT_ERROR = 'ClockOutError';
    
    // Configuration GPS
    const GPS_ACCURACY_THRESHOLD = 100; // mètres
    const GPS_TIMEOUT = 10000; // millisecondes
    const GPS_MAX_AGE = 300000; // millisecondes
    
    // Limites temporelles
    const MAX_SESSION_DURATION = 86400; // 24 heures en secondes
    const MIN_BREAK_DURATION = 300; // 5 minutes en secondes
    const UPDATE_INTERVAL = 60000; // 1 minute en millisecondes
    
    /**
     * Récupère une valeur de configuration avec fallback
     * 
     * @param DoliDB $db Instance de base de données
     * @param string $key Clé de configuration
     * @param mixed $default Valeur par défaut
     * @return mixed Valeur de configuration ou valeur par défaut
     */
    public static function getValue($db, string $key, $default = null)
    {
        // Utilisation de la classe TimeclockConfig existante pour compatibilité
        if (class_exists('TimeclockConfig')) {
            return TimeclockConfig::getValue($db, $key, $default);
        }
        
        // Fallback si TimeclockConfig n'est pas disponible
        return $default;
    }
    
    /**
     * Récupère toutes les valeurs de configuration par défaut
     * 
     * @return array Tableau associatif des configurations par défaut
     */
    public static function getDefaultValues(): array
    {
        return [
            self::REQUIRE_LOCATION => 0,
            self::MAX_HOURS_PER_DAY => self::DEFAULT_MAX_HOURS,
            self::OVERTIME_THRESHOLD => self::DEFAULT_OVERTIME_THRESHOLD,
            self::AUTO_BREAK_MINUTES => self::DEFAULT_BREAK_DURATION,
            self::VALIDATION_REQUIRED => 0
        ];
    }
    
    /**
     * Valide si un statut est valide
     * 
     * @param int $status Statut à valider
     * @return bool True si le statut est valide
     */
    public static function isValidStatus(int $status): bool
    {
        return in_array($status, [
            self::STATUS_DRAFT,
            self::STATUS_VALIDATED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED
        ]);
    }
    
    /**
     * Récupère le libellé d'un statut
     * 
     * @param int $status Statut
     * @return string Libellé du statut
     */
    public static function getStatusLabel(int $status): string
    {
        return match($status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_VALIDATED => 'Validated',
            self::STATUS_IN_PROGRESS => 'InProgress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown'
        };
    }
}