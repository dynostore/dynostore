<?php 
	//llama al archivo de conexion
	include_once "../config/Connection.php";
	//obtiene el token
	$tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';
	//valida el login para mostrar la vista
	if(!isset($tokenuser) || $tokenuser==null){
		print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
	}
?>
<!DOCTYPE html>
<html>
  	<head>
	  	<title>Control de Ficheros</title>
	  	<meta charset="utf-8">
	  	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
  		<title>Reemplazar</title>
		<!-- Importamos los estilos de Bootstrap -->
		<link rel="stylesheet" href="../css/bootstrap.min.css">
		<!-- Font Awesome: para los iconos -->
		<link rel="stylesheet" href="../css/font-awesome.min.css">
		<!-- Sweet Alert: alertas JavaScript presentables para el usuario (más bonitas que el alert) -->
		<link rel="stylesheet" href="../css/sweetalert.css">
  		<link rel="stylesheet" href="../css/style.css">
    </head>
    <?php 
    	//si el formulario fue enviado
	  	if(isset($_POST['save']))
		{
			//valida la ruta del archivo
			if(file_exists($_GET['na'])==true){
				//llama a la funcion de actualizar
				updateFile($_FILES);
				unset($_POST['save']);
			}
			else{
				echo "El archivo no existe.";
			}
		}
	?>
  	<body>
	  	<?php
	  		//llama al menu
	  		include "navbar.php";
	  	?>
	  	<div class="container col-lg-6 col-lg-offset-3">
			<div class="panel panel-default">
				<div class="panel-heading" style="background-color:#000000;">
					<div class="panel-title">
						<center><font face="arial" color="white"><h2>Reemplazar Fichero</h2></font></center>
					</div>
				</div>
				<div class="panel-body">
					<form method="post" enctype="multipart/form-data">
						<div class="form-group">
							<input type="file" name="archivo">	
						</div>
						<input type="submit" name="save" class="btn btn-primary">
					</form>
	  			</div>
	  		</div>
	  	</div>
    </body>
</html>
<?php
	//Funcion para reemplazar archivo.
	function updateFile($file){
		//Obtencion y declaracion de las variables necesarias.
		$ruta='C:/xampp/htdocs/Suscripcion_Publicacion/Archivos/';//$_GET['na'];
		$kf=$_GET['key'];
		$nombre=$file["archivo"]["name"];
		//$fecha=date("d") . " de " . date("M") . " del " . date("Y");
		//$tamano=$file['archivo']['size'];
		//Eliminar el fichero de la ruta en donde se encuentra.
		unlink($ruta);
		// Establece la ruta con el nuevo fichero
		//ruta windows
		$ruta2 = 'C:/xampp/htdocs/Suscripcion_Publicacion/Archivos/'.$file["archivo"]["name"][$key];
		//ruta ubuntu
		//$ruta2 = '/var/www/html/App/archivos/'.$file["archivo"]["name"]; 
		//Condicionar si el archivo es movido a la ruta, entonces actualizar los datos en la BD.
		if (move_uploaded_file($file['archivo']['tmp_name'],$ruta2)){
			//hace la conexion con la BD
			$conn = new Connection();
    		$connection = $conn->getConnection();
    		//realiza la actualizacion
    		$update=$connection->prepare("UPDATE files SET url='$ruta2', namefile='$nombre', sizefile='$tamano', date='$fecha' WHERE keyfile='$kf';" );
    		//retorna a la lista de los archivos de todos los catalogos
    		header("Location: listaArchivos.php");
		}
		else {
    		echo "¡Posible ataque de subida de ficheros!";
    		echo 'Más información de depuración:';
			print_r($file);	
			print "</pre>";
		}
	} 
 ?>