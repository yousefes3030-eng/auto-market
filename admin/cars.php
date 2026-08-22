<?php
$requireAdmin = true;
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = getDB();
$errors = [];
$success = false;

// Handle car deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $carId = intval($_GET['delete']);
    
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
    if ($stmt->execute([$carId])) {
        setFlashMessage('Car deleted successfully!', 'success');
    } else {
        setFlashMessage('Failed to delete car.', 'error');
    }
    
    header('Location: cars.php');
    exit;
}

// Get all cars with brand info
$stmt = $pdo->query("
    SELECT c.*, b.name as brand_name,
           (SELECT COUNT(*) FROM reservations WHERE car_id = c.id AND status IN ('pending', 'confirmed')) as active_reservations
    FROM cars c
    JOIN brands b ON c.brand_id = b.id
    ORDER BY c.created_at DESC
");
$cars = $stmt->fetchAll();

// Get brands for dropdown
$brands = getAllBrands();
$pageTitle = 'Manage Cars';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <div class="admin-header-row">
                <div>
                    <h1>Manage Cars</h1>
                    <p>Add, edit, and manage vehicle inventory</p>
                </div>
                <a href="car-form.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Car
                </a>
            </div>
        </div>
        
        <!-- Cars List -->
        <div class="admin-section">
            <h2>All Cars (<?php echo count($cars); ?>)</h2>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>Condition</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?php echo $car['id']; ?></td>
                                <td>
                                    <img src="<?php echo e(getPrimaryCarImage($car['id'])); ?>" 
                                         alt="<?php echo e($car['brand_name'] . ' ' . $car['model']); ?>"
                                         class="admin-thumb">
                                </td>
                                <td><?php echo e($car['brand_name']); ?></td>
                                <td><strong><?php echo e($car['model']); ?></strong></td>
                                <td><?php echo e($car['year']); ?></td>
                                <td><?php echo ucfirst(e($car['condition'])); ?></td>
                                <td><?php echo formatPrice($car['price']); ?></td>
                                <td>
                                    <span class="status-badge badge-<?php echo getStatusBadgeClass($car['status']); ?>">
                                        <?php echo e(formatCarPublicStatus($car['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="car-form.php?id=<?php echo $car['id']; ?>" class="admin-btn admin-btn-secondary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo $car['id']; ?>" class="admin-btn admin-btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this car?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
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
