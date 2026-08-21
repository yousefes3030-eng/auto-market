<?php
$requireAdmin = true;
$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';

$pdo = getDB();

// Get all users
$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM reservations WHERE user_id = u.id) as reservation_count,
           (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as review_count
    FROM users u
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Manage Users</h1>
            <p>View and manage user accounts</p>
        </div>
        
        <!-- Users List -->
        <div class="admin-section">
            <h2>All Users (<?php echo count($users); ?>)</h2>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Reservations</th>
                            <th>Reviews</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="admin-user-cell">
                                        <div class="admin-avatar">
                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                        </div>
                                        <strong><?php echo e($user['name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo e($user['email']); ?></td>
                                <td><?php echo e($user['phone'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] == 'admin' ? 'warning' : 'info'; ?>">
                                        <?php echo ucfirst(e($user['role'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge badge-<?php echo getStatusBadgeClass($user['status']); ?>">
                                        <?php echo ucfirst(e($user['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['reservation_count']; ?></td>
                                <td><?php echo $user['review_count']; ?></td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
