<?php
$requireAdmin = true;
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDB();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars");
$totalCars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM brands");
$totalBrands = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars WHERE status = 'available'");
$availableCars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars WHERE status = 'rented'");
$rentedCars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars WHERE status = 'sold'");
$soldCars = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations");
$totalReservations = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'");
$pendingReservations = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'");
$confirmedReservations = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'completed'");
$completedReservations = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'cancelled'");
$cancelledReservations = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
$totalRevenue = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
$totalReviews = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT AVG(rating) as avg FROM reviews");
$avgRating = $stmt->fetch()['avg'] ?? 0;

// Get recent reservations
$stmt = $pdo->query("
    SELECT r.*, u.name as user_name, c.model, b.name as brand_name
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    JOIN brands b ON c.brand_id = b.id
    ORDER BY r.created_at DESC
    LIMIT 5
");
$recentReservations = $stmt->fetchAll();

// Get popular cars
$stmt = $pdo->query("
    SELECT c.id, c.model, c.year, b.name as brand_name, COUNT(r.id) as reservation_count
    FROM cars c
    JOIN brands b ON c.brand_id = b.id
    LEFT JOIN reservations r ON c.id = r.car_id
    GROUP BY c.id
    ORDER BY reservation_count DESC
    LIMIT 5
");
$popularCars = $stmt->fetchAll();
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo e($currentUser['name']); ?>!</p>
        </div>
        
        <!-- Statistics Grid -->
        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <span class="stat-value"><?php echo number_format($totalUsers); ?></span>
                    <span class="stat-label">Total Users</span>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-car"></i>
                </div>
                <div class="stat-details">
                    <span class="stat-value"><?php echo number_format($totalCars); ?></span>
                    <span class="stat-label">Total Cars</span>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-tag"></i>
                </div>
                <div class="stat-details">
                    <span class="stat-value"><?php echo number_format($totalBrands); ?></span>
                    <span class="stat-label">Brands</span>
                </div>
            </div>
            
            <div class="admin-stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-details">
                    <span class="stat-value"><?php echo number_format($totalReservations); ?></span>
                    <span class="stat-label">Reservations</span>
                </div>
            </div>
        </div>
        
        <!-- Secondary Stats -->
        <div class="admin-secondary-stats">
            <div class="stat-group">
                <h3>Car Status</h3>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="label">Available:</span>
                        <strong><?php echo $availableCars; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span class="label">Rented:</span>
                        <strong><?php echo $rentedCars; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span class="label">Sold:</span>
                        <strong><?php echo $soldCars; ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="stat-group">
                <h3>Reservation Status</h3>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="label">Pending:</span>
                        <strong><?php echo $pendingReservations; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span class="label">Confirmed:</span>
                        <strong><?php echo $confirmedReservations; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span class="label">Completed:</span>
                        <strong><?php echo $completedReservations; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span class="label">Cancelled:</span>
                        <strong><?php echo $cancelledReservations; ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="stat-group">
                <h3>Revenue & Reviews</h3>
                <div class="stat-items">
                    <div class="stat-item">
                        <span class="label">Total Revenue:</span>
                        <strong><?php echo formatPrice($totalRevenue); ?></strong>
                    </div>
                    <div class="stat-item">
                        <span class="label">Total Reviews:</span>
                        <strong><?php echo $totalReviews; ?></strong>
                    </div>
                    <div class="stat-item">
                        <span class="label">Avg Rating:</span>
                        <strong><?php echo number_format($avgRating, 1); ?> / 5</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Reservations -->
        <div class="admin-section">
            <div class="section-header">
                <h2>Recent Reservations</h2>
                <a href="reservations.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Car</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentReservations as $res): ?>
                            <tr>
                                <td>#<?php echo str_pad($res['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo e($res['user_name']); ?></td>
                                <td><?php echo e($res['brand_name'] . ' ' . $res['model']); ?></td>
                                <td><?php echo formatDate($res['start_date']); ?></td>
                                <td><?php echo formatDate($res['end_date']); ?></td>
                                <td><?php echo formatPrice($res['total_amount']); ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo getStatusBadgeClass($res['status']); ?>">
                                        <?php echo ucfirst(e($res['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Popular Cars -->
        <div class="admin-section">
            <h2>Most Reserved Cars</h2>
            
            <div class="popular-cars-list">
                <?php foreach ($popularCars as $car): ?>
                    <div class="popular-car-item">
                        <div>
                            <strong><?php echo e($car['brand_name'] . ' ' . $car['model']); ?></strong>
                            <span><?php echo e($car['year']); ?></span>
                        </div>
                        <span class="reservation-count"><?php echo $car['reservation_count']; ?> reservations</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
