<?php
// Shared bootstrap: config, auth, and helpers with no HTML output.
if (defined('APP_BOOTSTRAPPED')) {
    return;
}
define('APP_BOOTSTRAPPED', true);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

if (!empty($requireAdmin)) {
    requireAdmin();
}

if (!empty($requireLogin)) {
    requireLogin();
}

$currentUser = getCurrentUser();
ensureCarLifecycleSchema();
