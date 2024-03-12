<?php 
  session_start();
  require_once "../../models/Curl.php";

  $_POST['fathers_token'] = '/';


  $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/catalogs/create?access_token='.$_SESSION['access_token'];
  $curl = new Curl();
  $_POST["processed"] = "false";
  $response = $curl->post($url, $_POST);
 
  if ($response['code']==201) {
    echo 'ok';
  }else if(isset($response['data']['message'])) {
      echo $response['data']['message'];
  }else{
    echo "Error";
  }

?>



