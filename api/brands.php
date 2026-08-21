<?php
// API endpoint for fetching brands
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();

$stmt = $pdo->query("
    SELECT b.*, COUNT(c.id) as car_count
    FROM brands b
    LEFT JOIN cars c ON b.id = c.brand_id AND c.status = 'available'
    GROUP BY b.id
    ORDER BY b.name ASC
");
$brands = $stmt->fetchAll();

$response = [
    'success' => true,
    'data' => $brands
];

echo json_encode($response);
exit;
