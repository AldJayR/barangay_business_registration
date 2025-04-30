<?php

/**
 * Notification Model
 * Handles data operations for the 'notification' table.
 */
class Notification {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new notification
     *
     * @param array $data Notification data
     * @return int|false The ID of the newly created notification, or false if creation failed
     */
    public function createNotification(array $data): int|false {
        $this->db->query("INSERT INTO notification (user_id, type, message, link, is_read, created_at) 
                          VALUES (:user_id, :type, :message, :link, :is_read, NOW())");
        
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':message', $data['message']);
        $this->db->bind(':link', $data['link']);
        $this->db->bind(':is_read', $data['is_read'] ?? 0);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Get all notifications for a specific user
     *
     * @param int $userId User ID
     * @param int $limit Limit the number of results (optional)
     * @return array Array of notification objects
     */
    public function getNotificationsByUserId(int $userId, int $limit = 0): array {
        $sql = "SELECT * FROM notification WHERE user_id = :user_id ORDER BY created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        $results = $this->db->resultSet();
        
        return $results ?: [];
    }

    /**
     * Get unread notifications count for a user
     *
     * @param int $userId User ID
     * @return int Count of unread notifications
     */
    public function getUnreadCount(int $userId): int {
        $this->db->query("SELECT COUNT(*) as count FROM notification WHERE user_id = :user_id AND is_read = 0");
        $this->db->bind(':user_id', $userId);
        $row = $this->db->single();
        
        return $row ? (int)$row->count : 0;
    }

    /**
     * Mark a notification as read
     *
     * @param int $id Notification ID
     * @return bool True if successful, false otherwise
     */
    public function markAsRead(int $id): bool {
        $this->db->query("UPDATE notification SET is_read = 1 WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param int $userId User ID
     * @return bool True if successful, false otherwise
     */
    public function markAllAsRead(int $userId): bool {
        $this->db->query("UPDATE notification SET is_read = 1 WHERE user_id = :user_id");
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    /**
     * Delete a notification
     *
     * @param int $id Notification ID
     * @return bool True if successful, false otherwise
     */
    public function deleteNotification(int $id): bool {
        $this->db->query("DELETE FROM notification WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Create permit renewal notifications for businesses expiring soon
     * 
     * @param int $daysBeforeExpiry Days before expiry to send notification
     * @return int Number of notifications created
     */
    public function createRenewalNotifications(int $daysBeforeExpiry = 30): int {
        // Calculate the date for which we want to check expiring permits
        $expiryDate = date('Y-m-d', strtotime("+$daysBeforeExpiry days"));
        
        // Find permits that expire on the target date and don't already have a notification
        $this->db->query("
            SELECT p.id, p.business_id, p.permit_number, p.expiration_date, 
                   b.name as business_name, b.user_id, u.email
            FROM permit p 
            JOIN business b ON p.business_id = b.id
            JOIN user u ON b.user_id = u.id
            WHERE p.expiration_date = :expiry_date
            AND NOT EXISTS (
                SELECT 1 FROM notification n 
                WHERE n.type = 'permit_renewal' 
                AND n.link LIKE CONCAT('%', p.id, '%')
                AND DATEDIFF(:expiry_date, n.created_at) <= 30
            )
        ");
        
        $this->db->bind(':expiry_date', $expiryDate);
        $expiringPermits = $this->db->resultSet();
        
        $count = 0;
        foreach ($expiringPermits as $permit) {
            // Create in-app notification
            $notificationData = [
                'user_id' => $permit->user_id,
                'type' => 'permit_renewal',
                'message' => "Your permit for {$permit->business_name} will expire in $daysBeforeExpiry days on " . date('F j, Y', strtotime($permit->expiration_date)),
                'link' => URLROOT . "/business/view/{$permit->business_id}",
                'is_read' => 0
            ];
            
            $notificationCreated = $this->createNotification($notificationData);
            
            // Send email notification if email notifications are enabled
            if (defined('EMAIL_NOTIFICATIONS_ENABLED') && EMAIL_NOTIFICATIONS_ENABLED && !empty($permit->email)) {
                require_once APPROOT . '/services/EmailService.php';
                $emailService = new EmailService();
                
                // Create renewal URL
                $renewalUrl = URLROOT . "/business/view/{$permit->business_id}";
                
                // Send email
                $emailSent = $emailService->sendRenewalReminderEmail(
                    $permit->email,
                    $permit->business_name,
                    $permit->expiration_date,
                    $daysBeforeExpiry,
                    $renewalUrl
                );
                
                if ($emailSent) {
                    error_log("Renewal reminder email sent to {$permit->email} for business {$permit->business_name}");
                } else {
                    error_log("Failed to send renewal reminder email to {$permit->email} for business {$permit->business_name}");
                }
            }
            
            if ($notificationCreated) {
                $count++;
            }
        }
        
        return $count;
    }
}