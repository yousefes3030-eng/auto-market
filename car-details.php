<?php
require_once 'includes/bootstrap.php';

$carId = intval($_GET['id'] ?? 0);

if (!$carId) {
    header('Location: cars.php');
    exit;
}

$car = getCarById($carId);

if (!$car) {
    header('Location: cars.php');
    exit;
}

$images = getCarImages($carId);
$rating = getAverageRating($carId);
$reviews = getCarReviews($carId);
$hasReviewed = isLoggedIn() ? hasUserReviewedCar($_SESSION['user_id'], $carId) : false;

$pageTitle = $car['brand_name'] . ' ' . $car['model'] . ' - ' . $car['year'];
require_once 'includes/header.php';
?>

<div class="car-details-page">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="cars.php">Cars</a>
            <span>/</span>
            <span><?php echo e($car['brand_name'] . ' ' . $car['model']); ?></span>
        </div>
        
        <div class="car-details-layout">
            <!-- Left Column: Images -->
            <div class="car-gallery">
                <div class="main-image" id="mainImage">
                    <?php if (!empty($images)): ?>
                        <img src="<?php echo e(imageUrl($images[0]['image_path'])); ?>" alt="<?php echo e($car['brand_name'] . ' ' . $car['model']); ?>" id="mainImg">
                        <?php if (count($images) > 1): ?>
                            <button type="button" class="gallery-nav gallery-prev" id="galleryPrev" aria-label="Previous image">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button type="button" class="gallery-nav gallery-next" id="galleryNext" aria-label="Next image">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                            <span class="gallery-counter" id="galleryCounter">1 / <?php echo count($images); ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <img src="<?php echo e(imageUrl('assets/images/placeholder-car.svg')); ?>" alt="Car image" id="mainImg">
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($images)): ?>
                    <div class="thumbnail-images" id="thumbnailImages">
                        <?php foreach ($images as $index => $image): ?>
                            <button type="button"
                                    class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                    data-index="<?php echo $index; ?>"
                                    data-src="<?php echo e(imageUrl($image['image_path'])); ?>"
                                    aria-label="Show photo <?php echo $index + 1; ?>">
                                <img src="<?php echo e(imageUrl($image['image_path'])); ?>" alt="Photo <?php echo $index + 1; ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column: Details -->
            <div class="car-info">
                <div class="car-header-section">
                    <h1><?php echo e($car['brand_name'] . ' ' . $car['model']); ?></h1>
                    <span class="car-badge badge-<?php echo e($car['condition']); ?>"><?php echo ucfirst(e($car['condition'])); ?></span>
                </div>
                
                <?php if ($rating['count'] > 0): ?>
                    <div class="car-rating-section">
                        <?php echo generateStarRating($rating['average']); ?>
                        <span class="rating-text"><?php echo e($rating['average']); ?> / 5</span>
                        <span class="rating-count">(<?php echo e($rating['count']); ?> reviews)</span>
                    </div>
                <?php endif; ?>
                
                <div class="car-pricing-section">
                    <div class="price-box">
                        <div class="price-item">
                            <span class="price-label">Purchase Price</span>
                            <span class="price-value"><?php echo formatPrice($car['price']); ?></span>
                        </div>
                        <div class="price-item">
                            <span class="price-label">Rental Price</span>
                            <span class="price-value"><?php echo formatPrice($car['rental_price_per_day']); ?> <small>/ day</small></span>
                        </div>
                    </div>
                </div>
                
                <div class="car-status-section">
                    <span class="status-badge badge-<?php echo getStatusBadgeClass($car['status']); ?>">
                        <?php echo ucfirst(e($car['status'])); ?>
                    </span>
                </div>
                
                <?php if ($car['status'] === 'available'): ?>
                    <div class="car-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="reservation.php?car_id=<?php echo $carId; ?>" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-calendar-check"></i> Rent This Car
                            </a>
                            <a href="purchase.php?car_id=<?php echo $carId; ?>" class="btn btn-success btn-lg btn-block">
                                <i class="fas fa-shopping-cart"></i> Purchase This Car
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-sign-in-alt"></i> Login to Rent or Buy
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        This vehicle is currently <?php echo e($car['status']); ?> and not available for rent or purchase.
                    </div>
                <?php endif; ?>
                
                <div class="car-specifications">
                    <h3>Specifications</h3>
                    <div class="spec-grid">
                        <div class="spec-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <span class="spec-label">Year</span>
                                <span class="spec-value"><?php echo e($car['year']); ?></span>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <i class="fas fa-cog"></i>
                            <div>
                                <span class="spec-label">Transmission</span>
                                <span class="spec-value"><?php echo ucfirst(e($car['transmission'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <i class="fas fa-gas-pump"></i>
                            <div>
                                <span class="spec-label">Fuel Type</span>
                                <span class="spec-value"><?php echo ucfirst(e($car['fuel_type'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <i class="fas fa-tachometer-alt"></i>
                            <div>
                                <span class="spec-label">Mileage</span>
                                <span class="spec-value"><?php echo number_format($car['mileage']); ?> km</span>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <i class="fas fa-engine"></i>
                            <div>
                                <span class="spec-label">Engine</span>
                                <span class="spec-value"><?php echo e($car['engine']); ?></span>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <i class="fas fa-palette"></i>
                            <div>
                                <span class="spec-label">Color</span>
                                <span class="spec-value"><?php echo e($car['color']); ?></span>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <span class="spec-label">Seats</span>
                                <span class="spec-value"><?php echo e($car['seats']); ?></span>
                            </div>
                        </div>
                        
                        <div class="spec-item">
                            <i class="fas fa-tag"></i>
                            <div>
                                <span class="spec-label">Category</span>
                                <span class="spec-value"><?php echo ucfirst(e($car['category'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Description -->
        <div class="car-description-section">
            <h2>Description</h2>
            <p><?php echo nl2br(e($car['description'])); ?></p>
        </div>
        
        <!-- Reviews Section -->
        <div class="car-reviews-section">
            <div class="reviews-header">
                <h2>Customer Reviews</h2>
                <?php if (isLoggedIn() && !$hasReviewed && $car['status'] === 'available'): ?>
                    <a href="add-review.php?car_id=<?php echo $carId; ?>" class="btn btn-primary">
                        <i class="fas fa-star"></i> Write a Review
                    </a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <h3>No Reviews Yet</h3>
                    <p>Be the first to review this car</p>
                </div>
            <?php else: ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div class="review-user">
                                    <i class="fas fa-user-circle"></i>
                                    <span><?php echo e($review['user_name']); ?></span>
                                </div>
                                <div class="review-rating">
                                    <?php echo generateStarRating($review['rating']); ?>
                                    <span class="review-date"><?php echo formatDate($review['created_at']); ?></span>
                                </div>
                            </div>
                            <?php if (!empty($review['comment'])): ?>
                                <div class="review-comment">
                                    <?php echo nl2br(e($review['comment'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    const images = <?php echo json_encode(array_values(array_map(function ($image) {
        return imageUrl($image['image_path']);
    }, $images))); ?>;
    const mainImg = document.getElementById('mainImg');
    const thumbs = Array.from(document.querySelectorAll('.thumbnail'));
    const counter = document.getElementById('galleryCounter');
    let current = 0;

    function show(index) {
        if (!images.length || !mainImg) return;
        current = (index + images.length) % images.length;
        mainImg.src = images[current];
        thumbs.forEach(function (thumb, i) {
            thumb.classList.toggle('active', i === current);
        });
        if (counter) {
            counter.textContent = (current + 1) + ' / ' + images.length;
        }
        const activeThumb = thumbs[current];
        if (activeThumb && activeThumb.scrollIntoView) {
            activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }
    }

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            show(parseInt(thumb.dataset.index, 10));
        });
    });

    const prev = document.getElementById('galleryPrev');
    const next = document.getElementById('galleryNext');
    if (prev) prev.addEventListener('click', function () { show(current - 1); });
    if (next) next.addEventListener('click', function () { show(current + 1); });

    document.addEventListener('keydown', function (e) {
        if (!images.length || images.length < 2) return;
        if (e.key === 'ArrowLeft') show(current - 1);
        if (e.key === 'ArrowRight') show(current + 1);
    });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
