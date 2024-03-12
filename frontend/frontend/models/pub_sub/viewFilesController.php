<?php
  //llama al archivo de conexion
  include_once "../config/Connection.php";
  //obtiene los datos de login
  $tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';//$_SESSION["tokenuser"];
  $keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
  //realiza la conexion
  $conn = new Connection();
  $connection = $conn->getConnection();
  //se obtiene el keycatalogue que se inserto temporalmente
  $query = $connection->prepare("SELECT keycatalogue from subscribe where status='temporal';");
  $query->execute();
  $table=$query->fetchAll();
  foreach ($table as $row) {
    $key=$row['keycatalogue'];
  }
  //se elimina el registro temporal
  $query = $connection->prepare("DELETE from subscribe where status='temporal';");
  $query->execute();
  //Selecciona todos los ficheros del catalogo seleccionado.
  /*$query = $connection->prepare("SELECT f.keyfile, u.nameuser,r.namecatalogue,p.timedate,sb.status 
  FROM files as f join push as p on f.keyfile=p.keyfile join users as u on u.keyuser=p.keyuser join catalogues as r on r.keycatalogue=p.keycatalogue join subscribe as sb on sb.keycatalogue=p.keycatalogue WHERE r.keycatalogue='$key';");*/
  $query = $connection->prepare("SELECT f.keyfile,r.namecatalogue,p.timedate,sb.status FROM files as f join push as p on f.keyfile=p.keyfile join catalogues as r on r.keycatalogue=p.keycatalogue join subscribe as sb on sb.keycatalogue=p.keycatalogue WHERE r.keycatalogue='$key';");
  $query->execute();
  $num = $query->rowCount();
  $table=$query->fetchAll();
  //se inicializan las variables a utilizar
  $tabla = "";
  $id=1;
  $editar="";
  $eliminar="";
  foreach ($table as $row) {
    //Botones para editar el fichero, eliminarlo o descargarlo (Los dos primeros solo a los propios)
    if($row['status']=='Propietario'){
      $editar = '<a href=\"../view/editarArchivo.php?key='.$row['keyfile'].'&na='.$row['url'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Sustituir\" class=\"btn btn-primary\"><i class=\"fa fa-exchange\" aria-hidden=\"true\"></i></a>';
      $eliminar = '<a href=\"../models/deleteArchivo.php?key='.$row['keyfile'].'&na='.$row['url'].'\" onclick=\"return confirm(\'¿Seguro que desea eliminiar este Catalogo?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" class=\"btn btn-danger\"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a>';
    }
    $descargar='<a href=\"../models/download.php?dir='.$row['url'].'&name='.$row['namefile'].'\" onclick=\"return confirm(\'¿Seguro que desea descargar este archivo?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Descargar\" class=\"btn btn-success\"><i class=\"fa fa-download\" aria-hidden=\"true\"></i></a>';
    //Se preparan los datos para mandarlos al ajax.   
    /*$tabla.='{
          "id":"'.$id.'",
          "archivo":"'.$row['namefile'].'",
          "user":"'.$row['nameuser'].'",
          "fecha":"'.$row['timedate'].'",
           "acciones":"'.$editar.$eliminar.$descargar.'"      
    },';*/
    $tabla.='{
          "id":"'.$id.'",
          "archivo":"Nombre del archivo",
          "user":"Propetario del archivo",
          "fecha":"'.$row['timedate'].'",
          "acciones":"'.$editar.$eliminar.$descargar.'"      
    },';    
    //se vacian las variables  
    $editar="";
    $eliminar="";
    //se aumenta el contador
    $id+=1; 
  }
  //eliminamos la coma que sobra
  $tabla = substr($tabla,0, strlen($tabla) - 1);
  //Manda los datos al js
  echo '{"data":['.$tabla.']}'; 
?>