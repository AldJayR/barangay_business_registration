<div class="sidebar-header p-3 border-bottom">
    <div class="user-profile">
        <div class="d-flex align-items-center">
            <div class="avatar-circle avatar-circle-lg me-3 bg-success-soft">
                <span class="avatar-initials"><?= substr($_SESSION['user_name'] ?? 'T', 0, 1) ?></span>
            </div>
            <div>
                <h6 class="mb-0"><?= sanitize($_SESSION['user_name'] ?? 'Treasurer') ?></h6>
                <div class="text-muted small">Treasurer</div>
            </div>
        </div>
    </div>
</div>

<div class="p-3">
    <h6 class="sidebar-heading text-uppercase text-muted d-flex justify-content-between align-items-center mb-2">
        <span>Main Menu</span>
    </h6>
    
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" aria-current="page" href="<?= URLROOT ?>/treasurer/dashboard">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/treasurer/verify">
                <i class="bi bi-patch-check me-2"></i>
                <span>Verify Payments</span>
                <span class="badge bg-danger rounded-pill ms-auto">
                    <?php
                    // Dynamically fetch pending payment count
                    require_once APPROOT . '/models/Payment.php';
                    $paymentModel = new Payment();
                    $pendingCount = $paymentModel->countPaymentsByStatus('Pending');
                    echo $pendingCount > 0 ? $pendingCount : '';
                    ?>
                </span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/treasurer/history">
                <i class="bi bi-clock-history me-2"></i>
                <span>Payment History</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/treasurer/reports">
                <i class="bi bi-graph-up me-2"></i>
                <span>Payment Reports</span>
            </a>
        </li>
    </ul>
    
    <h6 class="sidebar-heading text-uppercase text-muted d-flex justify-content-between align-items-center pt-3 mt-3 mb-2 border-top">
        <span>Account</span>
    </h6>
    
    <ul class="nav flex-column">
        
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/auth/logout">
                <i class="bi bi-box-arrow-right me-2"></i>
                <span>Sign Out</span>
            </a>
        </li>
    </ul>
</div>