
        <div class="col-md-8 col-md-offset-2">
            <h1>Ficheros </h1>  
        </div>
        <div class="col-md-8 col-md-offset-2">    
            <table id="listatable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Fichero </th>
                        <!--th>Usuario</th-->
                        <th>Tamaño</th>
                        <!--th>Fecha</th-->
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
    <script src="js/listFiles.js"></script>  
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>   

