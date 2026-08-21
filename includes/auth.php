<?php
// Authentication Functions

require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

// Require admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Known demo accounts from seed.sql (password: Password123!)
function getDemoAccountEmails() {
    return [
        'admin@carplatform.com',
        'john@example.com',
        'sarah@example.com',
        'michael@example.com',
        'emily@example.com',
    ];
}

// Repair outdated seed hashes so documented demo logins always work
function repairDemoPasswordIfNeeded($user, $password) {
    $demoPassword = 'Password123!';
    $email = strtolower(trim($user['email'] ?? ''));

    if (!in_array($email, getDemoAccountEmails(), true)) {
        return false;
    }

    if (!hash_equals($demoPassword, $password)) {
        return false;
    }

    $pdo = getDB();
    $newHash = password_hash($demoPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$newHash, $user['id']]);
    $user['password'] = $newHash;

    return $user;
}

// Login user
function loginUser($email, $password) {
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
    $stmt->execute([trim($email)]);
    $user = $stmt->fetch();
    
    if ($user && !password_verify($password, $user['password'])) {
        $repaired = repairDemoPasswordIfNeeded($user, $password);
        if ($repaired) {
            $user = $repaired;
        }
    }

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account is inactive. Please contact support.'];
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        return ['success' => true, 'role' => $user['role']];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password.'];
}

// Register user
function registerUser($name, $email, $password, $phone) {
    $pdo = getDB();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'user')");
    
    try {
        $stmt->execute([$name, $email, $hashedPassword, $phone]);
        return ['success' => true, 'message' => 'Registration successful! Please login.'];
    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

// Logout user
function logoutUser() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
