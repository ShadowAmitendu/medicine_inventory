<?php
/**
 * Centralized Path Configuration
 * This file defines all paths used throughout the application
 * to ensure consistency and make maintenance easier.
 */

// Define the root directory of the project
define('ROOT_PATH', dirname(__DIR__));

// Define main directories
const CONFIG_PATH = ROOT_PATH . '/config';
const INCLUDES_PATH = ROOT_PATH . '/includes';
const PUBLIC_PATH = ROOT_PATH . '/public';
const ASSETS_PATH = ROOT_PATH . '/assets';
const UPLOADS_PATH = ROOT_PATH . '/uploads';

/**
 * IMPORTANT: Set your project folder name here
 * If your project is at http://localhost/medicine_inventory/
 * then set this to '/medicine_inventory'
 *
 * If it's in the root (http://localhost/), set to ''
 */
const PROJECT_FOLDER = '/medicine_inventory';

/**
 * Get the base URL dynamically
 */
function getBaseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    return $protocol . $host . PROJECT_FOLDER;
}

// Define base URL constant
define('BASE_URL', getBaseUrl());

/**
 * URL Helper Functions
 */

// Get URL for public pages
function publicUrl($page = ''): string
{
    return BASE_URL . '/public/' . ltrim($page, '/');
}

// Get URL for assets
function assetUrl($file = ''): string
{
    return BASE_URL . '/assets/' . ltrim($file, '/');
}

// Get URL for uploads
function uploadUrl($file = ''): string
{
    return BASE_URL . '/uploads/' . ltrim($file, '/');
}

// Get URL for root pages (like index.php)
function rootUrl($page = ''): string
{
    return BASE_URL . '/' . ltrim($page, '/');
}

/**
 * Page URL Constants
 */
define('URL_HOME', rootUrl('index.php'));
define('URL_LOGIN', publicUrl('login.php'));
define('URL_LOGOUT', publicUrl('logout.php'));
define('URL_REGISTER', publicUrl('register.php'));
define('URL_PROFILE', publicUrl('profile.php'));
define('URL_CHANGE_PASSWORD', publicUrl('change_pw.php'));
define('URL_LIST_MEDICINES', publicUrl('list_medicines.php'));
define('URL_ADD_MEDICINE', publicUrl('add_medicine.php'));
define('URL_EDIT_MEDICINE', publicUrl('edit_medicine.php'));
define('URL_DELETE_MEDICINE', publicUrl('delete_medicine.php'));
define('URL_MANAGE_CATEGORIES', publicUrl('manage_categories.php'));

/**
 * Get current page name
 */
function getCurrentPage(): string
{
    return basename($_SERVER['PHP_SELF']);
}

/**
 * Check if current page matches given page
 */
function isCurrentPage($page): bool
{
    return getCurrentPage() === $page;
}

/**
 * Redirect helper function
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit();
}

/**
 * Get profile picture URL
 */
function getProfilePicUrl($filename)
{
    if (empty($filename)) {
        return null;
    }
    return uploadUrl($filename);
}

/**
 * Generate avatar URL for user
 */
function getAvatarUrl($name, $size = 64): string
{
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=00809D&color=fff&size=' . $size;
}