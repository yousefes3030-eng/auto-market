<?php
$pageTitle = 'Brands';
require_once 'includes/header.php';

$pdo = getDB();
$stmt = $pdo->query("
    SELECT b.*, COUNT(c.id) as car_count
    FROM brands b
    LEFT JOIN cars c ON c.brand_id = b.id AND c.status = 'available'
    GROUP BY b.id
    ORDER BY b.name ASC
");
$brands = $stmt->fetchAll();
?>

<div class="page-header">
    <div class="container">
        <h1>Our Brands</h1>
        <p>Explore vehicles from leading manufacturers</p>
    </div>
</div>

<div class="brands-page">
    <div class="container">
        <?php if (empty($brands)): ?>
            <div class="empty-state">
                <i class="fas fa-tag"></i>
                <h3>No Brands Yet</h3>
                <p>Brands will appear here once they are added.</p>
            </div>
        <?php else: ?>
            <div class="brand-grid-large">
                <?php foreach ($brands as $brand): ?>
                    <a href="cars.php?brand=<?php echo e($brand['id']); ?>" class="brand-card-large">
                        <div class="brand-logo-large">
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
                        <h3><?php echo e($brand['name']); ?></h3>
                        <?php if (!empty($brand['description'])): ?>
                            <p><?php echo e($brand['description']); ?></p>
                        <?php endif; ?>
                        <span class="brand-car-count">
                            <?php echo (int)$brand['car_count']; ?> available car<?php echo (int)$brand['car_count'] === 1 ? '' : 's'; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
