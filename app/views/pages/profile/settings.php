<?php
// Account settings page
$user = $data['user'] ?? null;
$errors = $data['errors'] ?? [];
$role = $_SESSION['user_role'] ?? '';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="bi bi-gear me-2"></i>Account Settings</h3>
        <div>
            <a href="<?= URLROOT ?>/profile" class="btn btn-outline-primary">
                <i class="bi bi-person me-1"></i> Back to Profile
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
        <!-- Settings Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="list-group list-group-flush">
                    <a href="#password-section" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-shield-lock me-3 fs-5"></i>
                            <div>
                                <h6 class="mb-0">Password & Security</h6>
                                <small class="text-muted">Update your password</small>
                            </div>
                        </div>
                    </a>
                    
                    <a href="#notifications-section" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-bell me-3 fs-5"></i>
                            <div>
                                <h6 class="mb-0">Notifications</h6>
                                <small class="text-muted">Manage notification settings</small>
                            </div>
                        </div>
                    </a>
                    
                    <?php if ($role === 'admin'): ?>
                    <a href="#admin-section" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-sliders me-3 fs-5"></i>
                            <div>
                                <h6 class="mb-0">Admin Settings</h6>
                                <small class="text-muted">Admin-specific settings</small>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <a href="#privacy-section" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-eye-slash me-3 fs-5"></i>
                            <div>
                                <h6 class="mb-0">Privacy</h6>
                                <small class="text-muted">Manage privacy settings</small>
                            </div>
                        </div>
                    </a>
                    
                    <a href="#account-section" class="list-group-item list-group-item-action" data-bs-toggle="list">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-x me-3 fs-5 text-danger"></i>
                            <div>
                                <h6 class="mb-0 text-danger">Account Management</h6>
                                <small class="text-muted">Account deactivation options</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6 class="mb-3">Account Information</h6>
                    <div class="mb-2">
                        <small class="text-muted d-block">Username</small>
                        <span><?= htmlspecialchars($user->username) ?></span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Account Type</small>
                        <span><?= ucwords(str_replace('_', ' ', $user->role)) ?></span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Created On</small>
                        <span><?= date('F d, Y', strtotime($user->created_at)) ?></span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Last Login</small>
                        <span><?= isset($_SESSION['login_time']) ? date('F d, Y g:i A', $_SESSION['login_time']) : 'Not available' ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Settings Content -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="tab-content">
                        <!-- Password & Security Section -->
                        <div class="tab-pane fade show active" id="password-section">
                            <h5 class="mb-4">Password & Security</h5>
                            
                            <form action="<?= URLROOT ?>/profile/update-password" method="POST">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                           id="current_password" name="current_password" required>
                                    <?php if (isset($errors['current_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['current_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                           id="new_password" name="new_password" 
                                           minlength="6" required>
                                    <?php if (isset($errors['new_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                                    <?php else: ?>
                                        <div class="form-text">Password must be at least 6 characters long</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                           id="confirm_password" name="confirm_password" required>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-shield-check me-1"></i> Update Password
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <h6 class="mb-3">Two-Factor Authentication</h6>
                            <p class="text-muted">Add an extra layer of security to your account by enabling two-factor authentication.</p>
                            
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="button" class="btn btn-outline-secondary" disabled>
                                    <i class="bi bi-shield-lock me-1"></i> Enable 2FA
                                </button>
                                <div class="form-text mt-2">Coming soon</div>
                            </div>
                        </div>
                        
                        <!-- Notifications Section -->
                        <div class="tab-pane fade" id="notifications-section">
                            <h5 class="mb-4">Notification Settings</h5>
                            
                            <div class="mb-4">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked disabled>
                                    <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="applicationUpdates" checked disabled>
                                    <label class="form-check-label" for="applicationUpdates">Application Status Updates</label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="paymentNotifications" checked disabled>
                                    <label class="form-check-label" for="paymentNotifications">Payment Notifications</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="systemAnnouncements" checked disabled>
                                    <label class="form-check-label" for="systemAnnouncements">System Announcements</label>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Notification preferences functionality will be available in a future update.
                            </div>
                        </div>
                        
                        <!-- Admin Settings Section -->
                        <?php if ($role === 'admin'): ?>
                        <div class="tab-pane fade" id="admin-section">
                            <h5 class="mb-4">Admin Settings</h5>
                            
                            <h6 class="mb-3">System Preferences</h6>
                            <div class="mb-3">
                                <label for="defaultApprovalStatus" class="form-label">Default Application Status</label>
                                <select class="form-select" id="defaultApprovalStatus" disabled>
                                    <option>Pending Approval</option>
                                    <option>Pending Payment</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="staffRegistration" class="form-label">Staff Registration</label>
                                <select class="form-select" id="staffRegistration" disabled>
                                    <option>Enabled</option>
                                    <option>Disabled</option>
                                    <option>Admin Approval Required</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Admin settings will be available in a future update.
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Privacy Section -->
                        <div class="tab-pane fade" id="privacy-section">
                            <h5 class="mb-4">Privacy Settings</h5>
                            
                            <div class="mb-3">
                                <label for="profileVisibility" class="form-label">Profile Visibility</label>
                                <select class="form-select" id="profileVisibility" disabled>
                                    <option>Public</option>
                                    <option>Private</option>
                                    <option>Staff Only</option>
                                </select>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="showContactInfo" checked disabled>
                                <label class="form-check-label" for="showContactInfo">Show Contact Information</label>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Privacy settings will be available in a future update.
                            </div>
                        </div>
                        
                        <!-- Account Management Section -->
                        <div class="tab-pane fade" id="account-section">
                            <h5 class="mb-4">Account Management</h5>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Warning:</strong> Deactivating your account will remove your access to the system. This action is reversible, but requires admin approval.
                            </div>
                            
                            <div class="mb-3">
                                <label for="deactivationReason" class="form-label">Reason for Deactivation</label>
                                <select class="form-select" id="deactivationReason" disabled>
                                    <option>Select a reason...</option>
                                    <option>No longer using the service</option>
                                    <option>Closing my business</option>
                                    <option>Creating a new account</option>
                                    <option>Other reason</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="deactivationComments" class="form-label">Additional Comments</label>
                                <textarea class="form-control" id="deactivationComments" rows="3" disabled></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                                <button type="button" class="btn btn-danger" disabled>
                                    <i class="bi bi-exclamation-octagon me-1"></i> Request Account Deactivation
                                </button>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Account deactivation functionality will be available in a future update.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-activate tabs when clicking on sidebar items
    const tabLinks = document.querySelectorAll('[data-bs-toggle="list"]');
    tabLinks.forEach(function(tabLink) {
        tabLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            tabLinks.forEach(function(link) {
                link.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Get the target tab pane
            const targetId = this.getAttribute('href');
            const targetPane = document.querySelector(targetId);
            
            // Hide all tab panes
            document.querySelectorAll('.tab-pane').forEach(function(pane) {
                pane.classList.remove('show', 'active');
            });
            
            // Show the target tab pane
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });
});
</script>

<style>
.list-group-item.active {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
    border-color: rgba(13, 110, 253, 0.1);
}
</style>