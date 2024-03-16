<?php
/**
 * Validar que los datos no esten vacios
 */

require_once "Curl.php";
require_once "Rest.php";

function upload($tokenuser, $file)
{
        $file_data = file_get_contents("php://input");
        // Save the file
        $bytes_written = file_put_contents($file, $file_data);

        // Check if fwrite failed
        if ($bytes_written === false) {
                $response['status'] = 200;
                $response['message'] = 'Error uploading file';
        } else {
                $response['status'] = 200;
                $response['message'] = 'ok';
        }

        return $response;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);



if (isset($_GET['tokenuser']) && isset($_GET['file'])) {
        $token_user = $_GET['tokenuser'];
        $url = getenv('GATEWAY') . '/auth/v1/users?tokenuser=' . $token_user;
        $curl = new Curl();
        $response = $curl->get($url);
        if ($response['code'] == 200) {
                $response = upload($_GET['tokenuser'], $_GET['file']);
        } else {
                $response['status'] = 401;
                $response['message'] = 'Unauthorized';
        }
} else {
        $response['status'] = 400;
        $response['message'] = 'Please fill all the required fields';
}

$rest = new Rest();
$rest->response($rest->json($response), $response['status']);