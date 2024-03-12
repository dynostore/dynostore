<?php
	session_start();
    require_once "../../models/Curl.php";

    // select all catalogs
    $url = $_ENV['PUB_SUB_HOST'].'/subscription/v1/catalogs/available/?keyuser='.$_SESSION['keyuser'];
    $curl = new Curl();
	$response = $curl->get($url);
	
   
	$tabla = "";
	if ($response['code']==200 && isset($response['data']['data'])) {
			$tipo="";
			//se recorre el resultado para poder colocarlos en la tabla
			// href=\"../models/suscribUsuario.php?key='.$row['keycatalogue'].'\"

			//onclick=\"return confirm(\'Tendrá que esperar a ser aceptado por el propietario \')\"
			foreach ($response['data']['data'] as $row) {
				//asigna la accion que tendra el catalogo con su respectivo valor
				$suscribir = '<a onclick=\"send_subscribe(this.id)\" id=\"'.$row['keycatalog'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Suscribir\" class=\"btn btn-primary\"><bt class=\"glyphicon glyphicon-log-in\" aria-hidden=\"true\"></i></a>';
				$tabla.='{
						"nombre":"'.$row['namecatalog'].'",
						"acciones":"'.$suscribir.'"
						},';		
			}
	}	
	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);
	echo '{"data":['.$tabla.']}';	
?>