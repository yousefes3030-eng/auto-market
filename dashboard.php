<?php
$requireLogin = true;
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

$userId = $_SESSION['user_id'];
$pdo = getDB();

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE user_id = ?");
$stmt->execute([$userId]);
$totalReservations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE user_id = ? AND status = 'confirmed'");
$stmt->execute([$userId]);
$activeReservations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$completedReservations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reservations WHERE user_id = ? AND status = 'cancelled'");
$stmt->execute([$userId]);
$cancelledReservations = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
$stmt->execute([$userId]);
$totalReviews = $stmt->fetch()['total'];

// Get recent reservations
$recentReservations = getUserReservations($userId);
$recentReservations = array_slice($recentReservations, 0, 5);
?>

<div class="dashboard-page">
    <div class="container">
        <div class="page-header-small">
            <h1>Welcome back, <?php echo e($currentUser['name']); ?>!</h1>
            <p>Manage your reservations and account</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $totalReservations; ?></span>
                    <span class="stat-label">Total Reservations</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $activeReservations; ?></span>
                    <span class="stat-label">Active Reservations</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $completedReservations; ?></span>
                    <span class="stat-label">Completed</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $totalReviews; ?></span>
                    <span class="stat-label">Reviews Written</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="dashboard-section">
            <h2>Quick Actions</h2>
            <div class="quick-actions">
                <a href="cars.php" class="action-card">
                    <i class="fas fa-car"></i>
                    <span>Browse Cars</span>
                </a>
                <a href="my-reservations.php" class="action-card">
                    <i class="fas fa-calendar"></i>
                    <span>My Reservations</span>
                </a>
                <a href="my-reviews.php" class="action-card">
                    <i class="fas fa-star"></i>
                    <span>My Reviews</span>
                </a>
                <a href="profile.php" class="action-card">
                    <i class="fas fa-user"></i>
                    <span>Edit Profile</span>
                </a>
            </div>
        </div>
        
        <!-- Recent Reservations -->
        <div class="dashboard-section">
            <div class="section-header-inline">
                <h2>Recent Reservations</h2>
                <a href="my-reservations.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            
            <?php if (empty($recentReservations)): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Reservations Yet</h3>
                    <p>Start exploring our collection of cars</p>
                    <a href="cars.php" class="btn btn-primary">Browse Cars</a>
                </div>
            <?php else: ?>
                <div class="reservations-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Days</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReservations as $reservation): ?>
                                <tr>
                                    <td>
                                        <div class="table-car-info">
                                            <img src="<?php echo e(imageUrl($reservation['image_path'] ?? '')); ?>" 
                                                 alt="<?php echo e($reservation['brand_name'] . ' ' . $reservation['model']); ?>"
                                                 onerror="this.src='assets/images/placeholder-car.jpg'">
                                            <span><?php echo e($reservation['brand_name'] . ' ' . $reservation['model']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo formatDate($reservation['start_date']); ?></td>
                                    <td><?php echo formatDate($reservation['end_date']); ?></td>
                                    <td><?php echo $reservation['number_of_days']; ?></td>
                                    <td><?php echo formatPrice($reservation['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge badge-<?php echo getStatusBadgeClass($reservation['status']); ?>">
                                            <?php echo ucfirst(e($reservation['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="car-details.php?id=<?php echo $reservation['car_id']; ?>" class="btn btn-sm btn-outline">
                                            View Car
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
