<?php
// Authentication and Session Management
session_start();

// Require database connection
require_once __DIR__ . '/../config/database.php';

// Require helper functions
require_once __DIR__ . '/functions.php';

// Check if user is authenticated, redirect to login if not
function requireAuth() {
    if (!isLoggedIn()) {
        redirect('login.php', 'Please login to continue.', 'warning');
    }
}

// Check if user is NOT authenticated (for login/register pages)
function requireGuest() {
    if (isLoggedIn()) {
        redirect('dashboard.php');
    }
}

// Regenerate session ID to prevent session fixation
function regenerateSession() {
    session_regenerate_id(true);
}

// Destroy session
function destroySession() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    
    session_destroy();
}
