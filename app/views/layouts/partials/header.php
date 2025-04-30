<?php
// Access session data safely
$userName = sanitize($_SESSION['user_name'] ?? 'User');
$userRole = sanitize($_SESSION['user_role'] ?? 'guest'); // Get role for potential display/logic
$userId = $_SESSION['user_id'] ?? 0;

// Fetch unread notifications count and recent notifications
$notificationModel = null;
$unreadCount = 0;
$recentNotifications = [];
if ($userId) {
    require_once APPROOT . '/models/Notification.php';
    $notificationModel = new Notification();
    $unreadCount = $notificationModel->getUnreadCount($userId);
    $recentNotifications = $notificationModel->getNotificationsByUserId($userId, 5); // Limit to 5 recent notifications
}
?>
<nav class="navbar navbar-expand-md navbar-light sticky-top flex-md-nowrap p-0 shadow-sm border-bottom">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 py-3 fs-5 fw-bold" href="<?= URLROOT ?>/dashboard">
        <i class="bi bi-building me-2"></i><?= SITENAME ?>
    </a>

    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Search Input - enhanced styling -->
    <div class="d-none d-md-flex flex-grow-0 ms-auto me-3">
        <div class="input-group input-group-sm search-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input class="form-control" type="text" placeholder="Search..." aria-label="Search">
        </div>
    </div>

    <div class="navbar-nav">
        <!-- Notifications -->
        <div class="nav-item dropdown">
            <a class="nav-link px-3 position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-5"></i>
                <?php if ($unreadCount > 0): ?>
                <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger">
                    <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                    <span class="visually-hidden">unread notifications</span>
                </span>
                <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm notification-dropdown" aria-labelledby="notificationsDropdown">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                
                <?php if (empty($recentNotifications)): ?>
                <li><div class="dropdown-item">
                    <p class="text-muted text-center mb-0">No notifications</p>
                </div></li>
                <?php else: ?>
                    <?php foreach ($recentNotifications as $notification): ?>
                        <?php 
                        // Set icon based on notification type
                        $icon = 'bell';
                        $bgClass = 'bg-primary-soft text-primary';
                        
                        switch ($notification->type) {
                            case 'permit_renewal':
                                $icon = 'calendar-check';
                                $bgClass = 'bg-warning-soft text-warning';
                                break;
                            case 'payment':
                                $icon = 'credit-card';
                                $bgClass = 'bg-success-soft text-success';
                                break;
                            case 'application':
                                $icon = 'file-earmark-check';
                                $bgClass = 'bg-info-soft text-info';
                                break;
                        }
                        ?>
                        <li><a class="dropdown-item notification-item <?= $notification->is_read ? '' : 'unread' ?>" href="<?= URLROOT ?>/notification/view/<?= $notification->id ?>">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="notification-icon <?= $bgClass ?> rounded-circle">
                                        <i class="bi bi-<?= $icon ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-0 notification-text"><?= htmlspecialchars(substr($notification->message, 0, 60)) ?><?= strlen($notification->message) > 60 ? '...' : '' ?></p>
                                    <small class="text-muted"><?= time_elapsed_string($notification->created_at) ?></small>
                                </div>
                            </div>
                        </a></li>
                    <?php endforeach; ?>
                <?php endif; ?>

                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center small text-primary" href="<?= URLROOT ?>/notification">View all notifications</a></li>
            </ul>
        </div>
        
        <!-- User profile dropdown -->
        <div class="nav-item dropdown">
            <a class="nav-link px-3 d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-circle avatar-circle-sm me-2">
                    <span class="avatar-initials"><?= substr($userName, 0, 1) ?></span>
                </div>
                <span class="d-none d-md-inline"><?= $userName ?></span>
                <i class="bi bi-chevron-down ms-1 small"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                <li><h6 class="dropdown-header">Hello, <?= $userName ?>!</h6></li>
                <li><a class="dropdown-item" href="<?= URLROOT ?>/user/profile">
                    <i class="bi bi-person-circle me-2 text-muted"></i> My Profile
                </a></li>
                <li><a class="dropdown-item" href="<?= URLROOT ?>/user/settings">
                    <i class="bi bi-gear me-2 text-muted"></i> Settings
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?= URLROOT ?>/auth/logout">
                    <i class="bi bi-box-arrow-right me-2 text-muted"></i> Sign Out
                </a></li>
            </ul>
        </div>
    </div>
</nav>

<?php 
/**
 * Helper function to format timestamps as "time ago"
 */
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Create a custom array to store our time values
    $timeUnits = [
        'y' => $diff->y,
        'm' => $diff->m,
        'd' => $diff->d,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s,
    ];
    
    // Calculate weeks separately (not part of standard DateInterval)
    $days = $diff->days; // Total days
    $weeks = floor($days / 7);
    if ($weeks > 0) {
        $timeUnits['w'] = $weeks;
        $timeUnits['d'] = $diff->d % 7; // Remaining days after weeks
    }

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if (isset($timeUnits[$k]) && $timeUnits[$k]) {
            $v = $timeUnits[$k] . ' ' . $v . ($timeUnits[$k] > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>