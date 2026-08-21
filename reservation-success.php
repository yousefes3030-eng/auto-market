<?php
$requireLogin = true;
require_once 'includes/bootstrap.php';

// Get completed transaction from session
$transaction = $_SESSION['completed_transaction'] ?? null;

if (!$transaction || $transaction['type'] !== 'reservation') {
    header('Location: dashboard.php');
    exit;
}

// Get reservation details
$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT r.*, c.model, c.year, b.name as brand_name, ci.image_path
    FROM reservations r
    JOIN cars c ON r.car_id = c.id
    JOIN brands b ON c.brand_id = b.id
    LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_primary = 1
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$transaction['id'], $_SESSION['user_id']]);
$reservation = $stmt->fetch();

// Clear completed transaction from session
unset($_SESSION['completed_transaction']);

if (!$reservation) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Reservation Confirmed';
require_once 'includes/header.php';
?>

<div class="success-page">
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h1>Reservation Confirmed!</h1>
            <p>Your car rental reservation has been successfully completed.</p>
            
            <div class="transaction-reference">
                <span>Transaction Reference:</span>
                <strong><?php echo e($transaction['transaction_ref']); ?></strong>
            </div>
            
            <div class="reservation-details-card">
                <h3>Reservation Details</h3>
                
                <div class="reservation-car">
                    <img src="<?php echo e(imageUrl($reservation['image_path'] ?? '')); ?>" 
                         alt="<?php echo e($reservation['brand_name'] . ' ' . $reservation['model']); ?>"
                         onerror="this.src='assets/images/placeholder-car.jpg'">
                    <div>
                        <h4><?php echo e($reservation['brand_name'] . ' ' . $reservation['model']); ?></h4>
                        <p><?php echo e($reservation['year']); ?></p>
                    </div>
                </div>
                
                <div class="reservation-info">
                    <div class="info-row">
                        <span>Reservation ID:</span>
                        <strong>#<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Start Date:</span>
                        <strong><?php echo formatDate($reservation['start_date']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>End Date:</span>
                        <strong><?php echo formatDate($reservation['end_date']); ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Duration:</span>
                        <strong><?php echo $reservation['number_of_days']; ?> days</strong>
                    </div>
                    <div class="info-row">
                        <span>Daily Rate:</span>
                        <strong><?php echo formatPrice($reservation['rental_price']); ?></strong>
                    </div>
                    <div class="info-row total">
                        <span>Total Paid:</span>
                        <strong><?php echo formatPrice($reservation['total_amount']); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="success-actions">
                <a href="my-reservations.php" class="btn btn-primary btn-lg">View My Reservations</a>
                <a href="cars.php" class="btn btn-outline">Browse More Cars</a>
                <a href="dashboard.php" class="btn btn-outline">Go to Dashboard</a>
            </div>
            
            <div class="success-note">
                <i class="fas fa-info-circle"></i>
                <p>A confirmation has been recorded in your account. You can view and manage your reservations from your dashboard.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
