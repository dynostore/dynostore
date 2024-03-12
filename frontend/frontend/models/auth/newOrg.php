<?php
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET");
        header("Access-Control-Allow-Headers:Content-Type");
        header("Access-Control-Allow-Credentials:true");
        session_start();
        require_once "../../models/Curl.php";

        //$_POST = json_decode(file_get_contents('php://input'), true);
        //$_POST = file_get_contents('php://input');

        //$data['acronym'] = $_POST['acronym'];
        //$data['fullname'] = $_POST['fullname'];
        $_POST['fathers_token'] = '/';

        $url = $_ENV['APIGATEWAY_HOST'].'/auth/v1/hierarchy/create';
        $curl = new Curl();
        $response = $curl->post($url, $_POST);

        //print_r($response);
        
        if (isset($response['data']['message'])) {
                echo json_encode($response['data']);
        }else{
                echo 'Error';
        }

?>