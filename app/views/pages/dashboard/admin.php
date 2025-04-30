<?php
// View loaded within 'app' layout
// Access $data['title'], $data['applications'] (when implemented)
?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h3>
    <div>
        <span class="badge bg-dark p-2">
            <i class="bi bi-calendar-event me-1"></i> <?= date('F d, Y') ?>
        </span>
    </div>
</div>

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Pending Applications</h6>
                        <h3 class="fw-bold mb-0"><?= isset($data['stats']) ? $data['stats']['pending'] ?? 0 : 0 ?></h3>
                    </div>
                    <div class="bg-primary-soft p-3 rounded">
                        <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Active Businesses</h6>
                        <h3 class="fw-bold mb-0"><?= isset($data['stats']) ? $data['stats']['active'] ?? 0 : 0 ?></h3>
                    </div>
                    <div class="bg-success-soft p-3 rounded">
                        <i class="bi bi-shop text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Registered Users</h6>
                        <h3 class="fw-bold mb-0"><?= isset($data['stats']) ? $data['stats']['users'] ?? 0 : 0 ?></h3>
                    </div>
                    <div class="bg-info-soft p-3 rounded">
                        <i class="bi bi-people text-info fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Revenue (This Month)</h6>
                        <h3 class="fw-bold mb-0">₱<?= isset($data['stats']) ? number_format($data['stats']['revenue'] ?? 0, 2) : '0.00' ?></h3>
                    </div>
                    <div class="bg-warning-soft p-3 rounded">
                        <i class="bi bi-cash-stack text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Applications Card -->
<div class="card shadow mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Pending Applications</h5>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">All Applications</a></li>
                <li><a class="dropdown-item" href="#">Pending Approval</a></li>
                <li><a class="dropdown-item" href="#">Pending Payment</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#">This Week</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Business Name</th>
                        <th scope="col">Owner</th>
                        <th scope="col">Date Submitted</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['applications'])): ?>
                        <?php foreach ($data['applications'] as $i => $app): ?>
                            <tr>
                                <td><?= htmlspecialchars($app->id) ?></td>
                                <td><?= htmlspecialchars($app->name) ?></td>
                                <td><?= htmlspecialchars(($app->first_name ?? '') . ' ' . ($app->last_name ?? '')) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($app->created_at))) ?></td>
                                <td>
                                    <?php if (strtolower($app->status) === 'pending approval'): ?>
                                        <span class="badge bg-warning text-dark">Pending Approval</span>
                                    <?php elseif (strtolower($app->status) === 'pending payment'): ?>
                                        <span class="badge bg-info text-dark">Pending Payment</span>
                                    <?php elseif (strtolower($app->status) === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($app->status) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= URLROOT . '/business/view/' . $app->id ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <!-- Approve/Reject buttons can be implemented here -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No pending applications found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">Showing recent applications</span>
        <a href="#" class="btn btn-sm btn-primary">View All Applications</a>
    </div>
</div>

<!-- Quick Actions Buttons -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= URLROOT ?>/admin/register-staff" class="btn btn-primary">
                <i class="bi bi-person-plus-fill me-1"></i> Register New Staff/Admin
            </a>
            <a href="<?= URLROOT ?>/admin/generate-renewal-notifications" class="btn btn-warning">
                <i class="bi bi-bell me-1"></i> Generate Renewal Notifications
            </a>
            <a href="<?= URLROOT ?>/admin/reports" class="btn btn-outline-dark">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Generate Reports
            </a>
            <a href="<?= URLROOT ?>/admin/settings" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-1"></i> System Settings
            </a>
        </div>
    </div>
</div>

<!-- Initialize tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>