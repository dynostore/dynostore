<?php
	include_once("../../includes/config.php");
	include_once(CLASES . "/Curl.php");
	include_once(SESIONES);

	//INICIA LA SESIÓN
	Sessions::startSession("muyalpainal");

	if (empty($_SESSION['tokenuser'])) {
		echo json_encode(array("code" => 1, "message" => "Error"));
	}else{
		#delete subcatalogs
		$curl = new Curl();
		$father = $_POST['key'];
        $url = $_ENV['APIGATEWAY_HOST'] . '/pub_sub/v1/view/catalogs/user/' . $_SESSION['tokenuser'] . '/results/' . '?access_token=' . $_SESSION['access_token'] . "&father=$father";
        $response = $curl->get($url);
        if ($response["code"] == 200) {
            $catalogs = $response["data"]["data"];

			foreach($catalogs as $cat){
				$url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/catalogs/'.$cat['tokencatalog'].'/delete?access_token='.$_SESSION['access_token'];
				$response = $curl->delete($url,array());
			}
        }

		#delete main catalog

		$url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/catalogs/'.$_POST['key'].'/delete?access_token='.$_SESSION['access_token'];
		
		$response = $curl->delete($url,array());
		
		if ($response['code']==200 || isset($response['data']['message'])) {
			echo json_encode(array("code" => 0, "message" => $response['data']['message']));
		}else{
			//echo $url;
			//print_r($response);
			echo json_encode(array("code" => 1, "message" => "Error"));
		}
	}
?>