<?php
	session_start();
	require_once "../../models/Curl.php";

	

	$url = $_ENV['PUB_SUB_HOST'].'/subscription/v1/groups/'.$_POST['key'].'/subscribe';
	$data['keyuser'] = $_SESSION['keyuser'];
    $curl = new Curl();
	$response = $curl->post($url,$data);
	
	if ($response['code']==201) {
		echo 'ok';
	}else if(isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else{
		echo "Error";
	}
?>