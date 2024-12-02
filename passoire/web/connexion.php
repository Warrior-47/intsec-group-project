<?php
// Include the database connection
include 'db_connect.php'; 

// Start the session to track user login status
session_start();

// Initialize variables
$error = '';
$maxAttempts = 3; 
$lockoutTime = 3 * 60; 



// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Track login attempts per IP
$ip = $_SERVER['REMOTE_ADDR'];
$timeNow = time();

// Check if IP-based rate limiting is needed
if (!isset($_SESSION['ip_attempts'][$ip])) {
    $_SESSION['ip_attempts'][$ip] = ['attempts' => 0, 'last_attempt' => $timeNow];
}

// Check if the user is locked out based on IP rate limiting
if ($_SESSION['ip_attempts'][$ip]['attempts'] >= $maxAttempts) {
    if ($timeNow - $_SESSION['ip_attempts'][$ip]['last_attempt'] < $lockoutTime) {
        $error = 'Too many failed attempts from your IP. Please try again later.';
    } else {
        // Reset the count after lockout time
        $_SESSION['ip_attempts'][$ip] = ['attempts' => 0, 'last_attempt' => $timeNow];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['ip_attempts'][$ip]['attempts'] < $maxAttempts) {
    $login = $_POST['login'];
    $password = $_POST['password'];

    
    if (!empty($login) && !empty($password)) {
        
        $sql = "SELECT id, pwhash FROM users WHERE login = :login";
        $stmt = $conn->prepare($sql); // Use $conn, as defined in db_connect.php
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);

        // Execute the query
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['pwhash'])) {
            // Reset login attempts on success
            $_SESSION['ip_attempts'][$ip] = ['attempts' => 0, 'last_attempt' => $timeNow];
            // Set the session variable
            $_SESSION['user_id'] = $user['id'];
            // Redirect to a different page (e.g., index.php)
            header('Location: index.php');
            exit();
        } else {
            // Increment the login attempt count for the IP
            $_SESSION['ip_attempts'][$ip]['attempts']++;
            $_SESSION['ip_attempts'][$ip]['last_attempt'] = $timeNow;

            $error = 'Invalid login or password. Please try again.';
        }
    } else {
        $error = 'Please fill in both fields.';
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
                                <p class="error"><?php echo htmlspecialchars($error); ?></p>
                            <?php endif; ?>

                            <form action="connexion.php" method="post">
                                <input type="text" class="w3-border w3-padding w3-margin" name="login" placeholder="Login" required><br />
                                <input type="password" class="w3-border w3-padding w3-margin" name="password" placeholder="Password" required><br />
                                <button type="submit" class="w3-button w3-theme w3-margin" <?php echo ($_SESSION['ip_attempts'][$ip]['attempts'] >= $maxAttempts && time() - $_SESSION['ip_attempts'][$ip]['last_attempt'] < $lockoutTime) ? 'disabled' : ''; ?>>Login</button><br />
                            </form>

                            <p>Don't have a login yet? <a href="signup.php"> Sign up here!</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
