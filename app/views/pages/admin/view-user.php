<?php require APPROOT . '/views/layouts/includes/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2">User Details</h1>
        <div>
            <a href="<?php echo URLROOT; ?>/admin/users" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
            <a href="<?php echo URLROOT; ?>/admin/edit-user/<?php echo $data['user']->id; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit User
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?php echo strtoupper(substr($data['user']->first_name, 0, 1) . substr($data['user']->last_name, 0, 1)); ?>
                        </div>
                        <h4 class="mt-3"><?php echo $data['user']->first_name . ' ' . $data['user']->last_name; ?></h4>
                        
                        <?php
                            $roleBadge = '';
                            switch ($data['user']->role) {
                                case 'admin':
                                    $roleBadge = '<span class="badge bg-primary">Administrator</span>';
                                    break;
                                case 'treasurer':
                                    $roleBadge = '<span class="badge bg-success">Treasurer</span>';
                                    break;
                                case 'owner':
                                    $roleBadge = '<span class="badge bg-info">Business Owner</span>';
                                    break;
                                default:
                                    $roleBadge = '<span class="badge bg-secondary">Unknown</span>';
                            }
                            echo $roleBadge;
                        ?>
                        
                        <?php if ($data['user']->status == 1) : ?>
                            <span class="badge bg-success ms-1">Active</span>
                        <?php else : ?>
                            <span class="badge bg-danger ms-1">Inactive</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-muted mb-1">Username</p>
                        <p class="fw-bold"><?php echo $data['user']->username; ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-muted mb-1">Email</p>
                        <p class="fw-bold"><?php echo $data['user']->email ?? 'Not provided'; ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-muted mb-1">Phone</p>
                        <p class="fw-bold"><?php echo $data['user']->phone_number ?? 'Not provided'; ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-muted mb-1">Created On</p>
                        <p class="fw-bold"><?php echo date('F d, Y', strtotime($data['user']->created_at)); ?></p>
                    </div>
                    
                    <div>
                        <p class="text-muted mb-1">Account Status</p>
                        <div class="d-flex align-items-center">
                            <?php if ($data['user']->status == 1) : ?>
                                <div class="spinner-grow text-success spinner-grow-sm me-2" role="status">
                                    <span class="visually-hidden">Active</span>
                                </div>
                                <span>Account Active</span>
                            <?php else : ?>
                                <div class="spinner-grow text-danger spinner-grow-sm me-2" role="status">
                                    <span class="visually-hidden">Inactive</span>
                                </div>
                                <span>Account Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-grid gap-2">
                        <?php if ($data['user']->status == 1) : ?>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deactivateModal">
                                <i class="fas fa-user-slash"></i> Deactivate Account
                            </button>
                        <?php else : ?>
                            <form action="<?php echo URLROOT; ?>/admin/activate-user/<?php echo $data['user']->id; ?>" method="POST">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-user-check"></i> Activate Account
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">First Name</p>
                            <p class="fw-bold"><?php echo $data['user']->first_name; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Last Name</p>
                            <p class="fw-bold"><?php echo $data['user']->last_name; ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <p class="text-muted mb-1">Address</p>
                        <p class="fw-bold"><?php echo $data['user']->address ?? 'Not provided'; ?></p>
                    </div>
                    
                    <?php if ($data['user']->role === 'owner' && !empty($data['businesses'])) : ?>
                    <div class="mb-3">
                        <p class="text-muted mb-1">Registered Businesses</p>
                        <div class="list-group">
                            <?php foreach ($data['businesses'] as $business) : ?>
                                <a href="<?php echo URLROOT; ?>/business/view/<?php echo $business->id; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo $business->business_name; ?></h6>
                                        <small>
                                            <?php
                                                switch ($business->status) {
                                                    case 'pending':
                                                        echo '<span class="badge bg-warning">Pending</span>';
                                                        break;
                                                    case 'approved':
                                                        echo '<span class="badge bg-success">Approved</span>';
                                                        break;
                                                    case 'rejected':
                                                        echo '<span class="badge bg-danger">Rejected</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">Unknown</span>';
                                                }
                                            ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?php echo $business->business_type; ?></p>
                                    <small><?php echo $business->address; ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity Log/Recent Actions (if available) -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['activities'])) : ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Activity</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['activities'] as $activity) : ?>
                                        <tr>
                                            <td><?php echo date('M d, Y g:i A', strtotime($activity->created_at)); ?></td>
                                            <td><?php echo $activity->activity_type; ?></td>
                                            <td><?php echo $activity->description; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">No recent activity recorded for this user.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deactivate Account Modal -->
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-labelledby="deactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deactivateModalLabel">Deactivate User Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate the account for <strong><?php echo $data['user']->first_name . ' ' . $data['user']->last_name; ?></strong>?</p>
                <p class="text-danger"><strong>Warning:</strong> The user will no longer be able to log in to the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo URLROOT; ?>/admin/deactivate-user/<?php echo $data['user']->id; ?>" method="POST">
                    <button type="submit" class="btn btn-danger">Deactivate Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APPROOT . '/views/layouts/includes/footer.php'; ?>