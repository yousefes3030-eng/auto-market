<?php
$requireLogin = true;
require_once 'includes/bootstrap.php';

// Get payment type
$paymentType = $_GET['type'] ?? '';

if (!in_array($paymentType, ['reservation', 'purchase'])) {
    header('Location: index.php');
    exit;
}

// Get pending transaction from session
$pendingTransaction = $_SESSION['pending_' . $paymentType] ?? null;

if (!$pendingTransaction) {
    setFlashMessage('No pending transaction found.', 'error');
    header('Location: index.php');
    exit;
}

// Get car details
$car = getCarById($pendingTransaction['car_id']);

if (!$car) {
    unset($_SESSION['pending_' . $paymentType]);
    header('Location: cars.php');
    exit;
}

$errors = [];
$processing = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'cancel') {
            // Cancel payment
            unset($_SESSION['pending_' . $paymentType]);
            setFlashMessage('Payment cancelled.', 'info');
            header('Location: car-details.php?id=' . $car['id']);
            exit;
        }
        
        if ($action === 'pay') {
            // Validate fake payment fields
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email) || !validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            }
            
            if (empty($errors)) {
                $processing = true;
                
                try {
                    $pdo = getDB();
                    $pdo->beginTransaction();
                    
                    if ($paymentType === 'reservation') {
                        $carRow = lockCarRow($pendingTransaction['car_id'], $pdo);
                        if (!$carRow || $carRow['status'] !== 'available') {
                            throw new Exception('This car is no longer available for reservation.');
                        }
                        if (hasOverlappingReservation(
                            $pendingTransaction['car_id'],
                            $pendingTransaction['start_date'],
                            $pendingTransaction['end_date']
                        )) {
                            throw new Exception('This car is already reserved for the selected dates.');
                        }

                        $stmt = $pdo->prepare("
                            INSERT INTO reservations (user_id, car_id, start_date, end_date, number_of_days, rental_price, total_amount, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
                        ");
                        $stmt->execute([
                            $_SESSION['user_id'],
                            $pendingTransaction['car_id'],
                            $pendingTransaction['start_date'],
                            $pendingTransaction['end_date'],
                            $pendingTransaction['number_of_days'],
                            $pendingTransaction['rental_price'],
                            $pendingTransaction['total_amount']
                        ]);
                        $reservationId = $pdo->lastInsertId();
                        
                        // Create payment record
                        $transactionRef = generateTransactionReference();
                        $stmt = $pdo->prepare("
                            INSERT INTO payments (reservation_id, purchase_id, user_id, amount, payment_method, transaction_reference, status)
                            VALUES (?, NULL, ?, ?, 'fake_paypal', ?, 'completed')
                        ");
                        $stmt->execute([
                            $reservationId,
                            $_SESSION['user_id'],
                            $pendingTransaction['total_amount'],
                            $transactionRef
                        ]);

                        syncCarPublicStatus($pendingTransaction['car_id'], $pdo);
                        
                        $pdo->commit();
                        
                        // Clear pending transaction
                        unset($_SESSION['pending_reservation']);
                        
                        // Redirect to success page
                        $_SESSION['completed_transaction'] = [
                            'type' => 'reservation',
                            'id' => $reservationId,
                            'transaction_ref' => $transactionRef
                        ];
                        
                        header('Location: reservation-success.php');
                        exit;
                        
                    } else if ($paymentType === 'purchase') {
                        $carRow = lockCarRow($pendingTransaction['car_id'], $pdo);
                        if (!$carRow || $carRow['status'] !== 'available') {
                            throw new Exception('Car is no longer available for purchase.');
                        }
                        
                        // Create purchase
                        $stmt = $pdo->prepare("
                            INSERT INTO purchases (user_id, car_id, amount, status)
                            VALUES (?, ?, ?, 'pending')
                        ");
                        $stmt->execute([
                            $_SESSION['user_id'],
                            $pendingTransaction['car_id'],
                            $pendingTransaction['amount']
                        ]);
                        $purchaseId = $pdo->lastInsertId();
                        
                        // Create payment record
                        $transactionRef = generateTransactionReference();
                        $stmt = $pdo->prepare("
                            INSERT INTO payments (reservation_id, purchase_id, user_id, amount, payment_method, transaction_reference, status)
                            VALUES (NULL, ?, ?, ?, 'fake_paypal', ?, 'completed')
                        ");
                        $stmt->execute([
                            $purchaseId,
                            $_SESSION['user_id'],
                            $pendingTransaction['amount'],
                            $transactionRef
                        ]);
                        $paymentId = $pdo->lastInsertId();
                        
                        // Update purchase with payment ID
                        $stmt = $pdo->prepare("UPDATE purchases SET payment_id = ? WHERE id = ?");
                        $stmt->execute([$paymentId, $purchaseId]);

                        syncCarPublicStatus($pendingTransaction['car_id'], $pdo);
                        
                        $pdo->commit();
                        
                        // Clear pending transaction
                        unset($_SESSION['pending_purchase']);
                        
                        // Redirect to success page
                        $_SESSION['completed_transaction'] = [
                            'type' => 'purchase',
                            'id' => $purchaseId,
                            'transaction_ref' => $transactionRef
                        ];
                        
                        header('Location: purchase-success.php');
                        exit;
                    }
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    error_log("Payment Error: " . $e->getMessage());
                    $errors[] = 'Payment processing failed: ' . $e->getMessage();
                    $processing = false;
                }
            }
        }
    }
}

$pageTitle = 'Payment';
require_once 'includes/header.php';
?>

<div class="payment-page">
    <div class="container">
        <div class="payment-warning">
            <i class="fas fa-info-circle"></i>
            <strong>DEMO PAYMENT SYSTEM</strong> - This is a simulated payment. No real money will be charged.
        </div>
        
        <div class="payment-layout">
            <!-- Payment Form -->
            <div class="payment-form-section">
                <div class="fake-paypal-card">
                    <div class="paypal-header">
                        <i class="fab fa-paypal"></i>
                        <h2>Fake PayPal Checkout</h2>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="payment-form">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="pay">
                        
                        <div class="form-group">
                            <label for="email">PayPal Email (Demo)</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo e($currentUser['email']); ?>" required>
                        </div>
                        
                        <div class="demo-notice">
                            <i class="fas fa-shield-alt"></i>
                            <p>This is a simulated payment system for demonstration purposes. No real payment will be processed.</p>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block" <?php echo $processing ? 'disabled' : ''; ?>>
                            <i class="fab fa-paypal"></i> 
                            <?php echo $processing ? 'Processing...' : 'Complete Payment'; ?>
                        </button>
                    </form>
                    
                    <form method="POST" action="" style="margin-bottom: 2rem; padding-left:2rem; padding-right: 2rem;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn btn-outline btn-block">Cancel Payment</button>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="order-summary-section">
                <div class="order-summary-card">
                    <h3>Order Summary</h3>
                    
                    <div class="summary-car">
                        <img src="<?php echo e(getPrimaryCarImage($car['id'])); ?>" 
                             alt="<?php echo e($car['brand_name'] . ' ' . $car['model']); ?>"
                             onerror="this.src='assets/images/placeholder-car.jpg'">
                        <div>
                            <h4><?php echo e($car['brand_name'] . ' ' . $car['model']); ?></h4>
                            <p><?php echo e($car['year']); ?> • <?php echo ucfirst(e($car['condition'])); ?></p>
                        </div>
                    </div>
                    
                    <?php if ($paymentType === 'reservation'): ?>
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Start Date:</span>
                                <strong><?php echo formatDate($pendingTransaction['start_date']); ?></strong>
                            </div>
                            <div class="summary-row">
                                <span>End Date:</span>
                                <strong><?php echo formatDate($pendingTransaction['end_date']); ?></strong>
                            </div>
                            <div class="summary-row">
                                <span>Number of Days:</span>
                                <strong><?php echo $pendingTransaction['number_of_days']; ?></strong>
                            </div>
                            <div class="summary-row">
                                <span>Daily Rate:</span>
                                <strong><?php echo formatPrice($pendingTransaction['rental_price']); ?></strong>
                            </div>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total Amount:</span>
                            <strong><?php echo formatPrice($pendingTransaction['total_amount']); ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Purchase Type:</span>
                                <strong><?php echo ucfirst(e($car['condition'])); ?> Car</strong>
                            </div>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total Amount:</span>
                            <strong><?php echo formatPrice($pendingTransaction['amount']); ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
