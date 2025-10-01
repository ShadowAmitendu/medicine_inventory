<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

/**
 * Check if a user is logged in
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Redirect to login page if user is not logged in
 */
function requireAuth()
{
    if (!isLoggedIn()) {
        header("Location: ../public/login.php");
        exit();
    }
}

/**
 * Alias for requireAuth()
 */
function requireLogin()
{
    requireAuth();
}

/**
 * Attempt to log in a user
 *
 * @param string $username
 * @param string $password
 * @return bool
 */
function login(string $username, string $password): bool
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['profile_pic'] = $user['profile_pic'];
            return true;
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
    }

    return false;
}

/**
 * Logout the user
 */
function logout()
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
    header("Location: ../public/login.php");
    exit();
}

/**
 * Get current user data
 *
 * @return array|null
 */
function getCurrentUser()
{
    global $pdo;

    if (!isLoggedIn()) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, name, email, phone, profile_pic, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}