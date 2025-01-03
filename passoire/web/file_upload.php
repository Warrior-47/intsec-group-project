<?php
// Include database connection and start session
include 'db_connect.php';
session_start();

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file']) && isset($_SESSION['user_id'])) {
        $ownerid = $_SESSION['user_id'];
        $file = $_FILES['file'];

        // Allowed file types
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
      
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            $error = "Invalid file type. Only JPG, PNG, and PDF files are allowed.";
        } else {
            // Prevent double extension attacks
            $fileName = $file['name'];
            $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);

            if (strpos($baseName, '.') !== false || !preg_match('/^[a-zA-Z0-9-_]+$/', $baseName)) {
                $error = "Invalid file name.";
            } else {
                // Handle file upload
                $uploadDir = 'uploads/';
                $uploadFile = $uploadDir . basename($file['name']);

                // Check if the directory exists, if not create it
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0750, true);
                }

                if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                    // Save file info to database using prepared statements

                    // Prepare SQL statement to insert file info
                    $stmt = $conn->prepare("INSERT INTO files (type, ownerid, date, path) VALUES (:type, :ownerid, NOW(), :path)");
                    $stmt->bindParam(':type', $file['type'], PDO::PARAM_STR);
                    $stmt->bindParam(':ownerid', $ownerid, PDO::PARAM_INT);
                    $stmt->bindParam(':path', $uploadFile, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        // Get the last inserted file ID
                        $file_id = $conn->lastInsertId();

                        $salt = random_int(100000000000, 999999999999);
                        // Generate the hash for the link table
                        $hash = sha1($ownerid . $salt. basename($file['name']));

                        // Insert the file link into the links table using prepared statements
                        $stmt2 = $conn->prepare("INSERT INTO links (fileid, secret, hash) VALUES (:fileid, :secret, :hash)");
                        $stmt2->bindParam(':fileid', $file_id, PDO::PARAM_INT);
                        $stmt2->bindParam(':hash', $hash, PDO::PARAM_STR);
                        $stmt2->bindParam(':secret', $salt, PDO::PARAM_INT);
                        $stmt2->execute();

                        $message = "File uploaded successfully!";
                    } else {
                        $error = "Error uploading the file.";
                    }
                } else {
                    $error = "Error moving the uploaded file.";
                }
            }
        }
    } else {
        $error = "Please log in to upload files.";
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Passoire: A simple file hosting server</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="./style/w3.css">
        <link rel="stylesheet" href="./style/w3-theme-blue-grey.css">
        <link rel="stylesheet" href="./style/css/fontawesome.css">
        <link href="./style/css/brands.css" rel="stylesheet" />
        <link href="./style/css/solid.css" rel="stylesheet" />
        <style>
            html, body, h1, h2, h3, h4, h5 {font-family: "Open Sans", sans-serif}
            .error { color: red; }
            .success { color: green; }
        </style>
    </head>
    <body class="w3-theme-l5">

        <?php include 'navbar.php'; ?>

        <!-- Page Container -->
        <div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
            <div class="w3-col m12">
                <div class="w3-card w3-round">
                    <div class="w3-container w3-center center-c w3-white">
                        <h1>File Upload</h1>
                    </div>

                    <div class="w3-container w3-padding w3-center center-c w3-white w3-margin-bottom w3-padding-bottom" id="drop-zone">
                        <?php if (isset($error)): ?>
                            <p class="error"><?php echo htmlspecialchars($error); ?></p>
                        <?php endif; ?>
                        <?php if (isset($message)): ?>
                            <p class="success"><?php echo htmlspecialchars($message); ?></p>
                        <?php endif; ?>

                        <form id="upload-form" action="file_upload.php" method="post" enctype="multipart/form-data" style="padding-bottom: 25px; border: 2px dotted darkgrey; margin-bottom: 25px; min-height: 200px;">
                            <div class="drop-zone" id="drop-zone">
                                <p id="drop-zone-file">Drag & Drop files here or click to select a file</p>
                                <input type="file" name="file" id="file-input" style="display:none;">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <!-- Footer -->
        <footer class="w3-container w3-theme-d3 w3-padding-16">
            <h5>About</h5>
        </footer>

    <script>
        const dropZone = document.getElementById('drop-zone');
        const dropZoneText = document.getElementById('drop-zone-file');
        const fileInput = document.getElementById('file-input');
        const form = document.getElementById('upload-form');

        // Handle click on drop zone to trigger file input
        dropZone.addEventListener('click', () => {
            fileInput.click();
        });

        // Handle drag over event
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });

        // Handle drag leave event
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });

        // Handle drop event
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');

            // Get the dropped files
            const files = e.dataTransfer.files;

            // Assign the files to the file input
            if (files.length > 0) {
                fileInput.files = files;
                form.submit();  // Automatically submit the form after dropping the file
            }
        });
        
        fileInput.addEventListener('change', function() {
            // Check if a file is selected
            if (fileInput.files.length > 0) {
                form.submit();
            }
        });

        // Prevent default drag and drop behavior
        document.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        document.addEventListener('drop', (e) => {
            e.preventDefault();
        });
    </script>
</body>
</html>
