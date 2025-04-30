<?php
/**
 * Base Controller
 * Loads models and views. Provides basic authorization helpers.
 */
abstract class Controller {

    /**
     * Loads and instantiates a model class.
     *
     * @param string $model The name of the model class (e.g., 'User').
     * @return object|null An instance of the model, or null if not found.
     */
    protected function model(string $model): ?object {
        $modelFile = APPROOT . '/models/' . $model . '.php';
        if (file_exists($modelFile)) {
            require_once $modelFile;
            if (class_exists($model)) {
                // Pass the Database instance to the model constructor if needed
                // Example: return new $model(Database::getInstance());
                // For now, assume models get DB instance themselves or it's passed differently
                return new $model();
            }
        }
        // Log error in production instead of dying
        error_log("Model file not found or class '$model' does not exist: " . $modelFile);
        return null; // Return null or throw exception
    }

    /**
     * Loads a view file.
     * Extracts data array into variables accessible within the view.
     * Determines the layout to use based on context (e.g., auth vs app).
     *
     * @param string $view The path to the view file relative to app/views (e.g., 'pages/auth/login').
     * @param array $data Data to pass to the view.
     * @param string $layout The layout file to use ('main' or 'auth'). Defaults to 'main'.
     * @return void
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        $viewPath = APPROOT . '/views/' . $view . '.php';
        
        // Log paths for debugging
        error_log("Attempting to load view: " . $viewPath);
        
        if (file_exists($viewPath)) {
            // Always set $view for the layout
            $data['view'] = $view;
            extract($data);
            
            $layoutPath = APPROOT . '/views/layouts/' . $layout . '.php';
            error_log("Attempting to load layout: " . $layoutPath);
            
            if (file_exists($layoutPath)) {
                // Use output buffering to prevent multiple outputs
                ob_start();
                require $layoutPath;
                echo ob_get_clean();
                exit(); // Add exit to prevent further execution
            } else {
                // More detailed error message with full path information
                error_log("Layout file not found: " . $layoutPath);
                echo '<div class="alert alert-danger">Layout file missing: ' . $layout . '.php</div>';
                echo '<div>Attempted path: ' . $layoutPath . '</div>';
                echo '<div>Available layouts: ';
                $layouts = glob(APPROOT . '/views/layouts/*.php');
                echo implode(', ', array_map('basename', $layouts));
                echo '</div>';
                
                // As a fallback, try to render just the view without a layout
                echo '<div class="alert alert-info mt-3">Attempting to render view without layout</div>';
                include $viewPath;
            }
        } else {
            error_log("View file not found: " . $viewPath);
            die('View file missing: ' . $view);
        }
    }

    /**
     * Checks if a user is currently logged in by checking session variables.
     *
     * @return bool True if logged in, false otherwise.
     */
    protected function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Explicitly check for user_id in session
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Checks if the logged-in user has a specific role.
     *
     * @param string $role The role to check against (use ROLE_* constants).
     * @return bool True if the user is logged in and has the specified role.
     */
    protected function hasRole(string $role): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $this->isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Redirects the user to the login page if they are not logged in.
     * Sets a flash message indicating the reason.
     * Should be called at the beginning of methods requiring authentication.
     *
     * @return void
     */
    protected function requireLogin(): void {
        if (!$this->isLoggedIn()) {
            SessionHelper::setFlash('error', 'Please log in to access that page.');
            redirect('auth/login');
        }
    }

    /**
     * Redirects the user if they do not have the required role.
     * Implicitly calls requireLogin first.
     *
     * @param string $role The required role (use ROLE_* constants).
     * @param string $redirectPath Optional path to redirect to if unauthorized (defaults to 'dashboard').
     * @return void
     */
    protected function requireRole(string $role, string $redirectPath = 'dashboard'): void {
        $this->requireLogin(); // Ensure user is logged in first
        if (!$this->hasRole($role)) {
            SessionHelper::setFlash('error', 'You do not have permission to access that page.');
            redirect($redirectPath); // Redirect to a default page or specific error page
        }
    }
}
?>