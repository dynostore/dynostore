<?php
include_once("includes/config.php");

include_once(SESIONES);

//INICIA LA SESIÓN
Sessions::startSession("muyalpainal");

if(empty($_SESSION['tokenuser'])){
  header("Location: login.php");
}else if(isset($_GET["logout"]) && $_GET["logout"]=="true"){
    unset($_SESSION["idUser"]);
    session_unset();
    session_destroy();
    header("Location: index.php");
}

