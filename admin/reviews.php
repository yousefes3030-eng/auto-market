<?php
$requireAdmin = true;
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = getDB();

// Handle review deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reviewId = intval($_GET['delete']);
    
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$reviewId])) {
        setFlashMessage('Review deleted successfully!', 'success');
    } else {
        setFlashMessage('Failed to delete review.', 'error');
    }
    
    header('Location: reviews.php');
    exit;
}

// Get all reviews
$stmt = $pdo->query("
    SELECT r.*, u.name as user_name, u.email as user_email,
           c.model, c.year, b.name as brand_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN cars c ON r.car_id = c.id
    JOIN brands b ON c.brand_id = b.id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();
$pageTitle = 'Manage Reviews';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Manage Reviews</h1>
            <p>View and manage customer reviews</p>
        </div>
        
        <!-- Reviews List -->
        <div class="admin-section">
            <h2>All Reviews (<?php echo count($reviews); ?>)</h2>
            
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <h3>No Reviews Yet</h3>
                    <p>No reviews have been submitted yet.</p>
                </div>
            <?php else: ?>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Car</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td><?php echo $review['id']; ?></td>
                                    <td>
                                        <div>
                                            <strong><?php echo e($review['user_name']); ?></strong>
                                            <small style="display: block; color: var(--gray-500);"><?php echo e($review['user_email']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo e($review['brand_name'] . ' ' . $review['model']); ?></td>
                                    <td>
                                        <div style="color: var(--warning);">
                                            <?php echo generateStarRating($review['rating']); ?>
                                        </div>
                                    </td>
                                    <td style="max-width: 300px;">
                                        <?php echo strlen($review['comment']) > 100 ? e(substr($review['comment'], 0, 100)) . '...' : e($review['comment']); ?>
                                    </td>
                                    <td><?php echo formatDate($review['created_at']); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $review['id']; ?>" class="admin-btn admin-btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete this review?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
