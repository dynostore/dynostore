<?php
    //se llama el archivo de conexion
    include_once "../config/Connection.php";
    //se obtiene el token para mostrar la vista
    $tokenuser='c860c27b216a9ef1e93e676ced8450aaf737fb97';
    if(!isset($tokenuser) || $tokenuser==null){
        print "<script>alert(\"Acceso invalido!\");window.location='../index.php';</script>";
    }
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
            //se llama al menu
            include "navbar.php"; 
        ?>
        <div class="container">
            <!--los datos ingresados se enviaran al siguiente archivo-->
            <form name="input" action="../controller/catalogoController.php" method="post">
                <div class="row">
                <div class="col-md-4">
                    <h2>Crear Catalogo</h2>     
                    <label class="sr-only" for="catalogo">Nombre del Catalogo</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </div>
                        <input type="text" class="form-control" id= "nombreCa" name="nombreCa" placeholder="Nombre del Catalogo">
                    </div>  
                    <div class="col-xs-12 center text-accent">
                    </div>
                </div>
                <br/><br/>
                Seleccione una Entidad
                <?php
                    //en base al token se obtiene el keyuser
                    $keyuser = '03de8c46bc5681ea540312f8cdc744d3af02f641';//getkeyuser($tokenuser);
                    //se hace la conexion con la BD de postgreSQL
                    $conn = new Connection();
                    $connection=$conn->getConnection();
                    //se obtiene la informacion de los diferentes nombres de grupos
                    $consulta=$connection->prepare("SELECT distinct on (namegroup) namegroup,keygroup FROM groups WHERE keygroup!='85a4e1c128c620a5ba6e18bb27c0c91e55c80148';");
                    //keygroup diferente del keygroup de publico
                    $consulta->execute();
                    $table=$consulta->fetchAll();
                ?>
                <div class="col-xs-100 center text-accent">        
                </div>
                <div>
                    <select data-placeholder="Compartir con..." multiple class="chosen-select" tabindex="8" name="grupos[]" style="width:200px;height:100px">
                        <option selected value="85a4e1c128c620a5ba6e18bb27c0c91e55c80148">Publico</option>
                        <?php
                            //con la informacion obtenida de los grupos se listan para que el usuario pueda elegir con que grupo compartir su nuevo catalogo
                            foreach ($table as $row){
                                echo '<option value="'.$row['keygroup'].'">'.$row['namegroup'].'</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-xs-100 center text-accent">        
                </div>
                <div class="col-xs-12 center text-accent">
                </div>
                <br/><br/>
                <!--se muestran los modos de dispersion-->
                Seleccione Dispersemode:
                <div class="col-xs-100 center text-accent">        
                </div>
                <input type="radio" name="disp" id= "disp" value="1" checked="checked"/> Single<br />
                <input type="radio" name="disp" id= "disp" value="2"/> IDA<br />
                <input type="radio" name="disp" id= "disp" value="3"/> SIDA<br />
                <input type="radio" name="disp" id= "disp" value="4"/> RAID5<br />
                <div class="col-xs-100 center text-accent">        
                </div>
                <!--opcion para elegir si el registro del catalogo estara cifrado o no-->
                Cifrado:
                <br/>
                <input type="radio" name="cifrado" id= "cifrado" value="true"/> Activado<br />
                <input type="radio" name="cifrado" id= "cifrado" value="false" checked="checked"/> Desactivado<br />
                <div class="col-xs-100 center text-accent">        
                </div>
                <div class="col-xs-12 center text-accent">
                </div>
                <div class="col-xs-4 col-xs-offset-2">
                    <div class="spacing-2"></div>
                        <button type="submit" class="btn btn-primary btn-block" name="button" id="guardar"  >Guardar Catalogo</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- Jquery -->
        <script src="../js/jquery.js"></script>
        <!-- Bootstrap js -->
        <script src="../js/bootstrap.min.js"></script>
        <!-- SweetAlert js -->
        <script src="../js/sweetalert.min.js"></script>
        <!-- Js personalizado-->
        <script src="../js/chosen.jquery.js" type="text/javascript"></script>
        <script src="../js/prism.js" type="text/javascript" charset="utf-8"></script>
        <script src="../js/init.js" type="text/javascript" charset="utf-8"></script>
    </body>
</html>