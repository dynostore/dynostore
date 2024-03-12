<?php
	session_start();
    require_once "../../models/Curl.php";

    // select all my catalogs
    $url = $_ENV['PUB_SUB_HOST'].'/subscription/v1/catalogs/bygroup?keygroup='.$_SESSION['key'];
    $curl = new Curl();
    $response = $curl->get($url);
    
		$tabla = "";
    if ($response['code']==200) {
    	if (isset($response['data']['data'])) {
	    //inicializa las variables a utilizar
		$tipo="";
		$disp="";
		$cifrad="";
			//recorre todos los catalogos y muestra las acciones que se pueden realizar con cada uno
		    foreach ($response['data']['data'] as $row) {
				$files = '<a onclick=\"see_resource(this.id)\" id=\"'.$row['keycatalog'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Ver ficheros\" class=\"btn btn-primary\"><i class=\"fa fa-files-o\" aria-hidden=\"true\"></i></a>';
				//$editar = '<a href=\"../view/editCatalogo.php?key='.$row['keycatalog'].'&na='.$row['namecatalog'].'&ty='.$row['typecatalog'].'&dis='.$row['dispersemode'].'&cifrad='.$row['encryption'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Editar\" class=\"btn btn-primary\"><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></a>';
				//$eliminar = '<a href=\"../models/deleteCatalogo.php?key='.$row['keycatalog'].'\" onclick=\"return confirm(\'¿Seguro que desea eliminar este Catalogo?\')\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Eliminar\" class=\"btn btn-danger\"><i class=\"fa fa-trash\" aria-hidden=\"true\"></i></a>';
				//$anadir='<a href=\"../view/subirArchivo.php?key='.$row['keycatalog'].'\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Subir Fichero\" class=\"btn btn-info\"><i class=\"fa fa-cloud-upload\" aria-hidden=\"true\"></i></a>';
		 		
			 	//como se vera encryption en el listado de catalogos	
		       	if($row['encryption']==true){
					$cifrad="Desactivado";
				}
				else{
					$cifrad="Activado";
				}

				//pasa los parametros al formato de tabla			
				$tabla.='{
						  	"nombre":"'.$row['namecatalog'].'",
						  	"disp":"'.$row['dispersemode'].'",
						  	"cifrado":"'.$cifrad.'",
						   	"acciones":"'.$files.'"			
						},';		
			}
			
		}
	}
	//eliminamos la coma que sobra
			$tabla = substr($tabla,0, strlen($tabla) - 1);
		echo '{"data":['.$tabla.']}';
?>