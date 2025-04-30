<div class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
        <div class="d-flex align-items-center justify-content-center mb-3">
        <div class="avatar-circle avatar-circle-lg me-3 bg-info-soft">
                <span class="avatar-initials"><?= substr($_SESSION['user_name'] ?? 'O', 0, 1) ?></span>
            </div>
        </div>
        <div class="text-center mb-3">
            <h6 class="sidebar-heading px-3 mt-1 mb-1 text-muted"><?= isset($_SESSION['user_name']) ? sanitize($_SESSION['user_name']) : 'Administrator' ?></h6>
            <span class="badge bg-primary">Administrator</span>
        </div>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= urlIs('/dashboard/admin') ? 'active' : '' ?>" href="<?= URLROOT ?>/admin/dashboard">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= urlIs('/business/list') ? 'active' : '' ?>" href="<?= URLROOT ?>/business/list">
                    <i class="bi bi-building me-2"></i>
                    Manage Businesses
                </a>
            </li>
        </ul>
        
        <hr>
        <div class="px-3 mt-3">
            <a href="<?= URLROOT ?>/logout" class="btn btn-danger btn-sm d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </div>
    </div>
</div>