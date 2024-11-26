<?php
// Start the session to check for login status
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// List of common SQL injection keywords to block
$sql_keywords = ['UNION', 'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'FROM', 'WHERE', 'ORDER', 'BY', 'LIMIT', 'DATABASE', 'INFORMATION_SCHEMA', 'TABLE', 'COLUMN'];

// Function to check for SQL injection attempts
function validate_sql_param($param) {
    global $sql_keywords;
    
    // Convert to uppercase for comparison
    $param_upper = strtoupper($param);
    
    // Check if the parameter contains any SQL keywords
    foreach ($sql_keywords as $keyword) {
        if (strpos($param_upper, $keyword) !== false) {
            die('Invalid input: SQL injection detected.');
        }
    }

    // Check for common special characters used in SQL injection, except the allowed ones (_ # ?)
    if (preg_match('/[\'";\-\*\(\)]+/', $param)) {
        die('Invalid input: special characters detected.');
    }

    return $param;
}

// Validate the 'filter' parameter (allow alphanumeric and special characters _ # ?)
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$filter = validate_sql_param($filter); // Validate filter

// Validate the 'page' parameter (only numbers allowed)
$page = isset($_GET['page']) ? $_GET['page'] : '';
if (!preg_match('/^\d+$/', $page) && !empty($page)) {
    die('Invalid input for page. Only numbers are allowed.');
}

// Validate the 'limit' parameter (only numbers allowed)
$limit = isset($_GET['limit']) ? $_GET['limit'] : '';
if (!preg_match('/^\d+$/', $limit) && !empty($limit)) {
    die('Invalid input for limit. Only numbers are allowed.');
}

// Include the database connection file
include 'db_connect.php'; // Use the updated PDO connection file

$user = "";

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Get user ID
    $user_id = $_SESSION['user_id'];

    try {
        // Fetch current user info from the database using a prepared statement
        $stmt = $conn->prepare("
            SELECT u.login, u.email, ui.birthdate, ui.location, ui.bio, ui.avatar 
            FROM users u 
            LEFT JOIN userinfos ui ON u.id = ui.userid 
            WHERE u.id = :user_id
        ");
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "No user data found.";
        }
    } catch (PDOException $e) {
        echo "Error fetching user data: " . htmlspecialchars($e->getMessage());
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
        html, body, h1, h2, h3, h4, h5 { font-family: "Open Sans", sans-serif; }
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
                    <?php if (isset($_SESSION['user_id']) && $user): ?>
                        <h4 class="w3-center"><?php echo htmlspecialchars($user['login']); ?></h4>
                        <p class="w3-center">
                            <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                 class="w3-circle" 
                                 style="height:106px;width:106px" 
                                 alt="Avatar">
                        </p>
                        <hr>
                        <p><i class="fa fa-pencil fa-fw w3-margin-right w3-text-theme"></i> 
                            <?php echo htmlspecialchars($user['bio']); ?>
                        </p>
                        <p><i class="fa fa-home fa-fw w3-margin-right w3-text-theme"></i> 
                            <?php echo htmlspecialchars($user['location']); ?>
                        </p>
                        <p><i class="fa fa-birthday-cake fa-fw w3-margin-right w3-text-theme"></i> 
                            <?php echo htmlspecialchars($user['birthdate']); ?>
                        </p>
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
</div>

<!-- Footer -->
<footer class="w3-container w3-theme-d3 w3-padding-16">
    <h5>About</h5>
</footer>

<script>
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
