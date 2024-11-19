
		<!-- Navbar -->
		<div class="w3-top">
		 <div class="w3-bar w3-theme-d2 w3-left-align w3-large">
			<a class="w3-bar-item w3-button w3-hide-medium w3-hide-large w3-right w3-padding-large w3-hover-white w3-large w3-theme-d2" href="javascript:void(0);" onclick="openNav()"><i class="fa fa-bars"></i></a>
			<a href="index.php" class="w3-bar-item w3-button w3-padding-large w3-theme-d4"><i class="fa fa-home w3-margin-right"></i>Home</a>
			<a href="my_files.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" title="Files"><i class="fa fa-folder-open"></i></a>
			<a href="settings.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" title="Account Settings"><i class="fa fa-gear"></i></a>
			<a href="crypto.php" class="w3-bar-item w3-button w3-hide-small w3-padding-large w3-hover-white" title="Crypto Helper"><i class="fa-solid fa-lock"></i></a>
			
			<?php if (isset($_SESSION['user_id'])): ?>
					<a href="logout.php" class="w3-bar-item w3-button w3-hide-small w3-right w3-padding-large w3-hover-white" title="Log out">
						<i class="fa fa-right-from-bracket"></i>
					</a>
			<?php else: ?>
					<a href="connexion.php" class="w3-bar-item w3-button w3-hide-small w3-right w3-padding-large w3-hover-white" title="Log in">
						<i class="fa fa-user w3-margin-right"></i>
					</a>
			<?php endif; ?>
			
			
			
		 </div>
		</div>
