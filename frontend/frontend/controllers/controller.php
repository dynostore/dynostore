<?php

if (isset($_POST['opt_form'])) {
		switch ($_POST['opt_form']){
			case '10':
				include_once '../views/home_V.php';
				break;
			case '101':
				include_once '../views/auth/listUsers_V.php';
				break;
			case '102':
				include_once '../views/auth/editUsername_V.php';
				break;
			case '103':
				include_once '../models/auth/editEmail_V.php';
				break;
			case '104':
				include_once '../models/auth/editPassword_V.php';
				break;
			case '105':
				include_once '../models/auth/setAdmin_V.php';
				break;
			case '106':
				include_once '../models/auth/unsetAdmin_V.php';
				break;
			case '107':
				include_once '../models/auth/setActive_V.php';
				break;
			case '108':
				include_once '../models/auth/setInactive_V.php';
				break;

			case '200':
				include_once '../views/pub_sub/newCatalog_V.php';
				break;
			case '201':
				//include_once '../views/pub_sub/listCatalogs_V.php';
				include_once '../models/pub_sub/listCatalogs.php';
				break;
			case '202':
				include_once '../models/pub_sub/listFiles.php';
				break;
			case '203':
				include_once '../views/pub_sub/listSubscribeCatalog.php';
				break;
			case '204':
				include_once '../models/pub_sub/Suscripciones.php';
				break;
			case '205':
				include_once '../views/pub_sub/listRequests.php';
				break;
			case '206':
				include_once '../models/pub_sub/SolicitudesGrupos.php';
				break;
			case '210':
				include_once '../views/pub_sub/listPublishWithUsers.php';
				break;
			
			case '300':
				include_once '../views/pub_sub/newGroup.php';
				break;
			case '301':
				include_once '../models/pub_sub/listGroups.php';
				break;
			case '302':
				include_once '../models/pub_sub/listSubscribeGroups.php';
				break;
			case '303':
				include_once '../views/pub_sub/seeGroup.php';
				break;
			case '305':
				include_once '../views/pub_sub/listRequestsGroups.php';
				break;
			case '310':
				include_once '../models/pub_sub/shareGroupWithUsers.php';
				break;

			default:
				echo "Error";
				break;
		}
	}else{
		echo "Error";
		exit;
	}
?>