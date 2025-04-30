<?php

class ProfileController extends Controller {
    private $userModel;

    public function __construct() {
        $this->requireLogin();
        $this->userModel = $this->model('User');
    }

    /**
     * Display the user profile page
     */
    public function index() {
        // Get user data
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('dashboard');
        }
        
        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'errors' => []
        ];
        
        $this->view('pages/profile/index', $data, 'main');
    }

    /**
     * Display the settings page
     */
    public function settings() {
        // Get user data
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('dashboard');
        }
        
        $data = [
            'title' => 'Account Settings',
            'user' => $user,
            'errors' => []
        ];
        
        $this->view('pages/profile/settings', $data, 'main');
    }

    /**
     * Update profile information
     */
    public function updateProfile() {
        if (!isPostRequest()) {
            redirect('profile');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Get user data
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        // Process form data
        $data = [
            'title' => 'My Profile',
            'user' => $user,
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'phone_number' => trim($_POST['phone_number']),
            'email' => trim($_POST['email']),
            'address' => trim($_POST['address']),
            'errors' => []
        ];
        
        // Validate input
        if (empty($data['first_name'])) {
            $data['errors']['first_name'] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $data['errors']['last_name'] = 'Last name is required';
        }
        
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['errors']['email'] = 'Invalid email format';
        }
        
        // Update profile if no errors
        if (empty($data['errors'])) {
            $updateData = [
                'user_id' => $userId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'address' => $data['address']
            ];
            
            if ($this->userModel->updateUserDetails($updateData)) {
                // Update session name if changed
                $_SESSION['user_name'] = $data['first_name'];
                SessionHelper::setFlash('success', 'Profile updated successfully');
                redirect('profile');
            } else {
                SessionHelper::setFlash('error', 'Something went wrong. Please try again.');
                $this->view('pages/profile/index', $data, 'main');
            }
        } else {
            // Show validation errors
            $this->view('pages/profile/index', $data, 'main');
        }
    }

    /**
     * Update user password
     */
    public function updatePassword() {
        if (!isPostRequest()) {
            redirect('profile/settings');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Get user data
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findByUserId($userId);
        
        // Process form data
        $data = [
            'title' => 'Account Settings',
            'user' => $user,
            'current_password' => $_POST['current_password'],
            'new_password' => $_POST['new_password'],
            'confirm_password' => $_POST['confirm_password'],
            'errors' => []
        ];
        
        // Validate input
        if (empty($data['current_password'])) {
            $data['errors']['current_password'] = 'Current password is required';
        } else {
            // Verify current password
            if (!password_verify($data['current_password'], $user->password)) {
                $data['errors']['current_password'] = 'Current password is incorrect';
            }
        }
        
        if (empty($data['new_password'])) {
            $data['errors']['new_password'] = 'New password is required';
        } elseif (strlen($data['new_password']) < 6) {
            $data['errors']['new_password'] = 'Password must be at least 6 characters';
        }
        
        if ($data['new_password'] !== $data['confirm_password']) {
            $data['errors']['confirm_password'] = 'Passwords do not match';
        }
        
        // Update password if no errors
        if (empty($data['errors'])) {
            $hashedPassword = password_hash($data['new_password'], PASSWORD_DEFAULT);
            
            if ($this->userModel->updatePassword($userId, $hashedPassword)) {
                SessionHelper::setFlash('success', 'Password updated successfully');
                redirect('profile/settings');
            } else {
                SessionHelper::setFlash('error', 'Something went wrong. Please try again.');
                $this->view('pages/profile/settings', $data, 'main');
            }
        } else {
            // Show validation errors
            $this->view('pages/profile/settings', $data, 'main');
        }
    }
}