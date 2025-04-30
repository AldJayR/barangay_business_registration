<?php
/**
 * PDO Database Class
 * Connects to the database using Singleton pattern.
 * Provides methods to query the database.
 */
class Database {
    private string $host = DB_HOST;
    private string $user = DB_USER;
    private string $pass = DB_PASS;
    private string $dbname = DB_NAME;
    private string $charset = 'utf8mb4';

    private ?PDO $dbh = null; // Database Handler
    private ?PDOStatement $stmt = null; // Statement
    private ?string $error = null;

    private static ?Database $instance = null; // Singleton instance

    // Private constructor to prevent direct instantiation
    private function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=' . $this->charset;
        $options = [
            PDO::ATTR_PERSISTENT => true, // Optional: Persistent connection
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Default fetch mode to object
            PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // In a real app, log this error instead of dying
            error_log('Database Connection Error: ' . $this->error); // Log error
            die('Database Connection Error. Please check logs or contact support.'); // User-friendly message
        }
    }

    /**
     * Gets the single instance of the Database class.
     *
     * @return Database The singleton instance.
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Prepares a statement with the SQL query.
     *
     * @param string $sql The SQL query string.
     * @return void
     */
    public function query(string $sql): void {
         if ($this->dbh === null) {
             $this->handleConnectionError();
         }
        try {
            $this->stmt = $this->dbh->prepare($sql);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log('Database Prepare Error: ' . $this->error . ' | SQL: ' . $sql);
            // Optionally re-throw or handle more gracefully
            throw new RuntimeException('Failed to prepare database statement.');
        }
    }

    /**
     * Binds a value to a corresponding named or question mark placeholder
     * in the SQL statement that was used to prepare the statement.
     *
     * @param string|int $param Parameter identifier (e.g., :name or 1).
     * @param mixed $value The value to bind to the parameter.
     * @param ?int $type Explicit data type for the parameter using PDO::PARAM_* constants. Auto-detected if null.
     * @return void
     */
    public function bind(string|int $param, mixed $value, ?int $type = null): void {
        if ($this->stmt === null) {
             throw new RuntimeException('Cannot bind value. No statement prepared.');
        }
        if ($type === null) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Executes the prepared statement.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function execute(): bool {
        if ($this->stmt === null) {
             throw new RuntimeException('Cannot execute. No statement prepared.');
        }
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log('Database Execute Error: ' . $this->error);
            return false; // Indicate failure
        }
    }

    /**
     * Fetches all result set rows as an array of objects.
     *
     * @return array|false An array containing all rows, or FALSE on failure.
     */
    public function resultSet(): array|false {
        if ($this->execute()) {
            return $this->stmt->fetchAll();
        }
        return false;
    }

    /**
     * Fetches a single row from the result set as an object.
     *
     * @return object|false A single row object, or FALSE on failure or if no row found.
     */
    public function single(): object|false {
        if ($this->execute()) {
            return $this->stmt->fetch();
        }
        return false;
    }

    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @return int The number of rows.
     */
    public function rowCount(): int {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @return string|false The ID of the last inserted row, or FALSE on failure.
     */
    public function lastInsertId(): string|false {
         if ($this->dbh === null) {
             $this->handleConnectionError();
         }
        return $this->dbh->lastInsertId();
    }

     /**
     * Initiates a transaction.
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function beginTransaction(): bool {
        if ($this->dbh === null) {
             $this->handleConnectionError();
         }
        return $this->dbh->beginTransaction();
    }

    /**
     * Commits a transaction.
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function commit(): bool {
         if ($this->dbh === null) {
             $this->handleConnectionError();
         }
        return $this->dbh->commit();
    }

    /**
     * Rolls back a transaction.
     *
     * @return bool TRUE on success or FALSE on failure.
     */
    public function rollBack(): bool {
         if ($this->dbh === null) {
             $this->handleConnectionError();
         }
        return $this->dbh->rollBack();
    }

    /**
     * Checks if inside a transaction.
     *
     * @return bool TRUE if a transaction is currently active, and FALSE if not.
     */
    public function inTransaction(): bool {
         if ($this->dbh === null) {
             $this->handleConnectionError();
         }
        return $this->dbh->inTransaction();
    }


    /**
     * Returns the last error message.
     *
     * @return string|null The error message or null if no error.
     */
    public function getError(): ?string {
        return $this->error;
    }

    /**
     * Handles database connection errors consistently.
     */
    private function handleConnectionError(): void {
         error_log('Attempted database operation with no active connection.');
         die('Database is not connected. Please check configuration and logs.');
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
?>