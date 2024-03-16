<?php

#include_once("../resources/conf.php");

/**
* CLASE PARA EL MANEJO DE SESIONES
*/
class Sessions{

  private $con;

  /**
  * CONSTRUCTOR
  */
  public function __construct(){
  }

  /**
  * INICIA LA SESIÓN DE LA PAGINA
  */
  public static function startSession($session_name){
    session_name($session_name);
    session_start();
    header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    $now = time();
    if (isset($_SESSION['discard_after']) && $now > $_SESSION['discard_after']) {
      // this session has worn out its welcome; kill it and start a brand new one
      session_unset();
      session_destroy();
      session_start();
    }

    // either new or old, it should live at most for another hour
    $_SESSION['discard_after'] = $now + 3600;
  }



  /**
  * COMPRUEBA SI LOS DATOS INGRESADOS POR UN USUARIO SON CORRECTOS
  */
  public function login($username,$password){
    try{
      $sql = "SELECT password,keyuser,nombre_usuario,email from usuarios where email = '$username' or nombre_usuario = '$username'";

      $data = $this->con->executeQuery($sql);
  		if(password_verify($password,$data[0]['password'])){
        return array('codigo' => 0, 'id' => $data[0]['keyuser'],'email' => $data[0]['email'], 'username' => $data[0]['nombre_usuario'],'mensaje' => "Datos correctos.");
      }else{
        return array('mensaje' => "Datos incorrectos.", 'codigo' => 1 );
      }
  	} catch(PDOException $e) {
      echo  $e->getMessage();
    }
  }

  /**
  * REGISTRA UN NUEVO USUARIO
  */
  public function signup($email,$username,$password){
    $password = password_hash($password, PASSWORD_BCRYPT);
    try{
      $msjuser = $this->checkusername($username);
      if($msjuser['codigo'] == 1){
        return $msjuser;
      }

      $msjemail = $this->checkemail($email);
      if($msjemail['codigo'] == 1){
        return $msjemail;
      }

      $sql = "INSERT INTO usuarios(password,email,nombre_usuario) VALUES('$password','$email','$username') returning keyuser";
      $data = $this->con->executeQuery($sql);
  		return array('mensaje' => "Usuario registrado con éxito.", 'codigo' => 0,'id' => $data[0]['keyuser']);
  	} catch(PDOException $e) {
      echo  $e->getMessage();
    }
  }
}
?>