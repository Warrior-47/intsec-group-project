<?php
// Include database connection
include 'db_connect.php';
session_start();

// Check if 'file' parameter is present in the query string
if (!isset($_GET['file'])) {
    // If not, redirect to home page or show an error message
    header("Location: index.php");
    exit();
}

// Get the hash value from the query string
$hash = $_GET['file'];

// Prepare and execute a query to find the corresponding file for the given hash

$sql = "SELECT f.path, f.type FROM links l JOIN files f ON l.fileid = f.id WHERE l.hash = \"" . $hash . "\" LIMIT 1";

// Execute query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
		// Fetch the first row of results into an array
		$file = $result->fetch_assoc();
} else {
		echo "No results found.";
}

if ($file) {
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
        echo "File does not exist.";
    }
} else {
    // File not found, display error or redirect
    echo "Invalid or expired link.";
    exit();
}
?>
