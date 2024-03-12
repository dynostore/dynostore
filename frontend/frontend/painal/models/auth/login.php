<?php

include_once("../../includes/config.php");
include_once(CLASES . "/Curl.php");
include_once(SESIONES);

Sessions::startSession("muyalpainal");


//$_POST = json_decode(file_get_contents('php://input'), true);
//$_POST = file_get_contents('php://input');

$url = $_ENV['APIGATEWAY_HOST'] . '/auth/v1/users/login';


$curl = new Curl();
$response = $curl->post($url, $_POST);
//print_r($response);
/*$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
$response = curl_exec($ch);
curl_close($ch);*/

if ($response['code'] == 200) {
    $_SESSION['connected'] = 1;
    foreach ($response['data']['data'] as $key => $value) {
        $_SESSION[$key] = $value;
    }
    /*$_SESSION['keyuser'] = $response['data']['keyuser'];
        $_SESSION['username'] = $response['data']['username'];
        $_SESSION['tokenuser'] = $response['data']['tokenuser'];
        $_SESSION['apikey'] = $response['data']['apikey'];
        //$_SESSION['email'] = $response['data']['email'];
        $_SESSION['tokenorg'] = $response['data']['tokenorg'];
        $_SESSION['access_token'] = $response['data']['access_token'];
        //$_SESSION['fecha'] = $response['data']['fecha'];
        $_SESSION['ip'] = $ip;
        */
    //print_r($_SESSION);
    echo 'ok';
} elseif (isset($response['data']['message'])) {
    echo $response['data']['message'];
} else {
    echo 'Error';
}
