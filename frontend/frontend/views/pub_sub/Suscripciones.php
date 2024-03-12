<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Suscripciones</title>
        <!--CSS-->    
        <link rel="stylesheet" href="../css/bootstrap.css">
        <link rel="stylesheet" href="../css/dataTables.bootstrap.min.css">
        <link rel="stylesheet" href="../css/font-awesome.css">
    </head>
    <?php
        //llama al menu
        include "navbar.php";
    ?>
    <body>
        <!--vista de la lista de los catalogos a los que esta suscrito el usuario-->
        <div class="col-md-8 col-md-offset-2">
            <h1> Suscripciones </h1>  
        </div>
        <div class="col-md-8 col-md-offset-2">    
            <table id="Catalogotable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Nombre de Usuario </th>
                        <th>Nombre Catalogo</th>
                        <th>Grupo</th>
                        <th>Tipo </th>
                        <th>Ficheros </th>
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
    <!--llama al archivo que da formato a la tabla-->
    <script src="../js/lenguajeinscrib.js"></script>  
    <script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip(); 
    });
    </script>     
</html>
