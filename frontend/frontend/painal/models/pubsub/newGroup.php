<?php
include_once("../../includes/config.php");

include_once(SESIONES);
include_once(CLASES . "/Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

if (empty($_SESSION['tokenuser'])) {
  echo json_encode(array("code" => 1, "message" => "Error"));
}else{
  
  //$data['keyuser'] = $_SESSION['keyuser'];
  $data['groupname'] = $_POST['name'];
  $data['fathers_token'] = "/";
  $isprivate = $_POST['visibilidad'] === 'true' ? 0: 1;
  $data['isprivate'] = $isprivate;
  $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/groups/create?access_token='.$_SESSION['access_token'];
  $curl = new Curl();
  $response = $curl->post($url, $data);

  if ($response['code']==201) {
    echo json_encode(array("code" => 0, "message" => "Grupo creado"));
  }else if(isset($response['data']['message'])) {
    echo json_encode(array("code" => 1, "message" => $response['data']['message']));
  }else{
    echo json_encode(array("code" => 1, "message" => "Error"));
  }
}
?>
