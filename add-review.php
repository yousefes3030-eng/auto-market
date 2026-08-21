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

if (!$car) {
    header('Location: cars.php');
    exit;
}

// Check if user has already reviewed
if (hasUserReviewedCar($_SESSION['user_id'], $carId)) {
    setFlashMessage('You have already reviewed this car.', 'info');
    header('Location: car-details.php?id=' . $carId);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $rating = intval($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        // Validation
        if ($rating < 1 || $rating > 5) {
            $errors[] = 'Please select a rating between 1 and 5 stars.';
        }
        
        if (empty($comment)) {
            $errors[] = 'Please write a comment about your experience.';
        } elseif (strlen($comment) < 10) {
            $errors[] = 'Comment must be at least 10 characters.';
        }
        
        if (empty($errors)) {
            $pdo = getDB();
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO reviews (user_id, car_id, rating, comment)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$_SESSION['user_id'], $carId, $rating, $comment]);
                
                setFlashMessage('Review submitted successfully!', 'success');
                header('Location: car-details.php?id=' . $carId);
                exit;
            } catch (PDOException $e) {
                error_log("Review Error: " . $e->getMessage());
                $errors[] = 'Failed to submit review. Please try again.';
            }
        }
    }
}

$pageTitle = 'Write Review';
require_once 'includes/header.php';
?>

<div class="add-review-page">
    <div class="container">
        <div class="page-header-small">
            <h1>Write a Review</h1>
            <p>Share your experience with this car</p>
        </div>
        
        <div class="review-layout">
            <!-- Review Form -->
            <div class="review-form-section">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="review-form">
                    <?php echo csrfField(); ?>
                    
                    <div class="form-group">
                        <label>Your Rating</label>
                        <div class="star-rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" 
                                       <?php echo (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : ''; ?> required>
                                <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment">Your Review</label>
                        <textarea id="comment" name="comment" class="form-control" rows="6" 
                                  placeholder="Share your experience with this car..." required><?php echo isset($_POST['comment']) ? e($_POST['comment']) : ''; ?></textarea>
                        <small class="form-text">Minimum 10 characters</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Submit Review
                    </button>
                    
                    <a href="car-details.php?id=<?php echo $carId; ?>" class="btn btn-outline btn-block">
                        Cancel
                    </a>
                </form>
            </div>
            
            <!-- Car Summary -->
            <div class="car-summary-section">
                <div class="car-summary-card">
                    <h3>Reviewing</h3>
                    
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
                            <i class="fas fa-tag"></i>
                            <span><?php echo ucfirst(e($car['condition'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
