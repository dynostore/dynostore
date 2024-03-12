<?php
	session_start();
	require_once "../../models/Curl.php";

	$url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/subscribe/group/user?access_token='.$_SESSION['access_token'];
	$data['tokengroup'] = $_POST['key'];
	$data['tokenuser'] = $_SESSION['tokenuser'];
	$curl = new Curl();
	$response = $curl->post($url,$data);
	//print_r($response);
	if ($response['code']==200 && isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else{
		echo "Error";
	}
?>