<?php 
  session_start();
  require_once "../../models/Curl.php";

  $data['keyuser'] = $_SESSION['keyuser'];
  $data['username'] = $_POST['username'];
  print_r($data);

  $url = $_ENV['AUTH_HOST'].'/auth/v1/users/'.$_SESSION['keyuser'].'/edit/username';
  echo $url;
  $curl = new Curl();
  $response = $curl->put($url, $data);
  var_dump($respose);
  if ($response['code']==200) {
    echo 'ok';
  }else if(isset($response['data']['message'])) {
      echo $response['data']['message'];
  }else{
    echo "Error";
  }

?>



