<?php
include_once("../../includes/config.php");

include_once(SESIONES);
include_once(CLASES . "/Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

if (empty($_SESSION['tokenuser'])) {
    echo json_encode(array("code" => 1, "message" => "Error"));
} else {
    $curl = new Curl();
    $url = $_ENV['APIGATEWAY_HOST'] . '/pub_sub/v1/view/groups/user/' . $_SESSION['tokenuser'] . '/subscribed?access_token=' . $_SESSION['access_token'];
    $response = $curl->get($url);
    $groups  = $response["data"]["data"];
    echo json_encode(array("code" => 0, "message" => "Success", "groups" => $groups));
}
