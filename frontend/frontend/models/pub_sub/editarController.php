<?php  
  ///////Editar un catalogo/////////
  //llama al archivo de conexion
  include_once "../config/Connection.php";
  //obtiene los datos enviados por POST
  $nombreCa = $_POST['nombreCa'];
  $val=$_POST['vall'];
  $disp=$_POST['disp'];
  $cifrad=$_POST['cifrad'];
  $grupo=$_POST['grupos'];
  $valor='Privado';
  //si entre los grupos seleccionados se encuentra publico el tipo de catalogo se vuelve publico
  foreach($grupo as $valor){
    if ($valor=='85a4e1c128c620a5ba6e18bb27c0c91e55c80148'){
      $valor='Publico';
    }
  }
  //----------------------------Actualizar datos de un catalago---------------------------------///
  //se hace la conexion con la BD
  $conn = new Connection();
  $connection = $conn->getConnection();
  //se realiza la actualizacion del catalogo
  $query = $connection->prepare("UPDATE catalogues set typecatalogue='$valor', namecatalogue='$nombreCa', dispersemode='$disp', encryption='$cifrad' where keycatalogue='$val';");
  $query->execute();
  //se recorren los grupos seleccionados para compartir el catalogo, para ello primero se eliminan las relaciones anteriores
  $eliminar=$connection->prepare("DELETE FROM groups_catalogues WHERE keycatalogue='$val'");
  $eliminar->execute();
  foreach ($grupo as $key) {
    //se insertan las nuevas relaciones
    $agregar=$connection->prepare("INSERT INTO groups_catalogues(keygroup,keycatalogue) VALUES (:tk,:tr)");
    $agregar->bindParam(":tk",$key);
    $agregar->bindParam(":tr",$val);
    $agregar->execute();
  }
  //cuando acaba la actualizacion del catalogo regresa a la lista de catalogos
  print "<script>alert(\"Catalogo editado!\");window.location='../view/Listcatalogos.php';</script>";
?>