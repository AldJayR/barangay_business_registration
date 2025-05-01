<?php

class AuthController extends Controller {

    private ?User $userModel = null; // Property to hold the User model instance

    public function __construct() {
        // Instantiate the User model when AuthController is created
        $this->userModel = $this->model('User');
        if ($this->userModel === null) {
             // Handle error if model fails to load
             // Log error, maybe throw exception or redirect to an error page
             error_log("Failed to load User model in AuthController.");
             // For simplicity in this project, we might let it proceed and fail later,
             // but in production, more robust handling is needed.
             die("Critical error: User model could not be loaded.");
        }
    }

    /**
     * Displays the login page.
     * Can be accessed via /login
     */
    public function login() {
        // Clear any existing session for testing if needed
        if (isset($_GET['clear_session'])) {
            unset($_SESSION['user_id']);
            unset($_SESSION['user_role']);
            session_destroy();
            session_start();
        }
        
        // Only redirect if user is actually logged in
        if ($this->isLoggedIn()) {
            // Redirect based on user role
            $this->redirectBasedOnRole();
            return;
        }

        // Otherwise, show the login page
        $data = [
            'title' => 'Login',
            'username' => '',
            'errors' => []
        ];
        
        // Render the login view with auth layout
        $this->view('pages/auth/login', $data, 'auth');
    }

    /**
     * Processes the login form submission.
     */
    public function processLogin() {
        // Ensure this is a POST request
        if (!isPostRequest()) {
            redirect('login'); // Now using clean URL
        }

        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $data = [
            'title' => 'Login',
            'username' => trim($_POST['username'] ?? ''),
            'password' => trim($_POST['password'] ?? ''),
            'remember_me' => isset($_POST['remember_me']), // Check if checkbox was checked
            'errors' => []
        ];

        // --- Validation ---
        if (empty($data['username'])) {
            $data['errors']['username'] = 'Please enter username.';
        }
        if (empty($data['password'])) {
            $data['errors']['password'] = 'Please enter password.';
        }

        // --- Check for User ---
        if (empty($data['errors'])) {
            $loggedInUser = $this->userModel->findByUsername($data['username']);

            if ($loggedInUser) {
                // User found, verify password
                if (password_verify($data['password'], $loggedInUser->password)) {
                    // Password is correct - Create Session
                    $this->createUserSession($loggedInUser);
                    // Handle Remember Me
                    // if ($data['remember_me']) {
                    //     // Create a secure persistent cookie
                    // }
                    SessionHelper::setFlash('success', 'You are now logged in!');
                    
                    // Redirect to appropriate dashboard based on role
                    $this->redirectBasedOnRole();
                } else {
                    // Password incorrect
                    $data['errors']['general'] = 'Invalid username or password.';
                }
            } else {
                // User not found
                $data['errors']['general'] = 'Invalid username or password.';
            }
        }

        // If errors occurred or login failed, reload the view with errors
        $this->view('pages/auth/login', $data, 'auth');
    }

    /**
     * Redirects user to appropriate dashboard based on their role
     */
    private function redirectBasedOnRole() {
        $userRole = $_SESSION['user_role'] ?? null;

        switch ($userRole) {
            case ROLE_OWNER:
                redirect('owner/dashboard');
                break;
            case ROLE_ADMIN:
                redirect('admin/dashboard');
                break;
            case ROLE_TREASURER:
                redirect('treasurer/dashboard');
                break;
            default:
                error_log("Invalid or missing user role for user ID: " . ($_SESSION['user_id'] ?? 'N/A'));
                SessionHelper::setFlash('error', 'Invalid user role detected. Please log in again.');
                redirect('logout');
                break;
        }
    }

    /**
     * Logs the user out.
     */
    public function logout() {
        // Unset all of the session variables.
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        // TODO: Clear "Remember Me" cookie if implemented

        SessionHelper::setFlash('success', 'You have been logged out.');
        redirect('login'); // Now using clean URL
    }


    /**
     * Sets user session variables after successful login.
     *
     * @param object $user The user object (from findByUsername or findById).
     * @return void
     */
    private function createUserSession(object $user): void {
         // Regenerate session ID for security (prevents session fixation)
         session_regenerate_id(true);

         // Fetch full details if needed (e.g., for first name)
         $fullUserDetails = $this->userModel->findById($user->id);

         $_SESSION['user_id'] = $user->id;
         $_SESSION['user_username'] = $user->username; // Store username if needed
         $_SESSION['user_role'] = $user->role;
         // Store name for display (handle case where details might be missing)
         $_SESSION['user_name'] = $fullUserDetails ? $fullUserDetails->first_name : $user->username;

         // Optional: Store login timestamp or other relevant session data
         $_SESSION['login_time'] = time();
    }


    // --- Registration methods (Keep placeholders for now) ---
    /**
     * Displays the registration page.
     */
    public function register() {
        if ($this->isLoggedIn()) { 
            $this->redirectBasedOnRole();
            return;
        }
        
        $data = [
            'title' => 'Register',
            'username' => '',
            'first_name' => '',
            'last_name' => '',
            'phone_number' => '',
            'email' => '',
            'address' => '',
            'password' => '',
            'confirm_password' => '',
            'errors' => []
        ];
        
        $this->view('pages/auth/register', $data, 'auth');
    }

    /**
     * Processes the registration form data.
     */
    public function processRegistration() {
        if (!isPostRequest()) { 
            redirect('register');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Initialize data with form values (for redisplaying the form if errors)
        $data = [
            'title' => 'Register',
            'username' => trim($_POST['username'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'password' => $_POST['password'] ?? '', // Will be hashed before DB insert
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'role' => 'business_owner', // Default role for public registration
            'errors' => []
        ];
        
        // Validation
        // Username validation
        if (empty($data['username'])) {
            $data['errors']['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 4) {
            $data['errors']['username'] = 'Username must be at least 4 characters';
        } else {
            // Check if username already exists
            if ($this->userModel->findByUsername($data['username'])) {
                $data['errors']['username'] = 'Username is already taken';
            }
        }
        
        // Name validation
        if (empty($data['first_name'])) {
            $data['errors']['first_name'] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $data['errors']['last_name'] = 'Last name is required';
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['errors']['email'] = 'Please enter a valid email address';
        }
        
        // Password validation
        if (empty($data['password'])) {
            $data['errors']['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 6) {
            $data['errors']['password'] = 'Password must be at least 6 characters';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $data['errors']['confirm_password'] = 'Passwords do not match';
        }
        
        // If no errors, process the registration
        if (empty($data['errors'])) {
            // Hash the password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Prepare user data for database
            $userData = [
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => $data['role']
            ];
            
            $userDetailData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'address' => $data['address']
            ];
            
            // Register the user
            if ($this->userModel->register($userData, $userDetailData)) {
                // Registration successful
                SessionHelper::setFlash('success', 'Registration successful! You can now log in.');
                redirect('login');
            } else {
                // Registration failed
                SessionHelper::setFlash('error', 'Something went wrong during registration. Please try again.');
                $this->view('pages/auth/register', $data, 'auth');
            }
        } else {
            // Display errors
            $this->view('pages/auth/register', $data, 'auth');
        }
    }

    /**
     * Displays the staff registration page (for admin only).
     */
    public function registerStaff() {
        // TEMPORARY: Disable role protection for testing
        // $this->requireLogin();
        // if ($_SESSION['user_role'] !== 'admin') {
        //     SessionHelper::setFlash('error', 'Unauthorized access. Only barangay officials can register staff.');
        //     redirect('dashboard');
        // }
        $data = [
            'title' => 'Register Staff',
            'username' => '',
            'first_name' => '',
            'last_name' => '',
            'phone_number' => '',
            'email' => '',
            'address' => '',
            'password' => '',
            'confirm_password' => '',
            'role' => '',
            'errors' => []
        ];
        $this->view('pages/auth/staff/register', $data, 'auth');
    }

    /**
     * Processes the staff registration form (for admin only).
     */
    public function processRegisterStaff() {
        // TEMPORARY: Disable role protection for testing
        // $this->requireLogin();
        // if ($_SESSION['user_role'] !== 'admin') {
        //     SessionHelper::setFlash('error', 'Unauthorized access. Only barangay officials can register staff.');
        //     redirect('dashboard');
        // }
        if (!isPostRequest()) {
            redirect('admin/register-staff');
        }
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $data = [
            'title' => 'Register Staff',
            'username' => trim($_POST['username'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'role' => trim($_POST['role'] ?? ''),
            'errors' => []
        ];
        // Validation (similar to processRegistration, but allow admin/treasurer roles)
        if (empty($data['username'])) {
            $data['errors']['username'] = 'Username is required';
        } elseif (strlen($data['username']) < 4) {
            $data['errors']['username'] = 'Username must be at least 4 characters';
        } elseif ($this->userModel->findByUsername($data['username'])) {
            $data['errors']['username'] = 'Username is already taken';
        }
        if (empty($data['first_name'])) {
            $data['errors']['first_name'] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $data['errors']['last_name'] = 'Last name is required';
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['errors']['email'] = 'Please enter a valid email address';
        }
        if (empty($data['role']) || !in_array($data['role'], ['admin', 'treasurer'])) {
            $data['errors']['role'] = 'Role must be admin or treasurer';
        }
        if (empty($data['password'])) {
            $data['errors']['password'] = 'Password is required';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $data['errors']['confirm_password'] = 'Passwords do not match';
        } else {
            // Password policy enforcement
            $contextWords = [
                $data['username'],
                $data['first_name'],
                $data['last_name'],
                $data['email']
            ];
            $passwordCheck = $this->userModel->validatePasswordStrength($data['password'], $contextWords);
            if ($passwordCheck !== true) {
                $data['errors']['password'] = $passwordCheck;
            }
        }
        if (empty($data['errors'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $userData = [
                'username' => $data['username'],
                'password' => $data['password'],
                'role' => $data['role']
            ];
            $userDetailData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'address' => $data['address']
            ];
            if ($this->userModel->register($userData, $userDetailData)) {
                SessionHelper::setFlash('success', 'Staff registration successful!');
                redirect('admin/register-staff');
            } else {
                SessionHelper::setFlash('error', 'Something went wrong during staff registration. Please try again.');
                $this->view('pages/auth/staff/register', $data, 'main');
            }
        } else {
            $this->view('pages/auth/staff/register', $data, 'main');
        }
    }

}