<?php
  //se llama al archivo de conexion
  include_once "../config/Connection.php";
  //se obtiene el token para acceder a la vista
  $tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';//$_SESSION["tokenuser"];
  if(!isset($tokenuser) || $tokenuser==null){
    print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
  }
  //se obtiene el keyuser en base al token
  $keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
?>
<html>
  <head>
    <title>.: SkyCDS :.</title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
  </head>
  <body>
    <?php
      //se manda a llamar al menu principal
      include "navbar.php";
      //muestra el menu de catalogos
    ?>
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h2>Catalogos</h2>
          <div class="list-group">
            <a href="#" class="list-group-item active">
              Opciones
            </a>
            <!--enlaza con la lista de los catalogos propios-->
            <a href="Listcatalogos.php" class="list-group-item">Mis Catalogos</a>
            <!--enlaza con la lista de los catalogos a los que se puede suscribir-->
            <a href="suscribirCatalogo.php" class="list-group-item">Suscribir un Catalogo</a>
            <!--enlaza con la lista de los catalogos a los que se esta suscrito-->
            <a href="Suscripciones.php" class="list-group-item">Suscripciones</a>
            <!--enlaza con la lista de las solicitudes para unirse a los catalogos propios-->
            <a href="Solicitudes.php" class="list-group-item">Solicitudes de Catalogos</a> 
            <!--enlaza con la lista de las solicitudes para unirse a un grupo propio--> 
            <a href="SolicitudesGrupos.php" class="list-group-item">Solicitudes de Entidades</a>   
          </div>
        </div>
      </div>
    </div>
  </body>
</html>