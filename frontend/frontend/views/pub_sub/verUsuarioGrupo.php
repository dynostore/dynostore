<?php
    //llama al menu
    include "navbar.php"; 
    //llama al archivo de conexion
    include_once "../config/Connection.php";
    //session_start();
    //obtiene el token
    $tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';//$_SESSION["tokenuser"];
    //valida el token para mostrar la vista
    if(!isset($tokenuser) || $tokenuser==null){
        print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
    }
    //Obtener el Id del catalogo mediante el 
    $kr=$_GET['key'];
    //si es grupo o subgrupo
    $gos=$_GET['nap'];
    $status=$_GET['status'];
    //hace la conexion con la BD
    $conn = new Connection();
    $connection = $conn->getConnection();
    //si es un grupo se inserta temporalmente una relacion para guardar el keygroup, status
    if($gos=='-'){
    $dbh = $connection->prepare("INSERT INTO users_groups VALUES('$status',?,?)");
        $dbh->bindParam(1, $kr);
        $dbh->bindParam(2, $gos);
        $dbh->execute();
    }
    //si es un sub-grupo se inserta temporalmente una relacion para guardar el keysubs. status
    else{
        $dbh = $connection->prepare("INSERT INTO users_sub VALUES('$status',?,?)");
        $dbh->bindParam(1, $kr);
        $dbh->bindParam(2, $gos);
        $dbh->execute();
    }
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Usuarios de la Entidad</title>
        <!--CSS-->    
        <link href="../css/bootstrap.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/bootstrap.css">
        <link rel="stylesheet" href="../css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="../css/font-awesome.css">
    </head>
    <body>
        <div class="col-md-8 col-md-offset-2">
            <h1> Usuarios de la Entidad </h1>  
        </div>
        <div class="col-md-8 col-md-offset-2">    
            <table id="usertable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Id </th>
                        <th>Usuario </th>
                        <th>Acciones </th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>        
        </div>
    </body>
    <!--Javascript-->    
    <script src="../js/jquery-1.10.2.js"></script>
    <script src="../js/jquery.dataTables.min.js"></script>
    <script src="../js/dataTables.bootstrap.min.js"></script>          
    <script src="../js/bootstrap.js"></script>
    <!--archivo que da formato a la tabla-->
    <script src="../js/lenguajeusuariogrupo.js"></script>    
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip(); 
        });
    </script>  
</html>