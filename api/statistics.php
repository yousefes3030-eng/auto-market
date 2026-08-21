<?php
// API endpoint for fetching statistics
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

// Get statistics
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['total_users'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars");
$stats['total_cars'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM cars WHERE status = 'available'");
$stats['available_cars'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reservations");
$stats['total_reservations'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
$stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
$stats['total_reviews'] = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT AVG(rating) as avg FROM reviews");
$stats['avg_rating'] = round($stmt->fetch()['avg'] ?? 0, 1);

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
$stats['popular_cars'] = $stmt->fetchAll();

$response = [
    'success' => true,
    'data' => $stats
];

echo json_encode($response);
exit;
