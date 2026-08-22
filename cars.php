<?php
$pageTitle = (($_GET['type'] ?? '') === 'rental') ? 'Rent a Car' : 'Browse Cars';
require_once 'includes/header.php';

$filters = normalizeCarFilters($_GET);
$page = max(1, intval($_GET['page'] ?? 1));
$result = getFilteredCars($filters, $page, ITEMS_PER_PAGE);

$search = $filters['search'];
$brandId = $filters['brand'];
$condition = $filters['condition'];
$category = $filters['category'];
$transmission = $filters['transmission'];
$fuelType = $filters['fuel_type'];
$minPrice = $filters['min_price'];
$maxPrice = $filters['max_price'];
$minYear = $filters['min_year'];
$maxYear = $filters['max_year'];
$sortBy = $filters['sort'];
$type = $filters['type'];
$totalCars = $result['total'];
$pagination = $result['pagination'];
$cars = $result['cars'];
$isRental = $type === 'rental';
$currentYear = (int)date('Y') + 1;

$brands = getAllBrands();
?>

<div class="page-header">
    <div class="container">
        <h1><?php echo $isRental ? 'Rent a Car' : 'Browse Cars'; ?></h1>
        <p><?php echo $isRental ? 'Choose a vehicle and book it by the day' : 'Find your perfect vehicle from our collection'; ?></p>
    </div>
</div>

<div class="cars-page">
    <div class="container">
        <div class="filters-overlay" id="filtersOverlay"></div>
        <div class="cars-layout">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar" id="filtersSidebar">
                <div class="filters-header">
                    <h3>Filters</h3>
                    <button type="button" class="filters-close" id="filtersClose" aria-label="Close filters">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form method="GET" action="cars.php" class="filters-form" id="filtersForm">
                    <input type="hidden" name="sort" value="<?php echo e($sortBy); ?>">
                    
                    <!-- Listing type -->
                    <div class="filter-group">
                        <label for="filterType">Listing Type</label>
                        <select id="filterType" name="type" class="form-control">
                            <option value="">Buy or Rent</option>
                            <option value="purchase" <?php echo $type === 'purchase' ? 'selected' : ''; ?>>For Purchase</option>
                            <option value="rental" <?php echo $type === 'rental' ? 'selected' : ''; ?>>For Rent</option>
                        </select>
                    </div>
                    
                    <!-- Search -->
                    <div class="filter-group">
                        <label>Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Model or brand" 
                               value="<?php echo e($search); ?>">
                    </div>
                    
                    <!-- Brand -->
                    <div class="filter-group">
                        <label>Brand</label>
                        <select name="brand" class="form-control">
                            <option value="">All Brands</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo e($brand['id']); ?>" 
                                        <?php echo $brandId == $brand['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Condition -->
                    <div class="filter-group">
                        <label>Condition</label>
                        <select name="condition" class="form-control">
                            <option value="">All Conditions</option>
                            <option value="new" <?php echo $condition === 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="used" <?php echo $condition === 'used' ? 'selected' : ''; ?>>Used</option>
                        </select>
                    </div>
                    
                    <!-- Category -->
                    <div class="filter-group">
                        <label>Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <option value="sedan" <?php echo $category === 'sedan' ? 'selected' : ''; ?>>Sedan</option>
                            <option value="suv" <?php echo $category === 'suv' ? 'selected' : ''; ?>>SUV</option>
                            <option value="hatchback" <?php echo $category === 'hatchback' ? 'selected' : ''; ?>>Hatchback</option>
                            <option value="coupe" <?php echo $category === 'coupe' ? 'selected' : ''; ?>>Coupe</option>
                            <option value="convertible" <?php echo $category === 'convertible' ? 'selected' : ''; ?>>Convertible</option>
                            <option value="pickup" <?php echo $category === 'pickup' ? 'selected' : ''; ?>>Pickup</option>
                            <option value="luxury" <?php echo $category === 'luxury' ? 'selected' : ''; ?>>Luxury</option>
                            <option value="electric" <?php echo $category === 'electric' ? 'selected' : ''; ?>>Electric</option>
                        </select>
                    </div>
                    
                    <!-- Transmission -->
                    <div class="filter-group">
                        <label>Transmission</label>
                        <select name="transmission" class="form-control">
                            <option value="">All Transmissions</option>
                            <option value="automatic" <?php echo $transmission === 'automatic' ? 'selected' : ''; ?>>Automatic</option>
                            <option value="manual" <?php echo $transmission === 'manual' ? 'selected' : ''; ?>>Manual</option>
                        </select>
                    </div>
                    
                    <!-- Fuel Type -->
                    <div class="filter-group">
                        <label>Fuel Type</label>
                        <select name="fuel_type" class="form-control">
                            <option value="">All Fuel Types</option>
                            <option value="petrol" <?php echo $fuelType === 'petrol' ? 'selected' : ''; ?>>Petrol</option>
                            <option value="diesel" <?php echo $fuelType === 'diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="electric" <?php echo $fuelType === 'electric' ? 'selected' : ''; ?>>Electric</option>
                            <option value="hybrid" <?php echo $fuelType === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="filter-group">
                        <label><?php echo $isRental ? 'Daily Rental Price' : 'Purchase Price'; ?></label>
                        <div class="range-inputs">
                            <input type="number" name="min_price" class="form-control" placeholder="Min" 
                                   value="<?php echo e($minPrice); ?>" min="0" step="1">
                            <span>-</span>
                            <input type="number" name="max_price" class="form-control" placeholder="Max" 
                                   value="<?php echo e($maxPrice); ?>" min="0" step="1">
                        </div>
                    </div>
                    
                    <!-- Year Range -->
                    <div class="filter-group">
                        <label>Year Range</label>
                        <div class="range-inputs">
                            <input type="number" name="min_year" class="form-control" placeholder="Min" 
                                   value="<?php echo e($minYear); ?>" min="1980" max="<?php echo $currentYear; ?>">
                            <span>-</span>
                            <input type="number" name="max_year" class="form-control" placeholder="Max" 
                                   value="<?php echo e($maxYear); ?>" min="1980" max="<?php echo $currentYear; ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                    <a href="cars.php" class="btn btn-outline btn-block">Clear Filters</a>
                </form>
            </aside>
            
            <!-- Cars Content -->
            <div class="cars-content">
                <div class="cars-toolbar">
                    <div class="results-info">
                        <strong><?php echo number_format($totalCars); ?></strong> cars found
                    </div>
                    
                    <button class="filters-toggle btn btn-outline btn-sm" id="filtersToggle" type="button">
                        <i class="fas fa-filter"></i> Filters
                    </button>
                    
                    <div class="sort-dropdown">
                        <label for="sortSelect">Sort by:</label>
                        <select id="sortSelect" class="form-control" onchange="window.location.href=updateQueryParam('sort', this.value)">
                            <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sortBy === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Purchase Price: Low to High</option>
                            <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Purchase Price: High to Low</option>
                            <option value="rental_low" <?php echo $sortBy === 'rental_low' ? 'selected' : ''; ?>>Rental Price: Low to High</option>
                            <option value="rental_high" <?php echo $sortBy === 'rental_high' ? 'selected' : ''; ?>>Rental Price: High to Low</option>
                            <option value="year_new" <?php echo $sortBy === 'year_new' ? 'selected' : ''; ?>>Year: Newest</option>
                            <option value="year_old" <?php echo $sortBy === 'year_old' ? 'selected' : ''; ?>>Year: Oldest</option>
                        </select>
                    </div>
                </div>
                
                <?php if (empty($cars)): ?>
                    <div class="empty-state">
                        <i class="fas fa-car"></i>
                        <h3>No Cars Found</h3>
                        <p>Try adjusting your filters or search criteria</p>
                        <a href="cars.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div class="car-grid">
                        <?php foreach ($cars as $car): 
                            $rating = getAverageRating($car['id']);
                            $primaryImage = getPrimaryCarImage($car['id']);
                        ?>
                            <div class="car-card<?php echo $car['status'] !== 'available' ? ' is-unavailable' : ''; ?>">
                                <div class="car-image">
                                    <img src="<?php echo e($primaryImage); ?>" alt="<?php echo e($car['brand_name'] . ' ' . $car['model']); ?>" onerror="this.src='assets/images/placeholder-car.jpg'">
                                    <span class="car-badge badge-<?php echo e($car['condition']); ?>"><?php echo ucfirst(e($car['condition'])); ?></span>
                                    <?php echo carStatusOverlayHtml($car['status']); ?>

                                     <span class="status-badge-class badge-<?php echo getStatusBadgeClass($car['status']); ?>">
                                        <?php echo e(formatCarPublicStatus($car['status'])); ?>
                                    </span>
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
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <a href="?<?php echo carFilterQuery($filters, ['page' => $pagination['current_page'] - 1]); ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <div class="pagination-numbers">
                                <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                    <a href="?<?php echo carFilterQuery($filters, ['page' => $i]); ?>" 
                                       class="pagination-number <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <a href="?<?php echo carFilterQuery($filters, ['page' => $pagination['current_page'] + 1]); ?>" class="pagination-btn">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function updateQueryParam(key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    url.searchParams.delete('page');
    return url.toString();
}
</script>

<?php require_once 'includes/footer.php'; ?>
