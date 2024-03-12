<?php
	//llama al archivo de conexion
	include_once "../config/Connection.php";
	//obtiene los datos del usuario
	$tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';
	$keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
	//inicializa las variables
	$keyR="";
	$nap="";
	$usuario="";
	//realiza la conexion con la BD
	$conn = new Connection();
	$connection = $conn->getConnection();
	//se obtiene el keygroup para visualizar usuarios de grupo
	$query = $connection->prepare("SELECT * from users_groups where keyuser='Propietario' or keyuser='Administrador' or keyuser='Miembro';");
	$query->execute();
	//borra la relacion usada temporalmente
	$query2 = $connection->prepare("DELETE from users_groups where keyuser='Propietario' or keyuser='Administrador' or keyuser='Miembro';");
	$query2->execute();
	$ke=$query->fetchAll();
	//si el registro esta en users_groups
	if(($query->rowCount())>0){
		//obtiene los valores guardados temporalmente
		foreach ($ke as $key) {
			//la clave del grupo
			$keyR=$key['keygroup'];
			//si es grupo o subgrupo
			$nap=$key['status'];
			//obtiene el status del usuario
			$usuario=$key['keyuser'];
		}
	}
	//el registro temporal se encuentra en users_sub
	else{
	 	//se obtiene el keysubs para visualizar usuarios de subgrupo
		$query = $connection->prepare("SELECT * from users_sub where keyuser='Propietario' or keyuser='Administrador' or keyuser='Miembro';");
		$query->execute();
		//borra la relacion usada temporalmente
		$query2 = $connection->prepare("DELETE from users_sub where keyuser='Propietario' or keyuser='Administrador' or keyuser='Miembro';");
		$query2->execute();
		$ke=$query->fetchAll();
		if(($query->rowCount())>0){
			foreach ($ke as $key) {
				//la clave del subgrupo
				$keyR=$key['keysubs'];
				//si es grupo o subgrupo
				$nap=$key['status'];
				//obtiene el status del usuario
				$usuario=$key['keyuser'];
			}
		}
 	}
 	//inicializa un contador
 	$id=1;
	/*$query = $connection->prepare("SELECT nameuser, u.keyuser,keygroup,ug.status FROM users as u join users_groups as ug on u.keyuser=ug.keyuser join groups as g on g.keygroup=ug.keygroup WHERE g.keygroup='$keyR' and ug.status!='Pendiente';");*/
	//si es un grupo
	if ($nap=='-'){
		//Selecciona todos los usuarios suscritos al grupo
		$query = $connection->prepare("SELECT ug.keyuser, g.keygroup,ug.status FROM users_groups as ug join groups as g on g.keygroup=ug.keygroup WHERE g.keygroup='$keyR' and ug.status!='Pendiente';");
	}
	else{
		//Selecciona todos los usuarios suscritos al subgrupo
		$query = $connection->prepare("SELECT ug.keyuser, g.keysubs,ug.status FROM users_sub as ug join group_subs as g on g.keysubs=ug.keysubs WHERE g.keysubs='$keyR' and ug.status!='Pendiente';");
	}
	$query->execute();
	$num = $query->rowCount();
    $table=$query->fetchAll();
    //inicializa las variables a utilizar
	$tabla = "";
	$expulsar="";
	$ascender="";
	//recorre la lista de usuarios
    foreach ($table as $row) {
    	//si es propietario o administrador puede expulsar o ascender a los usuarios
    	if($usuario=='Propietario' || $usuario=='Administrador'){
			//el usuario logueado no puede expulsarse o ascenderse a el mismo
			$rkey=$row['keyuser'];
			if($rkey!=$keyuser){
				//se pasa el key segun si es grupo o sub-grupo
				if ($nap=='-'){
					$keyg=$row['keygroup'];
				}
				else{
					$keyg=$row['keysubs'];
				}
				//solo si el usuario suscrito no es el propietario puede expulsarse o eliminarse
				if($row['status']!='Propietario'){
    				$expulsar='<a href=\"../models/expulsarUsuario.php?key='.$rkey.'&keyG='.$keyg.'&nap='.$nap.'\" onclick=\"return confirm(\'¿Seguro que desea expulsar a ese usuario?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Expulsar\" class=\"btn btn-danger\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>';
    				$ascender='<a href=\"../models/ascenderUsuario.php?key='.$rkey.'&keyG='.$keyg.'&nap='.$nap.'\" onclick=\"return confirm(\'¿Seguro que desea ascender a este usuario a administrador de la organizacion?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Hacer administrador de la organizacion\" class=\"btn btn-info\"><i class=\"fa fa-level-up\" aria-hidden=\"true\"></i></a>';
    			}
    		}
    		else{
	    		$expulsar='';
	    		$ascender='';
    		}
    	}
    	//si es miembro no puede expulsar o ascender usuarios
    	else {
    		$expulsar='';
    		$ascender='';
    	}
		//se rellena la tabla			
		/*$tabla.='{
				  "id":"'.$id.'",
				  "user":"'.$row['nameuser'].'",
				   "acciones":"'.$expulsar.$ascender.'"			
				},';	*/
		$tabla.='{
			  "id":"'.$id.'",
			  "user":"nombre usuario",
			   "acciones":"'.$expulsar.$ascender.'"			
		},';	
		//aumenta el contador		
		$id+=1;
	}	
	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);
	//Manda los datos al js
	echo '{"data":['.$tabla.']}';	
?>