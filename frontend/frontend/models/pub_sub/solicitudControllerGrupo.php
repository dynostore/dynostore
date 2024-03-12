<?php
	//llama al archivo de conexion
	include_once "../config/Connection.php";
	//obtiene los datos del usuario
	$tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';//$_SESSION["tokenuser"];
	$keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
	//realiza la conexion con la BD
	$conn = new Connection();
	$connection = $conn->getConnection();
	//Consulta para mostrar las solicitudes pendientes de los grupos propios
	/*$query = $connection->prepare("SELECT us.nameuser,us.keyuser as usr, re.keygroup, re.namegroup from  users_groups as ug inner join users us on ug.keyuser=us.keyuser inner join groups re on ug.keygroup=re.keygroup where ug.status='Pendiente' and ug.keygroup IN (SELECT keygroup from users_groups where keyuser='$keyuser' and status='Propietario';");*/
	$query = $connection->prepare("SELECT re.keygroup, re.namegroup from  users_groups as ug inner join groups re on ug.keygroup=re.keygroup where ug.status='Pendiente' and ug.keygroup IN (SELECT keygroup from users_groups where keyuser='$keyuser' and status='Propietario');");
	$query->execute();
	$num = $query->rowCount();
	$table=$query->fetchAll();
	//inicializa las variables a usar
	$tabla = "";
	$tipo="";
	//esta variable se utiliza para que el recorrido se haga en grupos y en sub-grupos(contador)
	$numt=1;
	//esta operacion se realiza 2 veces, uno en grupos y el segundo para los sub-grupos
	while($numt<3){
		//recorre el resultado de la consulta
	    foreach ($table as $row) {
	    	//establece los valores segun si es grupo o subgrupo
	    	//1=grupo,2=subgrupo
		   	if ($numt==1){
		    	$keyg=$row['keygroup'];
		    	$name=$row['namegroup'];
	    	}
	    	else{
		    	$keyg=$row['keysubs'];
		    	$name=$row['namesubs'];
    		}
	   		//Enlace para aceptar la solicitud
			$aceptar = '<a href=\"../models/aceptarSolicitudGrupo.php?keyre='.$keyg.'&keyus='.$row['usr'].'&t='.$id.'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Aceptar\"onclick=\"return confirm(\'¿Seguro que desea Aceptar la Solicitud? \')\" class=\"btn btn-primary\"><i class=\"glyphicon glyphicon-ok\" aria-hidden=\"falce\"></i></a>';

			//Enlace para eliminar la solicitud
			$eliminar = '<a href=\"../models/deleteSolitudGrupo.php?keyre='.$keyg.'&keyus='.$row['usr'].'&t='.$id.'\" onclick=\"return confirm(\'¿Seguro que desea rechazar Solicitud?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Rechazar\" class=\"btn btn-danger\"><i class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></i></a>';
			//se rellena la tabla	
			/*$tabla.='{
				      "user":"'.$row['nameuser'].'"	,
					  "nombre":"'.$name.'",
					   "acciones":"'.$aceptar.$eliminar.'"
					  		
					},';*/
			$tabla.='{
				      "user":"Usuario",
					  "nombre":"'.$name.'",
					  "acciones":"'.$aceptar.$eliminar.'"
					  		
					},';		
		}
		//aumenta el contador para recorrer los subgrupos
		$numt+=1;	
		//consulta que obtiene las solicitudes pendientes de los sub-grupos propios
		/*$query = $connection->prepare("SELECT us.nameuser,us.keyuser as usr, re.keysubs, re.namesubs from  users_sub as ug inner join users us on ug.keyuser=us.keyuser inner join group_subs re on ug.keysubs=re.keysubs where ug.status='Pendiente' and ug.keysubs IN (SELECT keysubs from users_sub where keyuser='$keyuser' and status='Propietario';");*/
		$query = $connection->prepare("SELECT re.keysubs, re.namesubs from  users_sub as ug inner join group_subs re on ug.keysubs=re.keysubs where ug.status='Pendiente' and ug.keysubs IN (SELECT keysubs from users_sub where keyuser='$keyuser' and status='Propietario');");
		$query->execute();
		$num = $query->rowCount();
	    $table=$query->fetchAll();
	}
	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);
	echo '{"data":['.$tabla.']}';	
?>