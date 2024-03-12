<?php
	session_start();
	require_once "../../models/Curl.php";

    if (isset($_POST['key']) && isset($_POST['status'])) {
		
		$curl = new Curl();
		switch ($_POST['status']) {
			case 2:
				$url = $_ENV['PUB_SUB_HOST'].'/subscription/v1/groups/notifications/'.$_POST['key'].'/allow';
				$response = $curl->put($url,array());
				
				if ($response['code']==200) {
					echo 'ok';
				}else if(isset($response['data']['message'])) {
					echo $response['data']['message'];
				}else{
					echo "Error";
				}
			break;
			case 3:
				$url = $_ENV['PUB_SUB_HOST'].'/subscription/v1/groups/notifications/'.$_POST['key'].'/deny';
				$response = $curl->delete($url,array());
								
				if ($response['code']==200) {
					echo 'ok';
				}else if(isset($response['data']['message'])) {
					echo $response['data']['message'];
				}else{
					echo "Error";
				}
			break;
			default:
				echo "Error";
			break;
		}
		
	}
?>