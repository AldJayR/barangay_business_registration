<?php
// View file for business permit application form
// Access form data with $formData variable and validation errors with $errors
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Apply for Business Permit</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= URLROOT ?>/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Apply for Permit</li>
                </ol>
            </nav>
        </div>
        <a href="<?= URLROOT ?>/business/applications" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Applications
        </a>
    </div>

    <!-- Instructions Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body p-4">
            <h5 class="card-title"><i class="bi bi-info-circle-fill text-primary me-2"></i>Application Instructions</h5>
            <p class="card-text">Follow the steps below to apply for a new business permit. All fields marked with <span class="text-danger">*</span> are required.</p>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Important:</strong> Make sure to provide accurate business information as it will appear on your official permit. After submission, your application will be reviewed by the barangay officials.
            </div>
            
            <!-- Progress Tracker -->
            <div class="mt-4">
                <div class="step-progress">
                    <div class="step-progress-bar">
                        <div class="step-progress-fill" id="progressBar"></div>
                    </div>
                    <div class="step-indicators">
                        <div class="step active" id="step-indicator-1">
                            <div class="step-circle">1</div>
                            <div class="step-label">Business Details</div>
                        </div>
                        <div class="step" id="step-indicator-2">
                            <div class="step-circle">2</div>
                            <div class="step-label">Owner Information</div>
                        </div>
                        <div class="step" id="step-indicator-3">
                            <div class="step-circle">3</div>
                            <div class="step-label">Review & Submit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Form -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-building me-2"></i><span id="step-title">Business Information</span></h5>
        </div>
        <div class="card-body p-4">
            <!-- Display system-level error if any -->
            <?php if (isset($errors['system'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <?= $errors['system'] ?>
                </div>
            <?php endif; ?>

            <form action="<?= URLROOT ?>/business/submitApplication" method="POST" id="businessApplicationForm">
                
                <!-- Step 1: Business Information Section -->
                <div class="step-content" id="step-1">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-muted text-uppercase mb-3 border-bottom pb-2">Business Details</h6>
                        </div>
                        
                        <!-- Business Name -->
                        <div class="col-md-6 mb-3">
                            <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                id="business_name" name="business_name" 
                                value="<?= htmlspecialchars($formData['name'] ?? '') ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= $errors['name'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Enter the complete registered name of your business.</div>
                        </div>
                        
                        <!-- Business Type -->
                        <div class="col-md-6 mb-3">
                            <label for="business_type" class="form-label">Business Type <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['type']) ? 'is-invalid' : '' ?>" 
                                    id="business_type" name="business_type" required>
                                <option value="" <?= empty($formData['type']) ? 'selected' : '' ?>>Select business type...</option>
                                <option value="Retail" <?= ($formData['type'] ?? '') === 'Retail' ? 'selected' : '' ?>>Retail</option>
                                <option value="Food" <?= ($formData['type'] ?? '') === 'Food' ? 'selected' : '' ?>>Food & Beverage</option>
                                <option value="Service" <?= ($formData['type'] ?? '') === 'Service' ? 'selected' : '' ?>>Service</option>
                                <option value="Manufacturing" <?= ($formData['type'] ?? '') === 'Manufacturing' ? 'selected' : '' ?>>Manufacturing</option>
                                <option value="Wholesale" <?= ($formData['type'] ?? '') === 'Wholesale' ? 'selected' : '' ?>>Wholesale</option>
                                <option value="Other" <?= ($formData['type'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <?php if (isset($errors['type'])): ?>
                                <div class="invalid-feedback"><?= $errors['type'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Select the primary category that best describes your business.</div>
                        </div>
                        
                        <!-- Business Address -->
                        <div class="col-md-12 mb-3">
                            <label for="business_address" class="form-label">Business Address <span class="text-danger">*</span></label>
                            <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                    id="business_address" name="business_address" rows="2" required><?= htmlspecialchars($formData['address'] ?? '') ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= $errors['address'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Please provide the complete address of your business within the barangay.</div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-primary next-step" data-step="1">
                            Next <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 2: Owner Information Section -->
                <div class="step-content" id="step-2" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-muted text-uppercase mb-3 border-bottom pb-2">Owner Information</h6>
                        </div>
                        
                        <!-- Owner First Name -->
                        <div class="col-md-6 mb-3">
                            <label for="owner_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['owner_first_name']) ? 'is-invalid' : '' ?>" 
                                id="owner_first_name" name="owner_first_name" 
                                value="<?= htmlspecialchars($formData['owner_first_name'] ?? '') ?>" required>
                            <?php if (isset($errors['owner_first_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['owner_first_name'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Owner Last Name -->
                        <div class="col-md-6 mb-3">
                            <label for="owner_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['owner_last_name']) ? 'is-invalid' : '' ?>" 
                                id="owner_last_name" name="owner_last_name" 
                                value="<?= htmlspecialchars($formData['owner_last_name'] ?? '') ?>" required>
                            <?php if (isset($errors['owner_last_name'])): ?>
                                <div class="invalid-feedback"><?= $errors['owner_last_name'] ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Owner Address -->
                        <div class="col-md-12 mb-3">
                            <label for="owner_address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                            <textarea class="form-control <?= isset($errors['owner_address']) ? 'is-invalid' : '' ?>" 
                                    id="owner_address" name="owner_address" rows="2" required><?= htmlspecialchars($formData['owner_address'] ?? '') ?></textarea>
                            <?php if (isset($errors['owner_address'])): ?>
                                <div class="invalid-feedback"><?= $errors['owner_address'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Your permanent residential address, even if different from business address.</div>
                        </div>
                        
                        <!-- Contact Information -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                id="email" name="email" 
                                value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Your active email address for official communications.</div>
                        </div>
                        
                        <!-- Phone Number -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                id="phone" name="phone" 
                                value="<?= htmlspecialchars($formData['phone'] ?? '') ?>" required>
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">Your active contact number for official communications.</div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary prev-step" data-step="2">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <button type="button" class="btn btn-primary next-step" data-step="2">
                            Next <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Step 3: Review & Submit Section -->
                <div class="step-content" id="step-3" style="display: none;">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="fw-bold text-muted text-uppercase mb-3 border-bottom pb-2">Review Application</h6>
                            <p class="text-muted">Please review your information before submitting your application.</p>
                        </div>
                        
                        <!-- Business Information Summary -->
                        <div class="col-md-12 mb-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Business Information</h6>
                                    <div class="row mb-2">
                                        <div class="col-md-4 text-muted">Business Name:</div>
                                        <div class="col-md-8 fw-bold" id="review-business-name"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 text-muted">Business Type:</div>
                                        <div class="col-md-8" id="review-business-type"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Business Address:</div>
                                        <div class="col-md-8" id="review-business-address"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Owner Information Summary -->
                        <div class="col-md-12 mb-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3">Owner Information</h6>
                                    <div class="row mb-2">
                                        <div class="col-md-4 text-muted">Owner's Name:</div>
                                        <div class="col-md-8" id="review-owner-name"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 text-muted">Residential Address:</div>
                                        <div class="col-md-8" id="review-owner-address"></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 text-muted">Email Address:</div>
                                        <div class="col-md-8" id="review-email"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 text-muted">Phone Number:</div>
                                        <div class="col-md-8" id="review-phone"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I declare that the information provided above is true and correct to the best of my knowledge. 
                                    I understand that providing false information may result in the rejection of my application or 
                                    revocation of any permit issued.
                                </label>
                            </div>
                            <div id="terms-error" class="text-danger mt-2" style="display: none;">
                                You must agree to the declaration before submitting.
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>What's Next?</strong>
                        <p class="mb-0">After submission, your application will be reviewed by our administrators. Once verified, you'll be notified to proceed with the payment for your business permit.</p>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-outline-secondary prev-step" data-step="3">
                            <i class="bi bi-arrow-left me-1"></i> Previous
                        </button>
                        <div>
                            <a href="<?= URLROOT ?>/dashboard" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-success" id="submitButton">
                                <i class="bi bi-send me-1"></i>Submit Application
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS for wizard steps -->
<style>
.step-progress {
    margin-bottom: 30px;
    position: relative;
}

.step-progress-bar {
    height: 4px;
    background-color: #e9ecef;
    position: relative;
    margin: 25px 0;
    z-index: 1;
}

.step-progress-fill {
    height: 100%;
    background-color: #0d6efd;
    width: 0%;
    transition: width 0.3s ease;
}

.step-indicators {
    display: flex;
    justify-content: space-between;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 2;
}

.step {
    text-align: center;
    width: 33.333%;
    position: relative;
}

.step-circle {
    width: 30px;
    height: 30px;
    background-color: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-weight: bold;
    color: #6c757d;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.step-label {
    margin-top: 8px;
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

.step.active .step-circle {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.step.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}

.step.completed .step-circle {
    background-color: #198754;
    color: white;
    border-color: #198754;
}

.step.completed .step-label {
    color: #198754;
}

.step-content {
    transition: all 0.3s ease;
}
</style>

<!-- Client-side validation and wizard management script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('businessApplicationForm');
    const steps = document.querySelectorAll('.step-content');
    const stepIndicators = document.querySelectorAll('.step');
    const progressBar = document.getElementById('progressBar');
    const stepTitle = document.getElementById('step-title');
    
    // Step titles
    const stepTitles = {
        1: 'Business Information',
        2: 'Owner Information',
        3: 'Review & Submit'
    };
    
    // Initialize the form wizard
    let currentStep = 1;
    updateProgressBar();
    
    // Next step button handler
    document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', function() {
            const step = parseInt(this.getAttribute('data-step'));
            
            // Validate current step before proceeding
            if (validateStep(step)) {
                // If validation passes, proceed to next step
                hideStep(step);
                showStep(step + 1);
                
                // Update progress indicators
                stepIndicators[step-1].classList.add('completed');
                stepIndicators[step].classList.add('active');
                
                // Update current step
                currentStep = step + 1;
                updateProgressBar();
                
                // Update step title
                stepTitle.textContent = stepTitles[currentStep];
                
                // If moving to review step, populate review fields
                if (currentStep === 3) {
                    populateReviewStep();
                }
            }
        });
    });
    
    // Previous step button handler
    document.querySelectorAll('.prev-step').forEach(button => {
        button.addEventListener('click', function() {
            const step = parseInt(this.getAttribute('data-step'));
            
            hideStep(step);
            showStep(step - 1);
            
            // Update progress indicators
            stepIndicators[step-1].classList.remove('active');
            stepIndicators[step-2].classList.remove('completed');
            stepIndicators[step-2].classList.add('active');
            
            // Update current step
            currentStep = step - 1;
            updateProgressBar();
            
            // Update step title
            stepTitle.textContent = stepTitles[currentStep];
        });
    });
    
    // Form submission handler
    form.addEventListener('submit', function(event) {
        // Check if terms checkbox is checked
        const termsCheckbox = document.getElementById('terms');
        const termsError = document.getElementById('terms-error');
        
        if (!termsCheckbox.checked) {
            event.preventDefault();
            termsError.style.display = 'block';
            return false;
        } else {
            termsError.style.display = 'none';
            return true;
        }
    });
    
    // Validate a specific step
    function validateStep(stepNumber) {
        let isValid = true;
        let firstInvalidField = null;
        
        if (stepNumber === 1) {
            // Validate Business Information fields
            const businessName = document.getElementById('business_name');
            const businessType = document.getElementById('business_type');
            const businessAddress = document.getElementById('business_address');
            
            if (!businessName.value.trim()) {
                showError(businessName, 'Business name is required');
                isValid = false;
                firstInvalidField = firstInvalidField || businessName;
            } else {
                hideError(businessName);
            }
            
            if (!businessType.value) {
                showError(businessType, 'Please select a business type');
                isValid = false;
                firstInvalidField = firstInvalidField || businessType;
            } else {
                hideError(businessType);
            }
            
            if (!businessAddress.value.trim()) {
                showError(businessAddress, 'Business address is required');
                isValid = false;
                firstInvalidField = firstInvalidField || businessAddress;
            } else {
                hideError(businessAddress);
            }
        } else if (stepNumber === 2) {
            // Validate Owner Information fields
            const firstName = document.getElementById('owner_first_name');
            const lastName = document.getElementById('owner_last_name');
            const address = document.getElementById('owner_address');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            
            if (!firstName.value.trim()) {
                showError(firstName, 'First name is required');
                isValid = false;
                firstInvalidField = firstInvalidField || firstName;
            } else {
                hideError(firstName);
            }
            
            if (!lastName.value.trim()) {
                showError(lastName, 'Last name is required');
                isValid = false;
                firstInvalidField = firstInvalidField || lastName;
            } else {
                hideError(lastName);
            }
            
            if (!address.value.trim()) {
                showError(address, 'Address is required');
                isValid = false;
                firstInvalidField = firstInvalidField || address;
            } else {
                hideError(address);
            }
            
            if (!email.value.trim()) {
                showError(email, 'Email address is required');
                isValid = false;
                firstInvalidField = firstInvalidField || email;
            } else if (!isValidEmail(email.value.trim())) {
                showError(email, 'Please enter a valid email address');
                isValid = false;
                firstInvalidField = firstInvalidField || email;
            } else {
                hideError(email);
            }
            
            if (!phone.value.trim()) {
                showError(phone, 'Phone number is required');
                isValid = false;
                firstInvalidField = firstInvalidField || phone;
            } else {
                hideError(phone);
            }
        }
        
        // Focus on the first invalid field if any
        if (firstInvalidField) {
            firstInvalidField.focus();
        }
        
        return isValid;
    }
    
    // Show error for a field
    function showError(field, message) {
        field.classList.add('is-invalid');
        
        // Check if error message element already exists
        let errorElement = field.nextElementSibling;
        if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            field.parentNode.insertBefore(errorElement, field.nextElementSibling);
        }
        
        errorElement.textContent = message;
    }
    
    // Hide error for a field
    function hideError(field) {
        field.classList.remove('is-invalid');
    }
    
    // Email validation helper
    function isValidEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
    
    // Show a specific step
    function showStep(stepNumber) {
        document.getElementById(`step-${stepNumber}`).style.display = 'block';
    }
    
    // Hide a specific step
    function hideStep(stepNumber) {
        document.getElementById(`step-${stepNumber}`).style.display = 'none';
    }
    
    // Update progress bar based on current step
    function updateProgressBar() {
        const progress = ((currentStep - 1) / (steps.length - 1)) * 100;
        progressBar.style.width = `${progress}%`;
    }
    
    // Populate review step with data from previous steps
    function populateReviewStep() {
        // Business Information
        document.getElementById('review-business-name').textContent = document.getElementById('business_name').value;
        document.getElementById('review-business-type').textContent = document.getElementById('business_type').options[document.getElementById('business_type').selectedIndex].text;
        document.getElementById('review-business-address').textContent = document.getElementById('business_address').value;
        
        // Owner Information
        const firstName = document.getElementById('owner_first_name').value;
        const lastName = document.getElementById('owner_last_name').value;
        document.getElementById('review-owner-name').textContent = `${firstName} ${lastName}`;
        document.getElementById('review-owner-address').textContent = document.getElementById('owner_address').value;
        document.getElementById('review-email').textContent = document.getElementById('email').value;
        document.getElementById('review-phone').textContent = document.getElementById('phone').value;
    }
});
</script>