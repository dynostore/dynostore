
        <div class="col-md-8 col-md-offset-2">
            <h1> Solicitudes</h1>  
        </div>
        <div class="col-md-8 col-md-offset-2">    
            <table id="Catalogotable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Nombre de Usuario </th>
                        <th>Nombre Grupo</th>
                        <!--th>Tipo </th-->
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
    <!--llama al archivo que dara formato a la tabla-->
    <script src="js/listRequestsGroups.js"></script>  
    <script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip(); 
    });
    </script>     
</html>