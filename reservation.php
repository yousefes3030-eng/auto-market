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
    setFlashMessage('This car is not available for reservation.', 'error');
    header('Location: cars.php');
    exit;
}

$errors = [];
$calculatedTotal = 0;
$calculatedDays = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        
        // Validation
        if (empty($startDate)) {
            $errors[] = 'Start date is required.';
        }
        
        if (empty($endDate)) {
            $errors[] = 'End date is required.';
        }
        
        if (empty($errors)) {
            // Validate dates
            $start = strtotime($startDate);
            $end = strtotime($endDate);
            $today = strtotime(date('Y-m-d'));
            
            if ($start < $today) {
                $errors[] = 'Start date cannot be in the past.';
            }
            
            if ($end <= $start) {
                $errors[] = 'End date must be after start date.';
            }
            
            // Check for overlapping reservations
            if (empty($errors) && hasOverlappingReservation($carId, $startDate, $endDate)) {
                $errors[] = 'This car is already reserved for the selected dates. Please choose different dates.';
            }
            
            // Calculate rental details
            if (empty($errors)) {
                $calculatedDays = calculateRentalDays($startDate, $endDate);
                $calculatedTotal = calculateRentalTotal($car['rental_price_per_day'], $calculatedDays);
                
                if ($calculatedDays < 1) {
                    $errors[] = 'Rental period must be at least 1 day.';
                }
                
                // Store reservation details in session and redirect to payment
                if (empty($errors)) {
                    $_SESSION['pending_reservation'] = [
                        'car_id' => $carId,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'number_of_days' => $calculatedDays,
                        'rental_price' => $car['rental_price_per_day'],
                        'total_amount' => $calculatedTotal
                    ];
                    
                    header('Location: payment.php?type=reservation');
                    exit;
                }
            }
        }
    }
}

// Set min date to today
$minDate = date('Y-m-d');
$maxDate = date('Y-m-d', strtotime('+1 year'));
$pageTitle = 'Make Reservation';
require_once 'includes/header.php';
?>

<div class="reservation-page">
    <div class="container">
        <div class="page-header-small">
            <h1>Make a Reservation</h1>
            <p>Reserve your car for rental</p>
        </div>
        
        <div class="reservation-layout">
            <!-- Reservation Form -->
            <div class="reservation-form-section">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="reservation-form" id="reservationForm">
                    <?php echo csrfField(); ?>
                    
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>"
                               value="<?php echo isset($_POST['start_date']) ? e($_POST['start_date']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" 
                               min="<?php echo $minDate; ?>" max="<?php echo $maxDate; ?>"
                               value="<?php echo isset($_POST['end_date']) ? e($_POST['end_date']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div id="rentalSummary" class="rental-summary" style="display: none;">
                        <div class="summary-row">
                            <span>Number of days:</span>
                            <strong id="daysCount">0</strong>
                        </div>
                        <div class="summary-row">
                            <span>Daily rate:</span>
                            <strong><?php echo formatPrice($car['rental_price_per_day']); ?></strong>
                        </div>
                        <div class="summary-row total">
                            <span>Estimated total:</span>
                            <strong id="totalAmount"><?php echo formatPrice(0); ?></strong>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Proceed to Payment
                    </button>
                    
                    <a href="car-details.php?id=<?php echo $carId; ?>" class="btn btn-outline btn-block">
                        Cancel
                    </a>
                </form>
            </div>
            
            <!-- Car Summary -->
            <div class="car-summary-section">
                <div class="car-summary-card">
                    <h3>Car Details</h3>
                    
                    <div class="summary-car-image">
                        <img src="<?php echo e(getPrimaryCarImage($carId)); ?>" 
                             alt="<?php echo e($car['brand_name'] . ' ' . $car['model']); ?>"
                             onerror="this.src='assets/images/placeholder-car.jpg'">
                    </div>
                    
                    <h4><?php echo e($car['brand_name'] . ' ' . $car['model']); ?></h4>
                    
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
                            <i class="fas fa-users"></i>
                            <span><?php echo e($car['seats']); ?> seats</span>
                        </div>
                    </div>
                    
                    <div class="summary-price">
                        <span>Rental Rate</span>
                        <strong><?php echo formatPrice($car['rental_price_per_day']); ?> / day</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const startDateInput = document.getElementById('start_date');
const endDateInput = document.getElementById('end_date');
const rentalSummary = document.getElementById('rentalSummary');
const daysCount = document.getElementById('daysCount');
const totalAmount = document.getElementById('totalAmount');
const dailyRate = <?php echo $car['rental_price_per_day']; ?>;

function calculateRental() {
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 0 && end > start) {
            daysCount.textContent = diffDays;
            const total = dailyRate * diffDays;
            totalAmount.textContent = '$' + total.toFixed(2);
            rentalSummary.style.display = 'block';
        } else {
            rentalSummary.style.display = 'none';
        }
    } else {
        rentalSummary.style.display = 'none';
    }
}

startDateInput.addEventListener('change', calculateRental);
endDateInput.addEventListener('change', calculateRental);

// Update end date min when start date changes
startDateInput.addEventListener('change', function() {
    const startDate = new Date(this.value);
    startDate.setDate(startDate.getDate() + 1);
    endDateInput.min = startDate.toISOString().split('T')[0];
});
</script>

<?php require_once 'includes/footer.php'; ?>
