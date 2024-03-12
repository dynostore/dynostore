<?php
session_start();

include_once "views/header.php";
include_once("models/Curl.php");

// check if a session already running
if (!isset($_SESSION["connected"]) && !$_SESSION["connected"] == 1) {
    include_once "views/auth/login_V.php";
} else {
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
    }
?>

    <html>

    <body>
        <?php
        include_once('views/navbar.php');
        ?>
        
        <div class="col-md-12 col-sm-12 col-xs-12 well"></div>
        <div class="col-md-10 col-md-offset-1 col-sm-12 col-xs-12 well" id="page-body">
            <h3>Catalogs</h3>
            <table id="datatable-buttons" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Created at</th>
                        <th>Filesize (KB)</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($catalogs as $rows) {
                        $delete = '';
                        if (isset($rows['status']) && ($rows['status'] == 'Owner')) {
                            $delete = '<a onclick="delete_catalog(this.id)" id="' . $rows['tokencatalog'] . '" data-toggle="tooltip" 
                                data-placement="top" title="Eliminar catálogo" class="btn btn-danger">
                                <i class="fa fa-trash" aria-hidden="true"></i></a>';
                            $share = '<a onclick="share_catalog_with_users(this.id)" id="' . $rows['tokencatalog'] . '" 
                                data-toggle="tooltip" data-placement="top" title="Compartir" class="btn btn-success"><i class="fa fa-share-alt" aria-hidden="true"></i></a>';
                        }
                        $files = '<a href="list_catalogs.php?cat=' . $rows['tokencatalog'] . '" id="' . $rows['tokencatalog'] . '" data-toggle="tooltip" data-placement="top" title="Ver ficheros" class="btn btn-primary"><i class="fa fa-files-o" aria-hidden="true"></i></a>';

                        echo "<tr>";
                        echo "<td>" . $rows['namecatalog'] . "</td>";
                        echo "<td>" . $rows['created_at'] . "</td>";
                        echo "<td></td>";

                        echo "<td>" . $files . $share . $delete . "</td>";
                        //echo "<td> <a onclick='edit(".$rows['id_usuario'].")'>Editar <i class='fa fa-edit'></i></a></td>";
                        //echo rolLabel($rows['id_rol']);
                        //echo statusLabel($rows['status']);
                        echo "</tr>";
                    }

                    foreach ($filesInCat as $rows) {
                        $delete = '';

                        echo "<tr>";
                        echo "<td>" . $rows['namefile'] . "</td>";
                        echo "<td>" . $rows['created_at'] . "</td>";
                        echo "<td>". $rows['sizefile'] / 1024 ."</td>";
                        echo "<td></td>";
                        //echo "<td> <a onclick='edit(".$rows['id_usuario'].")'>Editar <i class='fa fa-edit'></i></a></td>";
                        //echo rolLabel($rows['id_rol']);
                        //echo statusLabel($rows['status']);
                        echo "</tr>";
                    }

                    ?>
                </tbody>
            </table>
        </div>

        <!-- Animacion de load -->
        <!-- PARA FUTURAS MEJORAS -->
        <div class="row" id="load" hidden="hidden">
            <div class="col-xs-4 col-xs-offset-4 col-md-2 col-md-offset-5"></div>
            <div class="col-xs-12 center text-accent"></div>
        </div>
        <!-- Fin load -->
    </body>

    <!-- Js personalizado -->
    <script src="js/functions.js"></script>

    </html>

<?php
}
?>
