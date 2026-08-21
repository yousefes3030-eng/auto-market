<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Get featured cars
$featuredCars = getFeaturedCars(6);

// Get all brands for display
$brands = getAllBrands();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Find Your Perfect Car</h1>
            <p class="hero-subtitle">Rent a reliable used car or find your next new or pre-owned vehicle</p>
            <div class="hero-actions">
                <a href="cars.php" class="btn btn-primary btn-lg">Browse Cars</a>
                <a href="cars.php?type=rental" class="btn btn-outline btn-lg">Rent a Car</a>
            </div>
        </div>
    </div>
</section>

<!-- Search Section -->
<section class="search-section">
    <div class="container">
        <div class="search-card">
            <h2>Search for Your Car</h2>
            <form action="cars.php" method="GET" class="search-form">
                <div class="search-row">
                    <div class="search-field">
                        <label for="search">Search by Model</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="e.g., Camry, Civic">
                    </div>
                    
                    <div class="search-field">
                        <label for="brand">Brand</label>
                        <select id="brand" name="brand" class="form-control">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo e($brand['id']); ?>"><?php echo e($brand['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="search-field">
                        <label for="category">Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <option value="sedan">Sedan</option>
                            <option value="suv">SUV</option>
                            <option value="hatchback">Hatchback</option>
                            <option value="coupe">Coupe</option>
                            <option value="luxury">Luxury</option>
                            <option value="electric">Electric</option>
                        </select>
                    </div>
                    
                    <div class="search-field">
                        <label for="type">Listing Type</label>
                        <select id="type" name="type" class="form-control">
                            <option value="">Buy or Rent</option>
                            <option value="purchase">For Purchase</option>
                            <option value="rental">For Rent</option>
                        </select>
                    </div>
                    
                    <div class="search-field">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary btn-block">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Featured Cars -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Cars</h2>
            <p>Handpicked vehicles for you</p>
        </div>
        
        <?php if (empty($featuredCars)): ?>
            <div class="empty-state">
                <i class="fas fa-car"></i>
                <h3>No Featured Cars</h3>
                <p>Check back soon for featured vehicles</p>
            </div>
        <?php else: ?>
            <div class="car-grid">
                <?php foreach ($featuredCars as $car): 
                    $rating = getAverageRating($car['id']);
                    $primaryImage = getPrimaryCarImage($car['id']);
                ?>
                    <div class="car-card">
                        <div class="car-image">
                            <img src="<?php echo e($primaryImage); ?>" alt="<?php echo e($car['brand_name'] . ' ' . $car['model']); ?>" onerror="this.src='assets/images/placeholder-car.jpg'">
                            <span class="car-badge badge-<?php echo e($car['condition']); ?>"><?php echo ucfirst(e($car['condition'])); ?></span>
                            <?php if ($car['status'] !== 'available'): ?>
                                <span class="car-status-overlay"><?php echo ucfirst(e($car['status'])); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="car-content">
                            <div class="car-header">
                                <h3><?php echo e($car['brand_name'] . ' ' . $car['model']); ?></h3>
                                <?php if ($rating['count'] > 0): ?>
                                    <div class="car-rating">
                                        <?php echo generateStarRating($rating['average']); ?>
                                        <span><?php echo e($rating['average']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="car-details">
                                <span><i class="fas fa-calendar"></i> <?php echo e($car['year']); ?></span>
                                <span><i class="fas fa-cog"></i> <?php echo ucfirst(e($car['transmission'])); ?></span>
                                <span><i class="fas fa-gas-pump"></i> <?php echo ucfirst(e($car['fuel_type'])); ?></span>
                            </div>
                            
                            <div class="car-pricing">
                                <div class="price-item">
                                    <span class="price-label">Purchase</span>
                                    <span class="price-value"><?php echo formatPrice($car['price']); ?></span>
                                </div>
                                <div class="price-item">
                                    <span class="price-label">From</span>
                                    <span class="price-value"><?php echo formatPrice($car['rental_price_per_day']); ?>/day</span>
                                </div>
                            </div>
                            
                            <a href="car-details.php?id=<?php echo e($car['id']); ?>" class="btn btn-primary btn-block">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-footer">
                <a href="cars.php" class="btn btn-outline">View All Cars</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Popular Brands -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header">
            <h2>Popular Brands</h2>
            <p>Browse by your favorite manufacturers</p>
        </div>
        
        <div class="brand-grid">
            <?php foreach (array_slice($brands, 0, 8) as $brand): ?>
                <a href="cars.php?brand=<?php echo e($brand['id']); ?>" class="brand-card">
                    <div class="brand-logo">
                        <?php if (!empty($brand['logo'])): ?>
                            <img src="<?php echo e(imageUrl($brand['logo'])); ?>"
                                 alt="<?php echo e($brand['name']); ?> logo"
                                 class="brand-logo-img">
                        <?php else: ?>
                            <span class="brand-logo-placeholder" aria-hidden="true">
                                <i class="fas fa-car-side"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4><?php echo e($brand['name']); ?></h4>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer">
            <a href="brands.php" class="btn btn-outline">View All Brands</a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose <?php echo APP_NAME; ?></h2>
            <p>Your trusted partner for quality vehicles</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Verified Cars</h3>
                <p>All vehicles are thoroughly inspected and verified for quality and safety</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Easy Reservations</h3>
                <p>Simple and quick booking process with instant confirmation</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>Secure Platform</h3>
                <p>Your data and transactions are protected with industry-standard security</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h3>Flexible Options</h3>
                <p>Choose to rent short-term or purchase - we offer flexible solutions</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section section-gray">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Get started in four simple steps</p>
        </div>
        
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Browse</h3>
                <p>Explore our wide selection of quality vehicles</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Choose</h3>
                <p>Select the perfect car that meets your needs</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Reserve</h3>
                <p>Book your car with our easy reservation system</p>
            </div>
            
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Drive</h3>
                <p>Pick up your car and enjoy the journey</p>
            </div>
        </div>
        
        <div class="section-footer">
            <a href="cars.php" class="btn btn-primary btn-lg">Get Started Now</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
