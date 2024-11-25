<?php
// Database credentials
$host = 'db'; 
$dbname = 'passoire';
$username = 'passoire';
$password = 'hotdog';

try {
    // Create a PDO instance
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception mode
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

