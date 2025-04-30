<?php

class AdminController extends Controller {
    private $userModel;
    private $businessModel;
    
    public function __construct() {
        // Require admin role for all methods in this controller
        $this->requireLogin();
        $this->requireRole(ROLE_ADMIN);
        
        // Load models
        $this->userModel = $this->model('User');
        $this->businessModel = $this->model('Business');
    }
    
    /**
     * Redirect to admin dashboard
     */
    public function index() {
        redirect('dashboard/admin');
    }
    
    /**
     * Display list of all users
     */
    public function users($page = 1) {
        $itemsPerPage = 10;
        $offset = ($page - 1) * $itemsPerPage;
        
        // Get filters from query params
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = isset($_GET['status']) ? (int)$_GET['status'] : null;
        $searchQuery = $_GET['search'] ?? '';
        
        $filters = [];
        if (!empty($roleFilter)) {
            $filters['role'] = $roleFilter;
        }
        if ($statusFilter !== null) {
            $filters['status'] = $statusFilter;
        }
        if (!empty($searchQuery)) {
            $filters['search'] = $searchQuery;
        }
        
        // Get users with pagination and filtering
        $users = $this->userModel->getAllUsers($filters, $itemsPerPage, $offset);
        $totalUsers = $this->userModel->countUsers($filters);
        $totalPages = ceil($totalUsers / $itemsPerPage);
        
        // Count users by role for statistics
        $userCounts = $this->userModel->countUsersByRole();
        
        $data = [
            'title' => 'Manage Users',
            'users' => $users,
            'userCounts' => $userCounts,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalUsers
            ],
            'filters' => [
                'role' => $roleFilter,
                'status' => $statusFilter,
                'search' => $searchQuery
            ]
        ];
        
        $this->view('pages/admin/users', $data, 'main');
    }
    
    /**
     * View user details
     */
    public function viewUser($id) {
        // Get user data
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('admin/users');
            return;
        }
        
        // Get additional data like businesses if user is a business owner
        $businesses = [];
        if ($user->role === ROLE_OWNER) {
            $businesses = $this->businessModel->findByOwnerId($id);
        }
        
        $data = [
            'title' => 'User Details',
            'user' => $user,
            'businesses' => $businesses
        ];
        
        $this->view('pages/admin/view_user', $data, 'main');
    }
    
    /**
     * Edit user
     */
    public function editUser($id) {
        // Get user data
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form submission
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'title' => 'Edit User',
                'user' => $user,
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'phone_number' => trim($_POST['phone_number'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'role' => trim($_POST['role'] ?? $user->role),
                'status' => isset($_POST['status']) ? (int)$_POST['status'] : $user->status,
                'reset_password' => isset($_POST['reset_password']) ? true : false,
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
            
            // Check if role is valid
            $validRoles = [ROLE_ADMIN, ROLE_TREASURER, ROLE_OWNER];
            if (!in_array($data['role'], $validRoles)) {
                $data['errors']['role'] = 'Invalid role selected';
            }
            
            // Update user if no errors
            if (empty($data['errors'])) {
                // Update user role if changed
                if ($data['role'] !== $user->role) {
                    $this->userModel->updateUserRole($id, $data['role']);
                }
                
                // Update user status if changed
                if ($data['status'] !== $user->status) {
                    $this->userModel->updateUserStatus($id, $data['status']);
                }
                
                // Reset password if requested
                if ($data['reset_password']) {
                    // Generate a random password
                    $newPassword = bin2hex(random_bytes(4)); // 8 characters
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // Update the user's password
                    if ($this->userModel->updatePassword($id, $hashedPassword)) {
                        // Send email notification if email is available
                        if (!empty($data['email'])) {
                            // Load email service
                            require_once APPROOT . '/services/EmailService.php';
                            $emailService = new EmailService();
                            
                            $subject = 'Password Reset - Barangay Business Registration System';
                            $body = "Dear {$data['first_name']},\n\n";
                            $body .= "Your password has been reset by an administrator.\n";
                            $body .= "Your new password is: {$newPassword}\n\n";
                            $body .= "Please log in and change your password immediately.\n\n";
                            $body .= "Regards,\nBarangay Business Registration System";
                            
                            $emailService->sendEmail($data['email'], $subject, $body);
                            
                            SessionHelper::setFlash('success', 'Password has been reset and sent to the user\'s email.');
                        } else {
                            SessionHelper::setFlash('success', 'Password has been reset to: ' . $newPassword);
                        }
                    }
                }
                
                // Update user details
                $updateData = [
                    'user_id' => $id,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'email' => $data['email'],
                    'address' => $data['address']
                ];
                
                if ($this->userModel->updateUserDetails($updateData)) {
                    SessionHelper::setFlash('success', 'User updated successfully');
                    redirect('admin/view-user/' . $id);
                } else {
                    SessionHelper::setFlash('error', 'Something went wrong. Please try again.');
                    $this->view('pages/admin/edit_user', $data, 'main');
                }
            } else {
                // Show validation errors
                $this->view('pages/admin/edit_user', $data, 'main');
            }
        } else {
            // Display edit form
            $data = [
                'title' => 'Edit User',
                'user' => $user,
                'errors' => []
            ];
            
            $this->view('pages/admin/edit_user', $data, 'main');
        }
    }
    
    /**
     * Update user status (activate/deactivate)
     */
    public function updateUserStatus($id) {
        // Get user data
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Toggle user status
            $newStatus = ($user->status == 1) ? 0 : 1;
            $statusText = ($newStatus == 1) ? 'activated' : 'deactivated';
            
            if ($this->userModel->updateUserStatus($id, $newStatus)) {
                SessionHelper::setFlash('success', "User has been {$statusText} successfully");
            } else {
                SessionHelper::setFlash('error', 'Failed to update user status');
            }
        }
        
        redirect('admin/view-user/' . $id);
    }
    
    /**
     * Deactivate user account
     */
    public function deactivateUser($id) {
        // Get user data
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->userModel->updateUserStatus($id, 0)) {
                SessionHelper::setFlash('success', 'User has been deactivated successfully');
            } else {
                SessionHelper::setFlash('error', 'Failed to deactivate user');
            }
        }
        
        redirect('admin/view-user/' . $id);
    }
    
    /**
     * Activate user account
     */
    public function activateUser($id) {
        // Get user data
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->userModel->updateUserStatus($id, 1)) {
                SessionHelper::setFlash('success', 'User has been activated successfully');
            } else {
                SessionHelper::setFlash('error', 'Failed to activate user');
            }
        }
        
        redirect('admin/view-user/' . $id);
    }
    
    /**
     * Reset user password to a random one and send it via email
     */
    public function resetUserPassword($id) {
        // Get user data
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            SessionHelper::setFlash('error', 'User not found');
            redirect('admin/users');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Generate a random password
            $newPassword = bin2hex(random_bytes(4)); // 8 characters
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update the user's password
            if ($this->userModel->updatePassword($id, $hashedPassword)) {
                // Load email service
                require_once APPROOT . '/services/EmailService.php';
                $emailService = new EmailService();
                
                // Send the new password via email if the user has an email
                if (!empty($user->email)) {
                    $subject = 'Password Reset - Barangay Business Registration System';
                    $body = "Dear {$user->first_name},\n\n";
                    $body .= "Your password has been reset by an administrator.\n";
                    $body .= "Your new password is: {$newPassword}\n\n";
                    $body .= "Please log in and change your password immediately.\n\n";
                    $body .= "Regards,\nBarangay Business Registration System";
                    
                    $emailService->sendEmail($user->email, $subject, $body);
                    
                    SessionHelper::setFlash('success', 'Password has been reset and sent to the user\'s email.');
                } else {
                    SessionHelper::setFlash('success', 'Password has been reset to: ' . $newPassword);
                }
            } else {
                SessionHelper::setFlash('error', 'Failed to reset password');
            }
        }
        
        redirect('admin/view-user/' . $id);
    }
}