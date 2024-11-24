// Include the database connection
include 'db_connect.php';

// Start the session to track user login status
session_start();

// Initialize an error message variable
$error = '';

// Initialize login attempts in the session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

// Check for rate limiting
$max_attempts = 3;
$lockout_time = 300; 
$lockout = false;

if ($_SESSION['login_attempts'] >= $max_attempts) {
    $remaining_time = $lockout_time - (time() - $_SESSION['last_attempt_time']);
    if ($remaining_time > 0) {
        $error = "Too many failed attempts. Please try again in " . ceil($remaining_time / 60) . " minutes.";
        $lockout = true;  // Set flag to disable button
    } else {
        // Reset attempts after lockout period
        $_SESSION['login_attempts'] = 0;
        $lockout = false; // Allow login attempt after lockout
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['login_attempts'] < $max_attempts) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Check if login and password are provided
    if (!empty($login) && !empty($password)) {
        // Prepare the SQL query with placeholders
        $stmt = $conn->prepare("SELECT id, pwhash FROM users WHERE login = ?");
        
        // Bind the user input to the query
        $stmt->bind_param("s", $login); 

        // Execute the query
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password (ensure you're using a hashed password in the database)
            if (password_verify($password, $user['pwhash'])) {
                // Reset login attempts on successful login
                $_SESSION['login_attempts'] = 0;
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid password. Please try again.';
            }
        } else {
            $error = 'Invalid login. Please try again.';
        }

        // Close the statement
        $stmt->close();
    } else {
        $error = 'Please fill in both fields.';
    }

    // Increment login attempts
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
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
                        .center-c {
                                margin-bottom: 25px;
                                padding-bottom: 25px;
                        }
                </style>
        </head>
        <body class="w3-theme-l5">
<?php include 'navbar.php'; ?>


                <!-- Page Container -->
                <div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">


                        <!-- The Grid -->
                        <div class="w3-row">
                                <div class="w3-col m12">


                                <div class="w3-card w3-round">
                                        <div class="w3-container w3-center center-c">
                                                                <h2>Login</h2>

                                                                <?php if ($error): ?>
                                                                    <p class="error"><?php echo $error; ?></p>
                                                                <?php endif; ?>

                                                                <form action="connexion.php" method="post">
                                                                    <input type="text" class="w3-border w3-padding w3-margin" name="login" placeholder="Login" required><br />
                                                                    <input type="password" class="w3-border w3-padding w3-margin" name="password" placeholder="Password" required><br />
                                                                    <button type="submit" class="w3-button w3-theme w3-margin" <?php echo isset($lockout) && $lockout ? 'disabled' : ''; ?>>Login</button><br />
                                                                </form>

                                                                <p>Don't have a login yet? <a href="signup.php"> Sign up here!</a></p>
                                                </div>
                                        </div>
    
    
                                                </div>
                                        </div>
                                </div>
</body>
</html>
