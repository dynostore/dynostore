    <div class="col-md-12">
      <h1>Catalogos
        <a class="btn btn-primary pull-right menu" onclick="menu(200)">Nuevo Catalogo</a>
      </h1>  
    </div>
    <div class="col-md-12">    
      <table id="Catalogotable" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th>Nombre </th>
            <th>Dispersemode</th>
            <th>Cifrado</th>
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
  <!--llama al archivo que dara formato a la tabla "Catalogotable"-->
  <script src="js/listCatalogs.js"></script>
  <script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });
  </script>   
