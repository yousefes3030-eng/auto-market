<?php
require_once 'config/config.php';
require_once 'includes/auth.php';

// Logout user
logoutUser();

// Redirect to homepage
header('Location: ' . APP_URL . '/index.php');
exit;
