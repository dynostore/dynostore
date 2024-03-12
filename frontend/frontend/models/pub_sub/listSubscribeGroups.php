<?php
  session_start();
    require_once "../models/Curl.php";
?>    
    <div class="col-md-12">
      <h1>Grupos
        <!--a class="btn btn-primary pull-right menu" onclick="menu(300)">Nuevo Grupo</a-->
      </h1>  
    </div>
    <div class="col-md-12">
      <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Nombre</th>
                        <th>Opciones</th>
                      </tr>
                    </thead>
                    <tbody>
      <?php
      // select all my catalogs
      $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/view/groups/user/'.$_SESSION['tokenuser'].'/published?access_token='.$_SESSION['access_token'];
      $curl = new Curl();
      $response = $curl->get($url);
      //print_r($response);
      //print_r($data);
      if ($response['code']==200 && isset($response['data']['data'])) {
        foreach ($response['data']['data'] as $rows) {
          $subscribe = '';
          if ( isset($rows['status']) && $rows['status'] == 'Pub' ) {
            $subscribe='<a onclick="subscribe_group_to_users(this.id)" id="'.$rows['tokengroup'].'" data-toggle="tooltip" data-placement="top" title="Unirse" class="btn btn-success"><i class="fa fa-plus" aria-hidden="true"></i></a>';
          }
					echo "<tr>";	
						echo "<td>".$rows['namegroup']."</td>";
						echo "<td>".$subscribe."</td>";
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