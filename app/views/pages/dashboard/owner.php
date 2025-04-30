<?php
// View loaded within 'app' layout
// Access $data['title'], $data['businesses']
$businesses = $data['businesses'] ?? []; // Ensure $businesses is an array

// Helper function for all status badges (can be moved to a dedicated helper file later)
if (!function_exists('getBadgeClass')) {
    function getBadgeClass($status, $type = 'application') {
        $status = strtolower($status ?? '');
        
        // Status badge mappings by type
        $badgeClasses = [
            'application' => [
                'active' => 'bg-success',
                'pending approval' => 'bg-info text-dark',
                'pending payment' => 'bg-warning text-dark',
                'changes requested' => 'bg-primary',
                'rejected' => 'bg-danger',
                'expired' => 'bg-secondary',
            ],
            'payment' => [
                'verified' => 'bg-success',
                'pending' => 'bg-warning text-dark',
                'rejected' => 'bg-danger',
            ]
        ];
        
        // Return the appropriate badge class or default
        return $badgeClasses[$type][$status] ?? 'bg-light text-dark';
    }
}
?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-shop me-2"></i>Business Owner Dashboard</h3>
    <div>
        <span class="badge bg-dark p-2">
            <i class="bi bi-calendar-event me-1"></i> <?= date('F d, Y') ?>
        </span>
    </div>
</div>

<?php if (isset($data['error'])): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $data['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Total Businesses</h6>
                        <h3 class="fw-bold mb-0"><?= count($businesses) ?></h3>
                    </div>
                    <div class="bg-primary-soft p-3 rounded">
                        <i class="bi bi-shop-window text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Active Permits</h6>
                        <?php
                        $activePermits = array_filter($businesses, function($business) {
                            return strtolower($business->status ?? '') === 'active';
                        });
                        ?>
                        <h3 class="fw-bold mb-0"><?= count($activePermits) ?></h3>
                    </div>
                    <div class="bg-success-soft p-3 rounded">
                        <i class="bi bi-clipboard-check text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted fw-normal mb-0">Pending Applications</h6>
                        <?php
                        $pendingApplications = array_filter($businesses, function($business) {
                            return strtolower($business->status ?? '') === 'pending approval' 
                                || strtolower($business->status ?? '') === 'pending payment';
                        });
                        ?>
                        <h3 class="fw-bold mb-0"><?= count($pendingApplications) ?></h3>
                    </div>
                    <div class="bg-warning-soft p-3 rounded">
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Business Card -->
<div class="card shadow mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Your Businesses</h5>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">All Businesses</a></li>
                <li><a class="dropdown-item" href="#">Active</a></li>
                <li><a class="dropdown-item" href="#">Pending Approval</a></li>
                <li><a class="dropdown-item" href="#">Pending Payment</a></li>
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
                        <th scope="col">Application Status</th>
                        <th scope="col">Payment Status</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($businesses)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">You have not registered any businesses yet.</td>
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
                                            <h6 class="mb-0"><?= sanitize($business->name) ?></h6>
                                            <small class="text-muted"><?= sanitize($business->address ?? 'No Address') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= getBadgeClass($business->status) ?>">
                                        <?= sanitize(ucwords($business->status)) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php // Display payment status only if applicable (e.g., after approval) ?>
                                    <?php if (!empty($business->latest_payment_status)): ?>
                                        <span class="badge <?= getBadgeClass($business->latest_payment_status, 'payment') ?>">
                                            <?= sanitize(ucwords($business->latest_payment_status)) ?>
                                        </span>
                                    <?php elseif (in_array(strtolower($business->status), ['pending payment', 'active', 'expired'])): ?>
                                         <span class="badge bg-secondary">Not Paid</span>
                                    <?php else: ?>
                                         <span class="badge bg-light text-dark">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <!-- View Details Button (Always available) -->
                                        <a href="<?= URLROOT ?>/business/view/<?= $business->id ?>"
                                           class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Details">
                                           <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Pay Button (Available if payment is not 'Verified' or 'Pending') -->
                                        <?php
                                            $isPaymentVerified = strtolower($business->latest_payment_status ?? '') === 'verified';
                                            $isPaymentPending = strtolower($business->latest_payment_status ?? '') === 'pending';
                                            $canPay = !$isPaymentVerified && !$isPaymentPending;
                                        ?>
                                        <a href="<?= URLROOT ?>/payment/upload/<?= $business->id ?>"
                                           class="btn btn-sm btn-outline-success <?= $canPay ? '' : 'disabled' ?>" data-bs-toggle="tooltip" title="Upload Payment Proof">
                                           <i class="bi bi-cash-coin"></i>
                                        </a>

                                         <!-- Download Permit Button (Available if status is 'Active' and payment is 'Verified') -->
                                         <?php
                                            $isActive = strtolower($business->status) === 'active';
                                            $canDownload = $isActive && $isPaymentVerified; // Assuming Active implies payment verified for permit download
                                         ?>
                                         <a href="<?= URLROOT ?>/permit/generate/<?= $business->id ?>"
                                            class="btn btn-sm btn-outline-info <?= $canDownload ? '' : 'disabled' ?>" data-bs-toggle="tooltip" title="Download Permit">
                                            <i class="bi bi-download"></i>
                                         </a>

                                         <!-- Request Change Button (Available if 'Active') -->
                                          <?php $canRequestChange = $isActive; ?>
                                         <a href="<?= URLROOT ?>/business/edit/<?= $business->id ?>"
                                            class="btn btn-sm btn-outline-warning <?= $canRequestChange ? '' : 'disabled' ?>" data-bs-toggle="tooltip" title="Request Changes">
                                            <i class="bi bi-pencil-square"></i>
                                         </a>
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
        <span class="text-muted small">Showing all your businesses</span>
    </div>
    <?php endif; ?>
</div>

<!-- Row for Document and Quick Actions -->
<div class="row g-3 mb-4">
    <!-- Document Requirements Card -->
    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="bi bi-file-earmark-check me-2"></i>Document Requirements</h5>
                <div>
                    <a href="<?= URLROOT ?>/document/view" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-list-check me-1"></i> View All Documents
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($businesses)): ?>
                    <div class="text-center py-3">
                        <i class="bi bi-file-earmark-x text-muted fs-1"></i>
                        <p class="mt-3 mb-0">You don't have any registered businesses yet.</p>
                    </div>
                <?php else: ?>
                    <?php
                    // Properly check for missing documents using the Document model
                    $documentModel = new Document();
                    $anyRequiresDocuments = false;
                    $businessesRequiringDocs = [];
                    
                    foreach ($businesses as $business) {
                        // Check if this business has missing required documents
                        $missingDocs = $documentModel->getMissingRequiredDocuments($business->id, $business->type);
                        
                        if (!empty($missingDocs)) {
                            $anyRequiresDocuments = true;
                            $business->missing_docs = $missingDocs;
                            $businessesRequiringDocs[] = $business;
                        }
                    }
                    
                    if (!$anyRequiresDocuments): ?>
                        <div class="text-center py-3">
                            <i class="bi bi-check-circle text-success fs-1"></i>
                            <p class="mt-3 mb-0">All required documents have been submitted.</p>
                        </div>
                    <?php else: ?>
                        <p>You have <strong>pending document requirements</strong> for the following businesses:</p>
                        <div class="list-group list-group-flush mb-3">
                            <?php foreach ($businessesRequiringDocs as $business): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                                    <div>
                                        <h6 class="mb-1"><?= sanitize($business->name) ?></h6>
                                        <p class="text-muted small mb-0">
                                            Required documents: 
                                            <?php 
                                            $docNames = array_map(function($doc) {
                                                return $doc['name'];
                                            }, $business->missing_docs);
                                            echo sanitize(implode(', ', $docNames));
                                            ?>
                                        </p>
                                    </div>
                                    <a href="<?= URLROOT ?>/document/upload/<?= $business->id ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="col-md-6 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= URLROOT ?>/business/apply" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Apply for New Business Permit
                    </a>
                    <a href="<?= URLROOT ?>/document/upload" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload Documents
                    </a>
                    <a href="<?= URLROOT ?>/payment/history" class="btn btn-outline-secondary">
                        <i class="bi bi-clock-history me-1"></i> Payment History
                    </a>
                    <a href="<?= URLROOT ?>/notification" class="btn btn-outline-info">
                        <i class="bi bi-bell me-1"></i> View All Notifications
                    </a>
                </div>
            </div>
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