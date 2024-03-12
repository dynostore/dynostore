<?php
	session_start();
	require_once "../../models/Curl.php";

	$url = $_ENV['PUB_SUB_HOST'].'/subscription/v1/catalogs/notifications?keyuser='.$_SESSION['keyuser'];
	
    $curl = new Curl();
	$response = $curl->get($url);
	//var_dump($response);
    //inicializa la variable a utilizar
	$tabla = "";
	if ($response['code']==200 && isset($response['data'])) {
	//recorre el resultado para acomodarlos en la tabla
	foreach ($response['data'] as $row) {
	   	// href=\"../models/aceptarSolicitud.php?keyre='.$row['keycatalogue'].'&keyus='.$row['usr'].'\"
		$aceptar = '<a onclick=\"allow_notification(this.id)\" id=\"'.$row['id'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Aceptar\" class=\"btn btn-primary\"><i class=\"glyphicon glyphicon-ok\" aria-hidden=\"falce\"></i></a>';
		// href=\"../models/deleteSolitud.php?keyre='.$row['keycatalogue'].'&keyus='.$row['usr'].'\"
		$eliminar = '<a  onclick=\"deny_notification(this.id)\" id=\"'.$row['id'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Rechazar\" class=\"btn btn-danger\"><i class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></i></a>';
	 //////////////////////	
		/*$tabla.='{
			      "user":"'.$row['nameuser'].'"	,
				  "nombre":"'.$row['namecatalogue'].'",
				  "type":"'.$row['typecatalogue'].'",
				   "acciones":"'.$aceptar.$eliminar.'"
				  		
				},';*/
		$tabla.='{
	        "user":"Usuario",
		    "nombre":"'.$row['namecatalog'].'",
		    "acciones":"'.$aceptar.$eliminar.'"
				  		
		},';		
	}
}
	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);
	echo '{"data":['.$tabla.']}';	
?>