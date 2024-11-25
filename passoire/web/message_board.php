<?php
include 'db_connect.php'; // Include the database connection

// Initialize variables
$message = '';
$error = '';

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $content = trim($_POST['content']);

        // Check if message content is not empty
        if (!empty($content)) {
            // Insert the new message into the database using prepared statements
            $sql = "INSERT INTO messages (authorid, content, date) VALUES (:user_id, :content, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt->execute();
            
            $message = 'Message posted successfully!';
        } else {
            $error = 'Message content cannot be empty.';
        }
    } else {
        $error = 'You need to be logged in to post a message.';
    }
}

// Pagination
$limit = isset($_GET['limit']) ? $_GET['limit'] : 10; // Number of messages per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Prepare SQL query for filtering and pagination
if (isset($_GET['filter']) && strlen($_GET['filter']) > 0) {
    $sql2 = "SELECT m.id, m.content, m.date, u.login, i.avatar 
             FROM messages m 
             JOIN users u ON m.authorid = u.id 
             JOIN userinfos i ON u.id = i.userid 
             WHERE u.login = :filter 
             ORDER BY date DESC, id DESC 
             LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql2);
    $stmt->bindParam(':filter', $_GET['filter'], PDO::PARAM_STR);
} else {
    $sql2 = "SELECT m.id, m.content, m.date, u.login, i.avatar 
             FROM messages m 
             JOIN users u ON m.authorid = u.id 
             JOIN userinfos i ON u.id = i.userid 
             ORDER BY date DESC, id DESC 
             LIMIT :limit OFFSET :offset";
    $stmt = $conn->prepare($sql2);
}

$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch messages
$messages = [];
if ($stmt->rowCount() > 0) {
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "No messages found.";
}

// Get total messages for pagination
$sqlTotal = "SELECT COUNT(*) as total FROM messages";
$stmtTotal = $conn->query($sqlTotal);
$row = $stmtTotal->fetch(PDO::FETCH_ASSOC);
$totalMessages = $row['total'];
$totalPages = ceil($totalMessages / $limit);
?>

<?php if ($error): ?>
    <div class="w3-container w3-card w3-white w3-round w3-margin-right w3-margin-left w3-margin-bottom"><br>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="w3-container w3-card w3-white w3-round w3-margin-right w3-margin-left w3-margin-bottom"><br>
        <p class="success"><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['user_id'])): ?>
    <div class="w3-container w3-card w3-white w3-round w3-margin-right w3-margin-left w3-margin-bottom"><br>
        <form action="index.php" method="post">
            <h6 class="w3-opacity">Publish a message on the public board:</h6>
            <textarea name="content" contenteditable="true" class="w3-border w3-padding"  style="width: 100%; box-sizing: border-box;" required>Your message.</textarea><br />
            <div class="w3-margin"></div>
            <button type="submit" class="w3-button w3-theme w3-padding"><i class="fa fa-pencil"></i>  Post</button>
            <div class="w3-margin"></div>
        </form>
    </div>
<?php else: ?>
    <div class="w3-container w3-card w3-white w3-round w3-margin-right w3-margin-left w3-margin-bottom"><br>
        <p>Please <a href="connexion.php">log in</a> to post a message.</p>
    </div>
<?php endif; ?>

<div class="w3-container w3-card w3-white w3-round w3-margin"><br>
    <form action="index.php" method="GET">
        <label for="filter">Filter messages by author:</label>
        <input type="text" id="filter" name="filter" placeholder="Enter author name">
        <button type="submit" class="w3-button w3-theme w3-padding">Filter</button>
    </form>
    <div class="w3-margin"></div>
</div>

<?php foreach ($messages as $msg): ?>
    <div class="w3-container w3-card w3-white w3-round w3-margin"><br>
        <img src="<?php echo $msg['avatar']; ?>" alt="Avatar" class="w3-left w3-circle w3-margin-right" style="width:60px">
        <span class="w3-right w3-opacity"><?php echo htmlspecialchars($msg['date']); ?></span>
        <h4><?php echo htmlspecialchars($msg['login']); ?></h4><br>
        <hr class="w3-clear">
        <p><?php echo htmlspecialchars($msg['content']); ?></p>
    </div>
<?php endforeach; ?>

<div class="w3-container w3-card w3-white w3-round w3-margin">
    <?php if ($page > 1): ?>
        <a href="index.php?page=<?php echo $page - 1; ?>&limit=10">&laquo; Previous</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="index.php?page=<?php echo $i; ?>&limit=10" class="<?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="index.php?page=<?php echo $page + 1; ?>&limit=10">Next &raquo;</a>
    <?php endif; ?>
</div>
