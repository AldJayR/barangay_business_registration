<?php
// View for verifying payments
$payments = $data['payments'] ?? [];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="bi bi-patch-check me-2"></i>Verify Payments</h3>
    <div>
        <span class="badge bg-dark p-2">
            <i class="bi bi-calendar-event me-1"></i> <?= date('F d, Y') ?>
        </span>
    </div>
</div>

<!-- Notification Banner -->
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

<!-- Payment Verification Card -->
<div class="card shadow mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Pending Payment Verifications</h5>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= URLROOT ?>/treasurer/verify">All Payments</a></li>
                <li><a class="dropdown-item" href="<?= URLROOT ?>/treasurer/verify?date=today">Today</a></li>
                <li><a class="dropdown-item" href="<?= URLROOT ?>/treasurer/verify?date=week">This Week</a></li>
                <li><a class="dropdown-item" href="<?= URLROOT ?>/treasurer/verify?date=month">This Month</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Reference #</th>
                        <th scope="col">Business Details</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Payment Method</th>
                        <th scope="col">Date Submitted</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No pending payments found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><span class="fw-medium"><?= htmlspecialchars($payment->reference_number) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-2">
                                            <i class="bi bi-shop"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($payment->business_name) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($payment->owner_name ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="fw-bold">₱<?= number_format($payment->amount, 2) ?></span></td>
                                <td><?= htmlspecialchars($payment->payment_method) ?></td>
                                <td><?= date('M d, Y', strtotime($payment->created_at)) ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                         <button type="button" class="btn btn-sm btn-outline-primary view-proof-btn" data-action="openProofModal" data-payment-id="<?= $payment->id ?>" title="View Payment Proof">
                                            <i class="bi bi-eye"></i> View Proof
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-action="openApproveModal" data-payment-id="<?= $payment->id ?>" title="Approve Payment">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-action="openRejectModal" data-payment-id="<?= $payment->id ?>" title="Reject Payment">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Guidelines Card -->
<div class="card shadow mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Payment Verification Guidelines</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3 border-bottom pb-2">What to Check</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Verify that the payment amount matches the business permit fee
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Confirm the payment receipt or proof is valid and legible
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Check that the payment date is recent (within the last 30 days)
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Ensure the payment reference number is valid
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3 border-bottom pb-2">Common Rejection Reasons</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                        Payment proof is unclear or illegible
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                        Amount paid is incorrect or insufficient
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                        Payment reference doesn't match our records
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>
                        Suspected fraudulent payment activity
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Custom Modal Container -->
<div id="customModalContainer" class="custom-modal-container">
    <div id="customModalBackdrop" class="custom-modal-backdrop"></div>
    
    <!-- Payment Proof Modal Template -->
    <div id="proofModalTemplate" class="custom-modal custom-modal-lg">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="custom-modal-title">
                    <i class="bi bi-file-earmark-image me-2"></i>Payment Proof
                </h5>
                <button type="button" class="btn-close custom-close" data-action="closeModal"></button>
            </div>
            <div class="custom-modal-body">
                <!-- Content will be dynamically inserted -->
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-secondary" data-action="closeModal">Close</button>
                <button type="button" class="btn btn-success proof-approve-btn" data-action="openApproveModal">
                    <i class="bi bi-check-lg me-1"></i>Approve Payment
                </button>
                <button type="button" class="btn btn-danger proof-reject-btn" data-action="openRejectModal">
                    <i class="bi bi-x-lg me-1"></i>Reject Payment
                </button>
            </div>
        </div>
    </div>
    
    <!-- Approve Payment Modal Template -->
    <div id="approveModalTemplate" class="custom-modal">
        <div class="custom-modal-content">
            <form id="approveForm" action="" method="post">
                <div class="custom-modal-header">
                    <h5 class="custom-modal-title">
                        <i class="bi bi-check-circle text-success me-2"></i>Approve Payment
                    </h5>
                    <button type="button" class="btn-close custom-close" data-action="closeModal"></button>
                </div>
                <div class="custom-modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Approving this payment will activate the business permit.
                    </div>
                    <input type="hidden" name="status" value="Verified">
                    <div class="mb-3">
                        <label for="notes-approve" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes-approve" name="notes" rows="3" placeholder="Add any verification notes or comments"></textarea>
                    </div>
                    <div class="payment-details">
                        <!-- Payment details will be inserted here -->
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" data-action="closeModal">
                        <i class="bi bi-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i>Approve Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Reject Payment Modal Template -->
    <div id="rejectModalTemplate" class="custom-modal">
        <div class="custom-modal-content">
            <form id="rejectForm" action="" method="post">
                <div class="custom-modal-header">
                    <h5 class="custom-modal-title">
                        <i class="bi bi-x-circle text-danger me-2"></i>Reject Payment
                    </h5>
                    <button type="button" class="btn-close custom-close" data-action="closeModal"></button>
                </div>
                <div class="custom-modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        The business owner will be notified and asked to submit a new payment.
                    </div>
                    <input type="hidden" name="status" value="Rejected">
                    <div class="mb-3">
                        <label for="notes-reject" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes-reject" name="notes" rows="3" placeholder="Explain why the payment is being rejected" required></textarea>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-secondary" data-action="closeModal">
                        <i class="bi bi-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i>Reject Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal data store for all payments -->
<div id="paymentDataStore" style="display: none;" data-payments='<?= json_encode($payments) ?>'></div>

<!-- Custom Modal Styles -->
<style>
.custom-modal-container {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1050;
}

.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1051;
}

.custom-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #fff;
    border-radius: 0.5rem;
    max-width: 500px;
    width: 95%;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    z-index: 1052;
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.custom-modal.show {
    display: block;
    opacity: 1;
}

.custom-modal-lg {
    max-width: 800px;
}

.custom-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
}

.custom-modal-title {
    margin: 0;
}

.custom-modal-body {
    padding: 1rem;
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.custom-modal-footer {
    display: flex;
    justify-content: flex-end;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
    gap: 0.5rem;
}

.custom-close {
    background: transparent;
    border: 0;
    cursor: pointer;
}

/* Fade-in animation */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.fadeIn {
    animation: fadeIn 0.3s ease forwards;
}

/* Fade-out animation */
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

.fadeOut {
    animation: fadeOut 0.3s ease forwards;
}
</style>

<!-- Custom Modal Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Initialize custom modal system
    const modalContainer = document.getElementById('customModalContainer');
    const modalBackdrop = document.getElementById('customModalBackdrop');
    const proofModalTemplate = document.getElementById('proofModalTemplate');
    const approveModalTemplate = document.getElementById('approveModalTemplate');
    const rejectModalTemplate = document.getElementById('rejectModalTemplate');
    
    // Get payment data from store
    const paymentDataStore = document.getElementById('paymentDataStore');
    const payments = JSON.parse(paymentDataStore.getAttribute('data-payments'));
    
    // Keep track of current payment ID
    let currentPaymentId = null;
    
    // Handle all click events via delegation
    document.addEventListener('click', function(event) {
        const target = event.target;
        const action = target.getAttribute('data-action') || 
                      (target.parentElement ? target.parentElement.getAttribute('data-action') : null);
        
        if (!action) return;
        
        // Get payment ID
        const paymentId = target.getAttribute('data-payment-id') || 
                         (target.parentElement ? target.parentElement.getAttribute('data-payment-id') : null) ||
                         currentPaymentId;
        
        // Find payment data if we have an ID
        const paymentData = paymentId ? payments.find(p => p.id == paymentId) : null;
        
        switch (action) {
            case 'openProofModal':
                if (paymentData) {
                    openProofModal(paymentData);
                }
                break;
                
            case 'openApproveModal':
                if (paymentData) {
                    closeAllModals();
                    openApproveModal(paymentData);
                }
                break;
                
            case 'openRejectModal':
                if (paymentData) {
                    closeAllModals();
                    openRejectModal(paymentData);
                }
                break;
                
            case 'closeModal':
                closeAllModals();
                break;
        }
    });
    
    // Open the proof modal with payment data
    function openProofModal(payment) {
        // Set current payment ID
        currentPaymentId = payment.id;
        
        // Prepare modal content
        const modalBody = proofModalTemplate.querySelector('.custom-modal-body');
        let proofContent = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Business:</strong> ${escapeHtml(payment.business_name)}</p>
                    <p><strong>Owner:</strong> ${escapeHtml(payment.owner_name || 'N/A')}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p><strong>Reference #:</strong> ${escapeHtml(payment.reference_number)}</p>
                    <p><strong>Amount:</strong> ₱${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                </div>
            </div>
            
            <div class="text-center mb-3">`;
        
        // Handle proof file display
        const proofFile = payment.proof_file;
        const fileExt = proofFile.split('.').pop().toLowerCase();
        
        // Use our new PHP route to serve the file instead of trying to access it directly
        const proofFilename = proofFile.split('/').pop(); // Get just the filename from the path
        const proofUrl = `<?= URLROOT ?>/uploads/proofs/${proofFilename}`;
        
        // Add debug log
        console.log('Payment proof URL:', proofUrl);
        console.log('Payment proof file path:', proofFile);
        
        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
            // Display image
            proofContent += `<img src="${proofUrl}" class="img-fluid border rounded" alt="Payment Proof" style="max-height: 500px;">`;
        } else if (fileExt === 'pdf') {
            // Display PDF embed with fallback
            proofContent += `
                <div class="ratio ratio-16x9 mb-3">
                    <embed src="${proofUrl}" type="application/pdf">
                </div>
                <p class="text-muted">If the PDF doesn't display correctly, <a href="${proofUrl}" target="_blank">open it in a new window</a>.</p>`;
        } else {
            // Unsupported file type
            proofContent += `
                <div class="alert alert-info">
                    <i class="bi bi-file-earmark me-2"></i>File type not supported for preview.
                    <div class="mt-2"><a href="${proofUrl}" target="_blank" class="btn btn-sm btn-primary">Download File</a></div>
                </div>`;
        }
        
        proofContent += `
            </div>
            
            <div class="d-flex justify-content-between">
                <p class="text-muted mb-0"><small>Payment Date: ${new Date(payment.payment_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</small></p>
                <a href="${proofUrl}" target="_blank" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-download"></i> Download Proof
                </a>
            </div>`;
        
        // Set the content
        modalBody.innerHTML = proofContent;
        
        // Set the payment ID on approve/reject buttons
        const approveBtn = proofModalTemplate.querySelector('.proof-approve-btn');
        const rejectBtn = proofModalTemplate.querySelector('.proof-reject-btn');
        
        approveBtn.setAttribute('data-payment-id', payment.id);
        rejectBtn.setAttribute('data-payment-id', payment.id);
        
        // Show the modal container and the proof modal
        showModal(proofModalTemplate);
    }
    
    // Open the approve modal with payment data
    function openApproveModal(payment) {
        // Set current payment ID
        currentPaymentId = payment.id;
        
        // Set the form action
        const form = approveModalTemplate.querySelector('form');
        form.action = `<?= URLROOT ?>/treasurer/verifyPayment/${payment.id}`;
        
        // Set the payment details
        const paymentDetails = approveModalTemplate.querySelector('.payment-details');
        paymentDetails.innerHTML = `
            <div class="d-flex align-items-center border-top border-bottom py-3 my-3">
                <div class="me-auto">
                    <h6 class="mb-0">Payment Reference</h6>
                    <span class="text-muted">${escapeHtml(payment.reference_number)}</span>
                </div>
                <div class="text-end">
                    <h6 class="mb-0">Amount</h6>
                    <span class="fs-5 fw-bold text-success">₱${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
            </div>`;
        
        // Show the modal
        showModal(approveModalTemplate);
    }
    
    // Open the reject modal with payment data
    function openRejectModal(payment) {
        // Set current payment ID
        currentPaymentId = payment.id;
        
        // Set the form action
        const form = rejectModalTemplate.querySelector('form');
        form.action = `<?= URLROOT ?>/treasurer/verifyPayment/${payment.id}`;
        
        // Show the modal
        showModal(rejectModalTemplate);
    }
    
    // Show a modal
    function showModal(modal) {
        // Show container
        modalContainer.style.display = 'block';
        
        // Show and animate the modal
        modal.classList.add('show');
        modal.classList.add('fadeIn');
        
        // Focus on the first input if it exists
        setTimeout(() => {
            const firstInput = modal.querySelector('input, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
    }
    
    // Close all modals
    function closeAllModals() {
        const activeModals = document.querySelectorAll('.custom-modal.show');
        
        activeModals.forEach(modal => {
            modal.classList.remove('fadeIn');
            modal.classList.add('fadeOut');
            
            // Remove the modal after animation completes
            setTimeout(() => {
                modal.classList.remove('show');
                modal.classList.remove('fadeOut');
                
                // Hide container if no modals are visible
                if (document.querySelectorAll('.custom-modal.show').length === 0) {
                    modalContainer.style.display = 'none';
                }
            }, 300); // Match the animation duration
        });
    }
    
    // Close modals when clicking on backdrop
    modalBackdrop.addEventListener('click', closeAllModals);
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }
});
</script>