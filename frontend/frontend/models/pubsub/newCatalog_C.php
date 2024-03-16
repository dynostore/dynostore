<?php 
include_once("../../includes/config.php");

include_once(SESIONES);
include_once(CLASES . "/Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

if (empty($_SESSION['tokenuser'])) {
  echo json_encode(array("code" => 1, "message" => "Error"));
}else{
  $_POST['fathers_token'] = '/';
  $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/catalogs/create?access_token='.$_SESSION['access_token'];
  $curl = new Curl();
  $_POST["processed"] = "false";
  $response = $curl->post($url, $_POST);
  //print_r($response);
  if ($response['code']==201) {
    echo json_encode(array("code" => 0, "message" => "Catálogo creado"));
  }else if(isset($response['data']['message'])) {
      echo json_encode(array("code" => 1, "message" => $response['data']['message']));
  }else{
    echo json_encode(array("code" => 1, "message" => "Error"));
  }
}