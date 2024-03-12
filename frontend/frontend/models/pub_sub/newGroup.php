<?php
  session_start();
  require_once "../../models/Curl.php";

  //$data['keyuser'] = $_SESSION['keyuser'];
  $data['groupname'] = $_POST['namegroup'];
  $data['fathers_token'] = "/";
  $isprivate = $_POST['ispublic'] === 'true' ? 0: 1;
  $data['isprivate'] = $isprivate;
  $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/groups/create?access_token='.$_SESSION['access_token'];
  $curl = new Curl();
  $response = $curl->post($url, $data);
  if ($response['code']==201) {
    echo 'ok';
  }else if(isset($response['data']['message'])) {
      echo $response['data']['message'];
  }else{
    echo "Error";
  }
?>
