<?php
// Database credentials
$host = 'localhost';
$db = 'medicine_inventory';
$user = 'root';        // change to your MySQL username
$pass = '';            // change to your MySQL password
$charset = 'utf8mb4';

// DSN for PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES => false,                  // use native prepares if possible
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // If connection fails, display error (in production, log instead)
    echo "Database connection failed: " . $e->getMessage();
    exit;
}
