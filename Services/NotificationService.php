<?php
/**
 * Service Notifications - Responsabilité unique : Gestion notifications
 * 
 * Respecte le principe SRP : Seule responsabilité les notifications
 * Respecte le principe OCP : Extensible pour nouveaux types notifications
 * Respecte le principe DIP : Peut être injecté via interface
 */

if (!defined('DOL_DOCUMENT_ROOT')) {
    exit('This script cannot be run directly');
}

require_once DOL_DOCUMENT_ROOT.'/custom/appmobtimetouch/Constants/ValidationConstants.php';

class NotificationService implements NotificationServiceInterface 
{
    private DoliDB $db;
    
    public function __construct(DoliDB $db) 
    {
        $this->db = $db;
    }
    
    /**
     * Notifier manager des validations en attente
     */
    public function notifyPendingValidation(int $managerId, array $records): bool 
    {
        $count = count($records);
        if ($count === 0) {
            dol_syslog("NotificationService: No pending records to notify for manager $managerId", LOG_DEBUG);
            return true;
        }
        
        dol_syslog("NotificationService: Notifying manager $managerId of $count pending validations", LOG_DEBUG);
        
        $message = sprintf(
            "You have %d time record(s) pending validation", 
            $count
        );
        
        return $this->createNotification(
            $managerId,
            'pending_validation',
            $message,
            ['count' => $count, 'record_ids' => array_column($records, 'rowid')]
        );
    }
    
    /**
     * Notifier employé du statut de validation
     */
    public function notifyValidationStatus(int $userId, int $recordId, string $status): bool 
    {
        dol_syslog("NotificationService: Notifying user $userId of validation status $status for record $recordId", LOG_DEBUG);
        
        $statusLabels = [
            'approve' => 'approved',
            'reject' => 'rejected', 
            'partial' => 'partially approved'
        ];
        
        $message = sprintf(
            "Your time record has been %s", 
            $statusLabels[$status] ?? $status
        );
        
        return $this->createNotification(
            $userId,
            'validation_status',
            $message,
            ['record_id' => $recordId, 'status' => $status]
        );
    }
    
    /**
     * Alerter manager d'une anomalie
     */
    public function alertAnomaly(int $managerId, string $anomalyType, array $data): bool 
    {
        dol_syslog("NotificationService: Alerting manager $managerId of anomaly $anomalyType", LOG_DEBUG);
        
        $messages = [
            ValidationConstants::ANOMALY_OVERTIME => 'Overtime detected for employee',
            ValidationConstants::ANOMALY_MISSING_CLOCKOUT => 'Missing clock-out detected',
            ValidationConstants::ANOMALY_LONG_BREAK => 'Extended break detected',
            ValidationConstants::ANOMALY_LOCATION_MISMATCH => 'Location mismatch detected'
        ];
        
        $message = $messages[$anomalyType] ?? 'Anomaly detected';
        
        return $this->createNotification(
            $managerId,
            'anomaly_alert',
            $message,
            array_merge(['anomaly_type' => $anomalyType], $data)
        );
    }
    
    /**
     * Récupérer notifications non lues
     */
    public function getUnreadNotifications(int $userId): array 
    {
        $sql = "SELECT * FROM " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= " AND is_read = 0";
        $sql .= " ORDER BY created_date DESC";
        
        $result = $this->db->query($sql);
        $notifications = [];
        
        if ($result) {
            while ($obj = $this->db->fetch_object($result)) {
                $notifications[] = [
                    'id' => (int) $obj->rowid,
                    'type' => $obj->notification_type,
                    'message' => $obj->message,
                    'data' => json_decode($obj->notification_data, true) ?? [],
                    'created_date' => $obj->created_date,
                    'priority' => $this->getNotificationPriority($obj->notification_type)
                ];
            }
            $this->db->free($result);
        }
        
        dol_syslog("NotificationService: Found " . count($notifications) . " unread notifications for user $userId", LOG_DEBUG);
        return $notifications;
    }
    
    /**
     * Marquer notification comme lue
     */
    public function markAsRead(int $notificationId): bool 
    {
        dol_syslog("NotificationService: Marking notification $notificationId as read", LOG_DEBUG);
        
        $sql = "UPDATE " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " SET is_read = 1, read_date = NOW()";
        $sql .= " WHERE rowid = " . ((int) $notificationId);
        
        $result = $this->db->query($sql);
        
        if ($result) {
            dol_syslog("NotificationService: Notification $notificationId marked as read", LOG_DEBUG);
            return true;
        }
        
        dol_syslog("NotificationService: Failed to mark notification $notificationId as read", LOG_ERR);
        return false;
    }
    
    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(int $userId): bool 
    {
        dol_syslog("NotificationService: Marking all notifications as read for user $userId", LOG_DEBUG);
        
        $sql = "UPDATE " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " SET is_read = 1, read_date = NOW()";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= " AND is_read = 0";
        
        $result = $this->db->query($sql);
        
        if ($result) {
            $affected = $this->db->affected_rows($result);
            dol_syslog("NotificationService: Marked $affected notifications as read for user $userId", LOG_DEBUG);
            return true;
        }
        
        return false;
    }
    
    /**
     * Compter les notifications non lues d'un utilisateur
     */
    public function getUnreadCount(int $userId): int 
    {
        $sql = "SELECT COUNT(*) as count FROM " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " WHERE fk_user = " . ((int) $userId);
        $sql .= " AND is_read = 0";
        
        $result = $this->db->query($sql);
        
        if ($result && $obj = $this->db->fetch_object($result)) {
            $count = (int) $obj->count;
            $this->db->free($result);
            return $count;
        }
        
        return 0;
    }
    
    /**
     * Supprimer les anciennes notifications (nettoyage)
     */
    public function cleanupOldNotifications(int $daysOld = 30): int 
    {
        dol_syslog("NotificationService: Cleaning up notifications older than $daysOld days", LOG_DEBUG);
        
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " WHERE created_date < DATE_SUB(NOW(), INTERVAL " . ((int) $daysOld) . " DAY)";
        $sql .= " AND is_read = 1"; // Supprimer seulement les notifications lues
        
        $result = $this->db->query($sql);
        
        if ($result) {
            $deleted = $this->db->affected_rows($result);
            dol_syslog("NotificationService: Cleaned up $deleted old notifications", LOG_DEBUG);
            return $deleted;
        }
        
        return 0;
    }
    
    /**
     * Créer une notification personnalisée
     */
    public function createCustomNotification(int $userId, string $type, string $message, array $data = []): int 
    {
        dol_syslog("NotificationService: Creating custom notification for user $userId (type: $type)", LOG_DEBUG);
        
        return $this->createNotification($userId, $type, $message, $data) ? 1 : 0;
    }
    
    /**
     * Envoyer notification de rappel pour validations en retard
     */
    public function sendValidationReminder(int $managerId, array $overdueRecords): bool 
    {
        $count = count($overdueRecords);
        if ($count === 0) {
            return true;
        }
        
        dol_syslog("NotificationService: Sending validation reminder to manager $managerId for $count overdue records", LOG_DEBUG);
        
        $message = sprintf(
            "REMINDER: You have %d overdue time record(s) awaiting validation", 
            $count
        );
        
        return $this->createNotification(
            $managerId,
            'validation_reminder',
            $message,
            [
                'count' => $count, 
                'record_ids' => array_column($overdueRecords, 'rowid'),
                'priority' => 'high'
            ]
        );
    }
    
    // === MÉTHODES PRIVÉES UTILITAIRES ===
    
    /**
     * Créer une notification dans la base de données
     */
    private function createNotification(int $userId, string $type, string $message, array $data = []): bool 
    {
        // Vérifier si la table existe
        if (!$this->tableExists('timeclock_notifications')) {
            dol_syslog("NotificationService: Table timeclock_notifications does not exist, creating fallback notification", LOG_WARNING);
            return $this->createFallbackNotification($userId, $type, $message, $data);
        }
        
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "timeclock_notifications";
        $sql .= " (entity, fk_user, notification_type, message, notification_data, created_date, is_read)";
        $sql .= " VALUES (";
        $sql .= "1,"; // Entity
        $sql .= ((int) $userId) . ",";
        $sql .= "'" . $this->db->escape($type) . "',";
        $sql .= "'" . $this->db->escape($message) . "',";
        $sql .= "'" . $this->db->escape(json_encode($data)) . "',";
        $sql .= "NOW(),";
        $sql .= "0"; // Not read
        $sql .= ")";
        
        $result = $this->db->query($sql);
        
        if ($result) {
            dol_syslog("NotificationService: Notification created successfully for user $userId", LOG_DEBUG);
            return true;
        } else {
            dol_syslog("NotificationService: Failed to create notification: " . $this->db->lasterror(), LOG_ERR);
            return false;
        }
    }
    
    /**
     * Fallback: utiliser le système de notifications Dolibarr natif
     */
    private function createFallbackNotification(int $userId, string $type, string $message, array $data): bool 
    {
        // Utiliser les événements Dolibarr ou log comme fallback
        dol_syslog("NotificationService FALLBACK: $type for user $userId - $message", LOG_INFO);
        
        // Optionnel: Ajouter à l'agenda Dolibarr ou autre système
        // require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
        // $actioncomm = new ActionComm($this->db);
        // ...
        
        return true;
    }
    
    /**
     * Vérifier si une table existe
     */
    private function tableExists(string $tableName): bool 
    {
        $sql = "SHOW TABLES LIKE '" . MAIN_DB_PREFIX . $tableName . "'";
        $result = $this->db->query($sql);
        
        if ($result) {
            $exists = $this->db->num_rows($result) > 0;
            $this->db->free($result);
            return $exists;
        }
        
        return false;
    }
    
    /**
     * Déterminer la priorité d'une notification selon son type
     */
    private function getNotificationPriority(string $type): string 
    {
        return match($type) {
            'anomaly_alert', 'validation_reminder' => 'high',
            'pending_validation' => 'medium',
            'validation_status' => 'low',
            default => 'normal'
        };
    }
    
    /**
     * Formater les données de notification pour l'affichage
     */
    public function formatNotificationForDisplay(array $notification): array 
    {
        $icons = [
            'pending_validation' => 'md-schedule',
            'validation_status' => 'md-check-circle',
            'anomaly_alert' => 'md-warning',
            'validation_reminder' => 'md-alarm'
        ];
        
        $colors = [
            'pending_validation' => '#FF9800',
            'validation_status' => '#4CAF50',
            'anomaly_alert' => '#F44336',
            'validation_reminder' => '#E91E63'
        ];
        
        return array_merge($notification, [
            'icon' => $icons[$notification['type']] ?? 'md-info',
            'color' => $colors[$notification['type']] ?? '#607D8B',
            'formatted_date' => $this->formatRelativeTime($notification['created_date'])
        ]);
    }
    
    /**
     * Formater une date en temps relatif (il y a X minutes)
     */
    private function formatRelativeTime(string $datetime): string 
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return "Il y a " . $diff . " seconde(s)";
        } elseif ($diff < 3600) {
            return "Il y a " . floor($diff / 60) . " minute(s)";
        } elseif ($diff < 86400) {
            return "Il y a " . floor($diff / 3600) . " heure(s)";
        } else {
            return "Il y a " . floor($diff / 86400) . " jour(s)";
        }
    }
}