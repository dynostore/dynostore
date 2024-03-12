<?php 
	//llama archivo de conexion
	include_once "../config/Connection.php";
	//se obtiene el token d usuario
	$tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';
	//valida el login para mostrar vista
	if(!isset($tokenuser) || $tokenuser==null){
		print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
	}
?>
<!DOCTYPE html>
<html>
  	<head>
  		<title>Subir Archivo</title>
  		<meta charset="utf-8">
  		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
		<!-- Importamos los estilos de Bootstrap -->
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<!-- Font Awesome: para los iconos -->
		<link rel="stylesheet" href="../css/font-awesome.min.css">
		<!-- Sweet Alert: alertas JavaScript presentables para el usuario (más bonitas que el alert) -->
		<link rel="stylesheet" href="../css/sweetalert.css">
		<link rel="stylesheet" href="../css/style.css">
    </head>
  	<?php
  		//si se ha recibido un POST del formulario
  		if(isset($_POST['save'])){
  			//llama al archivo encargado de subir el archivo
  			include_once "../models/upload.php";
  			//se invoca la funcion que guarda el archivo en la BD
			uploadFile($_FILES);
			unset($_POST['save']);
		}
	?>
  	<body>
	  	<?php
	  		//lama al menu
	  		include "navbar.php";
	  	?>
	  	<!--vista de subir archivo-->
	    <div class="container col-lg-6 col-lg-offset-3">
			<div class="panel panel-default">
				<div class="panel-heading" style="background-color:#000000;">
					<div class="panel-title">
						<center><font face="arial" color="white"><h2>Subir Archivo</h2></font></center>
					</div>
				</div>
				<div class="panel-body">
					<form method="post" enctype="multipart/form-data">
						<div class="form-group">
							<input type="file" name="archivo[]" multiple="">	
						</div>
						<input type="submit" name="save" class="btn btn-primary" value="Guardar archivo">
					</form>
	  			</div>
	  		</div>
	  	</div>
	</body>
</html>