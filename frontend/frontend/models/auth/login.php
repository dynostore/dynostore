<?php

include_once("../../includes/config.php");
include_once(CLASES . "/Curl.php");
include_once(SESIONES);

Sessions::startSession("muyalpainal");

$url = $_ENV['APIGATEWAY_HOST'] . '/auth/user/login';

$curl = new Curl();
$response = $curl->post($url, $_POST);

if ($response['code'] == 200) {
    $_SESSION['connected'] = 1;
    foreach ($response['data']['data'] as $key => $value) {
        $_SESSION[$key] = $value;
    }
    echo 'ok';
} elseif (isset($response['data']['message'])) {
    echo $response['data']['message'];
} else {
    echo 'Error';
}
