<?php
$requireAdmin = true;
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $purchaseId = intval($_POST['purchase_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['pending', 'completed', 'cancelled'];

        if (in_array($newStatus, $validStatuses, true)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT * FROM purchases WHERE id = ? FOR UPDATE");
                $stmt->execute([$purchaseId]);
                $purchase = $stmt->fetch();

                if (!$purchase) {
                    throw new Exception('Purchase not found.');
                }

                $carRow = lockCarRow($purchase['car_id'], $pdo);
                if (!$carRow) {
                    throw new Exception('Car not found.');
                }

                if ($newStatus === 'completed') {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE car_id = ? AND status = 'confirmed'");
                    $stmt->execute([$purchase['car_id']]);
                    if ((int)$stmt->fetchColumn() > 0) {
                        throw new Exception('This car is currently rented. Complete or cancel the rental first.');
                    }

                    $stmt = $pdo->prepare("
                        SELECT id FROM reservations
                        WHERE car_id = ? AND status = 'pending'
                    ");
                    $stmt->execute([$purchase['car_id']]);
                    $pendingReservationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    if ($pendingReservationIds) {
                        $placeholders = implode(',', array_fill(0, count($pendingReservationIds), '?'));
                        $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id IN ($placeholders)")
                            ->execute($pendingReservationIds);
                        $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE reservation_id IN ($placeholders)")
                            ->execute($pendingReservationIds);
                    }
                }

                $stmt = $pdo->prepare("UPDATE purchases SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $purchaseId]);

                if ($newStatus === 'cancelled') {
                    $stmt = $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE purchase_id = ?");
                    $stmt->execute([$purchaseId]);
                }

                syncCarPublicStatus($purchase['car_id'], $pdo);

                $pdo->commit();
                setFlashMessage('Purchase updated. Car status is now ' . formatCarPublicStatus(derivedCarPublicStatus($purchase['car_id'], $pdo)) . '.', 'success');
            } catch (Exception $e) {
                $pdo->rollBack();
                setFlashMessage($e->getMessage() ?: 'Failed to update purchase.', 'error');
            }
        }
    }

    header('Location: purchases.php');
    exit;
}

$stmt = $pdo->query("
    SELECT p.*, u.name as user_name, u.email as user_email,
           c.model, c.year, c.status as car_status, b.name as brand_name
    FROM purchases p
    JOIN users u ON p.user_id = u.id
    JOIN cars c ON p.car_id = c.id
    JOIN brands b ON c.brand_id = b.id
    ORDER BY p.created_at DESC
");
$purchases = $stmt->fetchAll();
$pageTitle = 'Manage Purchases';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Manage Purchases</h1>
            <p>Order statuses stay on the purchase. The car’s public status (reserved, sold, available) updates automatically.</p>
        </div>
        
        <div class="admin-section">
            <h2>All Purchases (<?php echo count($purchases); ?>)</h2>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Car</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Order status</th>
                            <th>Car status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td>#<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <div>
                                        <strong><?php echo e($purchase['user_name']); ?></strong>
                                        <small style="display: block; color: var(--gray-500);"><?php echo e($purchase['user_email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo e($purchase['brand_name'] . ' ' . $purchase['model'] . ' (' . $purchase['year'] . ')'); ?></td>
                                <td><strong><?php echo formatPrice($purchase['amount']); ?></strong></td>
                                <td><?php echo formatDate($purchase['created_at']); ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo getStatusBadgeClass($purchase['status']); ?>">
                                        <?php echo ucfirst(e($purchase['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge badge-<?php echo getStatusBadgeClass($purchase['car_status']); ?>">
                                        <?php echo e(formatCarPublicStatus($purchase['car_status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="" class="admin-status-form">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="purchase_id" value="<?php echo (int)$purchase['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status" class="form-control" onchange="this.form.submit()">
                                            <option value="">Update</option>
                                            <option value="pending" <?php echo $purchase['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="completed" <?php echo $purchase['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $purchase['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
