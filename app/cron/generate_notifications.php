<?php
/**
 * Cron job script to automatically generate permit renewal notifications
 * 
 * This script should be scheduled to run daily via cron or Windows Task Scheduler:
 * 
 * Example cron entry (Linux/Mac):
 * 0 0 * * * php /path/to/barangay_business_registration/app/cron/generate_notifications.php
 * 
 * For Windows Task Scheduler:
 * Program/script: C:\xampp\php\php.exe
 * Arguments: C:\xampp\htdocs\barangay_business_registration\app\cron\generate_notifications.php
 */

// Load required files
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once APPROOT . '/models/Database.php';
require_once APPROOT . '/models/Notification.php';

// Set error logging
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('error_log', APPROOT . '/logs/cron_errors.log');

try {
    // Initialize the notification model
    $notificationModel = new Notification();

    // Generate notifications for 30, 15, and 7 days before expiry
    $periods = [30, 15, 7];
    $totalNotifications = 0;
    
    foreach ($periods as $days) {
        $count = $notificationModel->createRenewalNotifications($days);
        $totalNotifications += $count;
        
        // Log the results
        error_log(date('Y-m-d H:i:s') . " - Generated $count notifications for permits expiring in $days days");
    }
    
    // Output results
    echo date('Y-m-d H:i:s') . " - Notification generation completed. Created $totalNotifications notifications.\n";
    
} catch (Exception $e) {
    // Log errors
    error_log(date('Y-m-d H:i:s') . " - Error in notification cron job: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}
?>