<?php
$requireLogin = true;
require_once 'includes/bootstrap.php';

// Get completed transaction from session
$transaction = $_SESSION['completed_transaction'] ?? null;

if (!$transaction || $transaction['type'] !== 'purchase') {
    header('Location: dashboard.php');
    exit;
}

// Get purchase details
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT p.*, c.model, c.year, c.condition, b.name as brand_name, ci.image_path
    FROM purchases p
    JOIN cars c ON p.car_id = c.id
    JOIN brands b ON c.brand_id = b.id
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_primary = 1
    WHERE p.id = ? AND p.user_id = ?
");
$stmt->execute([$transaction['id'], $_SESSION['user_id']]);
$purchase = $stmt->fetch();

// Clear completed transaction from session
unset($_SESSION['completed_transaction']);

if (!$purchase) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Purchase Confirmed';
require_once 'includes/header.php';
?>

<div class="success-page">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Purchase Confirmed!</h1>
            <p>Congratulations! Your car purchase has been successfully completed.</p>
            
            <div class="transaction-reference">
                <span>Transaction Reference:</span>
                <strong><?php echo e($transaction['transaction_ref']); ?></strong>
            </div>
            
            <div class="reservation-details-card">
                <h3>Purchase Details</h3>
                
                <div class="reservation-car">
                    <img src="<?php echo e(imageUrl($purchase['image_path'] ?? '')); ?>" 
                         alt="<?php echo e($purchase['brand_name'] . ' ' . $purchase['model']); ?>"
                         onerror="this.src='assets/images/placeholder-car.jpg'">
                    <div>
                        <h4><?php echo e($purchase['brand_name'] . ' ' . $purchase['model']); ?></h4>
                        <p><?php echo e($purchase['year']); ?> • <?php echo ucfirst(e($purchase['condition'])); ?></p>
                    </div>
                </div>
                
                <div class="reservation-info">
                    <div class="info-row">
                        <span>Purchase ID:</span>
                        <strong>#<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Purchase Date:</span>
                        <strong><?php echo formatDate($purchase['created_at']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Status:</span>
                        <strong class="status-badge badge-<?php echo getStatusBadgeClass($purchase['status']); ?>">
                            <?php echo ucfirst(e($purchase['status'])); ?>
                        </strong>
                    </div>
                    <div class="info-row total">
                        <span>Total Paid:</span>
                        <strong><?php echo formatPrice($purchase['amount']); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="success-message-box">
                <h4>What's Next?</h4>
                <ul>
                    <li><i class="fas fa-check"></i> Our team will contact you within 24-48 hours</li>
                    <li><i class="fas fa-check"></i> We'll arrange vehicle inspection and delivery</li>
                    <li><i class="fas fa-check"></i> Complete necessary documentation and registration</li>
                    <li><i class="fas fa-check"></i> Take delivery of your new vehicle</li>
                </ul>
            </div>
            
            <div class="success-actions">
                <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                <a href="cars.php" class="btn btn-outline">Browse More Cars</a>
            </div>
            
            <div class="success-note">
                <i class="fas fa-info-circle"></i>
                <p>This is a demo transaction. No real money has been charged and no actual vehicle purchase has occurred.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
