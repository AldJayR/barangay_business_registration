<?php
// View file for displaying business details
// Access business data with $business variable

$role = $_SESSION['user_role'] ?? '';
$isOwner = strtolower($role) === 'owner';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-shop me-2"></i>Business Details</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/business/list"><?= $isOwner ? 'My Businesses' : 'Businesses' ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Business Details</li>
                </ol>
            </nav>
        </div>
        <?php if ($business && strtolower($business->status) === 'pending payment'): ?>
            <a href="<?= URLROOT ?>/payment/upload/<?= $business->id ?>" class="btn btn-success">
                <i class="bi bi-credit-card me-1"></i> Pay Permit Fee
            </a>
        <?php else: ?>
            <a href="<?= URLROOT ?>/business/list" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        <?php endif; ?>
    </div>

    <?php if (!$business): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Business not found or you don't have access to view this business.
        </div>
    <?php else: ?>
        <!-- Business Status Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-1"><?= htmlspecialchars($business->name) ?></h4>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt me-1"></i> <?= htmlspecialchars($business->address) ?>
                                </p>
                            </div>
                            <div>
                                <?php 
                                $statusClass = 'bg-secondary';
                                $statusIcon = 'question-circle';
                                
                                switch(strtolower($business->status)) {
                                    case 'pending approval':
                                        $statusClass = 'bg-info text-dark';
                                        $statusIcon = 'hourglass-split';
                                        break;
                                    case 'pending payment':
                                        $statusClass = 'bg-warning text-dark';
                                        $statusIcon = 'credit-card';
                                        break;
                                    case 'active':
                                        $statusClass = 'bg-success';
                                        $statusIcon = 'check-circle';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'bg-danger';
                                        $statusIcon = 'x-circle';
                                        break;
                                    case 'expired':
                                        $statusClass = 'bg-danger';
                                        $statusIcon = 'calendar-x';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $statusClass ?> fs-6 p-2">
                                    <i class="bi bi-<?= $statusIcon ?> me-1"></i> <?= htmlspecialchars($business->status) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Details Section -->
        <div class="row">
            <!-- Left Column: Business Information -->
            <div class="col-md-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="bi bi-info-circle me-2"></i>Business Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Business Type</h6>
                                <p class="mb-0 fs-5"><?= htmlspecialchars($business->type) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Registration Date</h6>
                                <p class="mb-0 fs-5"><?= date('M d, Y', strtotime($business->created_at)) ?></p>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Owner Name</h6>
                                <p class="mb-0 fs-5">
                                    <?= htmlspecialchars($business->first_name . ' ' . $business->last_name) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Last Updated</h6>
                                <p class="mb-0 fs-5"><?= date('M d, Y', strtotime($business->updated_at)) ?></p>
                            </div>
                        </div>
                        
                        <!-- Additional Business Details can be added here -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-2">Business Address</h6>
                                <p class="mb-0 fs-5"><?= htmlspecialchars($business->address) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Status Timeline and Actions -->
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2"></i>Status Timeline</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <!-- Application Submitted -->
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary">
                                    <i class="bi bi-file-earmark-check"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Application Submitted</h6>
                                    <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($business->created_at)) ?></p>
                                </div>
                            </div>

                            <!-- Application Review -->
                            <?php 
                            $pendingClass = in_array(strtolower($business->status), ['pending approval']) ? '' : 'inactive'; 
                            $approvedClass = in_array(strtolower($business->status), ['pending payment', 'active', 'expired']) ? '' : 'inactive'; 
                            $paymentClass = in_array(strtolower($business->status), ['active', 'expired']) ? '' : 'inactive';
                            $activeClass = strtolower($business->status) === 'active' ? '' : 'inactive';
                            ?>
                            
                            <!-- Application Review -->
                            <div class="timeline-item <?= $pendingClass ?>">
                                <div class="timeline-marker <?= $pendingClass ? 'bg-secondary' : 'bg-info' ?>">
                                    <i class="bi bi-search"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Application Review</h6>
                                    <p class="text-muted small mb-0">
                                        <?php if(strtolower($business->status) === 'pending approval'): ?>
                                            In progress
                                        <?php elseif(in_array(strtolower($business->status), ['pending payment', 'active', 'expired'])): ?>
                                            Completed
                                        <?php else: ?>
                                            Pending
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Application Approved -->
                            <div class="timeline-item <?= $approvedClass ?>">
                                <div class="timeline-marker <?= $approvedClass ? 'bg-secondary' : 'bg-success' ?>">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Application Approved</h6>
                                    <p class="text-muted small mb-0">
                                        <?php if(in_array(strtolower($business->status), ['pending payment', 'active', 'expired'])): ?>
                                            Application was reviewed and approved
                                        <?php else: ?>
                                            Pending approval
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Payment -->
                            <div class="timeline-item <?= $paymentClass ?>">
                                <div class="timeline-marker <?= $paymentClass ? 'bg-secondary' : 'bg-warning' ?>">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Payment Received</h6>
                                    <p class="text-muted small mb-0">
                                        <?php if(in_array(strtolower($business->status), ['active', 'expired'])): ?>
                                            Payment for permit fee was received and verified
                                        <?php else: ?>
                                            Waiting for payment
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Permit Issued -->
                            <div class="timeline-item <?= $activeClass ?>">
                                <div class="timeline-marker <?= $activeClass ? 'bg-secondary' : 'bg-success' ?>">
                                    <i class="bi bi-award"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Permit Issued</h6>
                                    <p class="text-muted small mb-0">
                                        <?php if(strtolower($business->status) === 'active'): ?>
                                            Business permit has been issued
                                        <?php else: ?>
                                            Pending issuance
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Rejected status (if applicable) -->
                            <?php if(strtolower($business->status) === 'rejected'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger">
                                    <i class="bi bi-x-lg"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Application Rejected</h6>
                                    <p class="text-muted small mb-0">Application was reviewed and rejected</p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Expired status (if applicable) -->
                            <?php if(strtolower($business->status) === 'expired'): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger">
                                    <i class="bi bi-calendar-x"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="mb-0">Permit Expired</h6>
                                    <p class="text-muted small mb-0">Business permit has expired</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4">
                            <h6 class="border-bottom pb-2 mb-3">Actions</h6>
                            <?php if(strtolower($business->status) === 'pending payment'): ?>
                                <a href="<?= URLROOT ?>/payment/upload/<?= $business->id ?>" class="btn btn-success btn-sm d-block mb-2">
                                    <i class="bi bi-credit-card me-1"></i> Pay Permit Fee
                                </a>
                            <?php endif; ?>
                            
                            <!-- Document Upload Button -->
                            <a href="<?= URLROOT ?>/document/upload/<?= $business->id ?>" class="btn btn-primary btn-sm d-block mb-2">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i> Manage Documents
                            </a>
                            
                            <?php if(strtolower($business->status) === 'active'): ?>
                                <a href="<?= URLROOT ?>/permit/viewPermit/<?= $business->id ?>" class="btn btn-primary btn-sm d-block mb-2" target="_blank">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> View Permit
                                </a>
                                <a href="<?= URLROOT ?>/permit/generate/<?= $business->id ?>" class="btn btn-info btn-sm d-block mb-2">
                                    <i class="bi bi-download me-1"></i> Download Permit
                                </a>
                            <?php endif; ?>
                            
                            <?php if(in_array(strtolower($business->status), ['rejected', 'expired'])): ?>
                                <a href="<?= URLROOT ?>/business/reapply/<?= $business->id ?>" class="btn btn-warning btn-sm d-block mb-2">
                                    <i class="bi bi-arrow-repeat me-1"></i> Reapply
                                </a>
                            <?php endif; ?>

                            <?php if(strtolower($business->status) === 'pending approval' && ($role === 'admin' || $role === 'treasurer')): ?>
                                <form method="post" action="<?= URLROOT ?>/business/approve/<?= $business->id ?>" class="d-inline">
                                    <button type="submit" class="btn btn-success btn-sm mb-2">Approve</button>
                                </form>
                                <form method="post" action="<?= URLROOT ?>/business/reject/<?= $business->id ?>" class="d-inline ms-2">
                                    <button type="submit" class="btn btn-danger btn-sm mb-2">Reject</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permit Details (if active) -->
        <?php if(strtolower($business->status) === 'active'): ?>
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="bi bi-card-text me-2"></i>Permit Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <h6 class="text-muted mb-1">Permit Number</h6>
                                <p class="fs-5 mb-0"><?= 'BP-' . str_pad($business->id, 5, '0', STR_PAD_LEFT) . '-' . date('Y') ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h6 class="text-muted mb-1">Issue Date</h6>
                                <p class="fs-5 mb-0"><?= date('M d, Y', strtotime($business->updated_at)) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h6 class="text-muted mb-1">Valid Until</h6>
                                <p class="fs-5 mb-0"><?= date('M d, Y', strtotime('+1 year', strtotime($business->updated_at))) ?></p>
                            </div>
                            <div class="col-md-3 mb-3">
                                <h6 class="text-muted mb-1">Fee Paid</h6>
                                <p class="fs-5 mb-0">₱500.00</p>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Reminder:</strong> Your business permit will expire on <?= date('F d, Y', strtotime('+1 year', strtotime($business->updated_at))) ?>. Make sure to renew it before expiration to avoid penalties.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payment Details Section -->
        <?php if (strtolower($role) === 'treasurer'): ?>
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0"><i class="bi bi-cash-stack me-2"></i>Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($business->payment_status)): ?>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Status</h6>
                                <span class="badge <?= strtolower($business->payment_status) === 'verified' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= htmlspecialchars(ucwords($business->payment_status)) ?>
                                </span>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Amount</h6>
                                <p class="fs-5 mb-0">₱<?= isset($business->payment_amount) ? number_format($business->payment_amount, 2) : '0.00' ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Date Paid</h6>
                                <p class="fs-5 mb-0">
                                    <?= !empty($business->payment_date) ? date('M d, Y', strtotime($business->payment_date)) : '-' ?>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-muted mb-1">Reference #</h6>
                                <p class="mb-0"><?= htmlspecialchars($business->payment_reference ?? '-') ?></p>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-secondary mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            No payment record found for this business yet.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Documents Section -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="bi bi-file-earmark me-2"></i>Required Documents</h5>
                        <a href="<?= URLROOT ?>/document/upload/<?= $business->id ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i> Upload Documents
                        </a>
                    </div>
                    <div class="card-body">
                        <?php
                        // This would normally be pulled from your Document model
                        // For now, we'll create a placeholder list of required documents
                        $requiredDocs = [
                            [
                                'name' => 'Business Registration Form',
                                'description' => 'Completed business registration form with all required fields',
                                'status' => 'pending' // or 'approved' or 'rejected'
                            ],
                            [
                                'name' => 'Valid ID',
                                'description' => 'Government-issued ID of the business owner',
                                'status' => 'pending'
                            ],
                            [
                                'name' => 'Proof of Address',
                                'description' => 'Recent utility bill or lease agreement showing business address',
                                'status' => 'pending'
                            ],
                            [
                                'name' => 'Business Tax Receipt',
                                'description' => 'Receipt of payment for local business tax',
                                'status' => 'pending'
                            ]
                        ];
                        
                        // In a real implementation, you would get the actual document status from your database
                        ?>
                        
                        <?php if (empty($requiredDocs)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-check-circle-fill text-success fs-1"></i>
                                <p class="mt-3">All required documents have been submitted and approved.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($requiredDocs as $doc): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($doc['name']) ?></strong></td>
                                                <td><?= htmlspecialchars($doc['description']) ?></td>
                                                <td>
                                                    <?php if ($doc['status'] === 'approved'): ?>
                                                        <span class="badge bg-success">Approved</span>
                                                    <?php elseif ($doc['status'] === 'rejected'): ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <a href="<?= URLROOT ?>/document/upload/<?= $business->id ?>?type=<?= urlencode($doc['name']) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-upload me-1"></i> Upload
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Please upload all required documents to proceed with your business permit application.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>