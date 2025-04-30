<?php
// Ensure dependencies are loaded
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/config.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/core/functions.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h4 class="my-0 fw-normal text-center"><?= $title ?? 'Register Account' ?></h4>
    </div>
    <div class="card-body p-4">
        <form action="<?= URLROOT ?>/register-process" method="POST" novalidate>
            <!-- Account Information Section -->
            <h5 class="mb-3 border-bottom pb-2">Account Information</h5>
            
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text"
                           class="form-control <?= isset($errors['username']) ? 'is-invalid' : ''; ?>"
                           id="username"
                           name="username"
                           value="<?= sanitize($username ?? '') ?>"
                           placeholder="Choose a username (min. 4 characters)"
                           required>
                </div>
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback d-block"><?= sanitize($errors['username']); ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password"
                               class="form-control <?= isset($errors['password']) ? 'is-invalid' : ''; ?>"
                               id="password"
                               name="password"
                               placeholder="Min. 6 characters"
                               required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="invalid-feedback d-block"><?= sanitize($errors['password']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password"
                               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>"
                               id="confirm_password"
                               name="confirm_password"
                               placeholder="Re-enter your password"
                               required>
                    </div>
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="invalid-feedback d-block"><?= sanitize($errors['confirm_password']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Personal Information Section -->
            <h5 class="mt-4 mb-3 border-bottom pb-2">Personal Information</h5>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text"
                           class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : ''; ?>"
                           id="first_name"
                           name="first_name"
                           value="<?= sanitize($first_name ?? '') ?>"
                           placeholder="Enter your first name"
                           required>
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="invalid-feedback"><?= sanitize($errors['first_name']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text"
                           class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : ''; ?>"
                           id="last_name"
                           name="last_name"
                           value="<?= sanitize($last_name ?? '') ?>"
                           placeholder="Enter your last name"
                           required>
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="invalid-feedback"><?= sanitize($errors['last_name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                    <input type="email"
                           class="form-control <?= isset($errors['email']) ? 'is-invalid' : ''; ?>"
                           id="email"
                           name="email"
                           value="<?= sanitize($email ?? '') ?>"
                           placeholder="Enter your email address">
                </div>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback d-block"><?= sanitize($errors['email']); ?></div>
                <?php endif; ?>
                <div class="form-text">We'll never share your email with anyone else.</div>
            </div>

            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                    <input type="tel"
                           class="form-control <?= isset($errors['phone_number']) ? 'is-invalid' : ''; ?>"
                           id="phone_number"
                           name="phone_number"
                           value="<?= sanitize($phone_number ?? '') ?>"
                           placeholder="Enter your phone number">
                </div>
                <?php if (isset($errors['phone_number'])): ?>
                    <div class="invalid-feedback d-block"><?= sanitize($errors['phone_number']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="address" class="form-label">Address</label>
                <textarea 
                    class="form-control <?= isset($errors['address']) ? 'is-invalid' : ''; ?>"
                    id="address"
                    name="address"
                    rows="3"
                    placeholder="Enter your complete address"><?= sanitize($address ?? '') ?></textarea>
                <?php if (isset($errors['address'])): ?>
                    <div class="invalid-feedback"><?= sanitize($errors['address']); ?></div>
                <?php endif; ?>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-person-plus-fill me-2"></i>Register
                </button>
            </div>

            <div class="text-center">
                <p class="mb-0">Already have an account? 
                    <a href="/login" class="text-decoration-none fw-bold">Login here</a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle password visibility for password field
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    });

    // Form validation on submit
    document.querySelector('form').addEventListener('submit', function(event) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Check if passwords match on client-side (in addition to server-side validation)
        if (password !== confirmPassword) {
            document.getElementById('confirm_password').classList.add('is-invalid');
            // Prevent form submission
            event.preventDefault();
        }
    });
</script>