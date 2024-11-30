<?php
// Start the session to check for login status
session_start();

// Database connection
include 'db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1); // Enable for development only; disable in production

// Initialize variables
$user = "";

// Function to sanitize input and prevent SQL injection
function sanitize_input($input) {
    // Define a list of common SQL keywords used in injection
    $sql_keywords = [
        'UNION', 'SELECT', 'INSERT', 'DELETE', 'UPDATE', 'DROP', 'SHOW', 
        'ALTER', 'TABLE', 'DATABASE', 'INFORMATION_SCHEMA', 'FROM', 'WHERE', 
        'JOIN', 'HAVING', 'EXEC', 'EXECUTE', '--', '#', ';', '/*', '*/'
    ];

    // Convert input to uppercase to make case insensitive comparison
    $input_upper = strtoupper($input);

    // Check if any dangerous keyword exists in the input
    foreach ($sql_keywords as $keyword) {
        if (strpos($input_upper, $keyword) !== false) {
            // Block input if a keyword is found
            return false;
        }
    }

    // Check for any special characters (quotes, semicolons, etc.)
    if (preg_match('/[\'";#\-\*]/', $input)) {
        // Block input if special characters are found
        return false;
    }

    return $input; // Return the sanitized input
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        // Sanitize user ID
        $user_id = sanitize_input($_SESSION['user_id']);
        if ($user_id === false) {
            // Return a clear error message and stop further execution
            echo "You are not allowed!";
            exit; // Stop the script if invalid input is detected
        }

        // Fetch current user info from the database (using prepared statements)
        $sql = "SELECT u.login, u.email, ui.birthdate, ui.location, ui.bio, ui.avatar 
                FROM users u 
                LEFT JOIN userinfos ui ON u.id = ui.userid 
                WHERE u.id = :userid";
        
        // Use $conn instead of $pdo here
        $stmt = $conn->prepare($sql); // Use $conn (as defined in db_connect.php)
        $stmt->execute(['userid' => $user_id]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "No results found.";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage()); // Log error for debugging
        echo "An error occurred. Please try again later.";
    }
}

// flag_13 is 4c67df2a507f7398c201a2327bad35e31306027e.

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
        html, body, h1, h2, h3, h4, h5 { font-family: "Open Sans", sans-serif }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body class="w3-theme-l5">

<?php include 'navbar.php'; ?>

<!-- Page Container -->
<div class="w3-container w3-content" style="max-width:1400px;margin-top:80px">
    <!-- The Grid -->
    <div class="w3-row">

        <!-- Left Column -->
        <div class="w3-col m3">
            <!-- Profile -->
            <div class="w3-card w3-round w3-white">
                <div class="w3-container">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <h4 class="w3-center"><?php echo htmlspecialchars($user['login']); ?></h4>
                        <p class="w3-center"><img src="<?php echo htmlspecialchars($user['avatar']); ?>" class="w3-circle" style="height:106px;width:106px" alt="Avatar"></p>
                        <hr>
                        <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> <?php echo htmlspecialchars($user['bio']); ?></p>
                        <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> <?php echo htmlspecialchars($user['location']); ?></p>
                        <p><i class="fa fa-birthday-cake fa-fw w3-margin-right w3-text-theme"></i> <?php echo htmlspecialchars($user['birthdate']); ?></p>
                    <?php else: ?>
                        <h4 class="w3-center">Not Connected</h4>
                        <hr>
                        <p><a href="connexion.php">Log in here.</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Middle Column -->
        <div class="w3-col m7">
            <?php include 'message_board.php'; ?>
        </div>

        <!-- Right Column -->
        <div class="w3-col m2">
            <div class="w3-card w3-round w3-white w3-center">
                <div class="w3-container">
                    <h5>Deadlines Reminder:</h5>
                    <hr>
                    <p><strong>Deadline 1</strong></p>
                    <p>Friday 2024-11-22 23:59</p>
                    <hr>
                    <p><strong>Deadline 2</strong></p>
                    <p>Friday 2024-12-06 23:59</p>
                    <hr>
                    <p><strong>Deadline 3</strong></p>
                    <p>Friday 2024-12-20 23:59</p>
                </div>
            </div>
        </div>
    </div>
    <br>
    <!-- Footer -->
    <footer class="w3-container w3-theme-d3 w3-padding-16">
        <h5>About</h5>
    </footer>
</div>

<script>
    // Accordion
    function toggleHideShow(id) {
        var x = document.getElementById(id);
        if (x.className.indexOf("w3-show") == -1) {
            x.className += " w3-show";
            x.previousElementSibling.className += " w3-theme-d1";
        } else {
            x.className = x.className.replace("w3-show", "");
            x.previousElementSibling.className =
                x.previousElementSibling.className.replace(" w3-theme-d1", "");
        }
    }

    // Used to toggle the menu on smaller screens when clicking on the menu button
    function openNav() {
        var x = document.getElementById("navDemo");
        if (x.className.indexOf("w3-show") == -1) {
            x.className += " w3-show";
        } else {
            x.className = x.className.replace(" w3-show", "");
        }
    }
</script>
</body>
</html>
