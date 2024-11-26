<?php
// Include the database connection
include 'db_connect.php';

// Start the session to track user login status
session_start();

// Initialize an error message variable
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and fetch the form data
    $login = filter_var($_POST['login'], FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Check if login and password are provided
    if (!empty($login) && !empty($password)) {
        // Prepare SQL query to fetch the user by login
        $sql = "SELECT id, pwhash FROM users WHERE login = :login";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':login', $login);
        $stmt->execute();

        // Check if user exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password with the hashed password stored in the database
            if (password_verify($password, $user['pwhash'])) {
                // Set session variable for the logged-in user
                $_SESSION['user_id'] = $user['id'];

                // Redirect to the index page (or any other page as needed)
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid password. Please try again.';
            }
        } else {
            $error = 'Invalid login. Please try again.';
        }
    } else {
        $error = 'Please enter both login and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
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
        input[type="text"], input[type="password"] {
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
                    <h2>Login</h2>

                    <!-- Display error or success messages -->
                    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

                    <!-- Login Form -->
                    <form method="POST" action="login.php">
                        <label for="login">Login:</label>
                        <input type="text" id="login" name="login" required>

                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>

                        <button type="submit" class="w3-button w3-theme w3-margin">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
