<?php
// This ensures any direct access to this layout has access to constants
require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/core/functions.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barangay Business Registration System">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= URLROOT ?>/css/style.css" rel="stylesheet">
    <title><?= isset($title) ? sanitize($title) . ' | ' . SITENAME : SITENAME ?></title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            border-bottom: none;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .invalid-feedback {
            font-size: 80%;
        }
        
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0.05) 100%);
        }
        
        .auth-logo img {
            max-height: 60px;
            max-width: 100%;
        }
        
        @media (max-width: 768px) {
            .auth-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Increase column width for larger forms like registration -->
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <div class="text-center mb-4 auth-logo">
                        <h2 class="fw-bold text-primary"><?= SITENAME ?></h2>
                    </div>

                    <!-- Flash Messages Area -->
                    <div id="flash-messages">
                        <?php if (class_exists('SessionHelper')): ?>
                            <?php SessionHelper::displayFlashMessages(); ?>
                        <?php endif; ?>
                    </div>

                    <!-- Main Content Area (Login/Register form) -->
                    <?php 
                    // Include the view file based on $view variable
                    if (isset($view)) {
                        $viewPath = APPROOT . '/views/' . $view . '.php';
                        if (file_exists($viewPath)) {
                            // Include the view file
                            include_once $viewPath;
                        } else {
                            echo '<div class="alert alert-danger">View file not found: ' . htmlspecialchars($view) . '</div>';
                        }
                    } else {
                        echo '<div class="alert alert-danger">No view specified</div>';
                    }
                    ?>

                    <p class="text-center text-muted mt-4 small">
                        © <?= date('Y') ?> <?= SITENAME ?>. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- Custom JS -->
    <script src="<?= URLROOT ?>/js/script.js"></script>
</body>
</html>