<?php

class BusinessController extends Controller {
    private ?Business $businessModel = null;

    public function __construct() {
        $this->requireLogin();
        $this->businessModel = $this->model('Business');
        
        if ($this->businessModel === null) {
            error_log("Failed to load Business model in BusinessController.");
            die("Critical error: Business model could not be loaded.");
        }
    }

    /**
     * Display the business permit application form
     */
    public function apply() {
        // Only business owners can apply for a permit
        $this->requireRole(ROLE_OWNER);
        
        $data = [
            'title' => 'Apply for Business Permit',
            'formData' => [
                'name' => '',
                'type' => '',
                'address' => '',
                'owner_first_name' => $_SESSION['user_first_name'] ?? '',
                'owner_last_name' => $_SESSION['user_last_name'] ?? '',
                'owner_address' => $_SESSION['user_address'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'phone' => $_SESSION['user_phone'] ?? ''
            ],
            'errors' => []
        ];
        
        $this->view('pages/business/apply', $data, 'main');
    }

    /**
     * Process the business permit application
     */
    public function submitApplication() {
        // Only business owners can apply for a permit
        $this->requireRole(ROLE_OWNER);
        
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('business/apply');
        }
        
        // Process the submitted form
        $formData = [
            'name' => trim($_POST['business_name'] ?? ''),
            'type' => trim($_POST['business_type'] ?? ''),
            'address' => trim($_POST['business_address'] ?? ''),
            'owner_first_name' => trim($_POST['owner_first_name'] ?? ''),
            'owner_last_name' => trim($_POST['owner_last_name'] ?? ''),
            'owner_address' => trim($_POST['owner_address'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];
        
        // Validate form data
        $errors = [];
        
        if (empty($formData['name'])) {
            $errors['name'] = 'Business name is required';
        }
        
        if (empty($formData['type'])) {
            $errors['type'] = 'Business type is required';
        }
        
        if (empty($formData['address'])) {
            $errors['address'] = 'Business address is required';
        }
        
        if (empty($formData['owner_first_name'])) {
            $errors['owner_first_name'] = 'Owner first name is required';
        }
        
        if (empty($formData['owner_last_name'])) {
            $errors['owner_last_name'] = 'Owner last name is required';
        }
        
        if (empty($formData['owner_address'])) {
            $errors['owner_address'] = 'Owner address is required';
        }
        
        if (empty($formData['email'])) {
            $errors['email'] = 'Email address is required';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        if (empty($formData['phone'])) {
            $errors['phone'] = 'Phone number is required';
        }
        
        // If there are validation errors, redisplay the form
        if (!empty($errors)) {
            $data = [
                'title' => 'Apply for Business Permit',
                'formData' => $formData,
                'errors' => $errors
            ];
            
            $this->view('pages/business/apply', $data, 'main');
            return;
        }
        
        // Prepare data for database
        $businessData = [
            'user_id' => $_SESSION['user_id'],
            'name' => $formData['name'],
            'type' => $formData['type'],
            'address' => $formData['address'],
            'status' => 'Pending Payment Verification'
        ];
        
        // Save business information
        $businessId = $this->businessModel->create($businessData);
        
        if ($businessId) {
            // TODO: Save additional business details to a business_details table if needed
            
            // Set success flash message
            SessionHelper::setFlash('success', 'Your business permit application has been submitted successfully. Please wait for approval.');
            
            // Redirect to my applications page
            redirect('business/applications');
        } else {
            // Set error flash message
            SessionHelper::setFlash('error', 'Something went wrong. Please try again.');
            
            $data = [
                'title' => 'Apply for Business Permit',
                'formData' => $formData,
                'errors' => ['system' => 'Failed to submit your application. Please try again.']
            ];
            
            $this->view('pages/business/apply', $data, 'main');
        }
    }

    /**
     * List all business applications for the current user
     */
    public function applications() {
        // Only business owners can view their applications
        $this->requireRole(ROLE_OWNER);
        
        $businesses = $this->businessModel->findByOwnerId($_SESSION['user_id']);
        
        $data = [
            'title' => 'My Applications',
            'businesses' => $businesses
        ];
        
        $this->view('pages/business/applications', $data, 'main');
    }

    /**
     * View details of a specific business
     */
    public function viewBusiness($id = null) {
        // Ensure an ID was provided
        if ($id === null) {
            SessionHelper::setFlash('error', 'No business ID specified');
            redirect('business/list');
            return;
        }
        
        if ($this->hasRole(ROLE_OWNER)) {
            $business = $this->businessModel->findByIdAndOwner($id, $_SESSION['user_id']);
            if (!$business) {
                SessionHelper::setFlash('error', 'Business not found or you do not have permission to view it');
                redirect('business/list');
                return;
            }
        } else if ($this->hasRole(ROLE_ADMIN) || $this->hasRole(ROLE_TREASURER)) {
            $business = $this->businessModel->getBusinessById($id);
            if (!$business) {
                SessionHelper::setFlash('error', 'Business not found');
                redirect('business/list');
                return;
            }
        } else {
            SessionHelper::setFlash('error', 'You do not have permission to view this business');
            redirect('dashboard');
            return;
        }
        
        $data = [
            'title' => 'Business Details',
            'business' => $business
        ];
        
        $this->view('pages/business/view', $data, 'main');
    }

    /**
     * Display a list of businesses for the current user or all if admin
     */
    public function list() {
        if ($this->hasRole(ROLE_ADMIN)) {
            // Admin: show all businesses
            $businesses = $this->businessModel->findAll();
            $title = 'All Businesses';
        } else {
            // Only business owners can view their business list
            $this->requireRole(ROLE_OWNER);
            $businesses = $this->businessModel->findByOwnerId($_SESSION['user_id']);
            $title = 'My Businesses';
        }
        $data = [
            'title' => $title,
            'businesses' => $businesses
        ];
        $this->view('pages/business/list', $data, 'main');
    }

    /**
     * Approve a business application (Admin/Treasurer only)
     */
    public function approve($id = null) {
        if (!(
            $this->hasRole(ROLE_ADMIN) || $this->hasRole(ROLE_TREASURER)
        )) {
            SessionHelper::setFlash('error', 'Unauthorized access.');
            redirect('dashboard');
            return;
        }
        if ($id === null) {
            SessionHelper::setFlash('error', 'No business ID specified');
            redirect('business/list');
            return;
        }
        $business = $this->businessModel->getBusinessById($id);
        if (!$business) {
            SessionHelper::setFlash('error', 'Business not found');
            redirect('business/list');
            return;
        }
        // Fetch latest payment status using the new model method
        $latestPaymentStatus = $this->businessModel->getLatestPaymentStatus($id);
        error_log('DEBUG: latest_payment_status for business ID ' . $id . ' is: ' . var_export($latestPaymentStatus, true));
        // Only allow approval if payment is verified
        if (isset($latestPaymentStatus) && strtolower($latestPaymentStatus) === 'verified') {
            $this->businessModel->updateBusinessStatus($id, 'Active');

            // Generate permit number
            $permitNumber = 'BP-' . str_pad($business->id, 5, '0', STR_PAD_LEFT) . '-' . date('Y');
            
            // Create permits directory if it doesn't exist
            $permitDir = APPROOT . '/../public/permits';
            if (!is_dir($permitDir)) {
                mkdir($permitDir, 0777, true);
            }
            
            // Prepare permit data
            $permitData = [
                'business_id' => $business->id,
                'permit_number' => $permitNumber,
                'issued_by' => $_SESSION['user_id'],
                'issued_date' => date('Y-m-d'),
                'expiration_date' => date('Y-m-d', strtotime('+1 year')),
                'permit_file' => 'permit_' . $business->id . '.pdf'
            ];

            // Save permit data to database
            $permitId = $this->businessModel->createPermit($permitData);

            // Send email notification for application approval
            if (defined('EMAIL_NOTIFICATIONS_ENABLED') && EMAIL_NOTIFICATIONS_ENABLED) {
                // Get the business owner's email
                $userModel = $this->model('User');
                $owner = $userModel->findById($business->user_id);
                
                if ($owner && !empty($owner->email)) {
                    require_once APPROOT . '/services/EmailService.php';
                    $emailService = new EmailService();
                    
                    // Construct the permit URL
                    $permitUrl = URLROOT . '/permit/viewPermit/' . $business->id;
                    
                    // Send permit approval email
                    $emailService->sendPermitApprovalEmail(
                        $owner->email,
                        $business->name,
                        $permitNumber,
                        $permitData['expiration_date'],
                        $permitUrl
                    );
                    
                    error_log("Approval email sent to {$owner->email} for business {$business->name}");
                }
            }

            SessionHelper::setFlash('success', 'Business application approved. A business permit has been generated.');
            
            // Redirect to view the permit
            redirect('permit/viewPermit/' . $id);
        } else {
            SessionHelper::setFlash('error', 'Cannot approve: Payment not verified.');
            redirect('business/view/' . $id);
        }
    }

    /**
     * Reject a business application (Admin/Treasurer only)
     */
    public function reject($id = null) {
        if (!(
            $this->hasRole(ROLE_ADMIN) || $this->hasRole(ROLE_TREASURER)
        )) {
            SessionHelper::setFlash('error', 'Unauthorized access.');
            redirect('dashboard');
            return;
        }
        if ($id === null) {
            SessionHelper::setFlash('error', 'No business ID specified');
            redirect('business/list');
            return;
        }
        $business = $this->businessModel->getBusinessById($id);
        if (!$business) {
            SessionHelper::setFlash('error', 'Business not found');
            redirect('business/list');
            return;
        }
        // Fetch latest payment status using the new model method
        $latestPaymentStatus = $this->businessModel->getLatestPaymentStatus($id);
        error_log('DEBUG: latest_payment_status for business ID ' . $id . ' is: ' . var_export($latestPaymentStatus, true));
        // Only allow rejection if payment is verified
        if (isset($latestPaymentStatus) && strtolower($latestPaymentStatus) === 'verified') {
            $this->businessModel->updateBusinessStatus($id, 'Rejected');
            
            // Get rejection reason if provided
            $rejectionReason = trim($_POST['rejection_reason'] ?? '');
            if (empty($rejectionReason)) {
                $rejectionReason = 'Your application did not meet the requirements for a business permit.';
            }
            
            // Send email notification for application rejection
            if (defined('EMAIL_NOTIFICATIONS_ENABLED') && EMAIL_NOTIFICATIONS_ENABLED) {
                // Get the business owner's email
                $userModel = $this->model('User');
                $owner = $userModel->findById($business->user_id);
                
                if ($owner && !empty($owner->email)) {
                    require_once APPROOT . '/services/EmailService.php';
                    $emailService = new EmailService();
                    
                    // Construct the business details URL
                    $businessUrl = URLROOT . '/business/view/' . $business->id;
                    
                    // Send status change email
                    $emailService->sendStatusChangeEmail(
                        $owner->email,
                        $business->name,
                        'Rejected',
                        "We regret to inform you that your application has been rejected. Reason: $rejectionReason",
                        $businessUrl
                    );
                    
                    error_log("Rejection email sent to {$owner->email} for business {$business->name}");
                }
            }
            
            SessionHelper::setFlash('success', 'Business application rejected.');
        } else {
            SessionHelper::setFlash('error', 'Cannot reject: Payment not verified.');
        }
        redirect('business/view/' . $id);
    }
}