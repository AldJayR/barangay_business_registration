<?php
class Payment {
    private $db;

    // Receipt number prefix - can be customized
    private const RECEIPT_PREFIX = 'BBR';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Get all payments
    public function getAllPayments() {
        $this->db->query('
            SELECT p.*, b.name as business_name 
            FROM payment p
            JOIN business b ON p.business_id = b.id
            ORDER BY p.created_at DESC
        ');
        
        return $this->db->resultSet();
    }

    // Get payments by user ID
    public function getPaymentsByUserId($userId) {
        $this->db->query('
            SELECT p.*, b.name as business_name 
            FROM payment p
            JOIN business b ON p.business_id = b.id
            WHERE b.user_id = :user_id
            ORDER BY p.created_at DESC
        ');
        
        $this->db->bind(':user_id', $userId);
        
        return $this->db->resultSet();
    }

    // Get payment by ID
    public function getPaymentById($id) {
        $this->db->query('SELECT * FROM payment WHERE id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->single();
    }

    // Create payment
    public function createPayment($data) {
        $this->db->query('
            INSERT INTO payment 
            (business_id, reference_number, payment_method, payment_status, amount, proof_file, payment_date, notes) 
            VALUES
            (:business_id, :reference_number, :payment_method, :payment_status, :amount, :proof_file, :payment_date, :notes)
        ');
        
        // Bind values
        $this->db->bind(':business_id', $data['business_id']);
        $this->db->bind(':reference_number', $data['reference_number']);
        $this->db->bind(':payment_method', $data['payment_method']);
        $this->db->bind(':payment_status', $data['payment_status']);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':proof_file', $data['proof_file']);
        $this->db->bind(':payment_date', $data['payment_date']);
        $this->db->bind(':notes', isset($data['notes']) ? $data['notes'] : null);
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Update payment status
    public function updatePaymentStatus($id, $data) {
        $this->db->query('
            UPDATE payment 
            SET payment_status = :payment_status,
                verified_by = :verified_by,
                verified_at = :verified_at,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id
        ');
        
        // Bind values
        $this->db->bind(':payment_status', $data['payment_status']);
        $this->db->bind(':verified_by', $data['verified_by']);
        $this->db->bind(':verified_at', $data['verified_at']);
        $this->db->bind(':notes', $data['notes']);
        $this->db->bind(':id', $id);
        
        // Execute
        return $this->db->execute();
    }

    // Delete payment
    public function deletePayment($id) {
        $this->db->query('DELETE FROM payment WHERE id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    // Count payments by status
    public function countPaymentsByStatus($status) {
        $this->db->query('SELECT COUNT(*) as count FROM payment WHERE payment_status = :status');
        $this->db->bind(':status', $status);
        
        $row = $this->db->single();
        return $row->count;
    }

    // Sum total payments (verified)
    public function getTotalPaymentsAmount() {
        $this->db->query('
            SELECT SUM(amount) as total 
            FROM payment 
            WHERE payment_status = "Verified"
        ');
        
        $row = $this->db->single();
        return $row && isset($row->total) ? $row->total : 0;
    }

    // Get payments by business ID
    public function getPaymentsByBusinessId($businessId) {
        $this->db->query('
            SELECT * FROM payment
            WHERE business_id = :business_id
            ORDER BY created_at DESC
        ');
        
        $this->db->bind(':business_id', $businessId);
        
        return $this->db->resultSet();
    }

    // Get recent payments (for dashboard)
    public function getRecentPayments($limit = 5) {
        $this->db->query('
            SELECT p.*, b.name as business_name 
            FROM payment p
            JOIN business b ON p.business_id = b.id
            ORDER BY p.created_at DESC
            LIMIT :limit
        ');
        
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    // Get payments pending verification
    public function getPendingVerificationPayments() {
        $this->db->query('
            SELECT p.*, b.name as business_name,
                CONCAT(ud.first_name, " ", ud.last_name) as owner_name
            FROM payment p
            JOIN business b ON p.business_id = b.id
            LEFT JOIN user u ON b.user_id = u.id
            LEFT JOIN user_detail ud ON u.id = ud.user_id
            WHERE p.payment_status = "Pending"
            ORDER BY p.created_at DESC
        ');
        return $this->db->resultSet();
    }

    // Get today's total verified payments
    public function getTodayPaymentsTotal() {
        $today = date('Y-m-d');
        $this->db->query("
            SELECT SUM(amount) as total 
            FROM payment 
            WHERE payment_status = 'Verified' 
            AND DATE(verified_at) = :today
        ");
        $this->db->bind(':today', $today);
        $result = $this->db->single();
        return $result->total ?? 0;
    }
    
    // Get monthly total verified payments
    public function getMonthlyPaymentsTotal() {
        $month = date('Y-m');
        $this->db->query("
            SELECT SUM(amount) as total 
            FROM payment 
            WHERE payment_status = 'Verified' 
            AND DATE_FORMAT(verified_at, '%Y-%m') = :month
        ");
        $this->db->bind(':month', $month);
        $result = $this->db->single();
        return $result->total ?? 0;
    }

    // Get all payments with filters and pagination
    public function getAllPaymentsWithFilters($filters = []) {
        $sql = 'SELECT p.*, b.name as business_name, b.type as business_type,
                    CONCAT(ud.first_name, " ", ud.last_name) as owner_name
                FROM payment p
                JOIN business b ON p.business_id = b.id
                LEFT JOIN user u ON b.user_id = u.id
                LEFT JOIN user_detail ud ON u.id = ud.user_id
                WHERE 1=1';
        $params = [];
        
        // Apply status filter
        if (!empty($filters['status'])) {
            $sql .= ' AND p.payment_status = :status';
            $params[':status'] = $filters['status'];
        }
        
        // Apply date range filter
        if (!empty($filters['date_range'])) {
            if ($filters['date_range'] === 'today') {
                $sql .= ' AND DATE(p.created_at) = CURDATE()';
            } elseif ($filters['date_range'] === 'week') {
                $sql .= ' AND YEARWEEK(p.created_at, 1) = YEARWEEK(CURDATE(), 1)';
            } elseif ($filters['date_range'] === 'month') {
                $sql .= ' AND MONTH(p.created_at) = MONTH(CURDATE()) AND YEAR(p.created_at) = YEAR(CURDATE())';
            } elseif ($filters['date_range'] === 'year') {
                $sql .= ' AND YEAR(p.created_at) = YEAR(CURDATE())';
            }
        }
        
        // Apply business type filter
        if (!empty($filters['business_type'])) {
            $sql .= ' AND b.type = :business_type';
            $params[':business_type'] = $filters['business_type'];
        }
        
        // Get total count
        $countSql = str_replace('p.*, b.name as business_name, b.type as business_type, CONCAT(ud.first_name, " ", ud.last_name) as owner_name', 'COUNT(*) as total', $sql);
        $this->db->query($countSql);
        
        // Bind params for count query
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $countResult = $this->db->single();
        $total = $countResult->total ?? 0;
        
        // Add order and pagination to main query
        $sql .= ' ORDER BY p.created_at DESC';
        
        if (!empty($filters['page']) && !empty($filters['per_page'])) {
            $page = max(1, (int)$filters['page']);
            $perPage = (int)$filters['per_page'];
            $offset = ($page - 1) * $perPage;
            
            $sql .= ' LIMIT :limit OFFSET :offset';
            $params[':limit'] = $perPage;
            $params[':offset'] = $offset;
        }
        
        $this->db->query($sql);
        
        // Bind params for main query
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        return [
            'payments' => $this->db->resultSet(),
            'total' => $total
        ];
    }
    
    // Get list of distinct payment statuses
    public function getDistinctPaymentStatuses() {
        $this->db->query('SELECT DISTINCT payment_status FROM payment ORDER BY payment_status');
        $statuses = $this->db->resultSet();
        return array_map(function($status) {
            return $status->payment_status;
        }, $statuses);
    }
    
    // Get monthly payment statistics with limit for number of months
    public function getMonthlyPaymentsStats($months = 12) {
        $this->db->query('
            SELECT 
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total_payments,
                SUM(CASE WHEN payment_status = "Verified" THEN amount ELSE 0 END) as verified_amount,
                SUM(CASE WHEN payment_status = "Pending" THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN payment_status = "Rejected" THEN 1 ELSE 0 END) as rejected_count
            FROM payment
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
            GROUP BY DATE_FORMAT(created_at, "%Y-%m")
            ORDER BY month ASC
        ');
        
        $this->db->bind(':months', $months);
        
        return $this->db->resultSet();
    }
    
    // Get payments by payment method
    public function getPaymentsByMethod() {
        $this->db->query('
            SELECT 
                payment_method,
                COUNT(*) as total_count,
                SUM(CASE WHEN payment_status = "Verified" THEN amount ELSE 0 END) as verified_amount
            FROM payment
            GROUP BY payment_method
            ORDER BY verified_amount DESC
        ');
        
        return $this->db->resultSet();
    }

    /**
     * Generate a unique receipt number
     * Format: BBR-YYYY-MM-XXXXX (where XXXXX is a sequential number)
     * 
     * @return string The generated receipt number
     */
    public function generateReceiptNumber(): string {
        $year = date('Y');
        $month = date('m');
        $prefix = 'BBR-' . $year . '-' . $month . '-';
        
        // Get the latest receipt number with this prefix
        $this->db->query('
            SELECT MAX(SUBSTRING_INDEX(receipt_number, "-", -1)) as last_number
            FROM payment 
            WHERE receipt_number LIKE :prefix
        ');
        $this->db->bind(':prefix', $prefix . '%');
        $result = $this->db->single();
        
        // Start with 1 or increment the last number
        $number = 1;
        if ($result && !empty($result->last_number)) {
            $number = intval($result->last_number) + 1;
        }
        
        // Format number with leading zeros (5 digits)
        return $prefix . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a receipt for a payment
     *
     * @param int $id The payment ID
     * @return bool|string Receipt number if successful, false otherwise
     */
    public function generateReceipt(int $id): bool|string {
        // Get the payment
        $payment = $this->getPaymentById($id);
        if (!$payment || $payment->payment_status !== 'Verified') {
            return false;
        }
        
        // Check if receipt already exists
        if (!empty($payment->receipt_number)) {
            return $payment->receipt_number;
        }
        
        // Generate receipt number
        $receiptNumber = $this->generateReceiptNumber();
        
        // Update payment with receipt number
        $this->db->query('
            UPDATE payment 
            SET receipt_number = :receipt_number,
                receipt_generated_at = NOW(),
                updated_at = NOW()
            WHERE id = :id
        ');
        
        $this->db->bind(':receipt_number', $receiptNumber);
        $this->db->bind(':id', $id);
        
        if ($this->db->execute()) {
            return $receiptNumber;
        }
        
        return false;
    }

    /**
     * Get payment details with business and user information for receipt
     *
     * @param int $id The payment ID
     * @return object|bool Payment details or false if not found
     */
    public function getPaymentDetailsForReceipt(int $id): object|bool {
        $this->db->query('
            SELECT 
                p.*,
                b.name as business_name,
                b.address as business_address,
                b.type as business_type,
                b.registration_number,
                CONCAT(ud.first_name, " ", ud.last_name) as owner_name,
                ud.contact_number as owner_contact,
                ud.email as owner_email,
                CONCAT(vud.first_name, " ", vud.last_name) as verified_by_name
            FROM payment p
            JOIN business b ON p.business_id = b.id
            LEFT JOIN user u ON b.user_id = u.id
            LEFT JOIN user_detail ud ON u.id = ud.user_id
            LEFT JOIN user vu ON p.verified_by = vu.id
            LEFT JOIN user_detail vud ON vu.id = vud.user_id
            WHERE p.id = :id
        ');
        
        $this->db->bind(':id', $id);
        
        return $this->db->single();
    }

    /**
     * Get all receipts with optional filtering
     * 
     * @param array $filters Optional filters (date range, business type, etc.)
     * @return array Array with receipts data and total count
     */
    public function getReceiptsWithFilters(array $filters = []): array {
        // Extract filters
        $page = $filters['page'] ?? 1;
        $perPage = $filters['per_page'] ?? 10;
        $dateRange = $filters['date_range'] ?? null;
        $businessType = $filters['business_type'] ?? null;
        
        // Calculate offset for pagination
        $offset = ($page - 1) * $perPage;
        
        // Build base query
        $baseQuery = '
            FROM payment p
            JOIN business b ON p.business_id = b.id
            LEFT JOIN user u ON b.user_id = u.id
            LEFT JOIN user_detail ud ON u.id = ud.user_id
            WHERE p.payment_status = "Verified"
            AND p.receipt_number IS NOT NULL
        ';
        
        // Apply filters
        $params = [];
        
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 2) {
                $startDate = date('Y-m-d', strtotime($dates[0]));
                $endDate = date('Y-m-d', strtotime($dates[1]));
                
                $baseQuery .= ' AND DATE(p.receipt_generated_at) BETWEEN :start_date AND :end_date';
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
        }
        
        if ($businessType) {
            $baseQuery .= ' AND b.type = :business_type';
            $params[':business_type'] = $businessType;
        }
        
        // Count total records
        $countQuery = 'SELECT COUNT(*) as total ' . $baseQuery;
        $this->db->query($countQuery);
        
        // Bind parameters for count query
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $totalResult = $this->db->single();
        $total = $totalResult->total ?? 0;
        
        // Get paginated records
        $mainQuery = '
            SELECT 
                p.*,
                b.name as business_name,
                b.type as business_type,
                CONCAT(ud.first_name, " ", ud.last_name) as owner_name
            ' . $baseQuery . '
            ORDER BY p.receipt_generated_at DESC
            LIMIT :offset, :limit
        ';
        
        $this->db->query($mainQuery);
        
        // Bind parameters for main query
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        $this->db->bind(':offset', $offset);
        $this->db->bind(':limit', $perPage);
        
        $receipts = $this->db->resultSet();
        
        return [
            'receipts' => $receipts,
            'total' => $total
        ];
    }
    
    /**
     * Get detailed monthly payment report
     * 
     * @param string $month Month in format 'YYYY-MM'
     * @return array Payment report data
     */
    public function getDetailedMonthlyReport(string $month): array {
        // Get month boundaries
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        // Get summary data
        $this->db->query('
            SELECT 
                COUNT(*) as total_transactions,
                SUM(amount) as total_revenue,
                AVG(amount) as average_amount
            FROM payment
            WHERE payment_status = "Verified"
            AND DATE(payment_date) BETWEEN :start_date AND :end_date
        ');
        
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        
        $summary = $this->db->single();
        
        // Get transaction details
        $this->db->query('
            SELECT 
                p.*,
                b.name as business_name,
                b.type as business_type,
                CONCAT(ud.first_name, " ", ud.last_name) as owner_name
            FROM payment p
            JOIN business b ON p.business_id = b.id
            LEFT JOIN user u ON b.user_id = u.id
            LEFT JOIN user_detail ud ON u.id = ud.user_id
            WHERE DATE(p.payment_date) BETWEEN :start_date AND :end_date
            ORDER BY p.payment_date DESC
        ');
        
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        
        $transactions = $this->db->resultSet();
        
        return [
            'summary' => $summary,
            'transactions' => $transactions
        ];
    }
    
    /**
     * Get payment methods report for a specific month
     * 
     * @param string $month Month in format 'YYYY-MM'
     * @return array Payment methods report data
     */
    public function getPaymentMethodsReport(string $month): array {
        // Get month boundaries
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $this->db->query('
            SELECT 
                payment_method,
                COUNT(*) as transaction_count,
                SUM(amount) as total_amount
            FROM payment
            WHERE payment_status = "Verified"
            AND DATE(payment_date) BETWEEN :start_date AND :end_date
            GROUP BY payment_method
            ORDER BY total_amount DESC
        ');
        
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get business types report for a specific month
     * 
     * @param string $month Month in format 'YYYY-MM'
     * @return array Business types report data
     */
    public function getBusinessTypesReport(string $month): array {
        // Get month boundaries
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $this->db->query('
            SELECT 
                b.type as business_type,
                COUNT(DISTINCT b.id) as business_count,
                SUM(p.amount) as total_revenue
            FROM payment p
            JOIN business b ON p.business_id = b.id
            WHERE p.payment_status = "Verified"
            AND DATE(p.payment_date) BETWEEN :start_date AND :end_date
            GROUP BY b.type
            ORDER BY total_revenue DESC
        ');
        
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        
        return $this->db->resultSet();
    }

}