<?php
require_once __DIR__ . '/bootstrap.php';

$flashMessage = getFlashMessage();
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$isAdminPage = (strpos($scriptPath, '/admin/') !== false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' . APP_NAME : APP_NAME . ' - Rent & Buy Quality Cars'; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? e($pageDescription) : 'Rent or buy quality new and used cars at competitive prices'; ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <?php if ($isAdminPage): ?>
        <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/admin.css">
    <?php endif; ?>
    
    <?php if (isset($additionalCSS)): ?>
        <?php echo $additionalCSS; ?>
    <?php endif; ?>
    <script>window.APP_URL = <?php echo json_encode(APP_URL); ?>;</script>
</head>
<body class="<?php echo $isAdminPage ? 'admin-body' : ''; ?>">
    <?php if (!$isAdminPage): ?>
        <?php include __DIR__ . '/navbar.php'; ?>
    <?php endif; ?>
    
    <?php if ($flashMessage): ?>
        <div class="flash-message flash-<?php echo e($flashMessage['type']); ?>" id="flashMessage">
            <div class="container">
                <span><?php echo e($flashMessage['message']); ?></span>
                <button onclick="this.parentElement.parentElement.remove()" class="close-btn" aria-label="Dismiss">&times;</button>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!$isAdminPage): ?>
    <main class="main-content">
    <?php endif; ?>
