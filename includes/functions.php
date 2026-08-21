<?php
// Helper Functions

require_once __DIR__ . '/../config/database.php';

// Sanitize output
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// Set flash message
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        if ($type === 'error') {
            $type = 'danger';
        }
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Format date
function formatDate($date) {
    return date(DISPLAY_DATE_FORMAT, strtotime($date));
}

// Get car by ID
function getCarById($carId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT c.*, b.name as brand_name, b.logo as brand_logo
        FROM cars c
        JOIN brands b ON c.brand_id = b.id
        WHERE c.id = ?
    ");
    $stmt->execute([$carId]);
    return $stmt->fetch();
}

// Get car images
function getCarImages($carId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM car_images WHERE car_id = ? ORDER BY is_primary DESC, id ASC");
    $stmt->execute([$carId]);
    return $stmt->fetchAll();
}

function deleteCarImageById($imageId, $carId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM car_images WHERE id = ? AND car_id = ?");
    $stmt->execute([$imageId, $carId]);
    $image = $stmt->fetch();
    if (!$image) {
        return false;
    }

    $relative = ltrim(str_replace('\\', '/', $image['image_path']), '/');
    $fullPath = dirname(__DIR__) . '/' . $relative;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }

    $stmt = $pdo->prepare("DELETE FROM car_images WHERE id = ? AND car_id = ?");
    $stmt->execute([$imageId, $carId]);

    if (!empty($image['is_primary'])) {
        $stmt = $pdo->prepare("SELECT id FROM car_images WHERE car_id = ? ORDER BY id ASC LIMIT 1");
        $stmt->execute([$carId]);
        $next = $stmt->fetch();
        if ($next) {
            $pdo->prepare("UPDATE car_images SET is_primary = 1 WHERE id = ?")->execute([$next['id']]);
        }
    }

    return true;
}

function setCarPrimaryImage($imageId, $carId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM car_images WHERE id = ? AND car_id = ?");
    $stmt->execute([$imageId, $carId]);
    if (!$stmt->fetch()) {
        return false;
    }

    $pdo->prepare("UPDATE car_images SET is_primary = 0 WHERE car_id = ?")->execute([$carId]);
    $pdo->prepare("UPDATE car_images SET is_primary = 1 WHERE id = ? AND car_id = ?")->execute([$imageId, $carId]);
    return true;
}

// Get primary car image
function getPrimaryCarImage($carId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ? AND is_primary = 1 LIMIT 1");
    $stmt->execute([$carId]);
    $image = $stmt->fetch();
    
    if ($image) {
        return imageUrl($image['image_path']);
    }
    
    // Fallback to first image
    $stmt = $pdo->prepare("SELECT image_path FROM car_images WHERE car_id = ? LIMIT 1");
    $stmt->execute([$carId]);
    $image = $stmt->fetch();
    
    return imageUrl($image ? $image['image_path'] : 'assets/images/placeholder-car.svg');
}

function imageUrl($path) {
    if (empty($path)) {
        $path = 'assets/images/placeholder-car.svg';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

// Get average rating
function getAverageRating($carId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE car_id = ?");
    $stmt->execute([$carId]);
    $result = $stmt->fetch();
    
    return [
        'average' => $result['avg_rating'] ? round($result['avg_rating'], 1) : 0,
        'count' => $result['review_count']
    ];
}

// Get all brands
function getAllBrands() {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
    return $stmt->fetchAll();
}

// Allowed car filter values
function getCarFilterOptions() {
    return [
        'conditions' => ['new', 'used'],
        'categories' => ['sedan', 'suv', 'hatchback', 'coupe', 'convertible', 'pickup', 'luxury', 'electric', 'other'],
        'transmissions' => ['automatic', 'manual'],
        'fuel_types' => ['petrol', 'diesel', 'electric', 'hybrid'],
        'types' => ['rental', 'purchase'],
        'sorts' => [
            'newest' => 'c.created_at DESC',
            'oldest' => 'c.created_at ASC',
            'price_low' => 'c.price ASC',
            'price_high' => 'c.price DESC',
            'year_new' => 'c.year DESC',
            'year_old' => 'c.year ASC',
            'rental_low' => 'c.rental_price_per_day ASC',
            'rental_high' => 'c.rental_price_per_day DESC',
        ],
    ];
}

// Normalize incoming car listing filters
function normalizeCarFilters($input) {
    $options = getCarFilterOptions();

    $search = trim((string)($input['search'] ?? ''));
    $brandId = isset($input['brand']) && $input['brand'] !== '' ? intval($input['brand']) : 0;
    $condition = (string)($input['condition'] ?? '');
    $category = (string)($input['category'] ?? '');
    $transmission = (string)($input['transmission'] ?? '');
    $fuelType = (string)($input['fuel_type'] ?? '');
    $type = (string)($input['type'] ?? '');
    $sortBy = (string)($input['sort'] ?? '');

    $minPrice = isset($input['min_price']) && $input['min_price'] !== '' ? $input['min_price'] : '';
    $maxPrice = isset($input['max_price']) && $input['max_price'] !== '' ? $input['max_price'] : '';
    $minYear = isset($input['min_year']) && $input['min_year'] !== '' ? $input['min_year'] : '';
    $maxYear = isset($input['max_year']) && $input['max_year'] !== '' ? $input['max_year'] : '';

    if (!in_array($condition, $options['conditions'], true)) {
        $condition = '';
    }
    if (!in_array($category, $options['categories'], true)) {
        $category = '';
    }
    if (!in_array($transmission, $options['transmissions'], true)) {
        $transmission = '';
    }
    if (!in_array($fuelType, $options['fuel_types'], true)) {
        $fuelType = '';
    }
    if (!in_array($type, $options['types'], true)) {
        $type = '';
    }
    if (!array_key_exists($sortBy, $options['sorts'])) {
        $sortBy = $type === 'rental' ? 'rental_low' : 'newest';
    }

    if ($minPrice !== '' && is_numeric($minPrice) && (float)$minPrice >= 0) {
        $minPrice = (float)$minPrice;
    } else {
        $minPrice = '';
    }

    if ($maxPrice !== '' && is_numeric($maxPrice) && (float)$maxPrice >= 0) {
        $maxPrice = (float)$maxPrice;
    } else {
        $maxPrice = '';
    }

    if ($minPrice !== '' && $maxPrice !== '' && $minPrice > $maxPrice) {
        [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
    }

    $currentYear = (int)date('Y') + 1;
    if ($minYear !== '' && ctype_digit((string)$minYear)) {
        $minYear = max(1980, min($currentYear, intval($minYear)));
    } else {
        $minYear = '';
    }

    if ($maxYear !== '' && ctype_digit((string)$maxYear)) {
        $maxYear = max(1980, min($currentYear, intval($maxYear)));
    } else {
        $maxYear = '';
    }

    if ($minYear !== '' && $maxYear !== '' && $minYear > $maxYear) {
        [$minYear, $maxYear] = [$maxYear, $minYear];
    }

    return [
        'search' => $search,
        'brand' => $brandId > 0 ? $brandId : '',
        'condition' => $condition,
        'category' => $category,
        'transmission' => $transmission,
        'fuel_type' => $fuelType,
        'type' => $type,
        'min_price' => $minPrice,
        'max_price' => $maxPrice,
        'min_year' => $minYear,
        'max_year' => $maxYear,
        'sort' => $sortBy,
    ];
}

// Build WHERE clause for car listings
function buildCarFilterQuery($filters) {
    $where = ["c.status = 'available'"];
    $params = [];

    if ($filters['search'] !== '') {
        $where[] = "(c.model LIKE ? OR b.name LIKE ? OR CONCAT(b.name, ' ', c.model) LIKE ?)";
        $like = '%' . $filters['search'] . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if ($filters['brand'] !== '') {
        $where[] = "c.brand_id = ?";
        $params[] = (int)$filters['brand'];
    }

    if ($filters['condition'] !== '') {
        $where[] = "c.`condition` = ?";
        $params[] = $filters['condition'];
    }

    if ($filters['category'] !== '') {
        $where[] = "c.category = ?";
        $params[] = $filters['category'];
    }

    if ($filters['transmission'] !== '') {
        $where[] = "c.transmission = ?";
        $params[] = $filters['transmission'];
    }

    if ($filters['fuel_type'] !== '') {
        $where[] = "c.fuel_type = ?";
        $params[] = $filters['fuel_type'];
    }

    $priceColumn = $filters['type'] === 'rental' ? 'c.rental_price_per_day' : 'c.price';

    if ($filters['min_price'] !== '') {
        $where[] = "$priceColumn >= ?";
        $params[] = $filters['min_price'];
    }

    if ($filters['max_price'] !== '') {
        $where[] = "$priceColumn <= ?";
        $params[] = $filters['max_price'];
    }

    if ($filters['min_year'] !== '') {
        $where[] = "c.year >= ?";
        $params[] = (int)$filters['min_year'];
    }

    if ($filters['max_year'] !== '') {
        $where[] = "c.year <= ?";
        $params[] = (int)$filters['max_year'];
    }

    return [
        'where' => implode(' AND ', $where),
        'params' => $params,
        'order_by' => getCarFilterOptions()['sorts'][$filters['sort']],
    ];
}

// Fetch filtered cars with pagination
function getFilteredCars($filters, $page = 1, $perPage = ITEMS_PER_PAGE) {
    $pdo = getDB();
    $query = buildCarFilterQuery($filters);

    $countSql = "SELECT COUNT(*) as total FROM cars c JOIN brands b ON c.brand_id = b.id WHERE {$query['where']}";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($query['params']);
    $totalCars = (int)$countStmt->fetch()['total'];

    $pagination = paginate($totalCars, $page, $perPage);
    $limit = (int)$pagination['items_per_page'];
    $offset = (int)$pagination['offset'];

    $sql = "
        SELECT c.*, b.name as brand_name, b.logo as brand_logo
        FROM cars c
        JOIN brands b ON c.brand_id = b.id
        WHERE {$query['where']}
        ORDER BY {$query['order_by']}
        LIMIT {$limit} OFFSET {$offset}
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($query['params']);

    return [
        'cars' => $stmt->fetchAll(),
        'total' => $totalCars,
        'pagination' => $pagination,
        'filters' => $filters,
    ];
}

// Query string helper that drops empty filter values
function carFilterQuery(array $filters, array $overrides = []) {
    $merged = array_merge($filters, $overrides);
    $clean = [];

    foreach ($merged as $key => $value) {
        if ($value === '' || $value === null) {
            continue;
        }
        $clean[$key] = $value;
    }

    return http_build_query($clean);
}

// Get featured cars
function getFeaturedCars($limit = 6) {
    $pdo = getDB();
    $limit = max(1, intval($limit));
    $stmt = $pdo->query("
        SELECT c.*, b.name as brand_name, b.logo as brand_logo
        FROM cars c
        JOIN brands b ON c.brand_id = b.id
        WHERE c.featured = 1 AND c.status = 'available'
        ORDER BY c.created_at DESC
        LIMIT {$limit}
    ");
    return $stmt->fetchAll();
}

// Check if car is available
function isCarAvailable($carId) {
    $car = getCarById($carId);
    return $car && in_array($car['status'], ['available']);
}

// Check for overlapping reservations
function hasOverlappingReservation($carId, $startDate, $endDate, $excludeReservationId = null) {
    $pdo = getDB();
    
    $sql = "
        SELECT COUNT(*) as count
        FROM reservations
        WHERE car_id = ?
        AND status IN ('pending', 'confirmed')
        AND (
            (start_date <= ? AND end_date >= ?) OR
            (start_date <= ? AND end_date >= ?) OR
            (start_date >= ? AND end_date <= ?)
        )
    ";
    
    $params = [$carId, $startDate, $startDate, $endDate, $endDate, $startDate, $endDate];
    
    if ($excludeReservationId) {
        $sql .= " AND id != ?";
        $params[] = $excludeReservationId;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    
    return $result['count'] > 0;
}

// Calculate rental days
function calculateRentalDays($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $diff = $start->diff($end);
    return $diff->days;
}

// Calculate rental total
function calculateRentalTotal($rentalPricePerDay, $numberOfDays) {
    return $rentalPricePerDay * $numberOfDays;
}

// Get brand by ID
function getBrandById($brandId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$brandId]);
    return $stmt->fetch();
}

// Generate star rating HTML
function generateStarRating($rating, $maxStars = 5) {
    $html = '<div class="star-rating">';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= $fullStars) {
            $html .= '<i class="fas fa-star"></i>';
        } elseif ($i == ($fullStars + 1) && $halfStar) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    
    $html .= '</div>';
    return $html;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone
function validatePhone($phone) {
    return preg_match('/^[\d\s\+\-\(\)]+$/', $phone);
}

// Validate password strength
function validatePassword($password) {
    // At least 8 characters, one letter, one number
    return strlen($password) >= 8 && preg_match('/[A-Za-z]/', $password) && preg_match('/\d/', $password);
}

// Upload image
function detectUploadedMimeType($tmpPath) {
    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            if (!empty($mime)) {
                return $mime;
            }
        }
    }

    if (function_exists('mime_content_type')) {
        $mime = @mime_content_type($tmpPath);
        if (!empty($mime)) {
            return $mime;
        }
    }

    return '';
}

function uploadImage($file, $targetDir = 'uploads/cars/') {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxSize = MAX_UPLOAD_SIZE;
    
    if (!isset($file['tmp_name']) || $file['tmp_name'] === '' || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    if (($file['size'] ?? 0) > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed size'];
    }
    
    $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return ['success' => false, 'message' => 'Invalid file extension. Only JPG, PNG, and WEBP allowed'];
    }

    $mimeType = detectUploadedMimeType($file['tmp_name']);
    if ($mimeType !== '' && !in_array($mimeType, $allowedTypes, true)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and WEBP allowed'];
    }

    $relativeDir = trim(str_replace('\\', '/', $targetDir), '/') . '/';
    $absoluteDir = dirname(__DIR__) . '/' . $relativeDir;
    $filename = str_replace('.', '', uniqid('img_', true)) . '.' . $extension;
    $absolutePath = $absoluteDir . $filename;
    
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
        return ['success' => false, 'message' => 'Failed to create upload directory'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
        return ['success' => true, 'filename' => $relativeDir . $filename];
    }
    
    return ['success' => false, 'message' => 'Failed to upload file'];
}

function deleteUploadedFile($relativePath) {
    if (empty($relativePath) || !is_string($relativePath)) {
        return;
    }

    $relative = ltrim(str_replace('\\', '/', $relativePath), '/');
    if ($relative === '' || strpos($relative, '..') !== false) {
        return;
    }

    $fullPath = dirname(__DIR__) . '/' . $relative;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

// Get user's reservations
function getUserReservations($userId, $status = null) {
    $pdo = getDB();
    
    $sql = "
        SELECT r.*, c.model, c.year, b.name as brand_name, ci.image_path
        FROM reservations r
        JOIN cars c ON r.car_id = c.id
        JOIN brands b ON c.brand_id = b.id
        LEFT JOIN car_images ci ON c.id = ci.car_id AND ci.is_primary = 1
        WHERE r.user_id = ?
    ";
    
    $params = [$userId];
    
    if ($status === 'active') {
        $sql .= " AND r.status IN ('pending', 'confirmed')";
    } elseif ($status) {
        $sql .= " AND r.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get user's reviews
function getUserReviews($userId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT r.*, c.model, c.year, b.name as brand_name
        FROM reviews r
        JOIN cars c ON r.car_id = c.id
        JOIN brands b ON c.brand_id = b.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Check if user has reviewed a car
function hasUserReviewedCar($userId, $carId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$userId, $carId]);
    return $stmt->fetch() !== false;
}

// Get car reviews
function getCarReviews($carId, $limit = null) {
    $pdo = getDB();
    
    $sql = "
        SELECT r.*, u.name as user_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.car_id = ?
        ORDER BY r.created_at DESC
    ";
    
    if ($limit) {
        $limit = max(1, intval($limit));
        $sql .= " LIMIT {$limit}";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$carId]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$carId]);
    }
    
    return $stmt->fetchAll();
}

// Generate transaction reference
function generateTransactionReference() {
    return 'FAKE-PAYPAL-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
}

// Get status badge class
function getStatusBadgeClass($status) {
    $classes = [
        'available' => 'success',
        'rented' => 'warning',
        'sold' => 'danger',
        'maintenance' => 'secondary',
        'inactive' => 'secondary',
        'pending' => 'warning',
        'confirmed' => 'info',
        'cancelled' => 'danger',
        'completed' => 'success',
        'active' => 'success'
    ];
    
    return $classes[$status] ?? 'secondary';
}

// Pagination helper
function paginate($totalItems, $currentPage, $itemsPerPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset
    ];
}
