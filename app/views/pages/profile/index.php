<?php
// User profile page
$user = $data['user'] ?? null;
$errors = $data['errors'] ?? [];
$role = $_SESSION['user_role'] ?? '';

// Role badges and colors
$roleBadges = [
    'business_owner' => '<span class="badge bg-success">Business Owner</span>',
    'admin' => '<span class="badge bg-primary">Administrator</span>',
    'treasurer' => '<span class="badge bg-warning text-dark">Treasurer</span>'
];

// Role-specific UI elements
$roleSpecific = [
    'business_owner' => [
        'icon' => 'bi bi-building',
        'color' => 'success'
    ],
    'admin' => [
        'icon' => 'bi bi-shield-lock',
        'color' => 'primary'
    ],
    'treasurer' => [
        'icon' => 'bi bi-cash-coin',
        'color' => 'warning'
    ]
];

$currentRole = $roleSpecific[$role] ?? $roleSpecific['business_owner'];
// Ensure user data is available
if (!$user) {
    echo '<div class="alert alert-danger">User data unavailable.</div>';
    return;
}
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="bi bi-person-circle me-2"></i>My Profile</h3>
        <div>
            <a href="<?= URLROOT ?>/profile/settings" class="btn btn-outline-primary">
                <i class="bi bi-gear me-1"></i> Account Settings
            </a>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php if (SessionHelper::hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= SessionHelper::getFlash('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (SessionHelper::hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= SessionHelper::getFlash('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Summary Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="avatar-circle bg-<?= $currentRole['color'] ?> bg-opacity-10 mx-auto mb-3">
                            <i class="<?= $currentRole['icon'] ?> fs-1 text-<?= $currentRole['color'] ?>"></i>
                        </div>
                        <h4 class="mb-1"><?= htmlspecialchars($user->first_name . ' ' . $user->last_name) ?></h4>
                        <p class="text-muted mb-2">@<?= htmlspecialchars($user->username) ?></p>
                        <?= $roleBadges[$role] ?? '' ?>
                        
                        <div class="mt-3 text-muted small">
                            Member since <?= date('F Y', strtotime($user->created_at)) ?>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Contact Information</h6>
                    <ul class="list-unstyled">
                        <?php if (!empty($user->email)): ?>
                        <li class="mb-2">
                            <div class="d-flex align-items-center">
                                <span class="icon-circle bg-primary bg-opacity-10 me-3">
                                    <i class="bi bi-envelope text-primary"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">Email</div>
                                    <div><?= htmlspecialchars($user->email) ?></div>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($user->phone_number)): ?>
                        <li class="mb-2">
                            <div class="d-flex align-items-center">
                                <span class="icon-circle bg-success bg-opacity-10 me-3">
                                    <i class="bi bi-telephone text-success"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">Phone</div>
                                    <div><?= htmlspecialchars($user->phone_number) ?></div>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($user->address)): ?>
                        <li>
                            <div class="d-flex align-items-center">
                                <span class="icon-circle bg-info bg-opacity-10 me-3">
                                    <i class="bi bi-geo-alt text-info"></i>
                                </span>
                                <div>
                                    <div class="text-muted small">Address</div>
                                    <div><?= htmlspecialchars($user->address) ?></div>
                                </div>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Role-specific information section -->
                    <?php if ($role === 'business_owner'): ?>
                    <div class="mt-4">
                        <h6 class="mb-3">Business Statistics</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">Active Businesses</div>
                                    <div class="fs-5 fw-bold text-success">
                                        <i class="bi bi-building me-1"></i> 
                                        <?php 
                                        // This would ideally be passed in from the controller
                                        echo isset($data['business_stats']) ? $data['business_stats']['active'] : 0; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">Pending</div>
                                    <div class="fs-5 fw-bold text-warning">
                                        <i class="bi bi-hourglass-split me-1"></i> 
                                        <?php 
                                        echo isset($data['business_stats']) ? $data['business_stats']['pending'] : 0; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($role === 'admin'): ?>
                    <div class="mt-4">
                        <h6 class="mb-3">Admin Activities</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">Applications</div>
                                    <div class="fs-5 fw-bold text-primary">
                                        <i class="bi bi-clipboard-check me-1"></i> 
                                        <?php 
                                        echo isset($data['admin_stats']) ? $data['admin_stats']['applications'] : 0; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">Staff</div>
                                    <div class="fs-5 fw-bold text-info">
                                        <i class="bi bi-people me-1"></i> 
                                        <?php 
                                        echo isset($data['admin_stats']) ? $data['admin_stats']['staff'] : 0; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($role === 'treasurer'): ?>
                    <div class="mt-4">
                        <h6 class="mb-3">Treasurer Statistics</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">Pending Payments</div>
                                    <div class="fs-5 fw-bold text-warning">
                                        <i class="bi bi-clock-history me-1"></i> 
                                        <?php 
                                        echo isset($data['treasurer_stats']) ? $data['treasurer_stats']['pending'] : 0; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border rounded bg-light">
                                    <div class="text-muted small">Total Verified</div>
                                    <div class="fs-5 fw-bold text-success">
                                        <i class="bi bi-check-circle me-1"></i> 
                                        <?php 
                                        echo isset($data['treasurer_stats']) ? $data['treasurer_stats']['verified'] : 0; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Edit Profile Form Card -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profile Information</h5>
                </div>
                <div class="card-body p-4">
                    <form action="<?= URLROOT ?>/profile/update" method="POST" enctype="multipart/form-data">
                        <!-- Personal Information Section -->
                        <h6 class="mb-3 text-muted">Personal Information</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                       id="first_name" name="first_name" 
                                       value="<?= htmlspecialchars($user->first_name) ?>" required>
                                <?php if (isset($errors['first_name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                       id="last_name" name="last_name" 
                                       value="<?= htmlspecialchars($user->last_name) ?>" required>
                                <?php if (isset($errors['last_name'])): ?>
                                    <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" name="email" 
                                       value="<?= htmlspecialchars($user->email ?? '') ?>">
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control <?= isset($errors['phone_number']) ? 'is-invalid' : '' ?>" 
                                       id="phone_number" name="phone_number" 
                                       value="<?= htmlspecialchars($user->phone_number ?? '') ?>">
                                <?php if (isset($errors['phone_number'])): ?>
                                    <div class="invalid-feedback"><?= $errors['phone_number'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                     id="address" name="address" rows="3"><?= htmlspecialchars($user->address ?? '') ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= $errors['address'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Account Information Section (Read-only) -->
                        <h6 class="mb-3 mt-4 text-muted">Account Information</h6>
                        
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control bg-light" 
                                       id="username" value="<?= htmlspecialchars($user->username) ?>" 
                                       readonly disabled>
                                <div class="form-text">Username cannot be changed</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="role" class="form-label">Account Type</label>
                                <input type="text" class="form-control bg-light" 
                                       id="role" value="<?= ucwords(str_replace('_', ' ', $user->role)) ?>" 
                                       readonly disabled>
                            </div>
                        </div>
                        
                        <!-- Role-specific form fields section -->
                        <?php if ($role === 'business_owner'): ?>
                        <h6 class="mb-3 mt-4 text-muted">Business Owner Information</h6>
                        
                        <div class="mb-3">
                            <label for="business_type" class="form-label">Business Type</label>
                            <select class="form-select" id="business_type" name="business_type">
                                <option value="" <?= empty($user->business_type) ? 'selected' : '' ?>>Select your main business type</option>
                                <option value="retail" <?= ($user->business_type ?? '') === 'retail' ? 'selected' : '' ?>>Retail</option>
                                <option value="service" <?= ($user->business_type ?? '') === 'service' ? 'selected' : '' ?>>Service</option>
                                <option value="food" <?= ($user->business_type ?? '') === 'food' ? 'selected' : '' ?>>Food & Beverage</option>
                                <option value="manufacturing" <?= ($user->business_type ?? '') === 'manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                                <option value="other" <?= ($user->business_type ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <div class="form-text">This helps us customize your business experience</div>
                        </div>
                        
                        <?php elseif ($role === 'treasurer'): ?>
                        <h6 class="mb-3 mt-4 text-muted">Treasurer Information</h6>
                        
                        <div class="mb-3">
                            <label for="signature" class="form-label">Digital Signature</label>
                            <div class="input-group">
                                <input type="file" class="form-control" id="signature" name="signature" disabled>
                                <button class="btn btn-outline-secondary" type="button" disabled>Upload</button>
                            </div>
                            <div class="form-text">Digital signature functionality coming soon</div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>