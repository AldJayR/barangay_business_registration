<?php

class TreasurerController extends Controller {
    private ?Payment $paymentModel = null;
    private ?Business $businessModel = null;

    public function __construct() {
        $this->requireLogin();
        $this->requireRole(ROLE_TREASURER);
        
        // Load required models
        $this->paymentModel = $this->model('Payment');
        $this->businessModel = $this->model('Business');
        
        // Basic check if models loaded
        if ($this->paymentModel === null || $this->businessModel === null) {
            error_log("Failed to load required models in TreasurerController.");
            die("Critical error: Required models could not be loaded.");
        }
    }

    /**
     * Verify Payments Page
     * Shows all payments that need verification
     */
    public function verify() {
        $payments = $this->paymentModel->getPendingVerificationPayments();
        
        $data = [
            'title' => 'Verify Payments',
            'payments' => $payments
        ];
        
        $this->view('pages/payment/verify', $data, 'main');
    }

    /**
     * Payment History Page
     * Shows the history of all payments
     */
    public function history() {
        // Get filter parameters
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : null;
        $businessType = isset($_GET['business_type']) ? $_GET['business_type'] : null;
        
        // Set default pagination params
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        // Get payments with filtering and pagination
        $result = $this->paymentModel->getAllPaymentsWithFilters([
            'status' => $status,
            'date_range' => $dateRange,
            'business_type' => $businessType,
            'page' => $page,
            'per_page' => $perPage
        ]);
        
        $payments = $result['payments'];
        $totalPayments = $result['total'];
        $totalPages = ceil($totalPayments / $perPage);
        
        // Get unique statuses for filter dropdown
        $statuses = $this->paymentModel->getDistinctPaymentStatuses();
        
        $data = [
            'title' => 'Payment History',
            'payments' => $payments,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalPayments,
            'filters' => [
                'status' => $status,
                'date_range' => $dateRange,
                'business_type' => $businessType
            ],
            'filterOptions' => [
                'statuses' => $statuses
            ]
        ];
        
        $this->view('pages/payment/history', $data, 'main');
    }

    /**
     * Payment Reports Page
     * Shows payment reports and analytics
     */
    public function reports() {
        // Get statistics
        $totalCollected = $this->paymentModel->getTotalPaymentsAmount();
        $monthlyStats = $this->paymentModel->getMonthlyPaymentsStats();
        $paymentsByMethod = $this->paymentModel->getPaymentsByMethod();
        
        $data = [
            'title' => 'Payment Reports',
            'stats' => [
                'total_collected' => $totalCollected,
                'monthly_stats' => $monthlyStats,
                'payment_methods' => $paymentsByMethod
            ]
        ];
        
        $this->view('pages/payment/reports', $data, 'main');
    }

    /**
     * Handle Payment Verification
     * Process the verification for a specific payment
     */
    public function verifyPayment($id = null) {
        // Check if payment ID is provided
        if ($id === null) {
            SessionHelper::setFlash('error', 'Invalid payment ID');
            redirect('treasurer/verify');
        }
        
        // Check if form was submitted
        if (!isPostRequest()) {
            redirect('treasurer/verify');
        }
        
        // Get the payment
        $payment = $this->paymentModel->getPaymentById($id);
        if (!$payment) {
            SessionHelper::setFlash('error', 'Payment not found');
            redirect('treasurer/verify');
        }
        
        // Get form data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $status = isset($_POST['status']) ? $_POST['status'] : '';
        $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
        
        // Validate status
        if (!in_array($status, ['Verified', 'Rejected'])) {
            SessionHelper::setFlash('error', 'Invalid payment status');
            redirect('payment/view/' . $id);
        }
        
        // Update payment status
        $verified = $this->paymentModel->updatePaymentStatus($id, [
            'payment_status' => $status,
            'verified_by' => $_SESSION['user_id'] ?? null,
            'verified_at' => date('Y-m-d H:i:s'),
            'notes' => $notes
        ]);
        
        if ($verified) {
            // If verified, update business status and generate receipt
            if ($status === 'Verified') {
                $business = $this->businessModel->getBusinessById($payment->business_id);
                if ($business) {
                    $this->businessModel->updateBusinessStatus($business->id, 'Active');
                }
                
                // Generate receipt
                $receiptNumber = $this->paymentModel->generateReceipt($id);
                if ($receiptNumber) {
                    SessionHelper::setFlash('success', "Payment verified and receipt #{$receiptNumber} generated.");
                } else {
                    SessionHelper::setFlash('success', 'Payment has been verified but receipt generation failed.');
                }
            } else {
                SessionHelper::setFlash('success', 'Payment has been ' . strtolower($status));
            }
        } else {
            SessionHelper::setFlash('error', 'Failed to update payment status');
        }
        
        redirect('treasurer/verify');
    }

    /**
     * Dashboard Page
     * Show dashboard overview for treasurer
     */
    public function dashboard() {
        // Get pending payments for the table
        $pendingPayments = $this->paymentModel->getPendingVerificationPayments();
        
        // Get stats for the dashboard cards
        $stats = [
            'pending' => $this->paymentModel->countPaymentsByStatus('Pending'),
            'today' => $this->paymentModel->getTodayPaymentsTotal(),
            'monthly' => $this->paymentModel->getMonthlyPaymentsTotal()
        ];
        
        // Get monthly revenue data for the chart (last 24 months)
        $monthlyStats = $this->paymentModel->getMonthlyPaymentsStats(24);
        
        // Get annual target - could be from a settings table in the future
        $annualTargetPerMonth = 8000; // For now we'll hardcode this
        
        $data = [
            'title' => 'Treasurer Dashboard',
            'payments' => $pendingPayments,
            'stats' => $stats,
            'chart_data' => [
                'monthly_stats' => $monthlyStats,
                'annual_target' => $annualTargetPerMonth
            ]
        ];
        
        $this->view('pages/dashboard/treasurer', $data, 'main');
    }

    /**
     * Generate a payment report in the requested format
     * 
     * @return void
     */
    public function generateReport() {
        // Check if the request is valid
        if (!isPostRequest() && !isset($_GET['type'])) {
            SessionHelper::setFlash('error', 'Invalid report request');
            redirect('treasurer/reports');
            return;
        }
        
        // Get report parameters
        $reportType = $_GET['type'] ?? $_POST['reportType'] ?? 'monthly';
        $reportMonth = $_GET['month'] ?? $_POST['reportMonth'] ?? date('Y-m');
        $reportFormat = $_GET['format'] ?? $_POST['reportFormat'] ?? 'pdf';
        
        // Get report data based on type
        $reportData = [];
        $reportTitle = '';
        
        switch ($reportType) {
            case 'monthly':
                $reportTitle = 'Monthly Revenue Report - ' . date('F Y', strtotime($reportMonth . '-01'));
                $reportData = $this->paymentModel->getDetailedMonthlyReport($reportMonth);
                break;
                
            case 'payment-methods':
                $reportTitle = 'Payment Methods Analysis - ' . date('F Y', strtotime($reportMonth . '-01'));
                $reportData = $this->paymentModel->getPaymentMethodsReport($reportMonth);
                break;
                
            case 'business-types':
                $reportTitle = 'Business Type Distribution - ' . date('F Y', strtotime($reportMonth . '-01'));
                $reportData = $this->paymentModel->getBusinessTypesReport($reportMonth);
                break;
                
            case 'complete':
                $reportTitle = 'Complete Financial Report - ' . date('F Y', strtotime($reportMonth . '-01'));
                $reportData = [
                    'monthly' => $this->paymentModel->getDetailedMonthlyReport($reportMonth),
                    'methods' => $this->paymentModel->getPaymentMethodsReport($reportMonth),
                    'types' => $this->paymentModel->getBusinessTypesReport($reportMonth)
                ];
                break;
                
            default:
                SessionHelper::setFlash('error', 'Invalid report type');
                redirect('treasurer/reports');
                return;
        }
        
        // Generate report based on format
        switch ($reportFormat) {
            case 'pdf':
                $this->generatePdfReport($reportTitle, $reportData, $reportType);
                break;
                
            case 'excel':
                $this->generateExcelReport($reportTitle, $reportData, $reportType);
                break;
                
            case 'csv':
                $this->generateCsvReport($reportTitle, $reportData, $reportType);
                break;
                
            default:
                SessionHelper::setFlash('error', 'Invalid report format');
                redirect('treasurer/reports');
                return;
        }
    }
    
    /**
     * Generate PDF report
     * 
     * @param string $title Report title
     * @param array $data Report data
     * @param string $type Report type
     * @return void
     */
    private function generatePdfReport($title, $data, $type) {
        // Load FPDF library
        require_once APPROOT . '/helpers/fpdf.php';
        
        // Create new PDF document
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        
        // Set document properties
        $pdf->SetTitle($title);
        $pdf->SetAuthor('Barangay Business System');
        
        // Add header
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'BARANGAY FINANCIAL REPORT', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 8, 'Generated on: ' . date('F d, Y'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Generate report content based on type
        switch ($type) {
            case 'monthly':
                $this->generateMonthlyReportContent($pdf, $data);
                break;
                
            case 'payment-methods':
                $this->generatePaymentMethodsReportContent($pdf, $data);
                break;
                
            case 'business-types':
                $this->generateBusinessTypesReportContent($pdf, $data);
                break;
                
            case 'complete':
                $this->generateCompleteReportContent($pdf, $data);
                break;
        }
        
        // Footer
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo() . ' of {nb}', 0, 0, 'C');
        
        // Output the PDF
        $pdf->AliasNbPages();
        $filename = strtolower(str_replace(' ', '_', $title)) . '.pdf';
        $pdf->Output('D', $filename);
        exit;
    }
    
    /**
     * Generate monthly report content for PDF
     * 
     * @param FPDF $pdf PDF object
     * @param array $data Report data
     * @return void
     */
    private function generateMonthlyReportContent($pdf, $data) {
        // Summary section
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'SUMMARY', 0, 1, 'L');
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 8, 'Total Transactions:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, isset($data['summary']) ? $data['summary']->total_transactions : '0', 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 8, 'Total Revenue:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, '₱' . (isset($data['summary']) ? number_format($data['summary']->total_revenue, 2) : '0.00'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 8, 'Average Transaction Amount:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 10);
        
        $avgAmount = 0;
        if (isset($data['summary']) && $data['summary']->total_transactions > 0) {
            $avgAmount = $data['summary']->total_revenue / $data['summary']->total_transactions;
        }
        $pdf->Cell(0, 8, '₱' . number_format($avgAmount, 2), 0, 1, 'L');
        
        $pdf->Ln(5);
        
        // Transactions table
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'TRANSACTION DETAILS', 0, 1, 'L');
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
        
        // Table header
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(30, 8, 'Transaction ID', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Business Name', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Date', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Amount', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Payment Method', 1, 0, 'C');
        $pdf->Cell(30, 8, 'Status', 1, 1, 'C');
        
        // Table data
        $pdf->SetFont('Arial', '', 8);
        
        if (isset($data['transactions']) && !empty($data['transactions'])) {
            foreach ($data['transactions'] as $transaction) {
                // Check if we need a new page
                if ($pdf->GetY() > 250) {
                    $pdf->AddPage();
                    
                    // Reprint the header
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->Cell(30, 8, 'Transaction ID', 1, 0, 'C');
                    $pdf->Cell(40, 8, 'Business Name', 1, 0, 'C');
                    $pdf->Cell(30, 8, 'Date', 1, 0, 'C');
                    $pdf->Cell(30, 8, 'Amount', 1, 0, 'C');
                    $pdf->Cell(30, 8, 'Payment Method', 1, 0, 'C');
                    $pdf->Cell(30, 8, 'Status', 1, 1, 'C');
                    
                    $pdf->SetFont('Arial', '', 8);
                }
                
                $pdf->Cell(30, 8, $transaction->id, 1, 0, 'C');
                $pdf->Cell(40, 8, substr($transaction->business_name, 0, 20), 1, 0, 'L');
                $pdf->Cell(30, 8, date('M d, Y', strtotime($transaction->payment_date)), 1, 0, 'C');
                $pdf->Cell(30, 8, '₱' . number_format($transaction->amount, 2), 1, 0, 'R');
                $pdf->Cell(30, 8, $transaction->payment_method, 1, 0, 'C');
                $pdf->Cell(30, 8, $transaction->status, 1, 1, 'C');
            }
        } else {
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(190, 8, 'No transactions found for this period', 1, 1, 'C');
        }
    }
    
    /**
     * Generate payment methods report content for PDF
     * 
     * @param FPDF $pdf PDF object
     * @param array $data Report data
     * @return void
     */
    private function generatePaymentMethodsReportContent($pdf, $data) {
        // Summary section
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'PAYMENT METHODS BREAKDOWN', 0, 1, 'L');
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
        
        // Table header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 8, 'Payment Method', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Transactions', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Total Amount', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Percentage', 1, 1, 'C');
        
        // Table data
        $pdf->SetFont('Arial', '', 9);
        
        if (isset($data) && !empty($data)) {
            $totalAmount = 0;
            foreach ($data as $method) {
                $totalAmount += $method->total_amount;
            }
            
            foreach ($data as $method) {
                $percentage = ($totalAmount > 0) ? ($method->total_amount / $totalAmount) * 100 : 0;
                
                $pdf->Cell(60, 8, $method->payment_method, 1, 0, 'L');
                $pdf->Cell(40, 8, $method->transaction_count, 1, 0, 'C');
                $pdf->Cell(40, 8, '₱' . number_format($method->total_amount, 2), 1, 0, 'R');
                $pdf->Cell(40, 8, number_format($percentage, 2) . '%', 1, 1, 'C');
            }
            
            // Total row
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(60, 8, 'TOTAL', 1, 0, 'L');
            $pdf->Cell(40, 8, array_sum(array_column($data, 'transaction_count')), 1, 0, 'C');
            $pdf->Cell(40, 8, '₱' . number_format($totalAmount, 2), 1, 0, 'R');
            $pdf->Cell(40, 8, '100.00%', 1, 1, 'C');
        } else {
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(180, 8, 'No payment methods data found for this period', 1, 1, 'C');
        }
    }
    
    /**
     * Generate business types report content for PDF
     * 
     * @param FPDF $pdf PDF object
     * @param array $data Report data
     * @return void
     */
    private function generateBusinessTypesReportContent($pdf, $data) {
        // Summary section
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'BUSINESS TYPES DISTRIBUTION', 0, 1, 'L');
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(2);
        
        // Table header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(60, 8, 'Business Type', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Businesses', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Revenue', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Percentage', 1, 1, 'C');
        
        // Table data
        $pdf->SetFont('Arial', '', 9);
        
        if (isset($data) && !empty($data)) {
            $totalRevenue = 0;
            foreach ($data as $type) {
                $totalRevenue += $type->total_revenue;
            }
            
            foreach ($data as $type) {
                $percentage = ($totalRevenue > 0) ? ($type->total_revenue / $totalRevenue) * 100 : 0;
                
                $pdf->Cell(60, 8, $type->business_type, 1, 0, 'L');
                $pdf->Cell(40, 8, $type->business_count, 1, 0, 'C');
                $pdf->Cell(40, 8, '₱' . number_format($type->total_revenue, 2), 1, 0, 'R');
                $pdf->Cell(40, 8, number_format($percentage, 2) . '%', 1, 1, 'C');
            }
            
            // Total row
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(60, 8, 'TOTAL', 1, 0, 'L');
            $pdf->Cell(40, 8, array_sum(array_column($data, 'business_count')), 1, 0, 'C');
            $pdf->Cell(40, 8, '₱' . number_format($totalRevenue, 2), 1, 0, 'R');
            $pdf->Cell(40, 8, '100.00%', 1, 1, 'C');
        } else {
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(180, 8, 'No business types data found for this period', 1, 1, 'C');
        }
    }
    
    /**
     * Generate complete report content for PDF
     * 
     * @param FPDF $pdf PDF object
     * @param array $data Report data
     * @return void
     */
    private function generateCompleteReportContent($pdf, $data) {
        // Monthly report section
        $this->generateMonthlyReportContent($pdf, $data['monthly']);
        
        // Add a new page for payment methods
        $pdf->AddPage();
        $this->generatePaymentMethodsReportContent($pdf, $data['methods']);
        
        // Add a new page for business types
        $pdf->AddPage();
        $this->generateBusinessTypesReportContent($pdf, $data['types']);
    }
    
    /**
     * Generate Excel report
     * 
     * @param string $title Report title
     * @param array $data Report data
     * @param string $type Report type
     * @return void
     */
    private function generateExcelReport($title, $data, $type) {
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . strtolower(str_replace(' ', '_', $title)) . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Start output buffering
        ob_start();
        
        // Begin Excel XML
        echo '<?xml version="1.0"?>';
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
        
        // Add worksheet based on report type
        switch ($type) {
            case 'monthly':
                $this->generateMonthlyExcelSheet($title, $data);
                break;
                
            case 'payment-methods':
                $this->generatePaymentMethodsExcelSheet($title, $data);
                break;
                
            case 'business-types':
                $this->generateBusinessTypesExcelSheet($title, $data);
                break;
                
            case 'complete':
                $this->generateMonthlyExcelSheet('Monthly Revenue', $data['monthly']);
                $this->generatePaymentMethodsExcelSheet('Payment Methods', $data['methods']);
                $this->generateBusinessTypesExcelSheet('Business Types', $data['types']);
                break;
        }
        
        echo '</Workbook>';
        
        // Send the Excel content
        echo ob_get_clean();
        exit;
    }
    
    /**
     * Generate monthly report Excel worksheet
     * 
     * @param string $title Sheet title
     * @param array $data Report data
     * @return void
     */
    private function generateMonthlyExcelSheet($title, $data) {
        echo '<Worksheet ss:Name="' . $title . '">';
        echo '<Table>';
        
        // Header row
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Generated on: ' . date('F d, Y') . '</Data></Cell>';
        echo '</Row>';
        
        // Empty row
        echo '<Row></Row>';
        
        // Summary section
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">SUMMARY</Data></Cell>';
        echo '</Row>';
        
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Total Transactions:</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . (isset($data['summary']) ? $data['summary']->total_transactions : '0') . '</Data></Cell>';
        echo '</Row>';
        
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Total Revenue:</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . (isset($data['summary']) ? $data['summary']->total_revenue : '0') . '</Data></Cell>';
        echo '</Row>';
        
        $avgAmount = 0;
        if (isset($data['summary']) && $data['summary']->total_transactions > 0) {
            $avgAmount = $data['summary']->total_revenue / $data['summary']->total_transactions;
        }
        
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Average Transaction Amount:</Data></Cell>';
        echo '<Cell><Data ss:Type="Number">' . $avgAmount . '</Data></Cell>';
        echo '</Row>';
        
        // Empty row
        echo '<Row></Row>';
        
        // Transactions table
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">TRANSACTION DETAILS</Data></Cell>';
        echo '</Row>';
        
        // Table header
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Transaction ID</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Business Name</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Date</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Amount</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Payment Method</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Status</Data></Cell>';
        echo '</Row>';
        
        // Table data
        if (isset($data['transactions']) && !empty($data['transactions'])) {
            foreach ($data['transactions'] as $transaction) {
                echo '<Row>';
                echo '<Cell><Data ss:Type="String">' . $transaction->id . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . $transaction->business_name . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . date('M d, Y', strtotime($transaction->payment_date)) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $transaction->amount . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . $transaction->payment_method . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . $transaction->status . '</Data></Cell>';
                echo '</Row>';
            }
        } else {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">No transactions found for this period</Data></Cell>';
            echo '</Row>';
        }
        
        echo '</Table>';
        echo '</Worksheet>';
    }
    
    /**
     * Generate payment methods Excel worksheet
     * 
     * @param string $title Sheet title
     * @param array $data Report data
     * @return void
     */
    private function generatePaymentMethodsExcelSheet($title, $data) {
        echo '<Worksheet ss:Name="' . $title . '">';
        echo '<Table>';
        
        // Header row
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Generated on: ' . date('F d, Y') . '</Data></Cell>';
        echo '</Row>';
        
        // Empty row
        echo '<Row></Row>';
        
        // Summary section
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">PAYMENT METHODS BREAKDOWN</Data></Cell>';
        echo '</Row>';
        
        // Table header
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Payment Method</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Transactions</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Total Amount</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Percentage</Data></Cell>';
        echo '</Row>';
        
        // Table data
        if (isset($data) && !empty($data)) {
            $totalAmount = 0;
            foreach ($data as $method) {
                $totalAmount += $method->total_amount;
            }
            
            foreach ($data as $method) {
                $percentage = ($totalAmount > 0) ? ($method->total_amount / $totalAmount) * 100 : 0;
                
                echo '<Row>';
                echo '<Cell><Data ss:Type="String">' . $method->payment_method . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $method->transaction_count . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $method->total_amount . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $percentage . '</Data></Cell>';
                echo '</Row>';
            }
            
            // Total row
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">TOTAL</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . array_sum(array_column($data, 'transaction_count')) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $totalAmount . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">100</Data></Cell>';
            echo '</Row>';
        } else {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">No payment methods data found for this period</Data></Cell>';
            echo '</Row>';
        }
        
        echo '</Table>';
        echo '</Worksheet>';
    }
    
    /**
     * Generate business types Excel worksheet
     * 
     * @param string $title Sheet title
     * @param array $data Report data
     * @return void
     */
    private function generateBusinessTypesExcelSheet($title, $data) {
        echo '<Worksheet ss:Name="' . $title . '">';
        echo '<Table>';
        
        // Header row
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Generated on: ' . date('F d, Y') . '</Data></Cell>';
        echo '</Row>';
        
        // Empty row
        echo '<Row></Row>';
        
        // Summary section
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">BUSINESS TYPES DISTRIBUTION</Data></Cell>';
        echo '</Row>';
        
        // Table header
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">Business Type</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Businesses</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Revenue</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Percentage</Data></Cell>';
        echo '</Row>';
        
        // Table data
        if (isset($data) && !empty($data)) {
            $totalRevenue = 0;
            foreach ($data as $type) {
                $totalRevenue += $type->total_revenue;
            }
            
            foreach ($data as $type) {
                $percentage = ($totalRevenue > 0) ? ($type->total_revenue / $totalRevenue) * 100 : 0;
                
                echo '<Row>';
                echo '<Cell><Data ss:Type="String">' . $type->business_type . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $type->business_count . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $type->total_revenue . '</Data></Cell>';
                echo '<Cell><Data ss:Type="Number">' . $percentage . '</Data></Cell>';
                echo '</Row>';
            }
            
            // Total row
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">TOTAL</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . array_sum(array_column($data, 'business_count')) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $totalRevenue . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">100</Data></Cell>';
            echo '</Row>';
        } else {
            echo '<Row>';
            echo '<Cell><Data ss:Type="String">No business types data found for this period</Data></Cell>';
            echo '</Row>';
        }
        
        echo '</Table>';
        echo '</Worksheet>';
    }
    
    /**
     * Generate CSV report
     * 
     * @param string $title Report title
     * @param array $data Report data
     * @param string $type Report type
     * @return void
     */
    private function generateCsvReport($title, $data, $type) {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="' . strtolower(str_replace(' ', '_', $title)) . '.csv"');
        
        // Create a file pointer
        $output = fopen('php://output', 'w');
        
        // Add BOM for UTF-8 CSV
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add title and date
        fputcsv($output, [$title]);
        fputcsv($output, ['Generated on:', date('F d, Y')]);
        fputcsv($output, []); // Empty row
        
        // Generate report content based on type
        switch ($type) {
            case 'monthly':
                $this->generateMonthlyCsvContent($output, $data);
                break;
                
            case 'payment-methods':
                $this->generatePaymentMethodsCsvContent($output, $data);
                break;
                
            case 'business-types':
                $this->generateBusinessTypesCsvContent($output, $data);
                break;
                
            case 'complete':
                $this->generateMonthlyCsvContent($output, $data['monthly']);
                fputcsv($output, []); // Empty row
                fputcsv($output, ['PAYMENT METHODS REPORT']);
                $this->generatePaymentMethodsCsvContent($output, $data['methods']);
                fputcsv($output, []); // Empty row
                fputcsv($output, ['BUSINESS TYPES REPORT']);
                $this->generateBusinessTypesCsvContent($output, $data['types']);
                break;
        }
        
        // Close the file pointer
        fclose($output);
        exit;
    }
    
    /**
     * Generate monthly report content for CSV
     * 
     * @param resource $output CSV file pointer
     * @param array $data Report data
     * @return void
     */
    private function generateMonthlyCsvContent($output, $data) {
        // Summary section
        fputcsv($output, ['SUMMARY']);
        fputcsv($output, ['Total Transactions:', isset($data['summary']) ? $data['summary']->total_transactions : '0']);
        fputcsv($output, ['Total Revenue:', '₱' . (isset($data['summary']) ? number_format($data['summary']->total_revenue, 2) : '0.00')]);
        
        $avgAmount = 0;
        if (isset($data['summary']) && $data['summary']->total_transactions > 0) {
            $avgAmount = $data['summary']->total_revenue / $data['summary']->total_transactions;
        }
        fputcsv($output, ['Average Transaction Amount:', '₱' . number_format($avgAmount, 2)]);
        fputcsv($output, []); // Empty row
        
        // Transactions table
        fputcsv($output, ['TRANSACTION DETAILS']);
        
        // Table header
        fputcsv($output, ['Transaction ID', 'Business Name', 'Date', 'Amount', 'Payment Method', 'Status']);
        
        // Table data
        if (isset($data['transactions']) && !empty($data['transactions'])) {
            foreach ($data['transactions'] as $transaction) {
                fputcsv($output, [
                    $transaction->id,
                    $transaction->business_name,
                    date('M d, Y', strtotime($transaction->payment_date)),
                    '₱' . number_format($transaction->amount, 2),
                    $transaction->payment_method,
                    $transaction->status
                ]);
            }
        } else {
            fputcsv($output, ['No transactions found for this period']);
        }
    }
    
    /**
     * Generate payment methods report content for CSV
     * 
     * @param resource $output CSV file pointer
     * @param array $data Report data
     * @return void
     */
    private function generatePaymentMethodsCsvContent($output, $data) {
        // Table header
        fputcsv($output, ['PAYMENT METHODS BREAKDOWN']);
        fputcsv($output, ['Payment Method', 'Transactions', 'Total Amount', 'Percentage']);
        
        // Table data
        if (isset($data) && !empty($data)) {
            $totalAmount = 0;
            foreach ($data as $method) {
                $totalAmount += $method->total_amount;
            }
            
            foreach ($data as $method) {
                $percentage = ($totalAmount > 0) ? ($method->total_amount / $totalAmount) * 100 : 0;
                
                fputcsv($output, [
                    $method->payment_method,
                    $method->transaction_count,
                    '₱' . number_format($method->total_amount, 2),
                    number_format($percentage, 2) . '%'
                ]);
            }
            
            // Total row
            fputcsv($output, [
                'TOTAL',
                array_sum(array_column($data, 'transaction_count')),
                '₱' . number_format($totalAmount, 2),
                '100.00%'
            ]);
        } else {
            fputcsv($output, ['No payment methods data found for this period']);
        }
    }
    
    /**
     * Generate business types report content for CSV
     * 
     * @param resource $output CSV file pointer
     * @param array $data Report data
     * @return void
     */
    private function generateBusinessTypesCsvContent($output, $data) {
        // Table header
        fputcsv($output, ['BUSINESS TYPES DISTRIBUTION']);
        fputcsv($output, ['Business Type', 'Businesses', 'Revenue', 'Percentage']);
        
        // Table data
        if (isset($data) && !empty($data)) {
            $totalRevenue = 0;
            foreach ($data as $type) {
                $totalRevenue += $type->total_revenue;
            }
            
            foreach ($data as $type) {
                $percentage = ($totalRevenue > 0) ? ($type->total_revenue / $totalRevenue) * 100 : 0;
                
                fputcsv($output, [
                    $type->business_type,
                    $type->business_count,
                    '₱' . number_format($type->total_revenue, 2),
                    number_format($percentage, 2) . '%'
                ]);
            }
            
            // Total row
            fputcsv($output, [
                'TOTAL',
                array_sum(array_column($data, 'business_count')),
                '₱' . number_format($totalRevenue, 2),
                '100.00%'
            ]);
        } else {
            fputcsv($output, ['No business types data found for this period']);
        }
    }

    /**
     * Generate and download a receipt for a payment
     * 
     * @param int $id Payment ID
     * @return void
     */
    public function generateReceipt($id = null) {
        // Check if payment ID is provided
        if ($id === null) {
            SessionHelper::setFlash('error', 'Invalid payment ID');
            redirect('treasurer/history');
            return;
        }
        
        // Get payment details for receipt
        $payment = $this->paymentModel->getPaymentDetailsForReceipt($id);
        if (!$payment) {
            SessionHelper::setFlash('error', 'Payment not found or cannot generate receipt');
            redirect('treasurer/history');
            return;
        }
        
        // Check if payment is verified
        if ($payment->payment_status !== 'Verified') {
            SessionHelper::setFlash('error', 'Cannot generate receipt for unverified payment');
            redirect('treasurer/history');
            return;
        }
        
        // Generate receipt number if not already generated
        if (empty($payment->receipt_number)) {
            $receiptNumber = $this->paymentModel->generateReceipt($id);
            if (!$receiptNumber) {
                SessionHelper::setFlash('error', 'Failed to generate receipt number');
                redirect('treasurer/history');
                return;
            }
            
            // Refresh payment details with new receipt number
            $payment = $this->paymentModel->getPaymentDetailsForReceipt($id);
        }
        
        // Create PDF receipt
        $this->generateReceiptPdf($payment);
    }
    
    /**
     * Generate a PDF receipt for a payment
     * 
     * @param object $payment Payment details
     * @return void
     */
    private function generateReceiptPdf($payment) {
        // Load FPDF library
        require_once APPROOT . '/helpers/fpdf.php';
        
        // Create new PDF document
        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        
        // Set document properties
        $pdf->SetTitle('Payment Receipt #' . $payment->receipt_number);
        $pdf->SetAuthor('Barangay Business Registration System');
        
        // Add header with logo (if exists)
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 10, 'BARANGAY BUSINESS REGISTRATION', 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'OFFICIAL RECEIPT', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Receipt number and date
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'Receipt No: ' . $payment->receipt_number, 0, 1, 'R');
        $pdf->Cell(0, 6, 'Date: ' . date('F d, Y', strtotime($payment->receipt_generated_at ?? $payment->verified_at)), 0, 1, 'R');
        $pdf->Ln(5);
        
        // Business information
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Received from:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $payment->business_name, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Address:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $payment->business_address, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Business Type:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $payment->business_type, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Owner:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $payment->owner_name, 0, 1);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(40, 8, 'Registration No:', 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $payment->registration_number, 0, 1);
        $pdf->Ln(5);
        
        // Payment details
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, 'PAYMENT DETAILS', 0, 1, 'L');
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(60, 8, 'Description', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Reference No.', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Payment Method', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Amount', 1, 1, 'C');
        
        // Table data
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 8, 'Business Registration Fee', 1, 0, 'L');
        $pdf->Cell(40, 8, $payment->reference_number, 1, 0, 'C');
        $pdf->Cell(40, 8, $payment->payment_method, 1, 0, 'C');
        $pdf->Cell(40, 8, '₱' . number_format($payment->amount, 2), 1, 1, 'R');
        
        // Total
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(140, 8, 'TOTAL', 1, 0, 'R');
        $pdf->Cell(40, 8, '₱' . number_format($payment->amount, 2), 1, 1, 'R');
        $pdf->Ln(10);
        
        // Amount in words
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(40, 8, 'Amount in Words:', 0, 0);
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->Cell(0, 8, $this->numberToWords($payment->amount) . ' Pesos Only', 0, 1);
        $pdf->Ln(10);
        
        // Verification details
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(0, 8, 'Payment Date: ' . date('F d, Y', strtotime($payment->payment_date)), 0, 1);
        $pdf->Cell(0, 8, 'Verified By: ' . $payment->verified_by_name, 0, 1);
        $pdf->Cell(0, 8, 'Verification Date: ' . date('F d, Y h:i A', strtotime($payment->verified_at)), 0, 1);
        $pdf->Ln(10);
        
        // Signature
        $pdf->Line(30, $pdf->GetY() + 15, 80, $pdf->GetY() + 15);
        $pdf->Line(120, $pdf->GetY() + 15, 170, $pdf->GetY() + 15);
        $pdf->SetY($pdf->GetY() + 15);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(80, 6, 'Barangay Treasurer', 0, 0, 'C');
        $pdf->Cell(40, 6, '', 0, 0);
        $pdf->Cell(80, 6, 'Business Owner/Representative', 0, 1, 'C');
        
        // Footer
        $pdf->SetY(-25);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, 'This receipt is computer-generated and does not require a signature to be valid.', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Barangay Business Registration System © ' . date('Y'), 0, 1, 'C');
        
        // Output PDF
        $filename = 'Receipt_' . $payment->receipt_number . '.pdf';
        $pdf->Output('D', $filename);
        exit;
    }
    
    /**
     * Convert a number to words (for receipt amount in words)
     * 
     * @param float $number Number to convert
     * @return string The number in words
     */
    private function numberToWords($number) {
        $ones = [
            0 => "",
            1 => "One",
            2 => "Two",
            3 => "Three",
            4 => "Four",
            5 => "Five",
            6 => "Six",
            7 => "Seven",
            8 => "Eight",
            9 => "Nine",
            10 => "Ten",
            11 => "Eleven",
            12 => "Twelve",
            13 => "Thirteen",
            14 => "Fourteen",
            15 => "Fifteen",
            16 => "Sixteen",
            17 => "Seventeen",
            18 => "Eighteen",
            19 => "Nineteen"
        ];
        
        $tens = [
            0 => "",
            2 => "Twenty",
            3 => "Thirty",
            4 => "Forty",
            5 => "Fifty",
            6 => "Sixty",
            7 => "Seventy",
            8 => "Eighty",
            9 => "Ninety"
        ];
        
        $number = number_format($number, 2, '.', '');
        $numberParts = explode('.', $number);
        $wholeNumber = (int)$numberParts[0];
        $decimal = (int)$numberParts[1];
        
        if ($wholeNumber == 0) {
            return "Zero";
        }
        
        $words = "";
        
        if ((int)($wholeNumber / 1000) > 0) {
            $words .= $this->numberToWords((int)($wholeNumber / 1000)) . " Thousand ";
            $wholeNumber = $wholeNumber % 1000;
        }
        
        if ((int)($wholeNumber / 100) > 0) {
            $words .= $ones[(int)($wholeNumber / 100)] . " Hundred ";
            $wholeNumber = $wholeNumber % 100;
        }
        
        if ($wholeNumber > 0) {
            if ($words != "") {
                $words .= "and ";
            }
            
            if ($wholeNumber < 20) {
                $words .= $ones[$wholeNumber];
            } else {
                $words .= $tens[(int)($wholeNumber / 10)];
                if ($wholeNumber % 10 > 0) {
                    $words .= "-" . $ones[$wholeNumber % 10];
                }
            }
        }
        
        // Add decimal part if not zero
        if ($decimal > 0) {
            $words .= " and " . $decimal . "/100";
        }
        
        return $words;
    }
    
    /**
     * View a list of receipts (accessible from the payment history)
     * 
     * @return void
     */
    public function receipts() {
        // Set default pagination params
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10;
        
        // Get receipts with pagination
        $result = $this->paymentModel->getReceiptsWithFilters([
            'page' => $page,
            'per_page' => $perPage
        ]);
        
        $receipts = $result['receipts'];
        $totalReceipts = $result['total'];
        $totalPages = ceil($totalReceipts / $perPage);
        
        $data = [
            'title' => 'Payment Receipts',
            'receipts' => $receipts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalReceipts
        ];
        
        $this->view('pages/payment/receipts', $data, 'main');
    }
}