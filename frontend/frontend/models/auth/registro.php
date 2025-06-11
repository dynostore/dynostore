<?php

include_once("../../includes/config.php");
include_once(CLASES . "/Curl.php");
include_once(SESIONES);

$url = $_ENV['APIGATEWAY_HOST'] . '/auth/user';

$curl = new Curl();
$response = $curl->post($url, $_POST);

#print_r($response);
if (isset($response["data"]['message'])) {
    echo json_encode($response["data"]);
} else {
    echo 'Error';
}
