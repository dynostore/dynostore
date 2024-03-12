<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Crear Sub-Entidad</title>
    <!-- Importamos los estilos de Bootstrap -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <!-- Font Awesome: para los iconos -->
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <!-- Sweet Alert: alertas JavaScript presentables para el usuario (más bonitas que el alert) -->
    <link rel="stylesheet" href="../css/sweetalert.css">
    <link rel="stylesheet" href="../css/style.css">
  </head>
  <?php
    //llama al menu
    include "navbar.php";
    //llama al archivo de conexion
    include_once "../config/Connection.php";
    //obtiene el keygroup
    $keygroup=$_GET['key'];
    //si hay un POST
    if(isset($_POST['user'])){
      //pasa el valor a la variable
      $nombre=$_POST['user'];
      //llama a la funcion encargada de crear el subgrupo
      grouptree($keygroup,$nombre);
      unset($_POST['user']);
    }
  ?>
  <body>
    <!-- Formulario Login -->
    <div class="container">
      <div class="row">
        <div class="col-xs-12 col-md-4 col-md-offset-4">
          <!-- Margen superior (css personalizado )-->
          <div class="spacing-1">
          </div>
          <form method="POST">
            <legend class="center">Crear Sub-Entidad</legend>
            <div id="resp"></div>
            <!-- Caja de texto para usuario -->
            <label class="sr-only" for="user">Nombre de la Sub-Entidad</label>
            <div class="input-group">
              <div class="input-group-addon"><i class="fa fa-user"></i>
              </div>
              <input type="text" class="form-control" id="user" name="user" placeholder="Ingresa el nombre de la entidad">
            </div>
            <!-- Div espaciador -->
            <button type="submit" class="btn btn-primary btn-block" name="button" id="login">Guardar</button>
          </form>
        </div>
      </div>
    </div>
    <!-- / Final Formulario login -->
    <!-- Jquery -->
    <script src="../js/jquery.js"></script>
    <!-- Bootstrap js -->
    <script src="../js/bootstrap.min.js"></script>
    <!-- SweetAlert js -->
    <script src="../js/sweetalert.min.js"></script>
    <!-- Js personalizado -->
  </body>
</html>
<?php
  //funcion que crea el subgrupo
  function grouptree($keygroup,$name){
    //obtiene datos del usuario
    $tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';// $_SESSION["tokenuser"];
    $keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
    //realiza la conexion
    $conn = new Connection();
    $connection = $conn->getConnection();
    //inserta el nuevo sub grupo
    $consulta=$connection->prepare("INSERT INTO group_subs(keygroup,namesubs,keyuser) VALUES(:k,:ng,:ow);");
    $consulta->bindParam(":k",$keygroup);
    $consulta->bindParam(":ng",$name);
    $consulta->bindParam(":ow",$keyuser);
    $consulta->execute();
    //obtiene el keysubs del recien creado sub grupo
    $consulta=$connection->prepare("SELECT max(keysubs) as key from group_subs;");
    $consulta->execute();
    $keysubs=$consulta->fetchAll();
    $k=0;
    foreach ($keysubs as $key1) {
      $k=$key1['key'];
    }
    //utiliza el keysubs para poder insertar la relacion con el propietario
    $c2=$connection->prepare("INSERT INTO users_sub(keyuser,keysubs,status) VALUES(:ku,:kg,'Propietario');");
    $c2->bindParam(":ku",$keyuser);
    $c2->bindParam(":kg",$k);
    $c2->execute();
    //llama al archivo encargado de enviar notificaciones
    include_once "../models/notificacion.php";
    //funcion que envia la notificacion via email
    enviar_correosubgrupo($keygroup,$keyuser);
    //regresa a la lista de grupos
    header("Location: listaMisGrupos.php");
  }
?>