<?php
// This ensures any direct access to this layout has access to constants
require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/functions.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    redirect('auth/login');
}

// Get user role for sidebar inclusion
$userRole = sanitize($_SESSION['user_role'] ?? 'guest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barangay Business Registration System">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= URLROOT ?>/css/style.css" rel="stylesheet">
    <style>
        /* Notification Styles */
        .notification-dropdown {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
        }
        .notification-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .notification-item {
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }
        .notification-item:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }
        .notification-item.unread {
            border-left-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.04);
        }
        .bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
        .bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
        .bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
        .bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
        .bg-danger-soft { background-color: rgba(220, 53, 69, 0.1); }
    </style>
    <title><?= isset($title) ? sanitize($title) . ' | ' . SITENAME : SITENAME ?></title>
</head>
<body>
    <!-- Include header -->
    <?php include_once 'partials/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Include sidebar based on user role -->
            <?php
            $sidebarPath = 'partials/sidebar_' . strtolower($userRole) . '.php';
            if (file_exists(APPROOT . '/views/layouts/' . $sidebarPath)) {
                // Check if we're using the admin sidebar which already has column classes
                if (strtolower($userRole) === 'admin') {
                    include_once $sidebarPath;
                } else {
                    // For other sidebars, wrap them with the appropriate column classes
                    echo '<div class="col-md-3 col-lg-2 d-md-block bg-white sidebar">';
                    include_once $sidebarPath;
                    echo '</div>';
                }
            } else {
                // Fallback to default sidebar if role-specific one doesn't exist
                echo '<div class="col-md-3 col-lg-2 d-md-block bg-white sidebar">';
                include_once 'partials/sidebar_owner.php';
                echo '</div>';
            }
            ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Flash Messages Area -->
                <div id="flash-messages" class="mb-4">
                    <?php if (class_exists('SessionHelper')): ?>
                        <?php SessionHelper::displayFlashMessages(); ?>
                    <?php endif; ?>
                </div>
                
                <!-- Main Content Area -->
                <?php 
                // Include the view file based on $view variable
                if (isset($view)) {
                    $viewPath = APPROOT . '/views/' . $view . '.php';
                    if (file_exists($viewPath)) {
                        include $viewPath; // Changed from require to include
                    } else {
                        echo '<div class="alert alert-danger">View file not found: ' . htmlspecialchars($view) . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">No view specified</div>';
                }
                ?>
            </main>
        </div>
    </div>
    
    <!-- Include footer -->
    <?php include_once 'partials/footer.php'; ?>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="<?= URLROOT ?>/js/script.js"></script>
    
    <!-- Notification JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to fetch notification count every 2 minutes
            function updateNotificationCount() {
                fetch('<?= URLROOT ?>/notification/count')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.querySelector('#notificationsDropdown .badge');
                        if (data.count > 0) {
                            // Create or update the badge
                            if (badge) {
                                badge.textContent = data.count > 99 ? '99+' : data.count;
                            } else {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger';
                                newBadge.innerHTML = (data.count > 99 ? '99+' : data.count) + 
                                                  '<span class="visually-hidden">unread notifications</span>';
                                document.querySelector('#notificationsDropdown').appendChild(newBadge);
                            }
                        } else if (badge) {
                            // Remove the badge if count is 0
                            badge.remove();
                        }
                    })
                    .catch(error => console.error('Error fetching notification count:', error));
            }
            
            // Initial count check
            updateNotificationCount();
            
            // Set up interval to check for new notifications
            setInterval(updateNotificationCount, 120000); // Check every 2 minutes
        });
    </script>
</body>
</html>