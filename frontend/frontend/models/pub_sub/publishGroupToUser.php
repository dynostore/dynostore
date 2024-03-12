<?php
	session_start();
	require_once "../../models/Curl.php";

	
	$url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/publish/group/user?access_token='.$_SESSION['access_token'];
	$data['tokengroup'] = $_POST['group'];
	$data['tokenuser'] = $_POST['user'];
    $curl = new Curl();
	$response = $curl->post($url,$data);
	//print_r($response);
	//echo $url;
	if ($response['code']==200 && isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else if(isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else{
		//echo $url;
		//print_r($response);
		echo "Error";
	}
?>