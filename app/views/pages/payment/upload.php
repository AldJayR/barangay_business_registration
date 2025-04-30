<div class="container my-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Upload Payment Proof</h5>
                </div>
                <div class="card-body">
                    <?php SessionHelper::displayFlashMessages(); ?>
                    
                    <form action="<?= URLROOT ?>/payment/upload/<?= $data['business_id'] ?>" method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <h6 class="text-muted">Business Details</h6>
                                <div class="alert alert-info">
                                    <p class="mb-1"><strong>Business Name:</strong> <?= $data['business']->name ?></p>
                                    <p class="mb-1"><strong>Business Type:</strong> <?= $data['business']->type ?></p>
                                    <p class="mb-0"><strong>Status:</strong> <?= $data['business']->status ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select <?= !empty($data['payment_method_err']) ? 'is-invalid' : '' ?>" 
                                id="payment_method" name="payment_method" required>
                                <option value="" disabled <?= empty($data['payment_method']) ? 'selected' : '' ?>>Select a payment method</option>
                                <option value="cash" <?= $data['payment_method'] === 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="gcash" <?= $data['payment_method'] === 'gcash' ? 'selected' : '' ?>>GCash</option>
                                <option value="card" <?= $data['payment_method'] === 'card' ? 'selected' : '' ?>>Credit/Debit Card</option>
                                <option value="transfer" <?= $data['payment_method'] === 'transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="online" <?= $data['payment_method'] === 'online' ? 'selected' : '' ?>>Other Online Payment</option>
                            </select>
                            <div class="invalid-feedback"><?= $data['payment_method_err'] ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Payment Amount (₱) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control <?= !empty($data['amount_err']) ? 'is-invalid' : '' ?>" 
                                id="amount" name="amount" value="<?= $data['amount'] ?? '' ?>" required>
                            <div class="invalid-feedback"><?= $data['amount_err'] ?? '' ?></div>
                            <div class="form-text">Amount to be paid for the business permit.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control <?= !empty($data['payment_date_err']) ? 'is-invalid' : '' ?>" 
                                id="payment_date" name="payment_date" value="<?= $data['payment_date'] ?>" required>
                            <div class="invalid-feedback"><?= $data['payment_date_err'] ?></div>
                        </div>
                        
                        <!-- Online Payment Section (GCash, Card, Transfer, etc.) -->
                        <div id="online-payment-section">
                            <!-- GCash QR Code Section -->
                            <div class="mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Barangay GCash QR Code</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <p>Scan this QR code to pay via GCash:</p>
                                        <img src="<?= URLROOT ?>/app/public/img/barangay_gcash_qr.jpg" alt="Barangay GCash QR Code" class="img-fluid mb-2" style="max-width: 250px;">
                                        <p class="mb-0 fw-bold">Barangay Business Registration Payment</p>
                                        <p class="text-muted small">Please make sure to enter the correct reference number after payment</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reference_number" class="form-label">Reference Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= !empty($data['reference_number_err']) ? 'is-invalid' : '' ?>" 
                                    id="reference_number" name="reference_number" value="<?= $data['reference_number'] ?? '' ?>">
                                <div class="invalid-feedback"><?= $data['reference_number_err'] ?? '' ?></div>
                                <div class="form-text">Enter the transaction reference number from your payment receipt.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="proof_file" class="form-label">Payment Receipt <span class="text-danger">*</span></label>
                                <input type="file" class="form-control <?= !empty($data['proof_file_err']) ? 'is-invalid' : '' ?>" 
                                    id="proof_file" name="proof_file">
                                <div class="invalid-feedback"><?= $data['proof_file_err'] ?? '' ?></div>
                                <div class="form-text">
                                    Upload a clear image or PDF of your payment receipt. Max size: 5MB. 
                                    Accepted formats: JPG, PNG, GIF, PDF.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cash Payment Notice -->
                        <div id="cash-payment-notice" class="alert alert-info mb-4" style="display: none;">
                            <h6 class="alert-heading">Cash Payment Instructions</h6>
                            <p>Please proceed to the Barangay Hall to make your cash payment. The treasurer will update your payment status after verification.</p>
                            <p class="mb-0">Business permit processing will begin after your payment has been verified.</p>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= URLROOT ?>/business/view/<?= $data['business_id'] ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/hide sections based on payment method
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethodSelect = document.getElementById('payment_method');
        const onlinePaymentSection = document.getElementById('online-payment-section');
        const cashPaymentNotice = document.getElementById('cash-payment-notice');
        const referenceNumberInput = document.getElementById('reference_number');
        const proofFileInput = document.getElementById('proof_file');
        
        function updateFormFields() {
            const selectedMethod = paymentMethodSelect.value;
            
            if (selectedMethod === 'cash') {
                onlinePaymentSection.style.display = 'none';
                cashPaymentNotice.style.display = 'block';
                referenceNumberInput.removeAttribute('required');
                proofFileInput.removeAttribute('required');
            } else {
                onlinePaymentSection.style.display = 'block';
                cashPaymentNotice.style.display = 'none';
                referenceNumberInput.setAttribute('required', 'required');
                proofFileInput.setAttribute('required', 'required');
            }
        }
        
        // Run on page load
        updateFormFields();
        
        // Run when payment method changes
        paymentMethodSelect.addEventListener('change', updateFormFields);
    });
</script>