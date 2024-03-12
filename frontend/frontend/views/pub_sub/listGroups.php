<?php
    session_start();
	if(!isset($_SESSION["connected"]) || $_SESSION["connected"] != 1){
        header("Location: /");
        exit;
    }
?>
        <div class="col-md-12">
            <h1>Grupos
                <!--Crear grupo-->
                <a onclick="menu(300)" class="btn btn-primary pull-right menu">Nuevo Grupo</a>
            </h1>  
        </div>
        <div class="col-md-12">    
            <table id="listatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Entidad </th>
                        <!--th>Propietario </th-->
                        <th>Fecha de creacion</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                </tbody> 
            </table>        
        </div>

    <!--Javascript-->    
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>  
    <!--archivo que da formato a la tabla-->        
    <script src="js/listGroups.js"></script>  
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>   
