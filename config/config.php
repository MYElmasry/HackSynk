<?php
$host = 'localhost';
$dbname = 'hacksynk';
$username = 'root';
$password = '';

// Create DSN string
$dsn = "mysql:host=$host;dbname=$dbname";

// PDO options
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>