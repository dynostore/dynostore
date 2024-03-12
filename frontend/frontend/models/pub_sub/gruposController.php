<?php
	//llama al archivo de conexion
	include_once "../config/Connection.php";
	//obtiene los datos del usuario
	$tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';//$_SESSION["tokenuser"];
 	$keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
 	//hace la conexion
	$conn= new Connection();
	$connection = $conn->getConnection();
	//Selecciona todos los grupos a los que el usuario no este suscrito
	$query = $connection->prepare("SELECT distinct on (namegroup) keygroup as idg,namegroup as name,date from groups WHERE keygroup!='85a4e1c128c620a5ba6e18bb27c0c91e55c80148' and keygroup NOT IN (SELECT keygroup from users_groups WHERE keyuser='$keyuser' and (status='Propietario' or status='Miembro'));");
	$query->execute();
	$num = $query->rowCount();
    $table=$query->fetchAll();
    //inicializa las variables a usar
    $id=1;
	$tabla = "";
	//es para que recorra grupos y subgrupos
	$numt=1;
	//realiza el recorrido 2 veces
	while($numt<3){
	    foreach ($table as $row) {
	    	//boton para unirse al grupo o subgrupo
			$unir ='<a href=\"../models/unirGrupo.php?key='.$row['idg'].'&t='.$numt.'\" onclick=\"return confirm(\'Tendrá que esperar a ser aceptado por el propietario\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Solicitar Unirse\" class=\"btn btn-primary\"><i class=\"fa fa-sign-in\" aria-hidden=\"true\"></i></a>';		
			$tabla.='{
					  "id":"'.$id.'",
					  "grupo":"'.$row['name'].'",
					  "fecha":"'.$row['date'].'",
					   "acciones":"'.$unir.'"			
					},';
			//aumenta el contador	
			$id+=1;		
		}
		//Selecciona todos los grupos a los que el usuario no este suscrito
		$query = $connection->prepare("SELECT distinct on (namesubs) keysubs as idg,namesubs as name,date from group_subs WHERE keysubs NOT IN (SELECT keysubs from users_sub WHERE keyuser='$keyuser' and (status='Propietario' or status='Administrador' or status='Miembro'));");
		$query->execute();
		$num = $query->rowCount();
	    $table=$query->fetchAll();
	    //se aumenta el contador
	    $numt+=1;
	}
	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);
	//Manda los datos al js
	echo '{"data":['.$tabla.']}';	
?>