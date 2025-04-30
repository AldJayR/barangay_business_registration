<?php

class DashboardController extends Controller {

    private ?Business $businessModel = null;
    private ?Payment $paymentModel = null;

    public function __construct() {
        $this->requireLogin();
        // Load models needed for dashboards
        $this->businessModel = $this->model('Business');
        $this->paymentModel = $this->model('Payment');

        // Basic check if model loaded
        if ($this->businessModel === null || $this->paymentModel === null) {
            error_log("Failed to load required models in DashboardController.");
            die("Critical error: Required models could not be loaded.");
        }
    }

    /**
     * Legacy method to support old dashboard URL
     * Redirects to the appropriate dashboard based on user role
     */
    public function redirectToDashboard() {
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
     * Original index method that routes based on user role
     * Kept for backward compatibility
     */
    public function index() {
        $this->redirectToDashboard();
    }

    /**
     * Owner Dashboard
     */
    public function ownerDashboard() {
        $this->requireRole(ROLE_OWNER);
        
        try {
            // Fetch businesses owned by the logged-in user
            $ownerId = $_SESSION['user_id'];
            $businesses = $this->businessModel->findByOwnerId($ownerId);
            
            $data = [
                'title' => 'Owner Dashboard',
                'businesses' => $businesses, // Pass the fetched businesses to the view
                'stats' => [
                    'pending' => count(array_filter($businesses, function($business) {
                        return strtolower($business->status ?? '') === 'pending approval' 
                            || strtolower($business->status ?? '') === 'pending payment';
                    })),
                    'active' => count(array_filter($businesses, function($business) {
                        return strtolower($business->status ?? '') === 'active';
                    }))
                ]
            ];
            
            $this->view('pages/dashboard/owner', $data, 'main');
        } catch (Exception $e) {
            // Log the error for debugging but show a friendly message
            error_log("Error in owner dashboard: " . $e->getMessage());
            
            // Create a default data array with empty businesses
            $data = [
                'title' => 'Owner Dashboard',
                'businesses' => [],
                'stats' => [
                    'pending' => 0,
                    'active' => 0
                ],
                'error' => 'We had trouble loading your businesses. Please try again later.'
            ];
            
            // Display the dashboard with error message
            $this->view('pages/dashboard/owner', $data, 'main');
        }
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboard() {
        $this->requireRole(ROLE_ADMIN);
        // Fetch pending applications (Pending Approval or Pending Payment)
        $pendingStatuses = ['Pending Approval', 'Pending Payment', 'Pending Payment Verification'];
        $applications = $this->businessModel->findAll(['status' => $pendingStatuses]);
        $data = [
            'title' => 'Admin Dashboard',
            'applications' => $applications,
            'stats' => [
                'pending' => count($applications),
                'active' => $this->businessModel->findAll(['status' => 'Active']) ? count($this->businessModel->findAll(['status' => 'Active'])) : 0,
                // Add more stats as needed
            ]
        ];
        $this->view('pages/dashboard/admin', $data, 'main');
    }

    /**
     * Treasurer Dashboard
     */
    public function treasurerDashboard() {
        $this->requireRole(ROLE_TREASURER);
        
        // Get pending payments
        $payments = $this->paymentModel->getPendingVerificationPayments();
        
        // Get statistics for dashboard cards
        $stats = [
            'pending' => $this->paymentModel->countPaymentsByStatus('Pending Verification'),
            'today' => $this->paymentModel->getTodayPaymentsTotal(),
            'monthly' => $this->paymentModel->getMonthlyPaymentsTotal()
        ];
        
        $data = [
            'title' => 'Treasurer Dashboard',
            'payments' => $payments,
            'stats' => $stats
        ];
        
        // Debug information if no payments are found
        if (empty($payments)) {
            error_log("No pending verification payments found in treasurer dashboard");
        }
        
        $this->view('pages/dashboard/treasurer', $data, 'main');
    }
}