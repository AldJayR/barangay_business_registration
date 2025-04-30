<?php
// View file for listing all businesses owned by the current user
// Access businesses array with $businesses variable

$role = $_SESSION['user_role'] ?? '';
$isOwner = strtolower($role) === 'owner';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-buildings me-2"></i><?= $isOwner ? 'My Businesses' : 'Businesses' ?></h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $isOwner ? 'My Businesses' : 'Businesses' ?></li>
                </ol>
            </nav>
        </div>
        <?php if ($isOwner): ?>
        <a href="<?= URLROOT ?>/business/apply" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Register New Business
        </a>
        <?php endif; ?>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row mb-4">
        <!-- Active Businesses Card -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted card-title mb-1 fw-normal">Active Businesses</h6>
                            <h3 class="mt-1 mb-0 fw-bold">
                                <?php 
                                $activeCount = 0;
                                foreach ($businesses as $business) {
                                    if (strtolower($business->status) === 'active') {
                                        $activeCount++;
                                    }
                                }
                                echo $activeCount;
                                ?>
                            </h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(47, 158, 68, 0.1);">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Approval Card -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted card-title mb-1 fw-normal">Pending Approval</h6>
                            <h3 class="mt-1 mb-0 fw-bold">
                                <?php 
                                $pendingApprovalCount = 0;
                                foreach ($businesses as $business) {
                                    if (strtolower($business->status) === 'pending approval') {
                                        $pendingApprovalCount++;
                                    }
                                }
                                echo $pendingApprovalCount;
                                ?>
                            </h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(13, 110, 253, 0.1);">
                            <i class="bi bi-hourglass-split text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payment Card -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted card-title mb-1 fw-normal">Pending Payment</h6>
                            <h3 class="mt-1 mb-0 fw-bold">
                                <?php 
                                $pendingPaymentCount = 0;
                                foreach ($businesses as $business) {
                                    if (strtolower($business->status) === 'pending payment') {
                                        $pendingPaymentCount++;
                                    }
                                }
                                echo $pendingPaymentCount;
                                ?>
                            </h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(255, 193, 7, 0.1);">
                            <i class="bi bi-credit-card text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expiring Soon Card -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted card-title mb-1 fw-normal">Expiring Soon</h6>
                            <h3 class="mt-1 mb-0 fw-bold">
                                <?php 
                                $expiringCount = 0;
                                // Calculate 30 days from now for "soon" expiration
                                $soonDate = date('Y-m-d', strtotime('+30 days'));
                                foreach ($businesses as $business) {
                                    // For demonstration - in a real app you'd have an expiration date field
                                    if (strtolower($business->status) === 'active') {
                                        // Assume permits are valid for 1 year from updated_at
                                        $expirationDate = date('Y-m-d', strtotime('+1 year', strtotime($business->updated_at)));
                                        if ($expirationDate <= $soonDate) {
                                            $expiringCount++;
                                        }
                                    }
                                }
                                echo $expiringCount;
                                ?>
                            </h3>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: rgba(220, 53, 69, 0.1);">
                            <i class="bi bi-exclamation-circle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Business List Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0"><i class="bi bi-list-ul me-2"></i>Business List</h5>
            
            <div class="d-flex gap-2">
                <!-- Search Box -->
                <div class="input-group input-group-sm me-2">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchBusiness" class="form-control form-control-sm border-start-0 ps-0" placeholder="Search businesses...">
                </div>
                
                <!-- Filter Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                        <li><a class="dropdown-item active" href="#">All Businesses</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Active</a></li>
                        <li><a class="dropdown-item" href="#">Pending Approval</a></li>
                        <li><a class="dropdown-item" href="#">Pending Payment</a></li>
                        <li><a class="dropdown-item" href="#">Rejected</a></li>
                        <li><a class="dropdown-item" href="#">Expired</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Business Name</th>
                            <th scope="col">Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Permit No.</th>
                            <th scope="col">Expiration</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($businesses)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="py-5">
                                        <i class="bi bi-shop text-muted fs-1"></i>
                                        <p class="text-muted mt-3">You don't have any registered businesses yet.</p>
                                        <a href="<?= URLROOT ?>/business/apply" class="btn btn-primary btn-sm mt-2">
                                            Register a New Business
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
                                            case 'expired':
                                                $statusClass = 'bg-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($business->status) ?></span>
                                    </td>
                                    <td>
                                        <?php if(strtolower($business->status) === 'active'): ?>
                                            <?= 'BP-' . str_pad($business->id, 5, '0', STR_PAD_LEFT) . '-' . date('Y') ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(strtolower($business->status) === 'active'): ?>
                                            <?php 
                                            // Calculate expiration (1 year from updated_at for active permits)
                                            $expirationDate = date('M d, Y', strtotime('+1 year', strtotime($business->updated_at)));
                                            $today = date('Y-m-d');
                                            $expiringSoon = date('Y-m-d', strtotime($expirationDate)) <= date('Y-m-d', strtotime('+30 days'));
                                            ?>
                                            <span class="<?= $expiringSoon ? 'text-danger fw-bold' : '' ?>">
                                                <?= $expirationDate ?>
                                                <?php if($expiringSoon): ?>
                                                    <i class="bi bi-exclamation-circle-fill ms-1 text-danger" data-bs-toggle="tooltip" title="Expiring soon"></i>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <!-- View Details Button (Always available) -->
                                            <a href="<?= URLROOT ?>/business/view/<?= $business->id ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <!-- Document Upload Button (Always available) -->
                                            <a href="<?= URLROOT ?>/document/upload/<?= $business->id ?>" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="Upload Documents">
                                                <i class="bi bi-file-earmark-arrow-up"></i>
                                            </a>
                                            
                                            <?php if (strtolower($business->status) === 'active'): ?>
                                                <a href="<?= URLROOT ?>/business/printPermit/<?= $business->id ?>" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Print Permit">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (strtolower($business->status) === 'pending payment'): ?>
                                                <a href="<?= URLROOT ?>/payment/upload/<?= $business->id ?>" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Pay Permit Fee">
                                                    <i class="bi bi-credit-card"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array(strtolower($business->status), ['expired', 'rejected'])): ?>
                                                <a href="<?= URLROOT ?>/business/reapply/<?= $business->id ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Reapply">
                                                    <i class="bi bi-arrow-repeat"></i>
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
                <span class="text-muted small">Showing <?= count($businesses) ?> businesses</span>
                <!-- Pagination can be added here if needed -->
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Initialize tooltips and search functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Simple client-side search
    const searchInput = document.getElementById('searchBusiness');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');
            
            tableRows.forEach(row => {
                // Skip the "no businesses" row
                if (row.cells.length === 1) return;
                
                const businessName = row.cells[1].textContent.toLowerCase();
                const businessType = row.cells[2].textContent.toLowerCase();
                const matchesSearch = businessName.includes(searchTerm) || businessType.includes(searchTerm);
                
                row.style.display = matchesSearch ? '' : 'none';
            });
        });
    }
});
</script>