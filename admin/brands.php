<?php
$requireAdmin = true;
require_once __DIR__ . '/../includes/bootstrap.php';

$pdo = getDB();
$errors = [];
$editBrand = null;
$editId = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

if ($editId > 0) {
    $editBrand = getBrandById($editId);
    if (!$editBrand) {
        setFlashMessage('Brand not found.', 'error');
        header('Location: brands.php');
        exit;
    }
}

$formName = $editBrand['name'] ?? '';
$formDescription = $editBrand['description'] ?? '';

// Handle brand creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brand'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $logo = null;
        $formName = $name;
        $formDescription = $description;

        if ($name === '') {
            $errors[] = 'Brand name is required.';
        }

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['logo'], 'uploads/brands/');
            if ($uploadResult['success']) {
                $logo = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['message'];
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO brands (name, description, logo) VALUES (?, ?, ?)");
                $stmt->execute([$name, $description, $logo]);
                setFlashMessage('Brand added successfully!', 'success');
                header('Location: brands.php');
                exit;
            } catch (PDOException $e) {
                if ($logo) {
                    deleteUploadedFile($logo);
                }
                if ($e->getCode() == 23000) {
                    $errors[] = 'Brand already exists.';
                } else {
                    $errors[] = 'Failed to add brand.';
                }
            }
        }
    }
}

// Handle brand update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_brand'])) {
    $brandId = intval($_POST['brand_id'] ?? 0);
    $editBrand = getBrandById($brandId);
    $editId = $brandId;

    if (!$editBrand) {
        setFlashMessage('Brand not found.', 'error');
        header('Location: brands.php');
        exit;
    }

    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $removeLogo = isset($_POST['remove_logo']);
        $logo = $editBrand['logo'];
        $formName = $name;
        $formDescription = $description;
        $uploadedLogo = null;

        if ($name === '') {
            $errors[] = 'Brand name is required.';
        }

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImage($_FILES['logo'], 'uploads/brands/');
            if ($uploadResult['success']) {
                $uploadedLogo = $uploadResult['filename'];
                $logo = $uploadedLogo;
            } else {
                $errors[] = $uploadResult['message'];
            }
        } elseif ($removeLogo) {
            $logo = null;
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE brands SET name = ?, description = ?, logo = ? WHERE id = ?");
                $stmt->execute([$name, $description, $logo, $brandId]);

                if ($uploadedLogo && !empty($editBrand['logo']) && $editBrand['logo'] !== $uploadedLogo) {
                    deleteUploadedFile($editBrand['logo']);
                } elseif ($removeLogo && !$uploadedLogo && !empty($editBrand['logo'])) {
                    deleteUploadedFile($editBrand['logo']);
                }

                setFlashMessage('Brand updated successfully!', 'success');
                header('Location: brands.php');
                exit;
            } catch (PDOException $e) {
                if ($uploadedLogo) {
                    deleteUploadedFile($uploadedLogo);
                }
                if ($e->getCode() == 23000) {
                    $errors[] = 'Another brand already uses this name.';
                } else {
                    $errors[] = 'Failed to update brand.';
                }
            }
        }
    }
}

// Handle brand deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $brandId = intval($_GET['delete']);

    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM cars WHERE brand_id = ?");
    $stmt->execute([$brandId]);
    $carCount = $stmt->fetch()['count'];

    if ($carCount > 0) {
        setFlashMessage('Cannot delete brand. It has associated cars.', 'error');
    } else {
        $brandToDelete = getBrandById($brandId);
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        if ($stmt->execute([$brandId])) {
            if ($brandToDelete && !empty($brandToDelete['logo'])) {
                deleteUploadedFile($brandToDelete['logo']);
            }
            setFlashMessage('Brand deleted successfully!', 'success');
        } else {
            setFlashMessage('Failed to delete brand.', 'error');
        }
    }

    header('Location: brands.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
$brands = $stmt->fetchAll();
$isEdit = $editBrand !== null;
$pageTitle = $isEdit ? 'Edit Brand' : 'Manage Brands';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin-navbar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Manage Brands</h1>
            <p>Add, edit, and delete car brands</p>
        </div>
        
        <div class="admin-form-card" id="brand-form">
            <h2><?php echo $isEdit ? 'Edit Brand' : 'Add New Brand'; ?></h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo $isEdit ? 'brands.php?edit=' . (int)$editBrand['id'] : 'brands.php'; ?>" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="brand_id" value="<?php echo (int)$editBrand['id']; ?>">
                <?php endif; ?>
                
                <div class="admin-form-grid">
                    <div class="form-group">
                        <label for="name">Brand Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required
                               value="<?php echo e($formName); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo e($formDescription); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <span class="form-label-text">Brand Logo</span>
                        <input type="file" id="logo" name="logo" class="image-upload-input"
                               accept="image/jpeg,image/jpg,image/png,image/webp" onchange="previewImage(this)">
                        <label class="image-upload" for="logo">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p><?php echo $isEdit ? 'Click to replace brand logo' : 'Click to upload brand logo'; ?></p>
                            <small>JPG, PNG, WEBP (max 5MB)</small>
                            <img id="logoPreview" class="image-preview" alt="Logo preview"
                                 <?php if ($isEdit && !empty($editBrand['logo'])): ?>
                                    src="<?php echo e(imageUrl($editBrand['logo'])); ?>" style="display: block;"
                                 <?php endif; ?>>
                        </label>
                        <?php if ($isEdit && !empty($editBrand['logo'])): ?>
                            <label class="brand-remove-logo">
                                <input type="checkbox" name="remove_logo" value="1">
                                Remove current logo
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="admin-form-actions">
                    <?php if ($isEdit): ?>
                        <button type="submit" name="update_brand" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="brands.php" class="btn btn-outline">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_brand" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Brand
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>All Brands (<?php echo count($brands); ?>)</h2>
            
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brands as $brand): ?>
                            <tr class="<?php echo ($isEdit && (int)$editBrand['id'] === (int)$brand['id']) ? 'is-editing' : ''; ?>">
                                <td><?php echo $brand['id']; ?></td>
                                <td>
                                    <?php if ($brand['logo']): ?>
                                        <img src="<?php echo e(imageUrl($brand['logo'])); ?>" alt="<?php echo e($brand['name']); ?>" class="admin-thumb" style="width: 40px; height: 40px;">
                                    <?php else: ?>
                                        <i class="fas fa-car" style="color: var(--gray-400);"></i>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo e($brand['name']); ?></strong></td>
                                <td><?php echo e($brand['description'] ?? 'No description'); ?></td>
                                <td><?php echo formatDate($brand['created_at']); ?></td>
                                <td>
                                    <div class="admin-actions">
                                        <a href="brands.php?edit=<?php echo (int)$brand['id']; ?>#brand-form" class="admin-btn admin-btn-secondary">
                                            <i class="fas fa-pen"></i> Edit
                                        </a>
                                        <a href="?delete=<?php echo (int)$brand['id']; ?>" class="admin-btn admin-btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this brand?')">
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

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logoPreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
        const removeLogo = document.querySelector('input[name="remove_logo"]');
        if (removeLogo) removeLogo.checked = false;
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
