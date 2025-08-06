<?php
/**
 * Interface service données - Ségrégation Interface (ISP)
 * Contrat spécifique à l'accès données uniquement
 * 
 * Respecte le principe ISP : Interface spécialisée pour l'accès aux données
 * Respecte le principe DIP : Définit l'abstraction pour l'inversion de dépendance
 */

interface DataServiceInterface 
{
    /**
     * Récupérer les enregistrements d'aujourd'hui pour un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des enregistrements du jour
     */
    public function getTodayRecords(int $userId): array;
    
    /**
     * Récupérer les enregistrements d'une semaine spécifique
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $year Année
     * @param int $week Numéro de semaine
     * @return array Liste des enregistrements de la semaine
     */
    public function getWeeklyRecords(int $userId, int $year, int $week): array;
    
    /**
     * Récupérer les enregistrements récents
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $limit Nombre maximum d'enregistrements
     * @return array Liste des enregistrements récents
     */
    public function getRecentRecords(int $userId, int $limit = 5): array;
    
    /**
     * Calculer le résumé journalier d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Résumé avec total_hours, total_breaks, etc.
     */
    public function calculateTodaySummary(int $userId): array;
    
    /**
     * Calculer le résumé hebdomadaire d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return WeeklySummary|null Résumé hebdomadaire ou null
     */
    public function calculateWeeklySummary(int $userId): ?WeeklySummary;
    
    /**
     * Calculer le résumé mensuel d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array|null Résumé mensuel avec total_hours, days_worked, etc.
     */
    public function calculateMonthlySummary(int $userId): ?array;
    
    /**
     * Récupérer les types de pointage actifs
     * 
     * @return array Liste des types de pointage disponibles
     */
    public function getActiveTimeclockTypes(): array;
    
    /**
     * Récupérer le type de pointage par défaut
     * 
     * @return int ID du type par défaut
     */
    public function getDefaultTimeclockType(): int;
}