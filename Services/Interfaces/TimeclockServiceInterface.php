<?php
/**
 * Interface service timeclock - Ségrégation Interface (ISP)
 * Contrat spécifique aux opérations timeclock uniquement
 * 
 * Respecte le principe ISP : Interface spécialisée pour les opérations de pointage
 * Respecte le principe DIP : Définit l'abstraction pour l'inversion de dépendance
 */

interface TimeclockServiceInterface 
{
    /**
     * Effectuer un pointage d'entrée
     * 
     * @param User $user Utilisateur qui pointe
     * @param array $params Paramètres de pointage (type, localisation, note)
     * @return int ID du record créé ou erreur négative
     * @throws InvalidArgumentException Si paramètres invalides
     * @throws RuntimeException Si pointage échoue
     */
    public function clockIn(User $user, array $params): int;
    
    /**
     * Effectuer un pointage de sortie
     * 
     * @param User $user Utilisateur qui pointe
     * @param array $params Paramètres de sortie (localisation, note)
     * @return int ID du record mis à jour ou erreur négative
     * @throws RuntimeException Si pointage échoue
     */
    public function clockOut(User $user, array $params): int;
    
    /**
     * Récupérer l'enregistrement actif d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return TimeclockRecord|null Record actif ou null si aucun
     */
    public function getActiveRecord(int $userId): ?TimeclockRecord;
    
    /**
     * Valider les paramètres de pointage d'entrée
     * 
     * @param array $params Paramètres à valider
     * @return array Liste des erreurs (vide si valide)
     */
    public function validateClockInParams(array $params): array;
    
    /**
     * Valider les paramètres de pointage de sortie
     * 
     * @param array $params Paramètres à valider
     * @return array Liste des erreurs (vide si valide)
     */
    public function validateClockOutParams(array $params): array;
}