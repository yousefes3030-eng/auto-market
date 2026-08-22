<?php
$requireLogin = true;
require_once 'includes/bootstrap.php';

// Get car ID
$carId = intval($_GET['car_id'] ?? 0);

if (!$carId) {
    header('Location: cars.php');
    exit;
}

// Get car details
$car = getCarById($carId);

if (!$car || !isCarAvailable($carId)) {
    setFlashMessage('This car is not available for purchase.', 'error');
    header('Location: cars.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $confirm = $_POST['confirm'] ?? '';
        
        if ($confirm !== 'yes') {
            $errors[] = 'Please confirm your purchase.';
        }
        
        if (empty($errors)) {
            // Store purchase details in session and redirect to payment
            $_SESSION['pending_purchase'] = [
                'car_id' => $carId,
                'amount' => $car['price']
            ];
            
            header('Location: payment.php?type=purchase');
            exit;
        }
    }
}

$pageTitle = 'Purchase Car';
require_once 'includes/header.php';
?>

<div class="purchase-page">
    <div class="container">
        <div class="page-header-small">
            <h1>Purchase Car</h1>
            <p>Complete your vehicle purchase</p>
        </div>
        
        <div class="purchase-layout">
            <!-- Purchase Form -->
            <div class="purchase-form-section">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="purchase-info-card">
                    <h3>Purchase Information</h3>
                    
                    <div class="info-section">
                        <h4>What happens next?</h4>
                        <ol>
                            <li>Complete the payment process</li>
                            <li>The car is reserved for you while an admin reviews the order</li>
                            <li>Once confirmed, the car is marked sold for other users</li>
                            <li>Our team will contact you for delivery arrangements</li>
                        </ol>
                    </div>
                    
                    <div class="info-section">
                        <h4>Important Notes</h4>
                        <ul>
                            <li>This is a demo payment system - no real money will be charged</li>
                            <li>Purchase is final once payment is processed</li>
                            <li>Vehicle will be reserved after payment, then marked sold after admin confirmation</li>
                            <li>All prices include applicable taxes</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="" class="purchase-form">
                        <?php echo csrfField(); ?>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="confirm" value="yes" required>
                                <span style="user-select: none;">I confirm that I want to purchase this vehicle for <?php echo formatPrice($car['price']); ?></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg btn-block" style="margin-bottom: 0.5rem;">
                            Proceed to Payment
                        </button>
                        
                        <a href="car-details.php?id=<?php echo $carId; ?>" class="btn btn-outline btn-block">
                            Cancel
                        </a>
                    </form>
                </div>
            </div>
            
            <!-- Car Summary -->
            <div class="car-summary-section">
                <div class="car-summary-card">
                    <h3>Vehicle Details</h3>
                    
                    <div class="summary-car-image">
                        <img src="<?php echo e(getPrimaryCarImage($carId)); ?>" 
                             alt="<?php echo e($car['brand_name'] . ' ' . $car['model']); ?>"
                             onerror="this.src='assets/images/placeholder-car.jpg'">
                    </div>
                    
                    <h4><?php echo e($car['brand_name'] . ' ' . $car['model']); ?></h4>
                    <span class="car-badge badge-<?php echo e($car['condition']); ?>"><?php echo ucfirst(e($car['condition'])); ?></span>
                    
                    <div class="summary-details">
                        <div class="summary-detail">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo e($car['year']); ?></span>
                        </div>
                        <div class="summary-detail">
                            <i class="fas fa-cog"></i>
                            <span><?php echo ucfirst(e($car['transmission'])); ?></span>
                        </div>
                        <div class="summary-detail">
                            <i class="fas fa-gas-pump"></i>
                            <span><?php echo ucfirst(e($car['fuel_type'])); ?></span>
                        </div>
                        <div class="summary-detail">
                            <i class="fas fa-tachometer-alt"></i>
                            <span><?php echo number_format($car['mileage']); ?> km</span>
                        </div>
                    </div>
                    
                    <div class="summary-price">
                        <span>Purchase Price</span>
                        <strong><?php echo formatPrice($car['price']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
