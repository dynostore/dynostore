<?php
session_start();

include_once "views/header.php";

// check if a session already running
if (!isset($_SESSION["connected"]) && !$_SESSION["connected"] == 1) {
	include_once "views/auth/login_V.php";
}else{

?>
<html>
<body>
	<?php
	include_once('views/navbar.php');
	?>
	<div class="col-md-12 col-sm-12 col-xs-12 well"></div>
	<div class="col-md-10 col-md-offset-1 col-sm-12 col-xs-12 well" id="page-body">
		<h3><?php echo $_SESSION["username"]; ?></h3>
		<ul>
			<li>
				<strong>Token of user:</strong> <?php echo $_SESSION["tokenuser"]; ?>
			</li>
			<li>
				<strong>Apikey:</strong> <?php echo $_SESSION["apikey"]; ?>
			</li>
			<li>
				<strong>Acces token:</strong> <?php echo $_SESSION["access_token"]; ?>
			</li>
		</ul>

	</div>

	<!-- Animacion de load -->
	<!-- PARA FUTURAS MEJORAS -->
	<div class="row" id="load" hidden="hidden">
		<div class="col-xs-4 col-xs-offset-4 col-md-2 col-md-offset-5"></div>
		<div class="col-xs-12 center text-accent"></div>
	</div>
	<!-- Fin load -->
</body>

<!-- Js personalizado -->
<script src="js/functions.js"></script>
</html>
<?php
}
?>