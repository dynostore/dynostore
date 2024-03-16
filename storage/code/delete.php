<?php
require_once "Curl.php";
require_once "Rest.php";

/*
* Validar que los datos no esten vacios
*/

$data = array();

if (isset($_GET['tokenuser']) && isset($_GET['file'])) {
    $token_user = $_GET['tokenuser'];
    $url = getenv('GATEWAY') . '/auth/v1/users?tokenuser=' . $token_user;
    $curl = new Curl();
    $response = $curl->get($url);
    if ($response['code'] == 200) {
        #delete the file on the server
        $file = "c/" .  $_GET['file'];
        if (file_exists($file)) {
            unlink($file);
            $data['status'] = 200;
            $data['message'] = 'ok';
        } else {
            $data['status'] = 401;
            $data['message'] = "$file not found";
        }
    } else {
        $data['status'] = 401;
        $data['message'] = 'Unauthorized';
    }
} else {
    $data['status'] = 400;
    $data['message'] = 'Bad credentials';
}

$rest = new Rest();
$rest->response($rest->json($data), $data['status']);