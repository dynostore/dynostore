<?php
	session_start();
	require_once "../../models/Curl.php";

	

	$url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/groups/'.$_POST['key'].'/delete?access_token='.$_SESSION['access_token'];
	//$data['keyuser'] = $_SESSION['keyuser'];
    $curl = new Curl();
	$response = $curl->delete($url,array());
	
	if ($response['code']==200) {
		echo 'ok';
	}else if(isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else{
		//echo $url;
		//print_r($response);
		echo "Error";
	}
?>