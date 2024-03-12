<?php
	session_start();
	//verifica el login del usuario al obtener el token
	$tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';
	//si alguien esta logueado entra a la vista principal
	if(isset($tokenuser)){
  		header("Location:view/Menu.php");
	}
	else{
  		//include_once 'view/login.php';
	}
?>
