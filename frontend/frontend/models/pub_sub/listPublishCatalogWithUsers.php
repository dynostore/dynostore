<?php
	session_start();
    require_once "../../models/Curl.php";
	
    // select all catalogs
    $url = $_ENV['AUTH_HOST'].'/auth/v1/users/byorg/'.$_SESSION['tokenorg'];
    $curl = new Curl();
	$response = $curl->get($url);
    
	if ($response['code']==200 && isset($response['data'])) {
		//foreach ($response['data'] as $key) {}
		print_r($response['data']);
    }
	/*$tabla = "";
	if ($response['code']==200 && isset($response['data'])) {
			//se recorre el resultado para poder colocarlos en la tabla
			// href=\"../models/suscribUsuario.php?key='.$row['keycatalogue'].'\"

			//onclick=\"return confirm(\'Tendrá que esperar a ser aceptado por el propietario \')\"
			foreach ($response['data'] as $row) {

                
				$suscribir = '<a onclick=\"subscribe_group(this.id)\" id=\"'.$row['keygroup'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Suscribir\" class=\"btn btn-primary\"><bt class=\"glyphicon glyphicon-log-in\" aria-hidden=\"true\"></i></a>';
				$tabla.='{
						"grupo":"'.$row['namegroup'].'",
						"propietario":"'.$row['keyuser'].'",
					  	"fecha":"'.date("Y-m-d H:i:s",strtotime($row['created_at'])).'",
						"acciones":"'.$suscribir.'"
						},';		
			}
	}	
	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);
	echo '{"data":['.$tabla.']}';*/
?>