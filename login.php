<?php
require_once 'includes/bootstrap.php';

if (isLoggedIn()) {
    $redirectUrl = isAdmin() ? APP_URL . '/admin/index.php' : APP_URL . '/dashboard.php';
    header('Location: ' . $redirectUrl);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!validateEmail($email)) {
            $errors[] = 'Invalid email format.';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }
        
        // Attempt login if no errors
        if (empty($errors)) {
            $result = loginUser($email, $password);
            
            if ($result['success']) {
                $redirectUrl = $_SESSION['redirect_after_login'] ?? 
                              ($result['role'] === 'admin' ? APP_URL . '/admin/index.php' : APP_URL . '/dashboard.php');
                unset($_SESSION['redirect_after_login']);
                
                setFlashMessage('Welcome back!', 'success');
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Login</h1>
                <p>Welcome back! Please login to your account.</p>
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
            
            <form method="POST" action="" class="auth-form">
                <?php echo csrfField(); ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
            </div>
            
            <div class="demo-accounts">
                <h4>Demo Accounts</h4>
                <div class="demo-account-list">
                    <div class="demo-account">
                        <strong>Admin:</strong> admin@carplatform.com / Password123!
                        <button type="button" class="btn btn-sm btn-outline demo-fill"
                                data-email="admin@carplatform.com" data-password="Password123!">Use</button>
                    </div>
                    <div class="demo-account">
                        <strong>User:</strong> john@example.com / Password123!
                        <button type="button" class="btn btn-sm btn-outline demo-fill"
                                data-email="john@example.com" data-password="Password123!">Use</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
