
        <!--vista de la lista de catalogos a los que se puede suscribir-->
        <div class="col-md-8 col-md-offset-2">
            <h1> Suscribir a Catalogos </h1>  
        </div>
        <div class="col-md-8 col-md-offset-2">    
            <table id="Catalogotable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Nombre Catalogo</th>
                        <!--th>Grupo </th-->
                        <th>Suscribir</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>        
        </div>


        <div id="example" class="modal fade" style="display: none;">
            <div class="modal-header">
                <a data-dismiss="modal" class="close">×</a>
                <h3>Cabecera de la ventana</h3>
            </div>
            <div class="modal-body">
                <h4>Texto de la ventana</h4>
                <p>Mas texto en la ventana.</p>                
            </div>
        </div>
    

    <!--Javascript-->    
  <script src="js/jquery.dataTables.min.js"></script>
  <script src="js/dataTables.bootstrap.min.js"></script> 
    <!--llama al archivo que da formato a la tabla-->
    <script src="js/listSubscribeCatalog.js"></script>  
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip(); 
        });
    </script>     

