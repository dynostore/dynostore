<?php 
include_once("../../includes/config.php");

include_once(SESIONES);
include_once(CLASES . "/Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

if (empty($_SESSION['tokenuser'])) {
    echo json_encode(array("code" => 1, "message" => "Error"));
}else{
    $catalogs = array();
    $curl = new Curl();
    $filesInCat = array();

    if (isset($_GET["cat"])) {
        $father = $_GET["cat"];
        $url = $_ENV['APIGATEWAY_HOST'] . '/pub_sub/v1/view/catalogs/user/' . $_SESSION['tokenuser'] . '/results/' . '?access_token=' . $_SESSION['access_token'] . "&father=$father";
        $response = $curl->get($url);
        if ($response["code"] == 200) {
            $catalogs = $response["data"]["data"];
        }

        $url = $_ENV['APIGATEWAY_HOST']  . '/pub_sub/v1/view/files/catalog/' . $father . '?access_token=' . $_SESSION['access_token'];
        $response = $curl->get($url);

        if ($response["code"] == 200) {
            $filesInCat = $response["data"]["data"];
        }
    } else {
        $url = $_ENV['APIGATEWAY_HOST'] . '/pub_sub/v1/view/catalogs/user/' . $_SESSION['tokenuser'] . '/subscribed?access_token=' . $_SESSION['access_token'];
        $response = $curl->get($url);
        //print_r($response);
        if ($response['code'] == 200 && isset($response['data']['data'])) {
            foreach ($response['data']['data'] as $rows) {
                if ($rows["father"] == "/") {
                    $catalogs[] = $rows;
                }
                //if($rows)
            }
            if (count($catalogs) == 0) {
                $tokens = array();
                foreach ($response['data']['data'] as $rows) {
                    $tokens[] = $rows["tokencatalog"];
                }
                foreach ($response['data']['data'] as $rows) {
                    if (!in_array($rows["father"], $tokens)) {
                        $catalogs[] = $rows;
                    }
                }
            }
        }

        foreach($catalogs as $k => $c){
            $catalogs[$k]["childs"] = get_catalogs($c["tokencatalog"], $_SESSION['access_token'], $_SESSION['tokenuser']);
            //print_r($c["childs"]);
            //echo "<br>";
        }

    }
    

    echo json_encode(array("code" => 0, "message" => "Success", "data" => $catalogs, "files" => $filesInCat));
}

function get_catalogs($father, $accesstoken, $tokenuser){
    $url = $_ENV['APIGATEWAY_HOST'] . '/pub_sub/v1/view/catalogs/user/' . $tokenuser . '/results/' . '?access_token=' . $accesstoken . "&father=$father";
    $curl = new Curl();
    $response = $curl->get($url);
    $catalogs = array();
    if ($response["code"] == 200) {
        $catalogs = $response["data"]["data"];

        foreach ($catalogs as $k => $c) {
            $catalogs[$k]["childs"] = get_catalogs($c["tokencatalog"], $accesstoken, $tokenuser);
        }

    }
    return $catalogs;
}