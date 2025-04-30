<?php

class PermitController extends Controller {
    private $businessModel;
    private $userModel;

    public function __construct() {
        $this->requireLogin();
        $this->businessModel = $this->model('Business');
        $this->userModel = $this->model('User');
    }

    /**
     * Generate and download a business permit as PDF
     * 
     * @param int $id Business ID
     * @return void
     */
    public function generate($id = null) {
        // Check if ID is provided
        if ($id === null) {
            SessionHelper::setFlash('error', 'No business ID specified');
            redirect('dashboard');
            return;
        }

        // Get business information
        $business = $this->businessModel->getBusinessById($id);
        if (!$business) {
            SessionHelper::setFlash('error', 'Business not found');
            redirect('dashboard');
            return;
        }

        // Check authorization
        $isOwner = ($_SESSION['user_id'] == $business->user_id);
        $isAdmin = ($_SESSION['user_role'] == 'admin');
        $isTreasurer = ($_SESSION['user_role'] == 'treasurer');

        if (!($isOwner || $isAdmin || $isTreasurer)) {
            SessionHelper::setFlash('error', 'You are not authorized to access this permit');
            redirect('dashboard');
            return;
        }

        // Check if business is approved
        if ($business->status !== 'Active') {
            SessionHelper::setFlash('error', 'Cannot generate permit: Business is not approved');
            redirect('business/view/' . $id);
            return;
        }

        // Get permit information
        $permit = $this->businessModel->getPermitByBusinessId($id);
        if (!$permit) {
            SessionHelper::setFlash('error', 'Permit information not found');
            redirect('business/view/' . $id);
            return;
        }

        // Get owner information
        $owner = $this->userModel->getUserById($business->user_id);
        if (!$owner) {
            SessionHelper::setFlash('error', 'Owner information not found');
            redirect('business/view/' . $id);
            return;
        }

        // Get issuer information
        $issuer = $this->userModel->getUserById($permit->issued_by);
        if (!$issuer) {
            SessionHelper::setFlash('error', 'Issuer information not found');
            redirect('business/view/' . $id);
            return;
        }

        // Generate the permit PDF
        $this->generatePermitPDF($business, $permit, $owner, $issuer);
    }

    /**
     * Generate the actual permit PDF using FPDF
     * 
     * @param object $business Business data
     * @param object $permit Permit data
     * @param object $owner Owner data
     * @param object $issuer Issuer data
     * @return void
     */
    private function generatePermitPDF($business, $permit, $owner, $issuer) {
        // Load FPDF library
        require_once APPROOT . '/helpers/fpdf.php';

        // Create new PDF document
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();

        // Set document properties
        $pdf->SetTitle('Business Permit - ' . $business->name);
        $pdf->SetAuthor('Barangay Business System');

        // Add header
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'REPUBLIC OF THE PHILIPPINES', 0, 1, 'C');
        $pdf->Cell(0, 10, 'CITY/MUNICIPALITY OF CABANATUAN', 0, 1, 'C');
        $pdf->Cell(0, 10, 'OFFICE OF THE BARANGAY CAPTAIN', 0, 1, 'C');
        $pdf->Ln(10);

        // Add title
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 15, 'BARANGAY BUSINESS PERMIT', 0, 1, 'C');
        $pdf->Ln(5);

        // Add permit number
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Permit No: ' . $permit->permit_number, 0, 1, 'C');
        $pdf->Ln(10);

        // Add content
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 8, 'This certifies that the business establishment described hereunder has complied with the requirements of the Barangay and is hereby granted this permit to operate within the territorial jurisdiction of this Barangay.', 0, 'L');
        $pdf->Ln(5);

        // Business information
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'BUSINESS INFORMATION', 0, 1, 'L');
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        // Two-column layout for business details
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, 'Business Name:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, $business->name, 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, 'Business Type:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, $business->type, 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, 'Business Address:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, $business->address, 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, 'Owner:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, $owner->first_name . ' ' . $owner->last_name, 0, 1, 'L');

        $pdf->Ln(5);

        // Permit validity
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'PERMIT VALIDITY', 0, 1, 'L');
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, 'Issued Date:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, date('F d, Y', strtotime($permit->issued_date)), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(50, 8, 'Expiration Date:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, date('F d, Y', strtotime($permit->expiration_date)), 0, 1, 'L');

        $pdf->Ln(10);

        // Important notice
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 8, 'IMPORTANT NOTICE:', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, '1. This permit must be displayed in a conspicuous place within the business premises.
2. This permit is not transferable and shall be valid only for the business for which it was issued.
3. This permit shall be valid until the expiration date indicated herein.
4. Any violation of applicable laws and ordinances shall be grounds for the revocation or non-renewal of this permit.', 0, 'L');

        $pdf->Ln(10);

        // Signatures
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(95, 8, 'APPROVED BY:', 0, 0, 'L');
        $pdf->Cell(0, 8, 'RECEIVED BY:', 0, 1, 'L');
        $pdf->Ln(15);

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(95, 8, strtoupper($issuer->first_name . ' ' . $issuer->last_name), 0, 0, 'L');
        $pdf->Cell(0, 8, strtoupper($owner->first_name . ' ' . $owner->last_name), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 8, 'Barangay Captain/Authorized Representative', 0, 0, 'L');
        $pdf->Cell(0, 8, 'Business Owner/Authorized Representative', 0, 1, 'L');

        $pdf->Ln(10);

        // Official seal placeholder
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 8, 'OFFICIAL SEAL', 0, 1, 'L');

        // Add QR code placeholder (in a real implementation, generate an actual QR code)
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 8, 'Scan to verify permit authenticity', 0, 1, 'R');
        
        // Save the PDF file
        $filePath = APPROOT . '/public/uploads/permits/';
        if (!is_dir($filePath)) {
            mkdir($filePath, 0777, true);
        }
        
        $fileName = 'permit_' . $business->id . '.pdf';
        $fullPath = $filePath . $fileName;
        
        // First save the file to the server
        $pdf->Output('F', $fullPath);
        
        // Then output for download
        $pdf->Output('D', 'Business_Permit_' . $business->name . '.pdf');
        exit;
    }

    /**
     * View or download an existing permit
     * 
     * @param int $id Business ID
     * @return void
     */
    public function viewPermit($id = null) {
        // Check if ID is provided
        if ($id === null) {
            SessionHelper::setFlash('error', 'No business ID specified');
            redirect('dashboard');
            return;
        }

        // Get business information
        $business = $this->businessModel->getBusinessById($id);
        if (!$business) {
            SessionHelper::setFlash('error', 'Business not found');
            redirect('dashboard');
            return;
        }

        // Check authorization
        $isOwner = ($_SESSION['user_id'] == $business->user_id);
        $isAdmin = ($_SESSION['user_role'] == 'admin');
        $isTreasurer = ($_SESSION['user_role'] == 'treasurer');

        if (!($isOwner || $isAdmin || $isTreasurer)) {
            SessionHelper::setFlash('error', 'You are not authorized to access this permit');
            redirect('dashboard');
            return;
        }

        // Check if business is approved
        if ($business->status !== 'Active') {
            SessionHelper::setFlash('error', 'No permit available: Business is not approved');
            redirect('business/view/' . $id);
            return;
        }

        // Get permit information
        $permit = $this->businessModel->getPermitByBusinessId($id);
        if (!$permit) {
            SessionHelper::setFlash('error', 'Permit information not found');
            redirect('business/view/' . $id);
            return;
        }

        // Check if permit file exists
        $filePath = APPROOT . '/../public/permits/' . $permit->permit_file;
        if (file_exists($filePath)) {
            // Serve the existing file
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . 'Business_Permit_' . $business->name . '.pdf' . '"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            readfile($filePath);
            exit;
        } else {
            // Generate a new permit if the file doesn't exist
            $this->generate($id);
        }
    }
}