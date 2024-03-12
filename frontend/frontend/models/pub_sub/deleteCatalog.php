<?php
	session_start();
	require_once "../../models/Curl.php";

	

	$url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/catalogs/'.$_POST['key'].'/delete?access_token='.$_SESSION['access_token'];
    $curl = new Curl();
	$response = $curl->delete($url,array());
	
	if ($response['code']==200 || isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else{
		//echo $url;
		//print_r($response);
		echo "Error";
	}
?>