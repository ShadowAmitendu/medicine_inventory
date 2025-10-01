<?php
require_once '../config/auth.php';

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Log the logout action (optional - for security audit)
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    error_log("User logout: ID=$user_id, Username=$username, Time=" . date('Y-m-d H:i:s'));
}

// Call the logout function which destroys session and redirects
logout();