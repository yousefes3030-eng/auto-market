<?php
$requireLogin = true;
require_once 'includes/bootstrap.php';

$userId = $_SESSION['user_id'];

// Get filter
$statusFilter = $_GET['status'] ?? '';

// Get reservations
$reservations = getUserReservations($userId, $statusFilter ?: null);

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reservation'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $reservationId = intval($_POST['reservation_id']);
        
        // Verify ownership and status
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
        $stmt->execute([$reservationId, $userId]);
        $reservation = $stmt->fetch();
        
        if ($reservation && in_array($reservation['status'], ['pending', 'confirmed'])) {
            try {
                $pdo->beginTransaction();
                
                // Update reservation status
                $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$reservationId]);
                
                // Update payment if exists
                $stmt = $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE reservation_id = ?");
                $stmt->execute([$reservationId]);

                syncCarPublicStatus($reservation['car_id'], $pdo);
                
                $pdo->commit();
                
                setFlashMessage('Reservation cancelled successfully.', 'success');
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlashMessage('Failed to cancel reservation.', 'error');
            }
        } else {
            setFlashMessage('Cannot cancel this reservation.', 'error');
        }
        
        header('Location: my-reservations.php');
        exit;
    }
}

$pageTitle = 'My Reservations';
require_once 'includes/header.php';
?>

<div class="my-reservations-page">
    <div class="container">
        <div class="page-header-small">
            <h1>My Reservations</h1>
            <p>View and manage your car rental reservations</p>
        </div>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="my-reservations.php" class="filter-tab <?php echo empty($statusFilter) ? 'active' : ''; ?>">
                All Reservations
            </a>
            <a href="?status=active" class="filter-tab <?php echo $statusFilter === 'active' ? 'active' : ''; ?>">
                Active
            </a>
            <a href="?status=completed" class="filter-tab <?php echo $statusFilter === 'completed' ? 'active' : ''; ?>">
                Completed
            </a>
            <a href="?status=cancelled" class="filter-tab <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">
                Cancelled
            </a>
        </div>
        
        <?php if (empty($reservations)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No Reservations Found</h3>
                <p>You haven't made any reservations yet</p>
                <a href="cars.php" class="btn btn-primary">Browse Cars</a>
            </div>
        <?php else: ?>
            <div class="reservations-list">
                <?php foreach ($reservations as $reservation): ?>
                    <div class="reservation-card">
                        <div class="reservation-image">
                            <img src="<?php echo e(imageUrl($reservation['image_path'] ?? '')); ?>" 
                                 alt="<?php echo e($reservation['brand_name'] . ' ' . $reservation['model']); ?>"
                                 onerror="this.src='assets/images/placeholder-car.jpg'">
                        </div>
                        
                        <div class="reservation-content">
                            <div class="reservation-header">
                                <h3><?php echo e($reservation['brand_name'] . ' ' . $reservation['model']); ?></h3>
                                <span class="status-badge badge-<?php echo getStatusBadgeClass($reservation['status']); ?>">
                                    <?php echo ucfirst(e($reservation['status'])); ?>
                                </span>
                            </div>
                            
                            <div class="reservation-details">
                                <div class="detail-item">
                                    <i class="fas fa-hashtag"></i>
                                    <span>Reservation #<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo formatDate($reservation['start_date']); ?> - <?php echo formatDate($reservation['end_date']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $reservation['number_of_days']; ?> days</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span><?php echo formatPrice($reservation['total_amount']); ?></span>
                                </div>
                            </div>
                            
                            <div class="reservation-actions">
                                <a href="car-details.php?id=<?php echo $reservation['car_id']; ?>" class="btn btn-sm btn-outline">
                                    View Car
                                </a>
                                
                                <?php if (in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmCancellation(<?php echo $reservation['id']; ?>)">
                                        Cancel Reservation
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div id="cancelModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Cancel Reservation</h3>
        <p>Are you sure you want to cancel this reservation?</p>
        
        <form method="POST" action="" id="cancelForm">
            <?php echo csrfField(); ?>
            <input type="hidden" name="reservation_id" id="cancelReservationId">
            <input type="hidden" name="cancel_reservation" value="1">
            
            <div class="modal-actions">
                <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()">No, Keep It</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCancellation(reservationId) {
    document.getElementById('cancelReservationId').value = reservationId;
    document.getElementById('cancelModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('cancelModal').style.display = 'none';
}
</script>

<?php require_once 'includes/footer.php'; ?>
