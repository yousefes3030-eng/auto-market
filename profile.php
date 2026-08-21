<?php
$requireLogin = true;
require_once 'includes/bootstrap.php';

$userId = $_SESSION['user_id'];
$pdo = getDB();

$errors = [];
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        
        if (empty($email) || !validateEmail($email)) {
            $errors[] = 'Valid email is required.';
        }
        
        if (!empty($phone) && !validatePhone($phone)) {
            $errors[] = 'Invalid phone number format.';
        }
        
        // Check if email is taken by another user
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $errors[] = 'Email is already taken by another user.';
            }
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $phone, $userId])) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                setFlashMessage('Profile updated successfully!', 'success');
                header('Location: profile.php');
                exit;
            } else {
                $errors[] = 'Failed to update profile.';
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Get current user password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        }
        
        if (empty($newPassword) || !validatePassword($newPassword)) {
            $errors[] = 'New password must be at least 8 characters with letters and numbers.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }
        
        if (empty($errors)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $userId])) {
                setFlashMessage('Password changed successfully!', 'success');
                header('Location: profile.php');
                exit;
            } else {
                $errors[] = 'Failed to change password.';
            }
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$pageTitle = 'My Profile';
require_once 'includes/header.php';
?>

<div class="profile-page">
    <div class="container">
        <div class="page-header-small">
            <h1>My Profile</h1>
            <p>Manage your account information</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="profile-layout">
            <!-- Profile Information -->
            <div class="profile-card">
                <h3>Profile Information</h3>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo e($user['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo e($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo e($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Member Since</label>
                        <input type="text" class="form-control" 
                               value="<?php echo formatDate($user['created_at']); ?>" readonly>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="profile-card">
                <h3>Change Password</h3>
                
                <form method="POST" action="">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" 
                               class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" 
                               class="form-control" required>
                        <small class="form-text">At least 8 characters with letters and numbers</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
