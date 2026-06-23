<?php
// db.php
// Database configuration
$host = 'localhost';
$dbname = 'inventorymanager';
$username = 'root'; // Default username for XAMPP/MAMP
$password = '';     // Default password is usually empty for local development

try {
    // Create a new PDO instance
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);

    // Configure PDO to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative arrays for easier data handling
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Catch any connection errors and terminate the script gracefully
    die("Database Connection Failed: " . $e->getMessage());
}
?>