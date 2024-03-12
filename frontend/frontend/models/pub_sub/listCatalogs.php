<?php
  session_start();
    require_once "../models/Curl.php";
    
?>    
    <div class="col-md-12">
      <h1>Catalogos
        <a class="btn btn-primary pull-right menu" onclick="menu(200)">Nuevo Catalogo</a>
      </h1>  
    </div>
    <div class="col-md-12">
      <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Nombre</th>
                        <th>Dispersemode</th>
                        <th>Cifrado</th>
                        <th>Opciones</th>
                      </tr>
                    </thead>
                    <tbody>
      <?php
     
      // select all my catalogs
      $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/view/catalogs/user/'.$_SESSION['tokenuser'].'/subscribed?access_token='.$_SESSION['access_token'];
      $curl = new Curl();
      $response = $curl->get($url);
      //print_r($response);
      //print_r($_SESSION);
      if ($response['code']==200 && isset($response['data']['data'])) {
        foreach ($response['data']['data'] as $rows) {
          $delete = '';
          if ( isset($rows['status']) && ($rows['status'] == 'Owner')  ) {
            $delete = '<a onclick="delete_catalog(this.id)" id="'.$rows['tokencatalog'].'" data-toggle="tooltip" 
                          data-placement="top" title="Eliminar catálogo" class="btn btn-danger">
                          <i class="fa fa-trash" aria-hidden="true"></i></a>';
          }
          $files = '<a onclick="see_resource(this.id)" id="'.$rows['tokencatalog'].'" data-toggle="tooltip" data-placement="top" title="Ver ficheros" class="btn btn-primary"><i class="fa fa-files-o" aria-hidden="true"></i></a>';
				  $share='<a onclick="share_catalog_with_users(this.id)" id="'.$rows['tokencatalog'].'" 
                      data-toggle="tooltip" data-placement="top" title="Compartir" class="btn btn-success"><i class="fa fa-share-alt" aria-hidden="true"></i></a>';
					echo "<tr>";	
						echo "<td>".$rows['namecatalog']."</td>";
            echo "<td>".$rows['dispersemode']."</td>";
            if($rows['encryption']==true){
					   $cifrad="Activado";
            }else{
              $cifrad="Desactivado";
            }
						echo "<td>".$cifrad."</td>";
						echo "<td>".$files.$share.$delete."</td>";
						//echo "<td> <a onclick='edit(".$rows['id_usuario'].")'>Editar <i class='fa fa-edit'></i></a></td>";
						//echo rolLabel($rows['id_rol']);
            //echo statusLabel($rows['status']);
					echo "</tr>";
        }
      }
			?>
			 </tbody>
      </table>
      </div>
      

     

 <!-- Datatables-->
        <script src="js/datatables/jquery.dataTables.min.js"></script>
        <script src="js/datatables/dataTables.bootstrap.js"></script>
        <script src="js/datatables/dataTables.buttons.min.js"></script>
        <script src="js/datatables/buttons.bootstrap.min.js"></script>
        <script src="js/datatables/jszip.min.js"></script>
        <script src="js/datatables/pdfmake.min.js"></script>
        <script src="js/datatables/vfs_fonts.js"></script>
        <script src="js/datatables/buttons.html5.min.js"></script>
        <script src="js/datatables/buttons.print.min.js"></script>
        <script src="js/datatables/dataTables.fixedHeader.min.js"></script>
        <script src="js/datatables/dataTables.keyTable.min.js"></script>
        <script src="js/datatables/dataTables.responsive.min.js"></script>
        <script src="js/datatables/responsive.bootstrap.min.js"></script>
        <script src="js/datatables/dataTables.scroller.min.js"></script>
        <script>
          var handleDataTableButtons = function() {
              "use strict";
              0 !== $("#datatable-buttons").length && $("#datatable-buttons").DataTable({
                dom: "Bfrtip",
                buttons: [ {
                  extend: "excel",
                  className: "btn-sm"
                }, {
                  extend: "pdf",
                  className: "btn-sm"
                }, {
                  extend: "print",
                  className: "btn-sm"
                }],
                responsive: !0
              })
            },
            TableManageButtons = function() {
              "use strict";
              return {
                init: function() {
                  handleDataTableButtons()
                }
              }
            }();
        </script>
        <script type="text/javascript">
          $(document).ready(function() {
            $('#datatable').dataTable();
            $('#datatable-keytable').DataTable({
              keys: true
            });
            $('#datatable-responsive').DataTable();
            $('#datatable-scroller').DataTable({
              ajax: "js/datatables/json/scroller-demo.json",
              deferRender: true,
              scrollY: 380,
              scrollCollapse: true,
              scroller: true
            });
            var table = $('#datatable-fixed-header').DataTable({
              fixedHeader: true
            });
          });
          TableManageButtons.init();
        </script>