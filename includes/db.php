<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Database Configuration
$host = 'localhost'; // CHANGE THIS back to your hosting DB host if needed
$db   = 'inventory_system'; // CHANGE THIS to your hosting DB name
$user = 'root'; // CHANGE THIS to your hosting DB username
$pass = ''; // CHANGE THIS to your hosting DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     global $pdo;
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Database Connection Failed: " . $e->getMessage());
}
?>
