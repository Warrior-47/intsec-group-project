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

// Handle file deletion
if (isset($_POST['delete']) && isset($_POST['file_id'])) {
    $file_id = $_POST['file_id'];

    // Retrieve file path before deletion
    /*$stmt = $pdo->prepare("SELECT path FROM files WHERE id = :file_id AND ownerid = :user_id");
    $stmt->execute(['file_id' => $file_id, 'user_id' => $user_id]);
    $file = $stmt->fetch();*/
    
    
		$sql = "SELECT path FROM files WHERE id = " . $file_id . " AND ownerid = " . $user_id . "";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
				// Fetch the first row of results into an array
				$file = $result->fetch_assoc();
		}

    if ($file) {
        // Delete file from the server
        $file_path = $file['path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete the file entry from the database
        //$stmt = $pdo->prepare("DELETE FROM files WHERE id = :file_id AND ownerid = :user_id");
        //$stmt->execute(['file_id' => $file_id, 'user_id' => $user_id]);
        
				$sql = "DELETE FROM files WHERE id = " . $file_id . " AND ownerid = " . $user_id . "";
				$conn->query($sql);

        // Also delete from the links table
        //$stmt = $pdo->prepare("DELETE FROM links WHERE fileid = :file_id");
        //$stmt->execute(['file_id' => $file_id]);
        
				$sql = "DELETE FROM links WHERE fileid = " . $file_id . "";
				$conn->query($sql);

        echo "File deleted successfully.";
    }
}


// Retrieve all files uploaded by the user
/*$stmt = $pdo->prepare("SELECT f.id, f.path, f.type, f.date, f.ownerid, l.hash FROM files f JOIN links l ON f.id = l.fileid WHERE f.ownerid = :user_id");
$stmt->execute(['user_id' => $user_id]);
$files = $stmt->fetchAll();*/

$sql = "SELECT f.id, f.path, f.type, f.date, f.ownerid, l.hash FROM files f JOIN links l ON f.id = l.fileid WHERE f.ownerid = " . $user_id;
$result = $conn->query($sql);
$files = [];
if ($result->num_rows > 0) {
	  // Fetch all rows and store them in an array
	  while($row = $result->fetch_assoc()) {
	      $files[] = $row;
	  }
} else {
	  echo "No messages found.";
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
		
		
				<div class="w3-card w3-round w3-white">
					<div class="w3-container w3-center center-c">
						<h1>My Files</h1>
						<br>
					
					
						<a href="file_upload.php" class="w3-button w3-theme w3-padding" title="Files"><i class="fa fa-folder-open"></i>Upload File(s)</a>
					</div>
					<br>
					
					
					<div class="w3-container w3-center center-c w3-white  w3-margin-bottom w3-padding-bottom">
					
					<?php if ($files): ?>
						  <table class="w3-table">
						      <tr>
						          <th>File Name</th>
						          <th>Type</th>
						          <th>Date Uploaded</th>
						          <th>Actions</th>
						      </tr>
						      <?php foreach ($files as $file): ?>
						          <tr>
						              <td><a href="link.php?file=<?= $file['hash'] ?>"><?= basename($file['path']) ?></a></td>
						              <td><?= htmlspecialchars($file['type']) ?></td>
						              <td><?= htmlspecialchars($file['date']) ?></td>
						              <td>
						                  <!-- Copy Link Button -->
						                  <button class="w3-button w3-theme w3-padding" onclick="copyLink('<?= $file['hash'] ?>')">Copy Link</button>
						                  
						                  <!-- Delete Button -->
						                  <form method="POST" action="my_files.php" style="display:inline;">
						                      <input type="hidden" name="file_id" value="<?= $file['id'] ?>">
						                      <button type="submit" class="w3-button w3-theme w3-padding" name="delete" onclick="return confirm('Are you sure you want to delete this file?')">Delete</button>
						                  </form>
						              </td>
						          </tr>
						      <?php endforeach; ?>
						  </table>
					<?php else: ?>
						  <p>You have not uploaded any files.</p>
					<?php endif; ?>
				
		<br>
				</div>
					<div class="w3-container w3-center center-c w3-white  w3-margin-bottom w3-padding-bottom">
					</div>
				</div>
			</div>
  	</div>
		<br>
		<!-- Footer -->
		<footer class="w3-container w3-theme-d3 w3-padding-16">
			<h5>About</h5>
		</footer>
  

    <!-- JavaScript for copying link -->
    <script>
        function copyLink(hash) {
            const link = window.location.origin + '/link.php?file=' + hash;
            navigator.clipboard.writeText(link).then(() => {
                alert('Link copied to clipboard: ' + link);
            }).catch(err => {
                alert('Failed to copy link: ' + err);
            });
        }
    </script>
</body>
</html>
