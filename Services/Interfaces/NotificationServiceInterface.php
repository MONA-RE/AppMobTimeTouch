<?php
/**
 * Interface NotificationService - Ségrégation Interface (ISP)
 * Contrat spécifique aux notifications uniquement
 * 
 * Respecte le principe ISP : Interface spécialisée pour les notifications
 * Respecte le principe DIP : Abstraction pour l'inversion de dépendances  
 * Respecte le principe SRP : Responsabilité unique gestion notifications
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

/**
 * Interface pour les services de notification
 */
interface NotificationServiceInterface 
{
    /**
     * Envoyer notification de validation en attente à un manager
     * 
     * @param int $managerId ID du manager à notifier
     * @param array $records Liste des enregistrements en attente
     * @return bool True si notification envoyée avec succès
     */
    public function notifyPendingValidation(int $managerId, array $records): bool;
    
    /**
     * Notifier un employé du statut de validation de son enregistrement
     * 
     * @param int $userId ID de l'employé à notifier
     * @param int $recordId ID de l'enregistrement validé
     * @param string $status Statut de validation ('approved', 'rejected', 'partial')
     * @return bool True si notification envoyée avec succès
     */
    public function notifyValidationStatus(int $userId, int $recordId, string $status): bool;
    
    /**
     * Alerter un manager d'une anomalie détectée
     * 
     * @param int $managerId ID du manager à alerter
     * @param string $anomalyType Type d'anomalie (constante ValidationConstants)
     * @param array $data Données contextuelles de l'anomalie
     * @return bool True si alerte envoyée avec succès
     */
    public function alertAnomaly(int $managerId, string $anomalyType, array $data): bool;
    
    /**
     * Récupérer les notifications non lues d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array Liste des notifications non lues avec métadonnées
     */
    public function getUnreadNotifications(int $userId): array;
    
    /**
     * Marquer une notification comme lue
     * 
     * @param int $notificationId ID de la notification
     * @return bool True si marquée comme lue avec succès
     */
    public function markAsRead(int $notificationId): bool;
    
    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     * 
     * @param int $userId ID de l'utilisateur
     * @return bool True si toutes marquées comme lues
     */
    public function markAllAsRead(int $userId): bool;
    
    /**
     * Compter les notifications non lues d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de notifications non lues
     */
    public function getUnreadCount(int $userId): int;
    
    /**
     * Supprimer les anciennes notifications (nettoyage)
     * 
     * @param int $daysOld Supprimer notifications plus anciennes que X jours
     * @return int Nombre de notifications supprimées
     */
    public function cleanupOldNotifications(int $daysOld = 30): int;
    
    /**
     * Créer une notification personnalisée
     * 
     * @param int $userId ID de l'utilisateur destinataire
     * @param string $type Type de notification
     * @param string $message Message de la notification
     * @param array $data Données additionnelles (optionnel)
     * @return int ID de la notification créée, 0 si échec
     */
    public function createCustomNotification(int $userId, string $type, string $message, array $data = []): int;
    
    /**
     * Envoyer notification de rappel pour validations en retard
     * 
     * @param int $managerId ID du manager
     * @param array $overdueRecords Enregistrements en retard de validation
     * @return bool True si rappel envoyé avec succès
     */
    public function sendValidationReminder(int $managerId, array $overdueRecords): bool;
}