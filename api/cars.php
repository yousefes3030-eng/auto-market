<?php
// API endpoint for fetching cars with filters
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$filters = normalizeCarFilters($_GET);
$page = max(1, intval($_GET['page'] ?? 0));
$limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : ITEMS_PER_PAGE;

if ($page < 1) {
    $offset = max(0, intval($_GET['offset'] ?? 0));
    $page = intdiv($offset, $limit) + 1;
}

$result = getFilteredCars($filters, $page, $limit);

$response = [
    'success' => true,
    'data' => $result['cars'],
    'total' => $result['total'],
    'limit' => $limit,
    'offset' => $result['pagination']['offset'],
    'page' => $result['pagination']['current_page'],
    'filters' => $filters
];

echo json_encode($response);
exit;
