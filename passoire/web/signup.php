<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'db_connect.php';

// Function to hash passwords securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $login = filter_var($_POST['login'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validate passwords
    if ($password !== $password_confirm) {
        $error = "<p class=\"error\">Passwords do not match. Please try again.</p>";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $error = "<p class=\"error\">Password must be at least 8 characters long and contain at least one letter and one number.</p>";
    } else {
        // Check if the login or email already exists using PDO
        $sql = "SELECT id FROM users WHERE login = :login OR email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':login', $login);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error = "<p class=\"error\">Login or email already exists. Please choose a different one.</p>";
        } else {
            // Hash the password securely
            $pwhash = hashPassword($password);

            // Insert into the users table using PDO
            $sql = "INSERT INTO users (login, email, pwhash) VALUES (:login, :email, :pwhash)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':pwhash', $pwhash);
            
            if ($stmt->execute()) {
                // Get the newly created user ID
                $user_id = $conn->lastInsertId();

                // Insert into the userinfos table using PDO
                $sql = "INSERT INTO userinfos (userid, birthdate, location, bio, avatar) VALUES (:userid, NULL, '', '', '')";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':userid', $user_id);

                if ($stmt->execute()) {
                    $success = "<p class=\"success\">Registration successful! You can now <a href='connexion.php'>log in</a>.</p>";
                } else {
                    echo "Error: " . $stmt->errorInfo()[2]; // Check for query error
                }
            } else {
                echo "Error: " . $stmt->errorInfo()[2]; // Check for query error
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
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
        .center-c {
            margin-bottom: 25px;
            padding-bottom: 25px;
        }

        form {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body class="w3-theme-l5">

<?php include 'navbar.php'; ?>

<!-- Page Container -->
<div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
    <div class="w3-row">
        <div class="w3-col m12">
            <div class="w3-card w3-round">
                <div class="w3-container w3-center center-c">
                    <h2>Sign Up</h2>

                    <!-- Display error or success messages -->
                    <?php if (isset($error)) echo $error; ?>
                    <?php if (isset($success)) echo $success; ?>

                    <!-- Sign Up Form -->
                    <form method="POST" action="signup.php">
                        <label for="login">Login:</label>
                        <input type="text" id="login" name="login" required>

                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>

                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>

                        <label for="password_confirm">Confirm Password:</label>
                        <input type="password" id="password_confirm" name="password_confirm" required>

                        <button type="submit" class="w3-button w3-theme w3-margin">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
