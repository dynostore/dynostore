<?php
	session_start();
	require_once "../../models/Curl.php";

	

	$url = $_ENV['AUTH_HOST'].'/auth/v1/users/'.$_POST['key'].'/delete';
	//$data['keyuser'] = $_SESSION['keyuser'];
    $curl = new Curl();
	$response = $curl->delete($url,array());
	
	if ($response['code']==200) {
		echo 'ok';
	}else if(isset($response['data']['message'])) {
		echo $response['data']['message'];
	}else{
		echo "Error";
	}
?>