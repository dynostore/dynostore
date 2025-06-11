<?php

include_once("../../includes/config.php");
include_once(CLASES . "/Curl.php");
include_once(SESIONES);

Sessions::startSession("muyalpainal");

$url = "http://" . $_ENV['APIGATEWAY_HOST'] . '/auth/organization/' . $_POST['fullname'] . "/" . $_POST['acronym'];

$_POST['fathers_token'] = '/';

$curl = new Curl();
$response = $curl->put($url, $_POST);


if (isset($response['data']['message'])) {
    if (isset($response['data']['tokenhierarchy'])){
        echo json_encode(array("code" => 0, "data" => $response['data']));
    }else{
        echo json_encode(array("code" => 1, "data" => $response['data']));
    }
    
}else{
    echo json_encode(array("code" => 1));
}
