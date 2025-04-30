<?php
/**
 * Redirects to a specific location within the application.
 * Uses the URLROOT constant.
 *
 * @param string $location The path relative to URLROOT (e.g., 'users/login').
 * @return void
 */
function redirect(string $location): void {
    // Ensure URLROOT is defined
    if (!defined('URLROOT')) {
        die('URLROOT constant is not defined');
    }
    
    // Create full URL, ensuring no double slashes
    $fullUrl = URLROOT . '/' . ltrim($location, '/');
    
    // Perform the redirect
    header('Location: ' . $fullUrl);
    exit();
}

/**
 * Basic sanitization for outputting data in HTML.
 * Prevents XSS attacks. Use before echoing user-provided data.
 *
 * @param mixed $data The data to sanitize (string or array).
 * @return mixed Sanitized data.
 */
function sanitize(mixed $data): mixed {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    } elseif (is_string($data)) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    // Return non-string/non-array data as is (e.g., numbers, booleans)
    return $data;
}

/**
 * Checks if the current request method is POST.
 *
 * @return bool True if POST, false otherwise.
 */
function isPostRequest(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Checks if the current request method is GET.
 *
 * @return bool True if GET, false otherwise.
 */
function isGetRequest(): bool {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Checks if the current URL path matches a specific path.
 * Used primarily for highlighting active navigation items.
 * 
 * @param string $path The path to check against (e.g., '/dashboard/admin').
 * @return bool True if the current URL path matches, false otherwise.
 */
function urlIs(string $path): bool {
    // Get current URL path
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Remove URLROOT from the current path if it exists
    if (defined('URLROOT')) {
        $urlRoot = parse_url(URLROOT, PHP_URL_PATH);
        if ($urlRoot && strpos($currentPath, $urlRoot) === 0) {
            $currentPath = substr($currentPath, strlen($urlRoot));
        }
    }
    
    // Ensure paths start with / for consistency
    $currentPath = '/' . ltrim($currentPath, '/');
    $path = '/' . ltrim($path, '/');
    
    // Check if the current path matches the provided path
    return $currentPath === $path;
}

// Add other simple, globally useful functions here if needed.
?>