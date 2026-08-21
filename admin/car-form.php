<?php
$requireAdmin = true;
require_once __DIR__ . '/../includes/bootstrap.php';

$carId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$pdo = getDB();
$errors = [];
$success = false;
$car = null;
$carImages = [];

if ($carId) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$carId]);
    $car = $stmt->fetch();
    
    if (!$car) {
        setFlashMessage('Car not found.', 'error');
        header('Location: cars.php');
        exit;
    }
    
    $carImages = getCarImages($carId);
}

$isEdit = $carId > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandId = intval($_POST['brand_id'] ?? 0);
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $condition = $_POST['condition'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $rentalPricePerDay = floatval($_POST['rental_price_per_day'] ?? 0);
    $mileage = intval($_POST['mileage'] ?? 0);
    $transmission = $_POST['transmission'] ?? '';
    $fuelType = $_POST['fuel_type'] ?? '';
    $engine = trim($_POST['engine'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $seats = intval($_POST['seats'] ?? 5);
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'available';
    $featured = isset($_POST['featured']) ? 1 : 0;

    $car = [
        'brand_id' => $brandId,
        'model' => $model,
        'year' => $year,
        'condition' => $condition,
        'category' => $category,
        'price' => $price,
        'rental_price_per_day' => $rentalPricePerDay,
        'mileage' => $mileage,
        'transmission' => $transmission,
        'fuel_type' => $fuelType,
        'engine' => $engine,
        'color' => $color,
        'seats' => $seats,
        'description' => $description,
        'status' => $status,
        'featured' => $featured,
    ];

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    }

    if (empty($brandId)) {
        $errors[] = 'Please select a brand.';
    }
    if ($model === '') {
        $errors[] = 'Model name is required.';
    }
    if (empty($year) || $year < 1900 || $year > date('Y') + 1) {
        $errors[] = 'Please enter a valid year.';
    }
    if (!in_array($condition, ['new', 'used'], true)) {
        $errors[] = 'Please select condition.';
    }
    if ($category === '') {
        $errors[] = 'Please select a category.';
    }
    if ($price <= 0) {
        $errors[] = 'Please enter a valid purchase price.';
    }
    if ($rentalPricePerDay <= 0) {
        $errors[] = 'Please enter a valid rental price.';
    }
    if (!in_array($transmission, ['automatic', 'manual'], true)) {
        $errors[] = 'Please select transmission.';
    }
    if (!in_array($fuelType, ['petrol', 'diesel', 'electric', 'hybrid'], true)) {
        $errors[] = 'Please select fuel type.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if ($isEdit) {
                $stmt = $pdo->prepare("
                    UPDATE cars SET
                        brand_id = ?, model = ?, year = ?, `condition` = ?, category = ?,
                        price = ?, rental_price_per_day = ?, mileage = ?, transmission = ?,
                        fuel_type = ?, engine = ?, color = ?, seats = ?, description = ?,
                        status = ?, featured = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $brandId, $model, $year, $condition, $category,
                    $price, $rentalPricePerDay, $mileage, $transmission,
                    $fuelType, $engine, $color, $seats, $description,
                    $status, $featured, $carId
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO cars (brand_id, model, year, `condition`, category, price,
                                    rental_price_per_day, mileage, transmission, fuel_type,
                                    engine, color, seats, description, status, featured)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $brandId, $model, $year, $condition, $category,
                    $price, $rentalPricePerDay, $mileage, $transmission,
                    $fuelType, $engine, $color, $seats, $description,
                    $status, $featured
                ]);
                $carId = (int)$pdo->lastInsertId();
            }

            $pdo->commit();

            $deleteIds = array_map('intval', $_POST['delete_images'] ?? []);
            $deleteIds = array_values(array_filter($deleteIds));
            foreach ($deleteIds as $imageId) {
                deleteCarImageById($imageId, $carId);
            }

            $imageErrors = [];
            $hasNewImages = isset($_FILES['images']) && is_array($_FILES['images']['name']);
            if ($hasNewImages && $carId) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM car_images WHERE car_id = ?");
                $stmt->execute([$carId]);
                $existingCount = (int)$stmt->fetchColumn();
                $assignedPrimary = false;

                foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
                    $fileError = $_FILES['images']['error'][$index] ?? UPLOAD_ERR_NO_FILE;
                    $originalName = $_FILES['images']['name'][$index] ?? '';
                    if ($fileError === UPLOAD_ERR_NO_FILE || $tmpName === '' || $originalName === '') {
                        continue;
                    }
                    if ($fileError !== UPLOAD_ERR_OK) {
                        $imageErrors[] = ($originalName !== '' ? $originalName : 'Image ' . ($index + 1)) . ' could not be uploaded.';
                        continue;
                    }

                    $uploadResult = uploadImage([
                        'name' => $originalName,
                        'type' => $_FILES['images']['type'][$index],
                        'tmp_name' => $tmpName,
                        'error' => $fileError,
                        'size' => $_FILES['images']['size'][$index]
                    ], 'uploads/cars/');

                    if (!$uploadResult['success']) {
                        $imageErrors[] = $originalName . ': ' . $uploadResult['message'];
                        continue;
                    }

                    $isPrimary = 0;
                    if ($existingCount === 0 && !$assignedPrimary) {
                        $isPrimary = 1;
                        $assignedPrimary = true;
                    }

                    $stmt = $pdo->prepare("INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)");
                    $stmt->execute([$carId, $uploadResult['filename'], $isPrimary]);
                    $existingCount++;
                }
            }

            $primaryId = intval($_POST['primary_image'] ?? 0);
            if ($primaryId && !in_array($primaryId, $deleteIds, true)) {
                setCarPrimaryImage($primaryId, $carId);
            }

            if (!empty($imageErrors)) {
                setFlashMessage(
                    ($isEdit ? 'Car updated' : 'Car added') . ', but some images failed: ' . implode(' ', $imageErrors),
                    'warning'
                );
            } else {
                setFlashMessage($isEdit ? 'Car updated successfully!' : 'Car added successfully!', 'success');
            }

            header('Location: cars.php');
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Car Save Error: ' . $e->getMessage());
            $errors[] = 'Failed to save car: ' . $e->getMessage();
        }
    }
}

$brands = getAllBrands();
$pageTitle = $carId ? 'Edit Car' : 'Add New Car';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1><?php echo $carId ? 'Edit Car' : 'Add New Car'; ?></h1>
            <p><?php echo $carId ? 'Update car information' : 'Add a new vehicle to inventory'; ?></p>
        </div>
        
        <div class="admin-form-card">
            <?php if (empty($brands)): ?>
                <div class="alert alert-warning">
                    <p>Please <a href="brands.php">add a brand</a> before creating a car.</p>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                
                <div class="admin-form-grid">
                    <div class="form-group">
                        <label for="brand_id">Brand *</label>
                        <select id="brand_id" name="brand_id" class="form-control" required>
                            <option value="">Select Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?php echo $brand['id']; ?>" 
                                        <?php echo ($car && $car['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($brand['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="model">Model *</label>
                        <input type="text" id="model" name="model" class="form-control" 
                               value="<?php echo $car ? e($car['model']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Year *</label>
                        <input type="number" id="year" name="year" class="form-control" 
                               value="<?php echo $car ? $car['year'] : date('Y'); ?>" 
                               min="1900" max="<?php echo date('Y') + 1; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="condition">Condition *</label>
                        <select id="condition" name="condition" class="form-control" required>
                            <option value="">Select Condition</option>
                            <option value="new" <?php echo ($car && $car['condition'] == 'new') ? 'selected' : ''; ?>>New</option>
                            <option value="used" <?php echo ($car && $car['condition'] == 'used') ? 'selected' : ''; ?>>Used</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="sedan" <?php echo ($car && $car['category'] == 'sedan') ? 'selected' : ''; ?>>Sedan</option>
                            <option value="suv" <?php echo ($car && $car['category'] == 'suv') ? 'selected' : ''; ?>>SUV</option>
                            <option value="hatchback" <?php echo ($car && $car['category'] == 'hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                            <option value="coupe" <?php echo ($car && $car['category'] == 'coupe') ? 'selected' : ''; ?>>Coupe</option>
                            <option value="convertible" <?php echo ($car && $car['category'] == 'convertible') ? 'selected' : ''; ?>>Convertible</option>
                            <option value="pickup" <?php echo ($car && $car['category'] == 'pickup') ? 'selected' : ''; ?>>Pickup</option>
                            <option value="luxury" <?php echo ($car && $car['category'] == 'luxury') ? 'selected' : ''; ?>>Luxury</option>
                            <option value="electric" <?php echo ($car && $car['category'] == 'electric') ? 'selected' : ''; ?>>Electric</option>
                            <option value="other" <?php echo ($car && $car['category'] == 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Purchase Price ($) *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01"
                               value="<?php echo $car ? $car['price'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="rental_price_per_day">Rental Price/Day ($) *</label>
                        <input type="number" id="rental_price_per_day" name="rental_price_per_day" class="form-control" step="0.01"
                               value="<?php echo $car ? $car['rental_price_per_day'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mileage">Mileage (km)</label>
                        <input type="number" id="mileage" name="mileage" class="form-control"
                               value="<?php echo $car ? $car['mileage'] : 0; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="transmission">Transmission *</label>
                        <select id="transmission" name="transmission" class="form-control" required>
                            <option value="">Select Transmission</option>
                            <option value="automatic" <?php echo ($car && $car['transmission'] == 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                            <option value="manual" <?php echo ($car && $car['transmission'] == 'manual') ? 'selected' : ''; ?>>Manual</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type *</label>
                        <select id="fuel_type" name="fuel_type" class="form-control" required>
                            <option value="">Select Fuel Type</option>
                            <option value="petrol" <?php echo ($car && $car['fuel_type'] == 'petrol') ? 'selected' : ''; ?>>Petrol</option>
                            <option value="diesel" <?php echo ($car && $car['fuel_type'] == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                            <option value="electric" <?php echo ($car && $car['fuel_type'] == 'electric') ? 'selected' : ''; ?>>Electric</option>
                            <option value="hybrid" <?php echo ($car && $car['fuel_type'] == 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="engine">Engine</label>
                        <input type="text" id="engine" name="engine" class="form-control"
                               value="<?php echo $car ? e($car['engine']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" class="form-control"
                               value="<?php echo $car ? e($car['color']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="seats">Seats</label>
                        <input type="number" id="seats" name="seats" class="form-control"
                               value="<?php echo $car ? $car['seats'] : 5; ?>" min="1" max="20">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="available" <?php echo ($car && $car['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="rented" <?php echo ($car && $car['status'] == 'rented') ? 'selected' : ''; ?>>Rented</option>
                            <option value="sold" <?php echo ($car && $car['status'] == 'sold') ? 'selected' : ''; ?>>Sold</option>
                            <option value="maintenance" <?php echo ($car && $car['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="inactive" <?php echo ($car && $car['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="featured" value="1" 
                                   <?php echo ($car && $car['featured']) ? 'checked' : ''; ?>>
                            <span>Featured Car</span>
                        </label>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?php echo $car ? e($car['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <span class="form-label-text">Car Images</span>
                        <p class="form-text">Add several photos. The first image becomes the cover photo on the website. You can keep adding more without losing previous selections.</p>

                        <div class="image-dropzone" id="imageDropzone">
                            <input type="file" id="images" name="images[]" class="image-upload-input"
                                   accept="image/jpeg,image/jpg,image/png,image/webp" multiple>
                            <div class="image-dropzone-content">
                                <i class="fas fa-images"></i>
                                <p><strong>Drop images here</strong> or <label for="images" class="image-browse-link">browse files</label></p>
                                <small>JPG, PNG, WEBP · up to 12 photos · 5MB each · multiple files allowed</small>
                            </div>
                        </div>
                        <div class="image-upload-meta">
                            <span id="imageCount">No new images selected</span>
                        </div>
                        <div id="imagePreviewContainer" class="image-preview-grid"></div>
                        
                        <?php if (!empty($carImages)): ?>
                            <div class="existing-images-block">
                                <h4>Saved photos</h4>
                                <p class="form-text">Choose a cover photo or mark photos to remove when you save.</p>
                                <div class="image-preview-grid">
                                    <?php foreach ($carImages as $img): ?>
                                        <article class="image-preview-card">
                                            <img src="<?php echo e(imageUrl($img['image_path'])); ?>" alt="Saved car photo">
                                            <?php if (!empty($img['is_primary'])): ?>
                                                <span class="image-cover-badge">Cover</span>
                                            <?php endif; ?>
                                            <div class="image-preview-actions">
                                                <label class="image-action-chip">
                                                    <input type="radio" name="primary_image" value="<?php echo (int)$img['id']; ?>"
                                                           <?php echo !empty($img['is_primary']) ? 'checked' : ''; ?>>
                                                    Cover
                                                </label>
                                                <label class="image-action-chip image-action-remove">
                                                    <input type="checkbox" name="delete_images[]" value="<?php echo (int)$img['id']; ?>">
                                                    Remove
                                                </label>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="admin-form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $carId ? 'Update Car' : 'Add Car'; ?>
                    </button>
                    <a href="cars.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
(function () {
    const input = document.getElementById('images');
    const dropzone = document.getElementById('imageDropzone');
    const preview = document.getElementById('imagePreviewContainer');
    const countEl = document.getElementById('imageCount');
    if (!input || !dropzone || !preview) return;

    const maxFiles = 12;
    const maxSize = 5 * 1024 * 1024;
    const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    let store = new DataTransfer();

    function filesFromStore() {
        return Array.from(store.files);
    }

    function syncInput() {
        input.files = store.files;
        render();
    }

    function addFiles(fileList) {
        const incoming = Array.from(fileList || []);
        incoming.forEach(function (file) {
            if (store.files.length >= maxFiles) return;
            if (allowed.indexOf(file.type) === -1) return;
            if (file.size > maxSize) return;
            const duplicate = filesFromStore().some(function (existing) {
                return existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified;
            });
            if (!duplicate) {
                store.items.add(file);
            }
        });
        syncInput();
    }

    function removeAt(index) {
        const next = new DataTransfer();
        filesFromStore().forEach(function (file, i) {
            if (i !== index) next.items.add(file);
        });
        store = next;
        syncInput();
    }

    function render() {
        const files = filesFromStore();
        preview.innerHTML = '';
        countEl.textContent = files.length
            ? files.length + ' new image' + (files.length === 1 ? '' : 's') + ' ready to upload'
            : 'No new images selected';

        files.forEach(function (file, index) {
            const card = document.createElement('article');
            card.className = 'image-preview-card';

            const img = document.createElement('img');
            img.alt = file.name;
            const reader = new FileReader();
            reader.onload = function (e) { img.src = e.target.result; };
            reader.readAsDataURL(file);

            const name = document.createElement('span');
            name.className = 'image-preview-name';
            name.textContent = file.name;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'image-preview-remove';
            remove.setAttribute('aria-label', 'Remove ' + file.name);
            remove.innerHTML = '&times;';
            remove.addEventListener('click', function () { removeAt(index); });

            if (index === 0) {
                const badge = document.createElement('span');
                badge.className = 'image-cover-badge';
                badge.textContent = 'Cover';
                card.appendChild(badge);
            }

            card.appendChild(img);
            card.appendChild(remove);
            card.appendChild(name);
            preview.appendChild(card);
        });
    }

    input.addEventListener('change', function () {
        addFiles(this.files);
    });

    dropzone.addEventListener('click', function (e) {
        if (e.target.closest('label') || e.target === input) return;
        input.click();
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(function (eventName) {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        });
    });
    dropzone.addEventListener('drop', function (e) {
        addFiles(e.dataTransfer.files);
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
