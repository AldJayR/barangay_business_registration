<?php
// View file for listing business applications
// Access businesses array with $businesses variable
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>My Applications</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Applications</li>
                </ol>
            </nav>
        </div>
        <a href="<?= URLROOT ?>/business/apply" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Application
        </a>
    </div>

    <!-- Applications List Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-list-ul me-2"></i>Application History</h5>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="#">All Applications</a></li>
                    <li><a class="dropdown-item" href="#">Pending Approval</a></li>
                    <li><a class="dropdown-item" href="#">Pending Payment</a></li>
                    <li><a class="dropdown-item" href="#">Approved</a></li>
                    <li><a class="dropdown-item" href="#">Rejected</a></li>
                </ul>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Business Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Date Applied</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($businesses)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="py-5">
                                        <i class="bi bi-clipboard-x text-muted fs-1"></i>
                                        <p class="text-muted mt-3">You haven't submitted any applications yet.</p>
                                        <a href="<?= URLROOT ?>/business/apply" class="btn btn-primary btn-sm mt-2">
                                            Apply for a Business Permit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $count = 1; ?>
                            <?php foreach ($businesses as $business): ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-2">
                                                <i class="bi bi-shop"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($business->name) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($business->address) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($business->type) ?></td>
                                    <td><?= date('M d, Y', strtotime($business->created_at)) ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = 'bg-secondary';
                                        switch(strtolower($business->status)) {
                                            case 'pending approval':
                                                $statusClass = 'bg-info text-dark';
                                                break;
                                            case 'pending payment':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            case 'active':
                                                $statusClass = 'bg-success';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'bg-danger';
                                                break;
                                            case 'changes requested':
                                                $statusClass = 'bg-primary';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($business->status) ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?= URLROOT ?>/business/view/<?= $business->id ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if (strtolower($business->status) === 'pending payment'): ?>
                                                <a href="<?= URLROOT ?>/payment/upload/<?= $business->id ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Upload Payment">
                                                    <i class="bi bi-credit-card"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($businesses)): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                <span class="text-muted small">Showing all your applications</span>
                <!-- Pagination can be added here if needed -->
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Initialize tooltips -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>