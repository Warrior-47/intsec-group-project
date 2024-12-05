<?php
// Include database connection
include 'db_connect.php';
session_start();

function unauthorized() {
    http_response_code(403);
    echo "<h1>Forbidden</h1>";
    echo "<p>You don't have permission to access this resource.</p>";
    echo "<hr>";
    exit();
}

// Check if 'file' parameter is present in the query string
if (!isset($_GET['file'])) {
    // If not, redirect to home page or show an error message
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    // If user is not logged in, show default apache2 403 error message
    unauthorized();
}
// Get the hash value from the query string
$hash = $_GET['file'];

// Prepare and execute a query to find the corresponding file for the given hash
$stmt = $conn->prepare("SELECT f.path, f.type, f.ownerid 
                        FROM links l 
                        JOIN files f ON l.fileid = f.id 
                        WHERE l.hash = :hash 
                        LIMIT 1");
$stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
$stmt->execute();

$file = $stmt->fetch(PDO::FETCH_ASSOC);

if ($file && $file['ownerid'] == $_SESSION['user_id']) {
    // File found, prepare the download
    $file_path = $file['path'];
    $file_type = $file['type'];
    $file_name = basename($file_path);  // Get the file name from the path

    // Check if the file exists on the server
    if (file_exists($file_path)) {
        // Set headers to force the file download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file_type);
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        header('X-Custom-Info: path="' . $file_path . '"');

        // Clear output buffer and read the file
        ob_clean();
        flush();
        readfile($file_path);
        exit();
    } else {
        http_response_code(404);
        echo "<h1>File does not exist.</h1>";
        echo "<hr>";
    }
} else {
    // File not found, display error or redirect
    unauthorized();
}
?>
