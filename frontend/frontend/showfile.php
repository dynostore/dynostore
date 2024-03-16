<?php

include_once("includes/config.php");

include_once(SESIONES);
include_once(CLASES . "/Curl.php");

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");


if (empty($_SESSION['tokenuser'])) {
    header("Location: login.php");
}

$curl = new Curl();

$url = 'http://decipher:5000//api/ejecuta';
$keyfile = $_GET['file'];
$tokencatalog = $_GET['catalog'];
$tokenuser = $_SESSION['tokenuser'];
$tokernapp = $_SESSION['apikey'];

$response = $curl->post($url, array("command" => "java -jar Decipher.jar $keyfile $tokencatalog $tokenuser $tokernapp downloads/", "data" => []));


$url = METADATA . '/file.php?tokenuser=' . $_SESSION['tokenuser'] . '&keyfile=' . $_GET['file'];


$responsefile = $curl->get($url);
$datafile = $responsefile["data"]["message"];
$mode = $datafile["disperse"];
$isciphered = $datafile["isciphered"];

#Get the server
/*$url = METADATA . '//pull.php?tokenuser=' . $_SESSION['tokenuser'] . '&keyresource=' . $_GET['catalog'] . "&keyfile=" . $_GET['file'] . '&dispersemode=' . $mode;

#echo $url;

$response = $curl->get($url);
$servers = $response["data"]["message"];
$key = $response["data"]["key"][0]["ruta"];

*/
$finalfilepath = "downloads/" . $datafile['namefile'];

/*if ($mode == "SINGLE") {
    $server = $servers[0]["ruta"];


    //print_r($keypath);

    //echo $server;
    file_put_contents("downloads/" . $_GET['file'] . ".ciph", fopen("http://$server", 'r'));
    file_put_contents($keypath, fopen("http://$key", 'r'));

    $url = 'http://decipher:5000//api/ejecuta';
    //$response = $curl->post($url, array("command" => "java -jar Decipher.jar $keypath $filepaht downloads/ " . $datafile['namefile'] . " " . $datafile['namefile'], "data" => []));
    #print_r($response);
} else {
    #TODO IDA
}*/

function displayJSON($json){
    $keys = array_keys($json);
    foreach($keys as $k){
        if(gettype($json[$k]) == "array"){
            echo "<tr><td><strong>$k</strong></td><td>";
            displayJSON($json[$k]);
        }else{
            echo "<tr><td><strong>$k</strong></td><td>$json[$k]</td></tr>";
        }
        //echo gettype($json[$k]);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<!-- header -->
<?php
$title = "Dashboard";
include_once(VISTAS . "/components/head.php");
?>

<body>

    <!-- Preloader 
<div class="preloader">
    <div class="preloader-icon"></div>
</div>
./ Preloader -->

    <!-- Layout wrapper -->
    <div class="layout-wrapper">
        <?php
        include_once(VISTAS . "/components/header_bar.php");

        ?>

        <!-- Content wrapper -->
        <div class="content-wrapper">
            <?php include_once(VISTAS . "/components/menu.php"); ?>

            <!-- Content body -->
            <div class="content-body">
                <!-- Content -->
                <div class="content">
                    <div class="page-header">
                        <h2>Archivo
                            <?php echo $datafile["namefile"]; ?>
                        </h2>
                        <br>
                        <span>
                            <?php echo $datafile["sizefile"] / 1024; ?> kiB
                        </span>
                        <br>
                        <a href="<?php echo $finalfilepath; ?>" type="button" class="btn btn-info">Descargar</a>
                    </div>

                    <div class="row">
                        <?php
                        //print_r($response);

                        $ext = pathinfo($finalfilepath, PATHINFO_EXTENSION);
                        if ($ext == "png" || $ext == "jpg") {
                            ?>
                            <img src="<?php echo $finalfilepath; ?>" alt="" width="400px" height="300px">
                        <?php
                        } else if ($ext == "json") {
                            $json = file_get_contents($finalfilepath);
                            //print_r($json);
                            $data =  json_decode($json,1);
                            echo "<table  class=\"table\">";
                            displayJSON($data);
                            echo "</table>";
                        } else if ($ext == "wav") {
                            ?>
                            <audio controls>
                                <source src="<?php echo $finalfilepath; ?>" type="audio/wav">
                                Your browser does not support the audio element.
                            </audio>
                            <?php
                        } else if($ext == "txt") {
                            #exec("python3 generateplot.py " . $finalfilepath);
                            $gestor = popen("python3 generateplot.py " . $finalfilepath . " 2>&1", "r");
                            #echo "'$gestor'; " . gettype($gestor) . "\n";
                            $leer = fread($gestor, 2096);
                            #echo $leer;
                            pclose($gestor);
                            ?>
                                <img src="<?php echo $finalfilepath; ?>.png" width="800px" height="600px" alt="">
                            <?php
                        }
                        ?>
                    </div>
                </div>

                <!-- ./ Content -->
            </div>
            <!-- ./ Content body -->


        </div>



    </div>

    <?php
    include_once(VISTAS . "/components/scripts.php");
    ?>
    <!-- Datatable -->
    <script src="<?php echo PROJECT_HOME; ?>/vendors/dataTable/datatables.min.js"></script>

    <!-- Jstree -->
    <script src="<?php echo PROJECT_HOME; ?>/vendors/jstree/jstree.min.js"></script>
    <script src="<?php echo PROJECT_HOME; ?>/vendors/jstree/jstree.min.js"></script>
    <script>

    </script>
</body>

</html>