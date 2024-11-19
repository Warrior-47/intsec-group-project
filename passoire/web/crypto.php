<?php
// Include database connection and start session
include 'db_connect.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
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
						<h1>Cryptographic Helper</h1>
					</div>
					<br>
					
					
					<div class="w3-container w3-center center-c w3-white  w3-margin-bottom w3-padding-bottom">
					 <form id="hashForm">
						  <h2>Hash Function</h2>
						  <label for="hashText">Text to Hash:</label>
						  <input type="text" class="w3-border w3-padding w3-margin" id="hashText" name="text" required>
					<br>
						  
						  <label for="hashType">Hash Type:</label>
						  <select id="hashType" name="type">
						      <option value="md5">MD5</option>
						      <option value="sha1">SHA1</option>
						  </select>
					<br>

						  <button type="submit" class="w3-button w3-theme w3-margin">Get Hash</button>
						  <div id="hashOutput" class="output"></div>
					</form>
				<br>
				</div>
					<div class="w3-container w3-center center-c w3-white  w3-margin-bottom w3-padding-bottom">
					<form id="cryptoForm">
						  <h2>Encryption/Decryption Function</h2>
						  <label for="cryptoText">Text to Encrypt/Decrypt:</label>
						  <input type="text" class="w3-border w3-padding w3-margin" id="cryptoText" name="text" required>
					<br>
						  
						  <label for="encryptionKey">Key (hex for DES, alpha-numerical for AES):</label>
						  <input type="text" class="w3-border w3-padding w3-margin" id="encryptionKey" name="key" required>
					<br>

						  <label for="operationType">Operation Type:</label>
						  <select id="operationType" name="operation">
						      <option value="encrypt">Encrypt</option>
						      <option value="decrypt">Decrypt</option>
						  </select>

						  <label for="cryptoType">Encryption Type:</label>
						  <select id="cryptoType" name="type">
						      <option value="des">DES</option>
						      <option value="aes">AES</option>
						  </select>
					<br>

						  <button type="submit" class="w3-button w3-theme w3-margin">Perform Operation</button>
					<br>
						  <div id="cryptoOutput" class="output"></div>
					</form>
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
  

    <!-- JavaScript interracting with the Crypto helper API -->
    <script>
    
    function sanitizeString(str) {
			return str.replace(/[&<>"']/g, function (match) {
				const escape = {
				  '&': '&amp;',
				  '<': '&lt;',
				  '>': '&gt;',
				  '"': '&quot;',
				  "'": '&#39;'
				};
				return escape[match];
			});
		}
        
        // Handle hash form submission
				document.getElementById('hashForm').addEventListener('submit', async function(e) {
						e.preventDefault();
						const text = document.getElementById('hashText').value;
						const type = document.getElementById('hashType').value;
						const hashOutput = document.getElementById('hashOutput');

						try {
								const response = await fetch(`http://localhost:3002/hash/${type}`, {
								    method: 'POST',
								    headers: { 'Content-Type': 'application/json' },
								    body: JSON.stringify({ text })
								});

								if (!response.ok) {
								    const errorData = await response.json();
								    hashOutput.innerText = `Error: ${errorData.error || 'Something went wrong'}`;
								    hashOutput.style.color = 'red';
								} else {
								    const result = await response.json();
								    hashOutput.innerText = `Hash: ${result.hash}`;
								    hashOutput.style.color = 'black';
								}
						} catch (error) {
								hashOutput.innerText = `Error: ${error.message}`;
								hashOutput.style.color = 'red';
						}
				});

				// Handle encryption/decryption form submission
				document.getElementById('cryptoForm').addEventListener('submit', async function(e) {
						e.preventDefault();
						const text = sanitizeString(document.getElementById('cryptoText').value);
						const key = document.getElementById('encryptionKey').value;
						const operation = document.getElementById('operationType').value;
						const type = document.getElementById('cryptoType').value;
						const cryptoOutput = document.getElementById('cryptoOutput');

						// Determine the correct endpoint based on operation (encrypt/decrypt)
						const endpoint = `http://localhost:3002/${operation}/${type}`;

						try {
								const response = await fetch(endpoint, {
								    method: 'POST',
								    headers: { 'Content-Type': 'application/json' },
								    body: JSON.stringify({ text, key })
								});

								if (!response.ok) {
								    const errorData = await response.json();
								    cryptoOutput.innerText = `Error: ${errorData.error || 'Something went wrong'}`;
								    cryptoOutput.style.color = 'red';
								} else {
								    const result = await response.json();
								    const outputText = operation === 'encrypt' ? `Encrypted Text: ${result.encrypted}` : `Decrypted Text: ${result.decrypted}`;
								    cryptoOutput.innerText = outputText;
								    cryptoOutput.style.color = 'black';
								}
						} catch (error) {
								cryptoOutput.innerText = `Error: ${error.message}`;
								cryptoOutput.style.color = 'red';
						}
				});
    </script>
</body>
</html>
