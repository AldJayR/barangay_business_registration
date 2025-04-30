<?php
class PaymentController extends Controller {
    private $businessModel;
    private $paymentModel;
    private $userModel;

    public function __construct() {
        $this->businessModel = $this->model('Business');
        $this->paymentModel = $this->model('Payment');
        $this->userModel = $this->model('User');
        
        // Check if user is logged in for all methods except publicly accessible ones
        if (!$this->isLoggedIn()) {
            redirect('auth/login');
        }
    }

    // Display payment history
    public function history() {
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['user_role'];
        
        // Get payments based on user role
        if ($role === 'business_owner') {
            // For business owners, get only their business payments
            $payments = $this->paymentModel->getPaymentsByUserId($userId);
        } elseif ($role === 'admin' || $role === 'treasurer') {
            // For admin and treasurer, get all payments
            $payments = $this->paymentModel->getAllPayments();
        } else {
            // Invalid role
            redirect('dashboard');
        }
        
        $data = [
            'title' => 'Payment History',
            'payments' => $payments
        ];
        
        $this->view('pages/payment/history', $data);
    }

    // View payment details
    public function viewPayment($id = null) {
        if ($id === null) {
            redirect('payment/history');
        }
        
        $payment = $this->paymentModel->getPaymentById($id);
        
        // Check if payment exists
        if (!$payment) {
            redirect('payment/history');
        }
        
        // Check if user is authorized to view this payment
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['user_role'];
        
        if ($role === 'business_owner') {
            $business = $this->businessModel->getBusinessById($payment->business_id);
            if ($business->user_id !== $userId) {
                // Not authorized
                redirect('dashboard');
            }
        }
        
        // Get business details
        $business = $this->businessModel->getBusinessById($payment->business_id);
        
        // Get verifier details if payment is verified
        $verifier = null;
        if ($payment->verified_by) {
            $verifier = $this->userModel->getUserById($payment->verified_by);
        }
        
        $data = [
            'title' => 'Payment Details',
            'payment' => $payment,
            'business' => $business,
            'verifier' => $verifier
        ];
        
        $this->view('pages/payment/view', $data);
    }

    // Upload payment proof
    public function upload($businessId = null) {
        if ($businessId === null) {
            redirect('business/list');
        }
        
        $business = $this->businessModel->getBusinessById($businessId);
        
        // Check if business exists
        if (!$business) {
            redirect('business/list');
        }
        
        // Check if user is authorized to upload payment
        $userId = $_SESSION['user_id'];
        if ($business->user_id !== $userId && $_SESSION['user_role'] !== 'treasurer') {
            // Not authorized
            redirect('dashboard');
        }

        // Calculate fees based on business type and other factors
        // This is a simplified calculation; customize as needed
        $paymentAmount = $this->calculatePaymentAmount($business);
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process the submitted form
            $data = [
                'business_id' => $businessId,
                'payment_method' => $_POST['payment_method'] ?? '',
                'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                'amount' => $_POST['amount'] ?? $paymentAmount,
                'payment_status' => 'Pending',
                
                // Error fields
                'payment_method_err' => '',
                'payment_date_err' => '',
                'amount_err' => '',
                'reference_number_err' => '',
                'proof_file_err' => ''
            ];
            
            // Validate payment method
            if (empty($data['payment_method'])) {
                $data['payment_method_err'] = 'Please select a payment method';
            }
            
            // Validate payment date
            if (empty($data['payment_date'])) {
                $data['payment_date_err'] = 'Please enter the payment date';
            }
            
            // Validate amount
            if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
                $data['amount_err'] = 'Please enter a valid payment amount';
            }
            
            // Add conditional fields based on payment method
            if ($data['payment_method'] !== 'cash') {
                $data['reference_number'] = $_POST['reference_number'] ?? '';
                $data['proof_file'] = $_FILES['proof_file'] ?? null;
                
                // Validate reference number for non-cash payments
                if (empty($data['reference_number'])) {
                    $data['reference_number_err'] = 'Please enter a reference number';
                }
                
                // Validate proof file for non-cash payments
                if (empty($data['proof_file']['name'])) {
                    $data['proof_file_err'] = 'Please upload a payment receipt';
                } else {
                    // Check file type
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                    if (!in_array($data['proof_file']['type'], $allowedTypes)) {
                        $data['proof_file_err'] = 'File must be JPEG, PNG, GIF, or PDF';
                    }
                    
                    // Check file size (max 5MB)
                    if ($data['proof_file']['size'] > 5000000) {
                        $data['proof_file_err'] = 'File size must be less than 5MB';
                    }
                }
            }
            
            // Check for errors before proceeding
            $hasErrors = false;
            
            // Check required field errors for all payment methods
            if (!empty($data['payment_method_err']) || 
                !empty($data['payment_date_err']) || 
                !empty($data['amount_err'])) {
                $hasErrors = true;
            }
            
            // For non-cash payments, check additional required fields
            if ($data['payment_method'] !== 'cash') {
                if (!empty($data['reference_number_err']) || !empty($data['proof_file_err'])) {
                    $hasErrors = true;
                }
            }
            
            // If no errors, process the payment
            if (!$hasErrors) {
                // For non-cash payments, handle file upload into app/uploads/proofs
                $proofPath = null;
                if ($data['payment_method'] !== 'cash') {
                    // Make sure storage directory exists & has proper permissions
                    $uploadDir = APPROOT . '/public/' . UPLOAD_PATH_PROOFS;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Generate unique filename and relative path
                    $filename = uniqid() . '_' . basename($data['proof_file']['name']);
                    $proofPath = UPLOAD_PATH_PROOFS . $filename;
                    
                    // Upload the file into app/uploads/proofs
                    if (!move_uploaded_file($data['proof_file']['tmp_name'], APPROOT . '/public/' . $proofPath)) {
                        SessionHelper::setFlash('error', 'Failed to upload file. Please try again.');
                        $data['business'] = $business;
                        $data['business_id'] = $businessId;
                        $data['payment_amount'] = $paymentAmount;
                        $this->view('pages/payment/upload', $data);
                        return;
                    }
                }
                
                // Prepare payment data for database
                $paymentData = [
                    'business_id' => $businessId,
                    'payment_method' => $data['payment_method'],
                    'payment_date' => $data['payment_date'],
                    'amount' => $data['amount'],
                    'payment_status' => 'Pending',
                    // For cash payments, provide a default reference number (required by DB)
                    'reference_number' => $data['payment_method'] !== 'cash' ? $data['reference_number'] : 'CASH-' . time(),
                    'proof_file' => $data['payment_method'] !== 'cash' ? $proofPath : null,
                    'notes' => isset($_POST['notes']) ? $_POST['notes'] : ''
                ];
                
                // Save payment to database
                if ($this->paymentModel->createPayment($paymentData)) {
                    // Update business status to "Payment Verification"
                    $this->businessModel->updateBusinessStatus($businessId, 'Pending Payment Verification');
                    
                    // Set success message without redirecting away from the upload page
                    SessionHelper::setFlash('success', 'Payment submitted successfully. It will be verified by the treasurer.');
                    
                    // Stay on the payment upload page instead of redirecting to business view
                    redirect('payment/upload/' . $businessId);
                } else {
                    // Failed to save payment
                    SessionHelper::setFlash('error', 'Failed to submit payment. Please try again.');
                    $data['business'] = $business;
                    $data['business_id'] = $businessId;
                    $data['payment_amount'] = $paymentAmount;
                    $this->view('pages/payment/upload', $data);
                }
            } else {
                // There are errors, re-render the form with error messages
                $data['business'] = $business;
                $data['business_id'] = $businessId;
                $data['payment_amount'] = $paymentAmount;
                $this->view('pages/payment/upload', $data);
            }
        } else {
            // Initial page load
            $data = [
                'title' => 'Upload Payment',
                'business' => $business,
                'business_id' => $businessId,
                'payment_amount' => $paymentAmount,
                
                // Empty form fields
                'payment_method' => '',
                'payment_date' => date('Y-m-d'),
                'amount' => $paymentAmount,
                'reference_number' => '',
                
                // Empty error fields
                'payment_method_err' => '',
                'payment_date_err' => '',
                'amount_err' => '',
                'reference_number_err' => '',
                'proof_file_err' => ''
            ];
            
            $this->view('pages/payment/upload', $data);
        }
    }
    
    // Helper function to calculate payment amount based on business details
    private function calculatePaymentAmount($business) {
        // This is a simplified calculation; customize as needed based on your requirements
        $baseAmount = 500; // Base fee
        
        // Additional fee based on business type
        $typeMultiplier = 1.0;
        switch (strtolower($business->type)) {
            case 'retail':
                $typeMultiplier = 1.2;
                break;
            case 'service':
                $typeMultiplier = 1.1;
                break;
            case 'manufacturing':
                $typeMultiplier = 1.5;
                break;
            case 'food':
                $typeMultiplier = 1.3;
                break;
            default:
                $typeMultiplier = 1.0;
        }
        
        // Calculate total amount
        $totalAmount = $baseAmount * $typeMultiplier;
        
        // Round to 2 decimal places
        return round($totalAmount, 2);
    }

    // Cancel a pending payment
    public function cancel($id = null) {
        if ($id === null) {
            redirect('payment/history');
        }
        
        $payment = $this->paymentModel->getPaymentById($id);
        
        // Check if payment exists
        if (!$payment) {
            redirect('payment/history');
        }
        
        // Check if payment status is "Pending"
        if (strtolower($payment->payment_status) !== 'pending') {
            SessionHelper::setFlash('error', 'Only pending payments can be cancelled');
            redirect('payment/history');
        }
        
        // Check if user is authorized to cancel this payment
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['user_role'];
        
        if ($role === 'business_owner') {
            $business = $this->businessModel->getBusinessById($payment->business_id);
            if ($business->user_id !== $userId) {
                // Not authorized
                redirect('dashboard');
            }
        }
        
        // Delete the payment
        if ($this->paymentModel->deletePayment($id)) {
            // Update business status back to "Pending Payment"
            $this->businessModel->updateBusinessStatus($payment->business_id, 'Pending Payment');
            
            // Delete proof file if it exists
            if ($payment->proof_file && file_exists(APPROOT . '/../public/proofs/' . $payment->proof_file)) {
                unlink(APPROOT . '/../public/proofs/' . $payment->proof_file);
            }
            
            SessionHelper::setFlash('success', 'Payment cancelled successfully');
        } else {
            SessionHelper::setFlash('error', 'Something went wrong. Please try again.');
        }
        
        redirect('payment/history');
    }

    // Verify a payment (for admin/treasurer)
    public function verify($id = null) {
        // Only admins and treasurers can verify payments
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'treasurer') {
            redirect('dashboard');
        }
        
        if ($id === null) {
            redirect('payment/history');
        }
        
        $payment = $this->paymentModel->getPaymentById($id);
        
        // Check if payment exists
        if (!$payment) {
            redirect('payment/history');
        }
        
        // Check if payment status is "Pending"
        if (strtolower($payment->payment_status) !== 'pending') {
            SessionHelper::setFlash('error', 'Only pending payments can be verified');
            redirect('payment/view/' . $id);
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            
            if ($status === 'Verified' || $status === 'Rejected') {
                // Update payment status
                $updateData = [
                    'payment_status' => $status,
                    'verified_by' => $_SESSION['user_id'],
                    'verified_at' => date('Y-m-d H:i:s'),
                    'notes' => $notes
                ];
                
                if ($this->paymentModel->updatePaymentStatus($id, $updateData)) {
                    // Get business details for email notification
                    $business = $this->businessModel->getBusinessById($payment->business_id);
                    
                    // Update business status based on payment status
                    if ($status === 'Verified') {
                        // If verified, update business status
                        $this->businessModel->updateBusinessStatus($payment->business_id, 'Pending Approval');
                        
                        // Send payment confirmation email
                        if (defined('EMAIL_NOTIFICATIONS_ENABLED') && EMAIL_NOTIFICATIONS_ENABLED) {
                            // Get business owner details
                            $owner = $this->userModel->getUserById($business->user_id);
                            
                            if ($owner && !empty($owner->email)) {
                                require_once APPROOT . '/services/EmailService.php';
                                $emailService = new EmailService();
                                
                                // Construct the receipt URL
                                $receiptUrl = URLROOT . '/payment/receipt/' . $payment->id;
                                
                                // Send payment confirmation email
                                $emailService->sendPaymentConfirmationEmail(
                                    $owner->email,
                                    $business->name,
                                    $payment->amount,
                                    $payment->reference_number,
                                    $receiptUrl
                                );
                                
                                error_log("Payment confirmation email sent to {$owner->email} for business {$business->name}");
                            }
                        }
                        
                        SessionHelper::setFlash('success', 'Payment verified');
                    } else {
                        // If rejected, set business back to Pending Payment
                        $this->businessModel->updateBusinessStatus($payment->business_id, 'Pending Payment');
                        
                        // Send payment rejection email
                        if (defined('EMAIL_NOTIFICATIONS_ENABLED') && EMAIL_NOTIFICATIONS_ENABLED) {
                            // Get business owner details
                            $owner = $this->userModel->getUserById($business->user_id);
                            
                            if ($owner && !empty($owner->email)) {
                                require_once APPROOT . '/services/EmailService.php';
                                $emailService = new EmailService();
                                
                                // Construct the payment URL
                                $paymentUrl = URLROOT . '/payment/view/' . $payment->id;
                                
                                // Prepare rejection message
                                $rejectionMessage = !empty($notes) 
                                    ? "Your payment has been rejected. Reason: $notes" 
                                    : "Your payment has been rejected. Please upload a valid payment proof or contact the office for assistance.";
                                
                                // Send status change email
                                $emailService->sendStatusChangeEmail(
                                    $owner->email,
                                    $business->name,
                                    'Payment Rejected',
                                    $rejectionMessage,
                                    $paymentUrl
                                );
                                
                                error_log("Payment rejection email sent to {$owner->email} for business {$business->name}");
                            }
                        }
                        
                        SessionHelper::setFlash('success', 'Payment rejected and business status updated');
                    }
                    
                    redirect('treasurer/dashboard');
                } else {
                    SessionHelper::setFlash('error', 'Something went wrong. Please try again.');
                    redirect('payment/view/' . $id);
                }
            } else {
                SessionHelper::setFlash('error', 'Invalid status');
                redirect('payment/view/' . $id);
            }
        } else {
            redirect('payment/view/' . $id);
        }
    }

    // Generate payment receipt
    public function receipt($id = null) {
        if ($id === null) {
            redirect('payment/history');
        }
        
        $payment = $this->paymentModel->getPaymentById($id);
        
        // Check if payment exists
        if (!$payment) {
            redirect('payment/history');
        }
        
        // Check if user is authorized to view this receipt
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['user_role'];
        
        if ($role === 'business_owner') {
            $business = $this->businessModel->getBusinessById($payment->business_id);
            if ($business->user_id !== $userId) {
                // Not authorized
                redirect('dashboard');
            }
        }
        
        // Get business details
        $business = $this->businessModel->getBusinessById($payment->business_id);
        
        // Get business owner details
        $owner = $this->userModel->getUserById($business->user_id);
        
        // Get verifier details if payment is verified
        $verifier = null;
        if ($payment->verified_by) {
            $verifier = $this->userModel->getUserById($payment->verified_by);
        }
        
        $data = [
            'title' => 'Payment Receipt',
            'payment' => $payment,
            'business' => $business,
            'owner' => $owner,
            'verifier' => $verifier
        ];
        
        $this->view('pages/payment/receipt', $data);
    }

    // New function to serve proof files
    public function serveProofFile($filename = null) {
        if ($filename === null) {
            http_response_code(404);
            echo "File not found";
            return;
        }

        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);

        // Full path to the file in app/uploads/proofs
        $filePath = APPROOT . '/' . UPLOAD_PATH_PROOFS . $filename;

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "File not found";
            return;
        }
        
        // Get the file extension
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        // Set the appropriate content type
        switch (strtolower($fileExtension)) {
            case 'jpg':
            case 'jpeg':
                header('Content-Type: image/jpeg');
                break;
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
            case 'pdf':
                header('Content-Type: application/pdf');
                break;
            default:
                header('Content-Type: application/octet-stream');
                break;
        }
        
        // Output the file
        readfile($filePath);
        exit;
    }
}