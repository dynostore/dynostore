<?php
	//llama al archivo de conexion
	include_once "../../config/Connection.php";
	//se obtiene el token para permitir el acceso a la vista, de los contrario regresa a la vista principal
	$tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';//$_SESSION["tokenuser"];
	if(!isset($tokenuser) || $tokenuser==null){
		print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
	}
	//se obtiene el keyuser del usuario logueado para su uso cuando las consultas lo necesiten
	$keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
	//se hace la conexion con la BD en postgreSQL
	$conn = new Connection();
	$con=$conn->getConnection();
	//se obtienen los grupos a los que el usuario se encuentre suscrito ignorando el grupo de publico ya que a este grupo pertenecen todos los usuarios
	/*$dbh=$con->prepare("SELECT distinct on (namegroup)namegroup FROM groups as g join users_groups as ug on g.keygroup=ug.keygroup WHERE ug.keyuser='$keyuser' and namegroup!='Publico';");
	$dbh->execute();
	$num=$dbh->rowCount();
	$v1=$dbh->fetchAll();*/
	$grupos="";
?>
<html>
	<head>
		<title>.: SkyCDS :.</title>
		<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
	</head>
	<body>
		<?php
			//manda a llamar al menu de suscripcion
			include "navbar.php";
		?>
		<div class="container">
		<div class="row">
		<div class="col-md-6">
		<h2>Bienvenido</h2>
		Hola usuario:    
		<?php
			//se obtiene el nombre del usuario logueado
			/*$nombre=$con->prepare("SELECT nameuser from users where keyuser='$keyuser';");
			$nombre->execute();
			$nomnum=$nombre->rowCount();
			$nombre1=$nombre->fetchAll();
			if($nomnum>0){
				foreach ($nombre1 as $key) {
					$nameuser=$key['nameuser'];
				}
			}*/
			$nameuser='jessy';
			echo $nameuser;
		?>
		<br>
		Perteneces a:
		<?php
			//si hay grupos a los que esta suscrito el usuario logueado se hace el recorrido para listarlos en la vista
			if($num>0){
				foreach ($v1 as $key) {
					$grupos.=$key['namegroup'].", ";
				} 
				$grupos = substr($grupos,0, strlen($grupos) - 2);
				echo $grupos;
			}
			else{
				echo "No pertenece a ninguna entidad.";
			}
		?>
		</div>
		</div>
		</div>
	</body>
</html>
