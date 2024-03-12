<?php  
  ///////editar un catalogo/////////
  //manda a llamar el archivo de conexion
  include_once "../config/Connection.php";
  //obtiene el token para validar el login y mostrar la vista
  $tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';
  //de no haber alguien logueado regresa a la vista principal
  if(!isset($tokenuser) || $tokenuser==null){
    print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
  }
  //se obtienen los valores enviados por GET
  $type=$_GET['ty'];
  $name=$_GET['na'];
  $val=$_GET['key'];
  $disp=$_GET['dis'];
  $cifrado=$_GET['cifrad'];
?>
<html>
  <head>
    <title>.: SkyCDS :.</title>
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/sweetalert.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/chosen.css">
  </head>
  <body>
    <?php
      //manda a llamar al menu
      include "navbar.php";
    ?>
    <div class="col-md-8 col-md-offset-2">
      <!--en caso de que se cancele la edicion regresa a la lista de los catalogos-->
      <a href="Listcatalogos.php" class="btn btn-primary pull-right menu"></i>Cancelar</a>
    </div>
    <div class="container">
      <!--llama al archivo que hace la operacion sobre la BD-->
      <form name="input" action="../controller/editarController.php" method="post">
        <div class="row">
          <div class="col-md-4">
            <h2>Editar Catalogo</h2>     
            <label class="sr-only" for="catalogo">Nombre del Catalogo</label>
            <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-user"></i>
              </div>
              <input type="text" class="form-control" id= "nombreCa" name="nombreCa" value="<?php echo $name;?>" >
            </div>  
            <div class="col-xs-12 center text-accent">
              <input type="hidden" class="form-control" id= "vall" name="vall" value="<?php echo $val;?>"  DISABLE>
            </div>
          </div>
          <br>
          <br>
          <br>
          <br>
          Seleccione una Entidad
          <?php
            //se realiza la conexion con la BD
            $conn = new Connection();
            $connection = $conn->getConnection();
            //se obtienen los nombres y clave principal de todos los grupos
            $consulta=$connection->prepare("SELECT distinct on (namegroup) namegroup,keygroup FROM groups;");
            $consulta->execute();
            $table=$consulta->fetchAll();
            //se obtienen los grupos con los que se comparte el catalogo a editar
            $gruposperte=$connection->prepare("SELECT g.keygroup, g.namegroup FROM groups as g join groups_catalogues as gr on g.keygroup=gr.keygroup WHERE gr.keycatalogue='$val';");
            $gruposperte->execute();
            $tablegrup=$gruposperte->fetchAll();
            $tokeng=array_column($tablegrup, 'keygroup');
          ?>
          <div class="col-xs-100 center text-accent">        
          </div>
          <div> 
            <select data-placeholder="Compartir con..." multiple class="chosen-select" tabindex="8" name="grupos[]" style="width:200px;height:100px">
              <?php
                //recorre el resultado de la consulta de todos los grupos
                foreach ($table as $row){
                  //si la clave del grupo se encuentra en el resultado de la consulta que obtiene los grupos con los que se comparte el catalogo los agrega al select y selecciona
                  if (in_array($row['keygroup'], $tokeng)){
                    echo '<option selected value="'.$row['keygroup'].'">'.$row['namegroup'].'</option>';
                  }
                  //de lo contrario solo lo agrega al select
                  else{
                    echo '<option value="'.$row['keygroup'].'">'.$row['namegroup'].'</option>';
                  }
                }
              ?>  
            </select>
          </div>
          <br/>
          <!--LA INFORMACION DEL CATALOGO SE PUEDE MOSTRAR A TRAVES DE LOS DATOS OBTENIDOS MEDIANTE GET-->
          Seleccione Dispersemode:
          <div class="col-xs-100 center text-accent">        
          </div>
          <input type="radio" name="disp" id= "disp" value="1"  <?php echo ($disp == "1" ? "checked" : "");?> /> Single<br />
          <input type="radio" name="disp" id= "disp" value="2" <?php echo ($disp == "2" ? "checked" : "");?>/> IDA<br />
          <input type="radio" name="disp" id= "disp" value="3" <?php echo ($disp == "3" ? "checked" : "");?>/> SIDA<br />
          <input type="radio" name="disp" id= "disp" value="4" <?php echo ($disp == "4" ? "checked" : "");?>/> RAID5<br />
          <div class="col-xs-100 center text-accent">        
          </div>
          Cifrado:
          <br/>
          <input type="radio" name="cifrad" id= "cifrad" value="true" <?php echo ($cifrado == "t" ? "checked" : "");?>/> Activado<br />
          <input type="radio" name="cifrad" id= "cifrad" value="false" <?php echo ($cifrado == "f" ? "checked" : "");?>/> Desactivado<br />
          <div class="col-xs-100 center text-accent">        
          </div>         
          <div class="col-xs-4 col-xs-offset-2">
            <div class="spacing-2">
            </div>
            <button type="submit" class="btn btn-primary btn-block" name="button" id="guardar"  >Guardar Cambios</button>                    
          </div>
        </div>
      </form>
    </div>
    <!-- Jquery -->
    <script src="../js/jquery.js"></script>
    <!-- Bootstrap js -->
    <script src="../js/bootstrap.min.js"></script>
    <!-- SweetAle../rt js -->
    <script src="../js/sweetalert.min.js"></script>
    <!-- Js personalizado -->
    <script src="../js/chosen.jquery.js" type="text/javascript"></script>
    <script src="../js/prism.js" type="text/javascript" charset="utf-8"></script>
    <script src="../js/init.js" type="text/javascript" charset="utf-8"></script>
  </body>
</html>