<?php
// Database credentials
$host = 'db';
$dbname = 'passoire';
$username = 'passoire';
$password = getenv('DB_PASS');

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    // Connection successful
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
