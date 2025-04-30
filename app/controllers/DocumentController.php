<?php

class DocumentController extends Controller {
    private ?Document $documentModel = null;
    private ?Business $businessModel = null;
    private ?User $userModel = null;

    public function __construct() {
        $this->requireLogin();
        $this->documentModel = $this->model('Document');
        $this->businessModel = $this->model('Business');
        $this->userModel = $this->model('User');
        
        if ($this->documentModel === null || $this->businessModel === null || $this->userModel === null) {
            error_log("Failed to load models in DocumentController.");
            die("Critical error: Models could not be loaded.");
        }
    }

    /**
     * Display document upload form for a business
     * 
     * @param int $businessId Business ID
     * @return void
     */
    public function upload($businessId = null) {
        if ($businessId === null) {
            redirect('business/list');
            return;
        }
        
        // Get business details
        $business = $this->businessModel->getBusinessById($businessId);
        
        // Check if business exists
        if (!$business) {
            SessionHelper::setFlash('error', 'Business not found');
            redirect('business/list');
            return;
        }
        
        // Check if user is authorized to upload documents for this business
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        if (strtolower($userRole) !== 'admin' && strtolower($userRole) !== 'treasurer' && $business->user_id !== $userId) {
            SessionHelper::setFlash('error', 'You are not authorized to upload documents for this business');
            redirect('business/list');
            return;
        }
        
        // Get existing documents for this business
        $existingDocuments = $this->documentModel->getDocumentsByBusinessId($businessId);
        
        // Get list of required documents based on business type
        $requiredDocuments = $this->documentModel->getRequiredDocuments($business->type);
        
        // Get missing required documents
        $missingDocuments = $this->documentModel->getMissingRequiredDocuments($businessId, $business->type);
        
        // Handle file upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $documentType = trim($_POST['document_type'] ?? '');
            $documentName = trim($_POST['document_name'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            // Initialize error array
            $errors = [];
            
            // Validate inputs
            if (empty($documentType)) {
                $errors['document_type'] = 'Please select a document type';
            }
            
            if (empty($documentName)) {
                $errors['document_name'] = 'Document name is required';
            }
            
            // Check if file was uploaded
            if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
                $errors['document_file'] = 'Please select a file to upload';
            } else {
                // Validate file type
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                $fileType = $_FILES['document_file']['type'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    $errors['document_file'] = 'Invalid file type. Only PDF, JPG, JPEG, PNG, and GIF files are allowed';
                }
                
                // Validate file size (max 5MB)
                $maxSize = 5 * 1024 * 1024; // 5MB
                if ($_FILES['document_file']['size'] > $maxSize) {
                    $errors['document_file'] = 'File size exceeds the maximum limit of 5MB';
                }
            }
            
            // If no errors, process the upload
            if (empty($errors)) {
                // Create directory if it doesn't exist
                $uploadDir = APPROOT . '/' . UPLOAD_PATH_DOCUMENTS;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Generate unique filename
                $filename = uniqid() . '_' . basename($_FILES['document_file']['name']);
                $filePath = UPLOAD_PATH_DOCUMENTS . $filename;
                
                // Upload file
                if (move_uploaded_file($_FILES['document_file']['tmp_name'], $uploadDir . $filename)) {
                    // Prepare document data
                    $documentData = [
                        'business_id' => $businessId,
                        'uploaded_by' => $userId,
                        'document_type' => $documentType,
                        'document_name' => $documentName,
                        'file_path' => $filePath,
                        'notes' => $notes,
                        'status' => 'Pending' // All uploads start as pending
                    ];
                    
                    // Save document to database
                    if ($this->documentModel->createDocument($documentData)) {
                        SessionHelper::setFlash('success', 'Document uploaded successfully and pending verification');
                        redirect('document/upload/' . $businessId);
                    } else {
                        SessionHelper::setFlash('error', 'Failed to save document information');
                    }
                } else {
                    SessionHelper::setFlash('error', 'Failed to upload document file');
                }
            } else {
                // If there are errors, pass them to the view
                $data = [
                    'title' => 'Upload Document',
                    'business' => $business,
                    'business_id' => $businessId,
                    'required_documents' => $requiredDocuments,
                    'existing_documents' => $existingDocuments,
                    'missing_documents' => $missingDocuments,
                    'document_type' => $documentType,
                    'document_name' => $documentName,
                    'notes' => $notes,
                    'errors' => $errors
                ];
                
                $this->view('pages/document/upload', $data);
                return;
            }
        }
        
        // Prepare view data
        $data = [
            'title' => 'Upload Document',
            'business' => $business,
            'business_id' => $businessId,
            'required_documents' => $requiredDocuments,
            'existing_documents' => $existingDocuments,
            'missing_documents' => $missingDocuments,
            'document_type' => '',
            'document_name' => '',
            'notes' => '',
            'errors' => []
        ];
        
        $this->view('pages/document/upload', $data);
    }

    /**
     * View a specific document
     * 
     * @param int $id Document ID
     * @return void
     */
    public function viewDocument($id = null) {
        if ($id === null) {
            redirect('dashboard');
            return;
        }
        
        // Get document details
        $document = $this->documentModel->getDocumentById($id);
        
        // Check if document exists
        if (!$document) {
            SessionHelper::setFlash('error', 'Document not found');
            redirect('dashboard');
            return;
        }
        
        // Get business details
        $business = $this->businessModel->getBusinessById($document->business_id);
        
        // Check if user is authorized to view this document
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        if (strtolower($userRole) !== 'admin' && strtolower($userRole) !== 'treasurer' && $business->user_id !== $userId) {
            SessionHelper::setFlash('error', 'You are not authorized to view this document');
            redirect('dashboard');
            return;
        }
        
        // Get uploader details
        $uploader = $this->userModel->getUserById($document->uploaded_by);
        
        // Get verifier details if document is verified
        $verifier = null;
        if ($document->verified_by) {
            $verifier = $this->userModel->getUserById($document->verified_by);
        }
        
        // Prepare view data
        $data = [
            'title' => 'View Document',
            'document' => $document,
            'business' => $business,
            'uploader' => $uploader,
            'verifier' => $verifier
        ];
        
        $this->view('pages/document/view', $data);
    }

    /**
     * Delete a document
     * 
     * @param int $id Document ID
     * @return void
     */
    public function delete($id = null) {
        if ($id === null) {
            redirect('dashboard');
            return;
        }
        
        // Get document details
        $document = $this->documentModel->getDocumentById($id);
        
        // Check if document exists
        if (!$document) {
            SessionHelper::setFlash('error', 'Document not found');
            redirect('dashboard');
            return;
        }
        
        // Check if user is authorized to delete this document
        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        
        // Only document uploader, admin, or treasurer can delete
        if ($document->uploaded_by !== $userId && 
            strtolower($userRole) !== 'admin' && 
            strtolower($userRole) !== 'treasurer') {
            SessionHelper::setFlash('error', 'You are not authorized to delete this document');
            redirect('document/viewDocument/' . $id);
            return;
        }
        
        // Cannot delete verified documents unless admin
        if ($document->status === 'Verified' && strtolower($userRole) !== 'admin') {
            SessionHelper::setFlash('error', 'Cannot delete verified documents');
            redirect('document/viewDocument/' . $id);
            return;
        }
        
        // Delete file from server
        $filePath = APPROOT . '/' . $document->file_path;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Delete document from database
        if ($this->documentModel->deleteDocument($id)) {
            SessionHelper::setFlash('success', 'Document deleted successfully');
        } else {
            SessionHelper::setFlash('error', 'Failed to delete document');
        }
        
        redirect('document/upload/' . $document->business_id);
    }

    /**
     * Verify or reject a document (Admin/Treasurer only)
     * 
     * @param int $id Document ID
     * @return void
     */
    public function verify($id = null) {
        // Only admin and treasurer can verify documents
        if (!(strtolower($_SESSION['user_role']) === 'admin' || strtolower($_SESSION['user_role']) === 'treasurer')) {
            SessionHelper::setFlash('error', 'Unauthorized access');
            redirect('dashboard');
            return;
        }
        
        if ($id === null) {
            redirect('dashboard');
            return;
        }
        
        // Get document details
        $document = $this->documentModel->getDocumentById($id);
        
        // Check if document exists
        if (!$document) {
            SessionHelper::setFlash('error', 'Document not found');
            redirect('dashboard');
            return;
        }
        
        // Check if document is already verified or rejected
        if ($document->status !== 'Pending') {
            SessionHelper::setFlash('error', 'Document is already ' . $document->status);
            redirect('document/viewDocument/' . $id);
            return;
        }
        
        // Process verification form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $status = $_POST['status'] ?? '';
            $notes = trim($_POST['notes'] ?? '');
            
            if (!in_array($status, ['Approved', 'Rejected'])) {
                SessionHelper::setFlash('error', 'Invalid status');
                redirect('document/viewDocument/' . $id);
                return;
            }
            
            // Update document status
            if ($this->documentModel->updateDocumentStatus($id, $status, $_SESSION['user_id'], $notes)) {
                SessionHelper::setFlash('success', 'Document ' . strtolower($status) . ' successfully');
            } else {
                SessionHelper::setFlash('error', 'Failed to update document status');
            }
            
            redirect('document/viewDocument/' . $id);
        } else {
            // Direct access without POST - redirect to view
            redirect('document/viewDocument/' . $id);
        }
    }

    /**
     * List all pending documents for verification (Admin/Treasurer only)
     * 
     * @return void
     */
    public function pending() {
        // Only admin and treasurer can access this page
        if (!(strtolower($_SESSION['user_role']) === 'admin' || strtolower($_SESSION['user_role']) === 'treasurer')) {
            SessionHelper::setFlash('error', 'Unauthorized access');
            redirect('dashboard');
            return;
        }
        
        // Get all pending documents
        $pendingDocuments = $this->documentModel->getPendingDocuments();
        
        // Prepare view data
        $data = [
            'title' => 'Pending Documents',
            'documents' => $pendingDocuments
        ];
        
        $this->view('pages/document/pending', $data);
    }

    /**
     * Serve a document file for viewing or download
     * 
     * @param string $filename Document filename
     * @return void
     */
    public function serve($filename = null) {
        if ($filename === null) {
            http_response_code(404);
            echo "File not found";
            return;
        }
        
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        
        // Full path to the file
        $filePath = APPROOT . '/' . UPLOAD_PATH_DOCUMENTS . $filename;
        
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