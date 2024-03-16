<?php

include_once("../../includes/config.php");
include_once(CLASES . "/Curl.php");
include_once(SESIONES);

$url = $_ENV['APIGATEWAY_HOST'] . '/auth/v1/users/create';

$curl = new Curl();
$response = $curl->post($url, $_POST);

#print_r($response["data"]["data"]['message']);

if (isset($response["data"]["data"]['message'])) {
    echo json_encode($response['data']["data"]);
} else {
    echo 'Error';
}
