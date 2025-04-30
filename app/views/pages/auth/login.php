<?php
// This ensures any direct access to this view has access to constants
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/config.php';
// Include functions.php to get access to the sanitize function
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/core/functions.php';

// This view file is loaded within the 'auth.php' layout.
// Variables like $title, $username, $password, $errors (if passed from controller) are available.
// We use the null coalescing operator (??) to avoid errors if variables aren't set initially.
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white">
        <h4 class="my-0 fw-normal text-center"><?= $title ?? 'Login' ?></h4>
    </div>
    <div class="card-body p-4">
        <!-- The action URL points to the controller method that will process the login -->
        <form action="<?= URLROOT ?>/auth/processLogin" method="POST" novalidate>
            <!-- CSRF Token Input -->
            <!-- <input type="hidden" name="csrf_token" value="<?php // echo createCsrfToken(); ?>"> -->

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text"
                           class="form-control <?= isset($errors['username']) ? 'is-invalid' : ''; ?>"
                           id="username"
                           name="username"
                           value="<?= sanitize($username ?? '') ?>"
                           placeholder="Enter your username"
                           required
                           autofocus>
                </div>
                <?php if (isset($errors['username'])): ?>
                    <div class="invalid-feedback d-block"><?= sanitize($errors['username']); ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password"
                           class="form-control <?= isset($errors['password']) ? 'is-invalid' : ''; ?>"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback d-block"><?= sanitize($errors['password']); ?></div>
                <?php endif; ?>
                
                <!-- General login error (e.g., "Invalid credentials") -->
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger mt-2 p-2 small"><?= sanitize($errors['general']); ?></div>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" value="1">
                    <label class="form-check-label" for="remember_me">Remember Me</label>
                </div>
                <div>
                    <a href="<?= URLROOT ?>/auth/forgotPassword" class="text-decoration-none small">Forgot Password?</a>
                </div>
            </div>

            <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </div>

            <div class="text-center">
                <p class="mb-0">Don't have an account? 
                    <a href="/register" class="text-decoration-none fw-bold">Register here</a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle password visibility
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
</script>