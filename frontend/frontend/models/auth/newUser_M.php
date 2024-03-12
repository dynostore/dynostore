<?php
        session_start();
        require_once "../../models/Curl.php";

        //$_POST = json_decode(file_get_contents('php://input'), true);
        //$_POST = file_get_contents('php://input');
        
        //$_POST['tokenorg'] = 'public';

        $url = $_ENV['APIGATEWAY_HOST'].'/auth/v1/users/create';
        
        $curl = new Curl();
        $response = $curl->post($url, $_POST);

        #print_r($response["data"]["data"]['message']);
        
        if (isset($response["data"]["data"]['message'])) {
                echo json_encode($response['data']["data"]);
        }else{
                echo 'Error';
        }
       

?>