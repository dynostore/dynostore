<?php
    session_start();

	$url = $_ENV['AUTH_HOST'].'/auth/v1/users/fulldata/';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	
	$res=json_decode($response,true);

	$tabla = "";
    foreach ($res as $row) {
      if ($row['keyuser']!=$_SESSION['keyuser']) {
    		//$usuario='<a onclick=\"menu(102)\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Cambiar Nombre\" class=\"btn btn-primary\"><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></a>';
		    //$correo='<a href=\"editEmail.php?keyuser='.$row['keyuser'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Cambiar E-mail\" class=\"btn btn-success\"><i class=\"fa fa-envelope-o\" aria-hidden=\"true\"></i></a>';
			//$contra='<a href=\"editPass.php?keyuser='.$row['keyuser'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Cambiar Contraseña\" class=\"btn btn-warning\"><i class=\"fa fa-key\" aria-hidden=\"true\"></i></a>';
			//href=\"deleteUser.php?keyuser='.$row['keyuser'].'\"
		    $eliminar='<a onclick=\"delete_user(this.id)\" id=\"'.$row['keyuser'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" class=\"btn btn-danger\"><i class=\"fa fa-times\" aria-hidden=\"true\"></i></a>';
    	if($row['isactive']=='T'){
    		$estado='Activo';
		    //$isactive='<a href=\"deactiveUser.php?keyuser='.$row['keyuser'].'\" onclick=\"return confirm(\'¿Seguro que desea deshabilitar a este usuario?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Deshabilitar\" class=\"btn btn-info\"><i class=\"fa fa-ban\" aria-hidden=\"true\"></i></a>';
		    
    	}else{
    		$estado='Inactivo';
		    //$isactive='<a href=\"activeUser.php?keyuser='.$row['keyuser'].'\" onclick=\"return confirm(\'¿Seguro que desea habilitar a este usuario?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Habilitar\" class=\"btn btn-info\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></a>';
		    
		}
		$tabla.='{
				  "nombre":"'.$row['username'].'",
				  "correo":"'.$row['email'].'",
				  "estado":"'.$estado.'",
    			  "acciones":"'.$eliminar.'"			
				},';
		$usuario='';
		$correo='';
		$contra='';
		$eliminar='';
		$isactive='';
	  }
			
	}

	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);

	//Manda los datos al js
	echo '{"data":['.$tabla.']}';	

?>