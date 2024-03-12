<?php

require_once "models/Catalog.php";

$api = new Catalog();

// print_r($_GET);
if (isset($_GET['type'])) {
	$type = $_GET['type'];
	switch ($type) {
		
		
		case 10: //this
			$api->getAllUserCatalogs();
			break;
		case 11:
			$api->getCatalogsWithAccess();
			break;
		case 12:
			//$api->getSubscribedCatalogs();
			break;
		case 13:
			$api->getCatalogsByGroup();
			break;
		case 14:
			$api->getAvailableCatalogs($_GET['keyuser']);
			break;
		case 15: // this in metadata
			$api->get($api->_request['id']);
			break;
		case 16:
			$api->createCatalog();
			break;
		case 17:
			$api->edit();
			break;
		case 18:
			$api->delete($api->_request['id']);
			break;
		
		case 20: //this 
			$api->addFileCatalog($_GET['keyresource']);
			//$api->addFileCatalog($api->_request['keyresource']);
			break;
		case 21: // this
			$api->getCatalogFiles($_GET['tokencatalog']);
			break;
		case 22: //this too
			$api->getCatalogKeyFiles($_GET['keyresource']);
			break;
		case 23:
			$api->subscribe($_GET['keyresource']);
			break;
		case 25:
			$api->getNotifications($_GET['keyuser']);
			break;
		case 26:
			$api->allowNotification($_GET['key']);
			break;
		case 27:
			$api->denyNotification($_GET['key']);
			break;
		
		case 32:
			$api->getGroupsWithAccess();
			break;
		case 33:
			//$api->getGroup($api->_request['user_id']);
			break;
		case 34:
			$api->getAvailableGroups($_GET['keyuser']);
			break;
		case 35:
			$api->createGroup();
			break;
		case 36:
			$api->editGroup();
			break;
		case 40:
			$api->deleteGroup($api->_request['id']);
			break;
		case 45:
			$api->subscribeGroup($_GET['keygroup']);
			break;
		case 46:
			$api->getNotificationsGroups($_GET['keyuser']);
			break;
		case 47:
			$api->allowNotificationGroup($_GET['key']);
			break;
		case 48:
			$api->denyNotificationGroup($_GET['key']);
			break;

		
		case 55:
			$api->getPublicGroups($_GET['keyuser']);
			break;
		case 60:
			$api->catalogFunction();
			break;
		case 70:
			$api->groupFunction();
			break;
		case 80:
			$api->visualizationFunction();
			break;
		case 980:
			$api->visualizationCatalogsByUser();
			break;
		case 981:
			$api->visualizationCatalogsByUserPub();
			break;
		case 81:
			$api->publicationFunction();
			break;
		case 82:
			$api->subscriptionFunction();
			break;
			
		case 90:
			//$api->viewFilesFunction();
			break;
		case 91:
			//$api->viewCatalogsFunction();
			break;
		case 92:
			//$api->viewUsersFunction();
			break;
		case 93:
			//$api->viewGroupsFunction();
			break;
		case 94:
			//$api->viewPublicationsFunction();
			break;
			
        case 200:
			$api->viewCatalogInfo($_GET['tokencatalog'],$_GET['access_token']);
			break;


		// for dev only
		case 100:
			$api->getAllCatalogs();
			break;
		case 101:
			$api->getAllGroups();
			break;
		case 102:
			$api->getTest();
			break;
		case 999:
			$api->deleteAllC();
			break;
		
		default:
			$api->notFound();
			break;
	}
}else{
	$api->notFound();	
}
