<?php
// Database Params
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barangay_business_registration');

// App Root - Points to the app directory
define('APPROOT', dirname(dirname(__FILE__)));

// Clean URL structure - use the domain name or just '/' for local development
// Make sure URLROOT points to the public folder for correct routing
define('URLROOT', 'http://localhost');
// Alternative for development without virtual host:
// define('URLROOT', 'http://localhost/barangay_business_registration/app/public');

// Site Name
define('SITENAME', 'Barangay Business Registration');

// Default Controller and Method
define('DEFAULT_CONTROLLER', 'Pages');
define('DEFAULT_METHOD', 'index');

// User Roles (Constants for consistency)
define('ROLE_OWNER', 'business_owner');
define('ROLE_ADMIN', 'admin');
define('ROLE_TREASURER', 'treasurer');

// File and Upload Paths
define('UPLOAD_PATH_PROOFS', 'uploads/proofs/'); // Payment proof uploads
define('UPLOAD_PATH_PERMITS', 'uploads/permits/'); // If you store generated permits
define('UPLOAD_PATH_DOCUMENTS', 'uploads/documents/'); // Business documents uploads

// Ensure upload directories exist and are writable by the web server
// You might need to create these directories manually: public/uploads/proofs, public/uploads/permits, public/uploads/documents
// And set appropriate permissions (e.g., chmod 755 or 775 depending on your server setup)

// Email Configuration
define('EMAIL_SENDER', 'no-reply@barangay-business.com');
define('EMAIL_SENDER_NAME', 'Barangay Business Registration');
define('EMAIL_NOTIFICATIONS_ENABLED', true); // Set to false to disable all email notifications

?>