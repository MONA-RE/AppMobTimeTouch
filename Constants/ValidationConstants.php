<?php
/**
 * Constants de validation - Responsabilité unique : Configuration workflow
 * 
 * Respecte le principe SRP : Seule responsabilité la configuration des validations
 * Respecte le principe OCP : Ouvert à l'extension pour nouveaux statuts/anomalies
 * Respecte le principe DIP : Prépare l'inversion de dépendance pour services validation
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

// Import de la classe parente
require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Utils/Constants.php';

/**
 * Classe de configuration pour le workflow de validation Sprint 2
 */
class ValidationConstants extends TimeclockConstants 
{
    // Statuts de validation
    const VALIDATION_PENDING = 0;
    const VALIDATION_APPROVED = 1; 
    const VALIDATION_REJECTED = 2;
    const VALIDATION_PARTIAL = 3;
    
    // Types d'anomalies
    const ANOMALY_OVERTIME = 'overtime';
    const ANOMALY_MISSING_CLOCKOUT = 'missing_clockout';
    const ANOMALY_LONG_BREAK = 'long_break';
    const ANOMALY_LOCATION_MISMATCH = 'location_mismatch';
    
    // Niveaux d'alerte
    const ALERT_INFO = 'info';
    const ALERT_WARNING = 'warning';
    const ALERT_CRITICAL = 'critical';
    
    // Configuration workflow
    const AUTO_APPROVE_THRESHOLD = 'VALIDATION_AUTO_APPROVE_HOURS'; // 8h
    const VALIDATION_DEADLINE_DAYS = 'VALIDATION_DEADLINE_DAYS';     // 3 jours
    const MANAGER_NOTIFICATION_ENABLED = 'VALIDATION_MANAGER_NOTIFY'; // 1
    
    /**
     * Correspondance statuts → labels
     * 
     * @return array Tableau associatif statut => label de traduction
     */
    public static function getValidationStatuses(): array 
    {
        return [
            self::VALIDATION_PENDING => 'ValidationPending',
            self::VALIDATION_APPROVED => 'ValidationApproved', 
            self::VALIDATION_REJECTED => 'ValidationRejected',
            self::VALIDATION_PARTIAL => 'ValidationPartial'
        ];
    }
    
    /**
     * Types d'anomalies avec seuils et niveaux d'alerte
     * 
     * @return array Configuration des anomalies avec seuils et niveaux
     */
    public static function getAnomalyTypes(): array 
    {
        return [
            self::ANOMALY_OVERTIME => [
                'threshold' => 8, 
                'level' => self::ALERT_WARNING,
                'unit' => 'hours',
                'description' => 'Heures supplémentaires détectées'
            ],
            self::ANOMALY_MISSING_CLOCKOUT => [
                'threshold' => 0, 
                'level' => self::ALERT_CRITICAL,
                'unit' => 'count',
                'description' => 'Pointage de sortie manquant'
            ],
            self::ANOMALY_LONG_BREAK => [
                'threshold' => 90, 
                'level' => self::ALERT_INFO,
                'unit' => 'minutes',
                'description' => 'Pause prolongée détectée'
            ],
            self::ANOMALY_LOCATION_MISMATCH => [
                'threshold' => 0, 
                'level' => self::ALERT_WARNING,
                'unit' => 'count',
                'description' => 'Incohérence de localisation'
            ]
        ];
    }
    
    /**
     * Configuration par défaut du workflow de validation
     * 
     * @return array Configuration par défaut
     */
    public static function getDefaultWorkflowConfig(): array 
    {
        return [
            self::AUTO_APPROVE_THRESHOLD => 8,
            self::VALIDATION_DEADLINE_DAYS => 3,
            self::MANAGER_NOTIFICATION_ENABLED => 1
        ];
    }
    
    /**
     * Couleurs associées aux niveaux d'alerte (pour l'interface)
     * 
     * @return array Couleurs par niveau d'alerte
     */
    public static function getAlertColors(): array 
    {
        return [
            self::ALERT_INFO => '#2196F3',      // Bleu
            self::ALERT_WARNING => '#FF9800',   // Orange
            self::ALERT_CRITICAL => '#f44336'   // Rouge
        ];
    }
    
    /**
     * Icônes associées aux types d'anomalies (pour l'interface)
     * 
     * @return array Icônes par type d'anomalie
     */
    public static function getAnomalyIcons(): array 
    {
        return [
            self::ANOMALY_OVERTIME => 'md-schedule',
            self::ANOMALY_MISSING_CLOCKOUT => 'md-error',
            self::ANOMALY_LONG_BREAK => 'md-pause',
            self::ANOMALY_LOCATION_MISMATCH => 'md-place'
        ];
    }
    
    /**
     * Labels de traduction pour les types d'anomalies
     * 
     * @return array Labels de traduction
     */
    public static function getAnomalyLabels(): array 
    {
        return [
            self::ANOMALY_OVERTIME => 'AnomalyOvertime',
            self::ANOMALY_MISSING_CLOCKOUT => 'AnomalyMissingClockout', 
            self::ANOMALY_LONG_BREAK => 'AnomalyLongBreak',
            self::ANOMALY_LOCATION_MISMATCH => 'AnomalyLocationMismatch'
        ];
    }
}