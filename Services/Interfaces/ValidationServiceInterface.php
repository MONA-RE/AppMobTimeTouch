<?php
/**
 * Interface ValidationService - Ségrégation Interface (ISP)
 * Contrat spécifique aux opérations de validation uniquement
 * 
 * Respecte le principe ISP : Interface spécialisée pour la validation
 * Respecte le principe DIP : Abstraction pour l'inversion de dépendances
 * Respecte le principe SRP : Responsabilité unique validation workflow
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

/**
 * Interface pour les services de validation manager
 */
interface ValidationServiceInterface 
{
    /**
     * Récupérer les temps en attente de validation pour un manager
     * 
     * @param int $managerId ID du manager
     * @return array Liste des enregistrements en attente avec informations enrichies
     */
    public function getPendingValidations(int $managerId): array;
    
    /**
     * Valider un temps de travail
     * 
     * @param int $recordId ID de l'enregistrement à valider
     * @param int $validatorId ID du validateur (manager)
     * @param string $action Action de validation ('approve', 'reject', 'partial')
     * @param string|null $comment Commentaire optionnel
     * @return bool True si succès, false sinon
     */
    public function validateRecord(int $recordId, int $validatorId, string $action, ?string $comment = null): bool;
    
    /**
     * Valider en lot plusieurs enregistrements
     * 
     * @param array $recordIds Liste des IDs d'enregistrements
     * @param int $validatorId ID du validateur
     * @param string $action Action de validation commune
     * @return array Résultats par enregistrement [recordId => success]
     */
    public function batchValidate(array $recordIds, int $validatorId, string $action): array;
    
    /**
     * Détecter les anomalies dans les temps d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur à analyser
     * @param string $period Période d'analyse ('week', 'month', 'day')
     * @return array Liste des anomalies détectées avec détails
     */
    public function detectAnomalies(int $userId, string $period): array;
    
    /**
     * Récupérer le statut de validation d'un enregistrement
     * 
     * @param int $recordId ID de l'enregistrement
     * @return array Informations de validation (statut, validateur, date, commentaire)
     */
    public function getValidationStatus(int $recordId): array;
    
    /**
     * Vérifier si un utilisateur peut valider un enregistrement
     * 
     * @param int $userId ID de l'utilisateur (potentiel validateur)
     * @param int $recordId ID de l'enregistrement à valider
     * @return bool True si l'utilisateur peut valider, false sinon
     */
    public function canValidate(int $userId, int $recordId): bool;
    
    /**
     * Récupérer les statistiques de validation pour un manager
     * 
     * @param int $managerId ID du manager
     * @param string $period Période d'analyse
     * @return array Statistiques (total en attente, validés, rejetés, etc.)
     */
    public function getValidationStats(int $managerId, string $period = 'week'): array;
    
    /**
     * Obtenir les membres d'équipe d'un manager
     * 
     * @param int $managerId ID du manager
     * @return array Liste des utilisateurs sous la responsabilité du manager
     */
    public function getTeamMembers(int $managerId): array;
    
    /**
     * Vérifier si une validation automatique est possible
     * 
     * @param int $recordId ID de l'enregistrement
     * @return bool True si peut être auto-validé selon les règles
     */
    public function canAutoValidate(int $recordId): bool;
}