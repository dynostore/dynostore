<?php
    //llama al archivo de conexion
    include_once "../config/Connection.php";
    //se obtiene el token
    $tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';//$_SESSION["tokenuser"];
    //se valida el login para mostrar la vista
    if(!isset($tokenuser) || $tokenuser==null){
        print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
    }
    //se obtiene el keycatalogue
    $key=$_GET['key'];
    //hace la conexion
    $conn = new Connection();
    $connection = $conn->getConnection();
    //inserta temporalmente el keycatalogue en subscribe
    $query = $connection->prepare("INSERT INTO subscribe values ('1','$key','temporal');");
    $query->execute();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Ficheros</title>
        <!--CSS-->    
        <link href="../css/bootstrap.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/bootstrap.css">
        <link rel="stylesheet" href="../css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="../css/font-awesome.css">
    </head>
    <?php
        //llama al menu
        include "navbar.php"; ?>
    <body>
        <div class="col-md-8 col-md-offset-2">
            <h1>Ficheros</h1>  
        </div>
        <div class="col-md-8 col-md-offset-2">    
            <table id="Filetable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Nombre Fichero </th>
                        <th>Usuario</th>
                        <th>Catalogo</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>        
        </div>
    </body> 
    <!--Javascript-->    
    <script src="../js/jquery-3.2.1.min.js"></script>
    <script src="../js/bootstrap.js"></script>
    <script src="../js/jquery.dataTables.min.js"></script>
    <script src="../js/dataTables.bootstrap.min.js"></script>  
    <!--llama al archivo que dara formato a la tabla-->        
    <script src="../js/lenguajefiles.js"></script> 
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>   
</html>
