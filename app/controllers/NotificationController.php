<?php

class NotificationController extends Controller {
    private ?Notification $notificationModel = null;
    private $db;

    public function __construct() {
        $this->requireLogin();
        $this->notificationModel = $this->model('Notification');
        
        // Initialize database instance
        $this->db = Database::getInstance();
        
        if ($this->notificationModel === null) {
            error_log("Failed to load Notification model in NotificationController.");
            die("Critical error: Notification model could not be loaded.");
        }
    }

    /**
     * Display all notifications for the current user
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $notifications = $this->notificationModel->getNotificationsByUserId($userId);
        
        $data = [
            'title' => 'My Notifications',
            'notifications' => $notifications
        ];
        
        $this->view('pages/notification/index', $data, 'main');
    }

    /**
     * Mark a notification as read and redirect to its link
     * 
     * @param int $id Notification ID
     */
    public function viewNotification($id = null) {
        if ($id === null) {
            redirect('notification');
            return;
        }
        
        // Get notification details
        $this->db->query("SELECT * FROM notification WHERE id = :id");
        $this->db->bind(':id', $id);
        $notification = $this->db->single();
        
        if (!$notification) {
            SessionHelper::setFlash('error', 'Notification not found');
            redirect('notification');
            return;
        }
        
        // Check if the notification belongs to the current user
        if ($notification->user_id !== $_SESSION['user_id']) {
            SessionHelper::setFlash('error', 'Unauthorized access');
            redirect('notification');
            return;
        }
        
        // Mark the notification as read
        $this->notificationModel->markAsRead($id);
        
        // Redirect to the notification link
        if (!empty($notification->link)) {
            redirect($notification->link);
        } else {
            redirect('notification');
        }
    }

    /**
     * Mark all notifications as read for the current user
     */
    public function markAllAsRead() {
        $userId = $_SESSION['user_id'];
        
        if ($this->notificationModel->markAllAsRead($userId)) {
            SessionHelper::setFlash('success', 'All notifications marked as read');
        } else {
            SessionHelper::setFlash('error', 'Failed to mark notifications as read');
        }
        
        redirect('notification');
    }

    /**
     * Delete a notification
     * 
     * @param int $id Notification ID
     */
    public function delete($id = null) {
        if ($id === null) {
            redirect('notification');
            return;
        }
        
        // Get notification details
        $this->db->query("SELECT * FROM notification WHERE id = :id");
        $this->db->bind(':id', $id);
        $notification = $this->db->single();
        
        if (!$notification) {
            SessionHelper::setFlash('error', 'Notification not found');
            redirect('notification');
            return;
        }
        
        // Check if the notification belongs to the current user
        if ($notification->user_id !== $_SESSION['user_id']) {
            SessionHelper::setFlash('error', 'Unauthorized access');
            redirect('notification');
            return;
        }
        
        // Delete the notification
        if ($this->notificationModel->deleteNotification($id)) {
            SessionHelper::setFlash('success', 'Notification deleted');
        } else {
            SessionHelper::setFlash('error', 'Failed to delete notification');
        }
        
        redirect('notification');
    }

    /**
     * Get notification count (for AJAX requests)
     */
    public function getCount() {
        $userId = $_SESSION['user_id'];
        $count = $this->notificationModel->getUnreadCount($userId);
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }

    /**
     * Admin function to generate permit renewal notifications
     * Accessible only to admin/treasurer roles
     */
    public function generateRenewalNotifications() {
        if (!(
            $this->hasRole(ROLE_ADMIN) || $this->hasRole(ROLE_TREASURER)
        )) {
            SessionHelper::setFlash('error', 'Unauthorized access.');
            redirect('dashboard');
            return;
        }
        
        // Define notification periods
        $notificationPeriods = [30, 15, 7]; // 30 days, 15 days, and 7 days before expiry
        $totalNotifications = 0;
        
        foreach ($notificationPeriods as $days) {
            $count = $this->notificationModel->createRenewalNotifications($days);
            $totalNotifications += $count;
        }
        
        if ($totalNotifications > 0) {
            SessionHelper::setFlash('success', "Generated $totalNotifications permit renewal notifications");
        } else {
            SessionHelper::setFlash('info', 'No new permit renewal notifications needed at this time');
        }
        
        redirect('admin/dashboard');
    }

    /**
     * Display and handle email notification settings
     */
    public function settings() {
        $userId = $_SESSION['user_id'];
        
        // Get user model to access email preferences
        $userModel = $this->model('User');
        $user = $userModel->findById($userId);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Notification Settings',
            'user' => $user,
            'emailPreferences' => [
                'permit_renewal' => $user->email_notify_renewal ?? 1,
                'payment_confirmation' => $user->email_notify_payment ?? 1,
                'application_status' => $user->email_notify_status ?? 1
            ],
            'errors' => []
        ];
        
        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update email notification preferences
            $emailPreferences = [
                'email_notify_renewal' => isset($_POST['permit_renewal']) ? 1 : 0,
                'email_notify_payment' => isset($_POST['payment_confirmation']) ? 1 : 0,
                'email_notify_status' => isset($_POST['application_status']) ? 1 : 0
            ];
            
            if ($userModel->updateEmailPreferences($userId, $emailPreferences)) {
                SessionHelper::setFlash('success', 'Notification settings updated successfully');
                redirect('notification/settings');
            } else {
                SessionHelper::setFlash('error', 'Failed to update notification settings');
                $data['errors']['update'] = 'Failed to update settings. Please try again.';
            }
        }
        
        $this->view('pages/notification/settings', $data, 'main');
    }
}