<?php
$requireLogin = true;
$pageTitle = 'My Reviews';
require_once 'includes/header.php';

$userId = $_SESSION['user_id'];
$reviews = getUserReviews($userId);
?>

<div class="my-reviews-page">
    <div class="container">
        <div class="page-header-small">
            <h1>My Reviews</h1>
            <p>Reviews you've written for cars</p>
        </div>
        
        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <i class="fas fa-star"></i>
                <h3>No Reviews Yet</h3>
                <p>You haven't written any reviews yet</p>
                <a href="cars.php" class="btn btn-primary">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card-full">
                        <div class="review-car-header">
                            <h3><?php echo e($review['brand_name'] . ' ' . $review['model']); ?></h3>
                            <span class="review-date"><?php echo formatDate($review['created_at']); ?></span>
                        </div>
                        
                        <div class="review-rating-display">
                            <?php echo generateStarRating($review['rating']); ?>
                            <span class="rating-number"><?php echo $review['rating']; ?> / 5</span>
                        </div>
                        
                        <?php if (!empty($review['comment'])): ?>
                            <div class="review-comment-display">
                                <?php echo nl2br(e($review['comment'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="review-actions">
                            <a href="car-details.php?id=<?php echo $review['car_id']; ?>" class="btn btn-sm btn-outline">
                                View Car
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
