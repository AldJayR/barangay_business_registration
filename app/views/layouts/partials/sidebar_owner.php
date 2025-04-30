<div class="sidebar-header p-3 border-bottom">
    <div class="user-profile">
        <div class="d-flex align-items-center">
            <div class="avatar-circle avatar-circle-lg me-3 bg-info-soft">
                <span class="avatar-initials"><?= substr($_SESSION['user_name'] ?? 'O', 0, 1) ?></span>
            </div>
            <div>
                <h6 class="mb-0"><?= sanitize($_SESSION['user_name'] ?? 'Business Owner') ?></h6>
                <div class="text-muted small">Business Owner</div>
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
            <a class="nav-link rounded-3 d-flex align-items-center" aria-current="page" href="<?= URLROOT ?>/owner/dashboard">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/business/apply">
                <i class="bi bi-file-earmark-plus me-2"></i>
                <span>Apply for Permit</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/business/list">
                <i class="bi bi-briefcase me-2"></i>
                <span>My Businesses</span>
                <span class="badge bg-info rounded-pill ms-auto">3</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/business/applications">
                <i class="bi bi-hourglass-split me-2"></i>
                <span>My Applications</span>
                <span class="badge bg-warning rounded-pill ms-auto">2</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-3 d-flex align-items-center" href="<?= URLROOT ?>/payment/history">
                <i class="bi bi-receipt me-2"></i>
                <span>Payment History</span>
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