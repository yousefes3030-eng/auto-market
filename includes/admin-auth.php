<?php
// Admin authentication is provided by includes/auth.php (loaded via header.php).
if (!function_exists('requireAdmin')) {
    function requireAdmin() {
        if (!isAdmin()) {
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }
}
