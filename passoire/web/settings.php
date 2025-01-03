<?php
// Include database connection and start session
include 'db_connect.php';

// Set secure session cookie parameters before starting the session
$cookie_params = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookie_params['lifetime'],
    'path' => $cookie_params['path'],
    'domain' => $cookie_params['domain'],
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Start secure session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Validate user ID format (optional for additional security)
if (!is_numeric($user_id)) {
    session_destroy();
    header("Location: connexion.php");
    exit();
}

// Fetch current user info from the database
$stmt = $conn->prepare("
    SELECT u.login, u.email, ui.birthdate, ui.location, ui.bio, ui.avatar 
    FROM users u
    LEFT JOIN userinfos ui ON u.id = ui.userid
    WHERE u.id = ?
");
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);  // Bind the user ID parameter (i = integer)
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    $user = $result;
} else {
    echo "No results found.";
    exit();
}

// Function to handle avatar upload
function uploadAvatar($file, $user_id) {
    $upload_dir = 'img/';
    // Ensure uploads directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // File upload process
    $file_name = basename($file['name']);
    $file_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $file_name); // Sanitize the file name

    // Validate double extensions
    if (substr_count($file_name, '.') > 1) {
        return "Invalid file name. Double extensions are not allowed.";
    }

    // Get the file extension and convert to lowercase
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    // Ensure the uploaded file is an image and has a valid extension
    if (!in_array($file_ext, $allowed_ext)) {
        return "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
    }

    // Check the MIME type of the file
    $file_info = finfo_open(FILEINFO_MIME_TYPE); // Open fileinfo resource
    $mime_type = finfo_file($file_info, $file['tmp_name']);
    finfo_close($file_info);
    
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime_type, $allowed_mime_types)) {
        return "Invalid file type. Only images (JPG, PNG, GIF) are allowed.";
    }

    // Set new file name and path
    $new_file_name = 'avatar_' . $user_id . '.' . $file_ext;
    $file_path = $upload_dir . $new_file_name;

    // Move the uploaded file to the server
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        return $file_path;
    } else {
        return "Failed to upload avatar.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); 
    $birthdate = htmlspecialchars($_POST['birthdate'], ENT_QUOTES, 'UTF-8'); 
    $location = htmlspecialchars($_POST['location'], ENT_QUOTES, 'UTF-8'); 
    $bio = htmlspecialchars($_POST['bio'], ENT_QUOTES, 'UTF-8');
    $avatar_path = $user['avatar']; // Keep existing avatar by default

    // Check if an avatar file was uploaded
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $avatar_upload_result = uploadAvatar($_FILES['avatar'], $user_id);
        if (strpos($avatar_upload_result, 'img/') !== false) {
            $avatar_path = $avatar_upload_result; // Set new avatar path if upload was successful
        } else {
            echo "<p>" . $avatar_upload_result . "</p>";
        }
    }

    // Securely update the users table (email)
    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->bindParam(1, $email, PDO::PARAM_STR);  // Bind email (string)
    $stmt->bindParam(2, $user_id, PDO::PARAM_INT);  // Bind user_id (integer)
    $stmt->execute();

    // Securely update or insert into the userinfos table (birthdate, location, bio, avatar)
    $stmt = $conn->prepare("
        INSERT INTO userinfos (userid, birthdate, location, bio, avatar)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE birthdate = VALUES(birthdate), location = VALUES(location), bio = VALUES(bio), avatar = VALUES(avatar)
    ");
    $stmt->bindParam(1, $user_id, PDO::PARAM_INT);  // Bind user_id (integer)
    $stmt->bindParam(2, $birthdate, PDO::PARAM_STR);  // Bind birthdate (string)
    $stmt->bindParam(3, $location, PDO::PARAM_STR);  // Bind location (string)
    $stmt->bindParam(4, $bio, PDO::PARAM_STR);  // Bind bio (string)
    $stmt->bindParam(5, $avatar_path, PDO::PARAM_STR);  // Bind avatar (string)
    $stmt->execute();
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
            form {
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
            }
            label {
                display: block;
                margin-bottom: 10px;
            }
            input[type="text"],
            input[type="email"],
            input[type="date"],
            textarea {
                width: 100%;
                padding: 10px;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            img.avatar {
                max-width: 150px;
                height: auto;
                margin-bottom: 20px;
                border-radius: 50%;
            }
        </style>
    </head>
    <body class="w3-theme-l5">

        <?php include 'navbar.php'; ?>

        <!-- Page Container -->
        <div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
            <div class="w3-col m12">

                <div class="w3-card w3-round">
                    <div class="w3-container w3-center center-c w3-white">
                        <h1>User Settings</h1>
                    </div>

                    <div class="w3-container w3-center center-c w3-white w3-margin-bottom w3-padding-bottom">

                        <form method="POST" action="settings.php" enctype="multipart/form-data">
                            <!-- Display current avatar -->
                            <?php if ($user['avatar']): ?>
                                <img src="<?= htmlspecialchars($user['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="avatar">
                            <?php endif; ?>

                            <!-- Email -->
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" required>

                            <!-- Birthdate -->
                            <label for="birthdate">Birth Date:</label>
                            <input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($user['birthdate'], ENT_QUOTES, 'UTF-8') ?>" required>

                            <!-- Location -->
                            <label for="location">Location:</label>
                            <input type="text" id="location" name="location" value="<?= htmlspecialchars($user['location'], ENT_QUOTES, 'UTF-8') ?>">

                            <!-- Bio -->
                            <label for="bio">Bio:</label>
                            <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8') ?></textarea>

                            <!-- Avatar -->
                            <label for="avatar">Avatar:</label>
                            <input type="file" name="avatar" accept="image/*">

                            <!-- Submit -->
                            <input type="submit" value="Save Changes" class="w3-button w3-theme">
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </body>
</html>
