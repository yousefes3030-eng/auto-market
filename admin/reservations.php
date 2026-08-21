<?php
$requireAdmin = true;
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = getDB();
$errors = [];
$success = false;

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $reservationId = intval($_POST['reservation_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];
        
        if (in_array($newStatus, $validStatuses)) {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $reservationId]);
                
                // If cancelled, update payment status
                if ($newStatus === 'cancelled') {
                    $stmt = $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE reservation_id = ?");
                    $stmt->execute([$reservationId]);
                }
                
                $pdo->commit();
                setFlashMessage('Reservation updated successfully!', 'success');
                
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlashMessage('Failed to update reservation.', 'error');
            }
        }
    }
    
    header('Location: reservations.php');
    exit;
}

// Get all reservations with details
$stmt = $pdo->query("
    SELECT r.*, u.name as user_name, u.email as user_email,
           c.model, c.year, b.name as brand_name
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    JOIN brands b ON c.brand_id = b.id
    ORDER BY r.created_at DESC
");
$reservations = $stmt->fetchAll();
$pageTitle = 'Manage Reservations';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Manage Reservations</h1>
            <p>View and manage all car rental reservations</p>
        </div>
        
        <!-- Reservations List -->
        <div class="admin-section">
            <h2>All Reservations (<?php echo count($reservations); ?>)</h2>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
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
                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td>#<?php echo str_pad($res['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo e($res['user_name']); ?></strong>
                                        <small style="display: block; color: var(--gray-500);"><?php echo e($res['user_email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo e($res['brand_name'] . ' ' . $res['model'] . ' (' . $res['year'] . ')'); ?></td>
                                <td><?php echo formatDate($res['start_date']); ?></td>
                                <td><?php echo formatDate($res['end_date']); ?></td>
                                <td><?php echo $res['number_of_days']; ?></td>
                                <td><strong><?php echo formatPrice($res['total_amount']); ?></strong></td>
                                <td>
                                    <span class="status-badge badge-<?php echo getStatusBadgeClass($res['status']); ?>">
                                        <?php echo ucfirst(e($res['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="" class="admin-status-form">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="">Update</option>
                                            <option value="pending" <?php echo $res['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $res['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $res['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $res['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
