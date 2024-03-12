<?php
	session_start();
	require_once "../../models/Curl.php";
	
	function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		// Uncomment one of the following alternatives
		// $bytes /= pow(1024, $pow);
		 $bytes /= (1 << (10 * $pow)); 
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	} 

    //$url = $_ENV['PUB_SUB_HOST'].'/subscription/v1/catalogs/'.$_SESSION['key'].'/files';
    $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/view/files/catalog/'.$_SESSION['key'].'?access_token='.$_SESSION['access_token'];
    $curl = new Curl();
	$response = $curl->get($url);
	//var_dump($response);	
	//inicializa las variables
	$tabla = "";
	$editar="";
	$eliminar="";
	if ($response['code']==200 || isset($response['data']['data'])) {
	//recorre el resultado de la consulta
	foreach ($response['data']['data'] as $row) {
		//$editar = '<a href=\"../view/editarArchivo.php?key='.$row['keyfile'].'&na='.$row['url'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Sustituir\" class=\"btn btn-primary\"><i class=\"fa fa-exchange\" aria-hidden=\"true\"></i></a>';
		//$eliminar = '<a href=\"../models/deleteArchivo.php?key='.$row['keyfile'].'&na='.$row['url'].'\" onclick=\"return confirm(\'¿Seguro que desea eliminiar este Catalogo?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" class=\"btn btn-danger\"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a>';
		
		//accion que pueden realizar propietarios y miembros
		$descargar='<a href=\"../models/download.php?dir='.$row['url'].'&name='.$row['namefile'].'\" onclick=\"return confirm(\'¿Seguro que desea descargar este archivo?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Descargar\" class=\"btn btn-success\"><i class=\"fa fa-download\" aria-hidden=\"true\"></i></a>';
		
		$tabla.='{
			  "namefile":"'.$row['namefile'].'",
			  "size":"'.$row['sizefile'].'",
			   "acciones":"'.$descargar.'"			
		},';
		//vacia las variables
		$editar="";
		$eliminar="";			
	}
}
	//eliminamos la coma que sobra
	$tabla = substr($tabla,0, strlen($tabla) - 1);
	//Manda los datos al js
	echo '{"data":['.$tabla.']}';	
?>