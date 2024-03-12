<?php
  session_start();
    require_once "../models/Curl.php";

    function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		// Uncomment one of the following alternatives
		// $bytes /= pow(1024, $pow);
		 $bytes /= (1 << (10 * $pow)); 
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	} 
?>    
    <div class="col-md-12">
      <h1>Ficheros</h1>  
    </div>
    <div class="col-md-12">
      <table id="datatable-buttons" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Fichero</th>
                        <th>Tamaño</th>
                        <!--th>Chunks</th-->
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody>
      <?php
      
      
      $url = $_ENV['APIGATEWAY_HOST'].'/pub_sub/v1/view/files/catalog/'.$_SESSION['key'].'?access_token='.$_SESSION['access_token'];
      $curl = new Curl();
      $response = $curl->get($url);
      //$response['url'] = $url;
      //print_r($response);
      //print_r($data);
      if ($response['code']==200 && isset($response['data']['data'])) {
        foreach ($response['data']['data'] as $rows) {
          $delete = '<a onclick="delete(this.id)" id="'.$rows['tokengroup'].'" data-toggle="tooltip" data-placement="top" title="Eliminar" class="btn btn-danger"><i class="fa fa-trash" aria-hidden="true"></i></a>';
          $share='<a onclick="share_with_users(this.id)" id="'.$rows['tokengroup'].'" data-toggle="tooltip" data-placement="top" title="Compartir" class="btn btn-success"><i class="fa fa-share-alt" aria-hidden="true"></i></a>';
          $descargar='<a href=\"../models/download.php?dir='.$row['url'].'&name='.$row['namefile'].'\" onclick=\"return confirm(\'¿Seguro que desea descargar este archivo?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Descargar\" class=\"btn btn-success\"><i class=\"fa fa-download\" aria-hidden=\"true\"></i></a>';
					echo "<tr>";	
						echo "<td>".$rows['namefile']."</td>";
						echo "<td>".formatBytes($rows['sizefile'])."</td>";
						echo "<td>".$delete."</td>";
						//echo "<td>".$descargar.$share.$delete."</td>";
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