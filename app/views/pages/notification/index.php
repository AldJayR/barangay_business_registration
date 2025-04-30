<?php
// View file for displaying user notifications
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-bell me-2"></i>My Notifications</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                </ol>
            </nav>
        </div>
        <?php if (!empty($notifications)): ?>
        <div>
            <a href="<?= URLROOT ?>/notification/mark-read" class="btn btn-outline-primary">
                <i class="bi bi-check-all me-1"></i> Mark All as Read
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php SessionHelper::displayFlashMessages(); ?>

    <!-- Notifications Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-bell me-2"></i>Notifications</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($notifications)): ?>
                <div class="p-4 text-center">
                    <i class="bi bi-bell-slash text-muted fs-1"></i>
                    <p class="mt-3 mb-0">You have no notifications</p>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item p-3 <?= $notification->is_read ? '' : 'list-group-item-light' ?>">
                            <div class="d-flex align-items-center">
                                <?php
                                // Set icon based on notification type
                                $icon = 'bell';
                                $iconBg = 'bg-primary-soft text-primary';
                                
                                switch ($notification->type) {
                                    case 'permit_renewal':
                                        $icon = 'calendar-check';
                                        $iconBg = 'bg-warning-soft text-warning';
                                        break;
                                    case 'payment':
                                        $icon = 'credit-card';
                                        $iconBg = 'bg-success-soft text-success';
                                        break;
                                    case 'application':
                                        $icon = 'file-earmark-check';
                                        $iconBg = 'bg-info-soft text-info';
                                        break;
                                }
                                ?>
                                <div class="flex-shrink-0">
                                    <div class="notification-icon <?= $iconBg ?> rounded-circle">
                                        <i class="bi bi-<?= $icon ?>"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <a href="<?= URLROOT ?>/notification/view/<?= $notification->id ?>" class="text-decoration-none text-dark">
                                            <p class="mb-1 <?= $notification->is_read ? '' : 'fw-bold' ?>"><?= htmlspecialchars($notification->message) ?></p>
                                        </a>
                                        <small class="text-muted ms-2"><?= time_elapsed_string($notification->created_at) ?></small>
                                    </div>
                                    <div class="d-flex w-100 justify-content-between align-items-center mt-2">
                                        <small class="text-muted"><?= date('M d, Y h:i A', strtotime($notification->created_at)) ?></small>
                                        <a href="<?= URLROOT ?>/notification/delete/<?= $notification->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this notification?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.notification-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-soft { background-color: rgba(13, 110, 253, 0.1); }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.1); }
.bg-info-soft { background-color: rgba(13, 202, 240, 0.1); }
</style>