<?php
	include_once("../../includes/config.php");

	include_once(SESIONES);
	include_once(CLASES . "/Curl.php");
	
	//INICIA LA SESIÓN
	Sessions::startSession("muyalpainal");

	if (empty($_SESSION['tokenuser'])) {
		echo json_encode(array("code" => 1, "message" => "Error"));
	} else {
	
		$url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/groups/'.$_POST['key'].'/delete?access_token='.$_SESSION['access_token'];
		//$data['keyuser'] = $_SESSION['keyuser'];
		$curl = new Curl();
		$response = $curl->delete($url,array());
		
		if ($response['code']==200) {
			echo json_encode(array("code" => 0, "message" => "Grupo eliminado"));
		}else if(isset($response['data']['message'])) {
			echo json_encode(array("code" => 1, "message" => $response['data']['message']));
		}else{
			//echo $url;
			//print_r($response);
			echo json_encode(array("code" => 1, "message" => "Error"));
		}
	}
