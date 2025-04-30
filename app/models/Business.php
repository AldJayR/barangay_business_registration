<?php

/**
 * Business Model
 * Handles data operations for the 'business' table.
 */
class Business {
    private Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Creates a new business record.
     *
     * @param array $data The business data to insert.
     * @return int|false The ID of the newly created business, or false if creation failed.
     */
    public function create(array $data): int|false {
        $this->db->query("INSERT INTO business (user_id, name, type, address, status, created_at, updated_at) 
                        VALUES (:user_id, :name, :type, :address, :status, NOW(), NOW())");
        
        // Bind values
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':status', $data['status']);
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Finds all businesses registered by a specific user (owner).
     * Includes the latest payment status for each business.
     *
     * @param int $userId The ID of the business owner (user).
     * @return array An array of business objects, or an empty array if none found.
     */
    public function findByOwnerId(int $userId): array {
        try {
            // Check if payment table exists
            $this->db->query("SHOW TABLES LIKE 'payment'");
            $paymentTableExists = !empty($this->db->resultSet());
            
            // Base query to get business data
            if ($paymentTableExists) {
                // If payment table exists, include payment status
                $this->db->query("
                    SELECT
                        b.id,
                        b.name,
                        b.type,
                        b.address,
                        b.status,
                        b.created_at,
                        b.updated_at,
                        (SELECT p.payment_status
                         FROM payment p
                         WHERE p.business_id = b.id
                         ORDER BY p.created_at DESC, p.id DESC
                         LIMIT 1) AS latest_payment_status
                    FROM business b
                    WHERE b.user_id = :userId
                    ORDER BY b.created_at DESC
                ");
            } else {
                // If payment table doesn't exist, exclude payment status
                $this->db->query("
                    SELECT
                        b.id,
                        b.name,
                        b.type,
                        b.address,
                        b.status,
                        b.created_at,
                        b.updated_at,
                        NULL AS latest_payment_status
                    FROM business b
                    WHERE b.user_id = :userId
                    ORDER BY b.created_at DESC
                ");
            }

            $this->db->bind(':userId', $userId);
            $results = $this->db->resultSet();

            return $results ?: []; // Return results or empty array
        } catch (Exception $e) {
            // Log the error but don't expose details to the user
            error_log("Error in findByOwnerId: " . $e->getMessage());
            
            // Fallback to simpler query without payment data if there was an error
            try {
                $this->db->query("
                    SELECT
                        b.id,
                        b.name,
                        b.type,
                        b.address,
                        b.status,
                        b.created_at,
                        b.updated_at,
                        NULL AS latest_payment_status
                    FROM business b
                    WHERE b.user_id = :userId
                    ORDER BY b.created_at DESC
                ");
                
                $this->db->bind(':userId', $userId);
                $results = $this->db->resultSet();
                
                return $results ?: [];
            } catch (Exception $innerEx) {
                error_log("Fallback query also failed: " . $innerEx->getMessage());
                return []; // Return empty array if all attempts fail
            }
        }
    }

    /**
     * Finds a business by its ID.
     *
     * @param int $id The Business ID.
     * @return object|false Business object or false if not found.
     */
    public function getBusinessById(int $id): object|false {
        $this->db->query("SELECT b.*, ud.first_name, ud.last_name
                         FROM business b
                         LEFT JOIN user u ON b.user_id = u.id
                         LEFT JOIN user_detail ud ON u.id = ud.user_id
                         WHERE b.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Finds a business by ID and checks if it belongs to the specified owner.
     *
     * @param int $id The business ID.
     * @param int $ownerId The owner (user) ID.
     * @return object|false Business object or false if not found or not owned by the specified user.
     */
    public function findByIdAndOwner(int $id, int $ownerId): object|false {
        $this->db->query("SELECT b.*, ud.first_name, ud.last_name,
                         (SELECT p.payment_status
                          FROM payment p
                          WHERE p.business_id = b.id
                          ORDER BY p.created_at DESC, p.id DESC
                          LIMIT 1) AS latest_payment_status
                         FROM business b
                         LEFT JOIN user u ON b.user_id = u.id
                         LEFT JOIN user_detail ud ON u.id = ud.user_id
                         WHERE b.id = :id AND b.user_id = :ownerId");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':ownerId', $ownerId);
        
        return $this->db->single();
    }

    /**
     * Updates the status of a business.
     *
     * @param int $id The Business ID.
     * @param string $status The new status.
     * @return bool True if update successful, false otherwise.
     */
    public function updateBusinessStatus(int $id, string $status): bool {
        $this->db->query("UPDATE business SET status = :status, updated_at = NOW() WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':status', $status);
        return $this->db->execute();
    }

    /**
     * Finds all businesses with optional filters (for Admin/Treasurer)
     * @param array $filters Optional associative array of filters (e.g., ['status' => 'Pending Approval'])
     * @return array
     */
    public function findAll(array $filters = []): array {
        $query = "SELECT b.*, u.username, ud.first_name, ud.last_name
                  FROM business b
                  LEFT JOIN user u ON b.user_id = u.id
                  LEFT JOIN user_detail ud ON u.id = ud.user_id";
        $where = [];
        $params = [];
        if (isset($filters['status'])) {
            if (is_array($filters['status'])) {
                $in = implode(',', array_fill(0, count($filters['status']), '?'));
                $where[] = "b.status IN ($in)";
                $params = array_merge($params, $filters['status']);
            } else {
                $where[] = "b.status = ?";
                $params[] = $filters['status'];
            }
        }
        if ($where) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        $query .= ' ORDER BY b.created_at DESC';
        $this->db->query($query);
        foreach ($params as $idx => $val) {
            $this->db->bind($idx + 1, $val); // PDO positional binding (1-based)
        }
        $results = $this->db->resultSet();
        return $results ?: [];
    }

    /**
     * Gets the latest payment status for a business by business ID.
     *
     * @param int $businessId
     * @return string|null Latest payment status or null if not found
     */
    public function getLatestPaymentStatus(int $businessId): ?string {
        $this->db->query("SELECT payment_status FROM payment WHERE business_id = :businessId ORDER BY created_at DESC, id DESC LIMIT 1");
        $this->db->bind(':businessId', $businessId);
        $result = $this->db->single();
        return $result && isset($result->payment_status) ? $result->payment_status : null;
    }

    /**
     * Gets the permit information for a business
     *
     * @param int $businessId The business ID
     * @return object|false Permit object or false if not found
     */
    public function getPermitByBusinessId(int $businessId): object|false {
        $this->db->query("SELECT * FROM permit WHERE business_id = :business_id ORDER BY issued_date DESC LIMIT 1");
        $this->db->bind(':business_id', $businessId);
        return $this->db->single();
    }

    public function createPermit(array $data): int|false {
        $this->db->query("INSERT INTO permit (business_id, permit_number, issued_date, issued_by, expiration_date, permit_file) VALUES (:business_id, :permit_number, :issued_date, :issued_by, :expiration_date, :permit_file)");
        $this->db->bind(':business_id', $data['business_id']);
        $this->db->bind(':permit_number', $data['permit_number']);
        $this->db->bind(':issued_date', $data['issued_date']);
        $this->db->bind(':issued_by', $data['issued_by']);
        $this->db->bind(':expiration_date', $data['expiration_date']);
        $this->db->bind(':permit_file', $data['permit_file']);
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    // --- Add other methods later as needed ---
    // requestChange(int $id, array $data)
    // approveChange(int $id, string $previousStatus = 'Active')
}