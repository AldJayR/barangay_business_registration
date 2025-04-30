<?php
/**
 * Helper class for managing session flash messages.
 * Flash messages are displayed once and then cleared.
 */
class SessionHelper {
    private const FLASH_KEY = 'flash_messages';

    /**
     * Sets a flash message.
     *
     * @param string $type The type of message (e.g., 'success', 'error', 'info'). Corresponds to Bootstrap alert classes.
     * @param string $message The message content.
     * @return void
     */
    public static function setFlash(string $type, string $message): void {
        // Ensure session is started (might be redundant if started in index.php, but safe)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION[self::FLASH_KEY][$type] = $message;
    }

    /**
     * Displays all flash messages and clears them from the session.
     * Should be called in the layout file where messages should appear.
     *
     * @return void
     */
    public static function displayFlashMessages(): void {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION[self::FLASH_KEY]) && is_array($_SESSION[self::FLASH_KEY])) {
            foreach ($_SESSION[self::FLASH_KEY] as $type => $message) {
                // Sanitize output just in case, though messages are usually developer-set
                $safeType = htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
                $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

                echo '<div class="alert alert-' . $safeType . ' alert-dismissible fade show" role="alert">';
                echo $safeMessage;
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            }
            // Clear messages after displaying
            unset($_SESSION[self::FLASH_KEY]);
        }
    }

    /**
     * Checks if a specific type of flash message exists.
     *
     * @param string $type The type of message to check for.
     * @return bool True if exists, false otherwise.
     */
    public static function hasFlash(string $type): bool {
         // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION[self::FLASH_KEY][$type]);
    }
}
?>