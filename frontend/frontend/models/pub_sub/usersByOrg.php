<?php
	session_start();
	require_once "../../models/Curl.php";

	$url = $_ENV['APIGATEWAY_HOST'].'/auth/v1/view/users/all?access_token='.$_SESSION['access_token'];
    $curl = new Curl();
	$response = $curl->get($url);
	// print_r($response["code"]);
	// print_r($response['data']['data']);
	if ($response['code']==200 && isset($response['data']['data'])) {
		//print_r($response['data']);
		$lista = [];
		foreach ($response['data']['data'] as $key ) {
			if( $key['tokenuser'] != $_SESSION['tokenuser']){
				$lista[] = array('text' => $key['username'],'value' => $key['tokenuser']);
			}
		}
		
		echo json_encode($lista);
		//echo 'ok';
	}else if(isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else{
		echo "Error";
	}
?>