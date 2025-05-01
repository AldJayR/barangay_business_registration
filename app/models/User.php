<?php

/**
 * User Model
 * Handles data operations for the 'user' and 'user_detail' tables.
 */
class User {
    private Database $db; // Use type hinting for Database object

    public function __construct() {
        // Get the database instance (Singleton)
        $this->db = Database::getInstance();
    }

    /**
     * Finds a user by their username.
     * Selects only necessary fields for login verification initially.
     *
     * @param string $username The username to search for.
     * @return object|false User object (id, username, password, role) or false if not found.
     */
    public function findByUsername(string $username): object|false {
        $this->db->query('SELECT id, username, password, role FROM user WHERE username = :username LIMIT 1');
        $this->db->bind(':username', $username);
        return $this->db->single(); // Returns single record object or false
    }

    /**
     * Finds a user and their details by user ID.
     * Used after successful login to get display name, etc.
     *
     * @param int $id The user ID.
     * @return object|false Combined user and user_detail object or false if not found.
     */
    public function findById(int $id): object|false {
        // Join user and user_detail tables
        $this->db->query('SELECT u.id, u.username, u.role, u.created_at,
                               ud.first_name, ud.last_name, ud.phone_number, ud.email, ud.address
                        FROM user u
                        LEFT JOIN user_detail ud ON u.id = ud.user_id
                        WHERE u.id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Registers a new user and their details.
     * Uses a transaction to ensure atomicity.
     *
     * @param array $userData User data (username, hashed_password, role).
     * @param array $userDetailData User detail data (user_id will be set, first_name, last_name, etc.).
     * @return bool True on success, false on failure.
     */
    public function register(array $userData, array $userDetailData): bool {
        // Start Transaction
        if (!$this->db->beginTransaction()) {
            error_log("Failed to start transaction for user registration.");
            return false;
        }

        try {
            // Insert into user table
            $this->db->query('INSERT INTO user (username, password, role) VALUES (:username, :password, :role)');
            $this->db->bind(':username', $userData['username']);
            $this->db->bind(':password', $userData['password']); // Password should already be hashed
            $this->db->bind(':role', $userData['role']);

            if (!$this->db->execute()) {
                $this->db->rollBack();
                error_log("Failed to insert into user table for username: " . $userData['username']);
                return false;
            }

            // Get the last inserted user ID
            $userId = $this->db->lastInsertId();
            if (!$userId) {
                 $this->db->rollBack();
                 error_log("Failed to get last insert ID for user: " . $userData['username']);
                 return false;
            }

            // Insert into user_detail table
            $this->db->query('INSERT INTO user_detail (user_id, first_name, last_name, phone_number, email, address)
                              VALUES (:user_id, :first_name, :last_name, :phone_number, :email, :address)');
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':first_name', $userDetailData['first_name']);
            $this->db->bind(':last_name', $userDetailData['last_name']);
            $this->db->bind(':phone_number', $userDetailData['phone_number'] ?? null); // Handle optional fields
            $this->db->bind(':email', $userDetailData['email'] ?? null);
            $this->db->bind(':address', $userDetailData['address'] ?? null);

            if (!$this->db->execute()) {
                $this->db->rollBack();
                 error_log("Failed to insert into user_detail table for user ID: " . $userId);
                return false;
            }

            // If all queries were successful, commit the transaction
            if ($this->db->commit()) {
                return true;
            } else {
                $this->db->rollBack(); // Attempt rollback if commit fails (rare)
                error_log("Failed to commit transaction for user ID: " . $userId);
                return false;
            }

        } catch (Exception $e) {
            // Catch any exceptions during the process
            $this->db->rollBack();
            error_log("Exception during user registration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user details
     * 
     * @param array $data User detail data to update
     * @return bool True on success, false on failure
     */
    public function updateUserDetails(array $data): bool {
        $this->db->query('UPDATE user_detail SET 
                          first_name = :first_name, 
                          last_name = :last_name, 
                          phone_number = :phone_number, 
                          email = :email, 
                          address = :address 
                          WHERE user_id = :user_id');
        
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':phone_number', $data['phone_number'] ?? null);
        $this->db->bind(':email', $data['email'] ?? null);
        $this->db->bind(':address', $data['address'] ?? null);
        $this->db->bind(':user_id', $data['user_id']);
        
        return $this->db->execute();
    }
    
    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $hashedPassword Hashed password
     * @return bool True on success, false on failure
     */
    public function updatePassword(int $userId, string $hashedPassword): bool {
        $this->db->query('UPDATE user SET password = :password WHERE id = :id');
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Find user by ID including password for verification
     * 
     * @param int $userId User ID
     * @return object|false User object with password or false if not found
     */
    public function findByUserId(int $userId): object|false {
        $this->db->query('SELECT u.id, u.username, u.password, u.role, u.created_at
                        FROM user u
                        WHERE u.id = :id');
        $this->db->bind(':id', $userId);
        return $this->db->single();
    }

    /**
     * Gets comprehensive user information by user ID
     * Includes both user and user_detail data
     * 
     * @param int $userId User ID
     * @return object|false User object with details or false if not found
     */
    public function getUserById(int $userId): object|false {
        $this->db->query('SELECT u.id, u.username, u.role, u.created_at,
                          ud.first_name, ud.last_name, ud.phone_number, ud.email, ud.address
                          FROM user u
                          LEFT JOIN user_detail ud ON u.id = ud.user_id
                          WHERE u.id = :userId');
        $this->db->bind(':userId', $userId);
        return $this->db->single();
    }

    /**
     * Update email notification preferences for a user
     *
     * @param int $userId User ID
     * @param array $preferences Associative array of preference keys and values
     * @return bool True if update was successful, false otherwise
     */
    public function updateEmailPreferences(int $userId, array $preferences): bool {
        // Build the SQL query dynamically based on provided preferences
        $sql = "UPDATE user SET ";
        $updates = [];
        
        foreach ($preferences as $key => $value) {
            $updates[] = "$key = :$key";
        }
        
        $sql .= implode(', ', $updates);
        $sql .= " WHERE id = :user_id";
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach ($preferences as $key => $value) {
            $this->db->bind(":$key", $value);
        }
        $this->db->bind(':user_id', $userId);
        
        // Execute the query
        return $this->db->execute();
    }

    /**
     * Get all users with pagination and filtering
     * 
     * @param array $filters Optional filters for the query
     * @param int|null $limit Limit number of results
     * @param int|null $offset Offset for pagination
     * @return array Array of user objects
     */
    public function getAllUsers(array $filters = [], ?int $limit = null, ?int $offset = null): array {
        $sql = 'SELECT u.id, u.username, u.role, u.status, u.created_at,
                ud.first_name, ud.last_name, ud.phone_number, ud.email, ud.address
                FROM user u
                LEFT JOIN user_detail ud ON u.id = ud.user_id';
        
        // Apply filters if provided
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['role'])) {
            $whereConditions[] = 'u.role = :role';
            $params[':role'] = $filters['role'];
        }
        
        if (isset($filters['status'])) {
            $whereConditions[] = 'u.status = :status';
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $whereConditions[] = '(u.username LIKE :search OR ud.first_name LIKE :search OR ud.last_name LIKE :search OR ud.email LIKE :search)';
            $params[':search'] = $searchTerm;
        }
        
        if (!empty($whereConditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $sql .= ' ORDER BY u.created_at DESC';
        
        // Add pagination if limit is set
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
            if ($offset !== null) {
                $sql .= ' OFFSET :offset';
            }
        }
        
        $this->db->query($sql);
        
        // Bind parameters if any
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        // Bind pagination parameters if set
        if ($limit !== null) {
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            if ($offset !== null) {
                $this->db->bind(':offset', $offset, PDO::PARAM_INT);
            }
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Count total users with filters
     * 
     * @param array $filters Optional filters for the query
     * @return int Total count of users matching filters
     */
    public function countUsers(array $filters = []): int {
        $sql = 'SELECT COUNT(*) as total FROM user u LEFT JOIN user_detail ud ON u.id = ud.user_id';
        
        // Apply filters if provided
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['role'])) {
            $whereConditions[] = 'u.role = :role';
            $params[':role'] = $filters['role'];
        }
        
        if (isset($filters['status'])) {
            $whereConditions[] = 'u.status = :status';
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $whereConditions[] = '(u.username LIKE :search OR ud.first_name LIKE :search OR ud.last_name LIKE :search OR ud.email LIKE :search)';
            $params[':search'] = $searchTerm;
        }
        
        if (!empty($whereConditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereConditions);
        }
        
        $this->db->query($sql);
        
        // Bind parameters if any
        foreach ($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $result = $this->db->single();
        return $result->total;
    }
    
    /**
     * Update user status (active/inactive)
     * 
     * @param int $userId User ID
     * @param int $status Status value (1 for active, 0 for inactive)
     * @return bool True on success, false on failure
     */
    public function updateUserStatus(int $userId, int $status): bool {
        $this->db->query('UPDATE user SET status = :status WHERE id = :id');
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Update user role
     * 
     * @param int $userId User ID
     * @param string $role New role
     * @return bool True on success, false on failure
     */
    public function updateUserRole(int $userId, string $role): bool {
        $this->db->query('UPDATE user SET role = :role WHERE id = :id');
        $this->db->bind(':role', $role);
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Count users by role
     * 
     * @return array Associative array with counts by role
     */
    public function countUsersByRole(): array {
        $this->db->query('SELECT role, COUNT(*) as count FROM user GROUP BY role');
        $results = $this->db->resultSet();
        
        $counts = [];
        foreach ($results as $result) {
            $counts[$result->role] = $result->count;
        }
        
        return $counts;
    }

    /**
     * Delete a user (soft delete by setting status to inactive)
     * 
     * @param int $userId User ID
     * @return bool True on success, false on failure
     */
    public function deleteUser(int $userId): bool {
        $this->db->query('UPDATE user SET status = 0 WHERE id = :id');
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }

    /**
     * Password strength validation for registration and password change
     *
     * @param string $password
     * @param array $contextWords (e.g. username, email, first/last name)
     * @return bool|string True if strong, otherwise error message
     */
    public function validatePasswordStrength($password, $contextWords = []) {
        // Length check
        if (mb_strlen($password) < 8) {
            return 'Password must be at least 8 characters.';
        }
        if (mb_strlen($password) > 64) {
            return 'Password must not exceed 64 characters.';
        }
        // Allow all printable ASCII and Unicode symbols (no control chars)
        if (!preg_match('/^[\P{C}]+$/u', $password)) {
            return 'Password contains invalid characters.';
        }
        // Check for dictionary/breached passwords
        $dictPath = __DIR__ . '/../helpers/common_passwords.txt';
        if (file_exists($dictPath)) {
            $dictionary = file($dictPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($dictionary as $word) {
                if ($word && stripos($password, $word) !== false) {
                    return 'Password is too common or easily guessed.';
                }
            }
        }
        // Check for repeated sequences (3+)
        if (preg_match('/(.)\\1{2,}/u', $password)) {
            return 'Password contains repeated characters or sequences.';
        }
        // Check for context-specific words
        foreach ($contextWords as $word) {
            if ($word && stripos($password, $word) !== false) {
                return 'Password should not contain your personal information.';
            }
        }
        return true;
    }

}
?>