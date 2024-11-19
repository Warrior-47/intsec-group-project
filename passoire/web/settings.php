<?php
// Include database connection and start session
include 'db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Get user ID
$user_id = $_SESSION['user_id'];

// Fetch current user info from the database
/*$stmt = $pdo->prepare("
    SELECT u.login, u.email, ui.birthdate, ui.location, ui.bio, ui.avatar 
    FROM users u
    LEFT JOIN userinfos ui ON u.id = ui.userid
    WHERE u.id = \"" . $user_id . "\"
");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();*/


$sql = "
    SELECT u.login, u.email, ui.birthdate, ui.location, ui.bio, ui.avatar 
    FROM users u
    LEFT JOIN userinfos ui ON u.id = ui.userid
    WHERE u.id = \"" . $user_id . "\"
";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
		// Fetch the first row of results into an array
		$user = $result->fetch_assoc();
} else {
		echo "No results found.";
}

// Function to handle avatar upload
function uploadAvatar($file, $user_id) {
    $upload_dir = 'uploads/';
    // Ensure uploads directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // File upload process
    $file_name = basename($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

    // Ensure the uploaded file is an image and has a valid extension
    if (!in_array($file_ext, $allowed_ext)) {
        return "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
    }

    // Set new file name and path
    $new_file_name = 'avatar_' . $user_id . '.' . $file_ext;
    $file_path = $upload_dir . $new_file_name;

    // Move the uploaded file to the server
    if (move_uploaded_file($file_tmp, $file_path)) {
        return $file_path;
    } else {
        return "Failed to upload avatar.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $birthdate = $_POST['birthdate'];
    $location = $_POST['location'];
    $bio = $_POST['bio'];
    $avatar_path = $user['avatar']; // Default to current avatar

    // Check if an avatar file was uploaded
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $avatar_upload_result = uploadAvatar($_FILES['avatar'], $user_id);
        if (strpos($avatar_upload_result, 'uploads/') !== false) {
            $avatar_path = $avatar_upload_result; // Set new avatar path if upload was successful
        } else {
            echo "<p>" . $avatar_upload_result . "</p>";
        }
    }

    // Update the users table (email)
    $sql = "UPDATE users SET email = '" . $email . "' WHERE id = " . $user_id;
		$result = $conn->query($sql);



    // Update the userinfos table (birthdate, location, bio, avatar)
    
    $sql = "
        INSERT INTO userinfos (userid, birthdate, location, bio, avatar)
        VALUES (" . $user_id . ", '" . $birthdate . "', '" . $location . "', '" . $bio . "', '" . $avatar_path . "')
        ON DUPLICATE KEY UPDATE birthdate = '" . $birthdate . "', location = '" . $location . "', bio = '" . $bio . "', avatar = '" . $avatar_path . "'
    ";
		$result = $conn->query($sql);

    echo "<p>Profile updated successfully!</p>";
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
					
					
					<div class="w3-container w3-center center-c w3-white  w3-margin-bottom w3-padding-bottom">

						<form method="POST" action="settings.php" enctype="multipart/form-data">
								<!-- Display current avatar -->
								<?php if ($user['avatar']): ?>
								    <img src="<?= $user['avatar'] ?>" alt="Avatar" class="avatar">
								<?php endif; ?>
								
								<!-- Email -->
								<label for="email">Email:</label>
								<input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

								<!-- Birthdate -->
								<label for="birthdate">Birth Date:</label>
								<input type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($user['birthdate']) ?>" required>

								<!-- Location -->
								<label for="location">Location:</label>
								<input type="text" id="location" name="location" value="<?= htmlspecialchars($user['location']) ?>">

								<!-- Bio -->
								<label for="bio">Bio:</label>
								<textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>

								<!-- Avatar Upload -->
								<label for="avatar">Change Avatar:</label>
								<input type="file" id="avatar" name="avatar" accept="image/*">
								<br/>
		
								<!-- Submit Button -->
								<p>
									<button type="submit" class="w3-button w3-theme w3-padding">Update Profile</button>
								</p>
						</form>
								<br/>
			</div>
		</div>
	</div>
</body>
</html>

