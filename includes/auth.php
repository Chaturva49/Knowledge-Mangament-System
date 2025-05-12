<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to check if user is a regular user
function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

// Function to check if user is a guest
function isGuest() {
    return !isLoggedIn() || (isset($_SESSION['role']) && $_SESSION['role'] === 'guest');
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current username
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

// Function to get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? 'guest';
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please log in to access this page.';
        header('Location: login.php');
        exit();
    }
}

// Function to require admin
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: index.php');
        exit();
    }
}

// Function to require user role
function requireUser() {
    if (!isUser() && !isAdmin()) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: index.php');
        exit();
    }
}

// Function to log user activity
function logActivity($userId, $action, $details = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action, details) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $action, $details]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

// Function to sanitize output
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Function to generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
    return true;
}

// Function to check if user has permission to access a resource
function hasPermission($requiredRole) {
    $userRole = getCurrentUserRole();

    // Admin has access to everything
    if ($userRole === 'admin') {
        return true;
    }

    // Role hierarchy: admin > user > guest
    $roleHierarchy = [
        'admin' => 3,
        'user' => 2,
        'guest' => 1
    ];

    // Validate requiredRole
    if (!isset($roleHierarchy[$requiredRole])) {
        return false; // Invalid or empty role
    }

    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}
?> 