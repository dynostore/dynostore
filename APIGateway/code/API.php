<?php

header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET");
header("Access-Control-Allow-Headers:Content-Type");
header("Access-Control-Allow-Credentials:true");

require_once "models/Gateway.php";

$api = new Gateway;

if (isset($_GET['type'])) {
	$type = $_GET['type'];

	switch ($type) {
			//------- DEV -------------------------------------
		case 1:
			$api->authGetAllUsers($_GET['access_token']);
			break;
		case 2:
			$api->authGetAllHierarchy();
			break;
		case 3: //here
			$api->pub_subGetAllCatalogs();
			break;
		case 4:
			$api->pub_subGetAllGroups();
			break;

			//------- authentication -------------------------		
		case 10:
			$api->getUserByTokenuser($_GET['tokenuser']);
			break;
		case 11:
			$api->getUserByAccesstoken($_GET['access_token']);
			break;
		case 13:
			$api->newUser();
			break;
		case 14:
			$api->delUser($_GET['tokenuser'], $_GET['access_token']);
			break;
		case 15:
			$api->activation($_GET['code'], $_GET['tokenuser']);
			break;
		case 16:
			$api->newUserFromGlobal();
			break;
		case 20:
			$api->login();
			break;
		case 22:
			$api->getUsersByOrg($_GET['tokenorg'], $_GET['access_token']);
			break;

		case 23:
			$api->newOrg();
			break;
		case 24:
			$api->delHierarchy($_GET['tokenhierarchy'], $_GET['access_token']);
			break;
		case 27:
			$api->getHierarchyDown($_GET['tokenhierarchy'], $_GET['access_token']);
			break;
		case 28:
			$api->getHierarchyUp($_GET['tokenhierarchy'], $_GET['access_token']);
			break;
		case 29:
			$api->checkIfExistsOrg();
			break;
			//------- publication / subscription -------------
		case 110:
			$api->newCatalog($_GET['access_token']);
			break;
			//aqui va edit catalog
		case 112:
			$api->delCatalog($_GET['tokencatalog'], $_GET['access_token']);
			break;
		case 113:
			$api->newGroup($_GET['access_token']);
			break;
			//fun edit group
		case 115:
			$api->delGroup($_GET['tokengroup'], $_GET['access_token']);
			break;

		case 116:
			$api->publishGroupToUser($_GET['access_token']);
			break;
		
		case 2000:
			$api->publishCatalogToUser($_GET['access_token']);
			break;

		case 128:
			$api->subscribeGroupToUser($_GET['access_token']);
			break;
		case 132:
			$api->getCatalog($_GET['id'], $_GET['access_token']);
			break;
		case 133:
			$api->addFileToCatalog($_GET['keyresource']);
			break;
		case 142:
			$api->pub_subGetGroupsByUser_Sub($_GET['tokenuser'], $_GET['access_token']);
			break;
		case 143:
			$api->pub_subGetGroupsByUser_Pub($_GET['tokenuser'], $_GET['access_token']);
			break;
			#users by catalog
		case 146:
			$api->pub_subGetUsersByGroup_Sub($_GET['tokengroup'], $_GET['access_token']);
			break;
		case 147:
			$api->pub_subGetUsersByGroup_Pub($_GET['tokengroup'], $_GET['access_token']);
			break;

		case 148:
			//echo 148;
			$api->pub_subGetCatalogsByUser_Sub($_GET['tokenuser'], $_GET['access_token']);
			break;
		case 948:
			//148 subs
			//echo 948;
			//$api->pub_subGetCatalogsByUser_Temp($_GET['tokenuser'],$_GET['access_token']);
			break;
		case 149:
			$api->pub_subGetCatalogsByUser_Pub($_GET['tokenuser'], $_GET['access_token']);
			break;
		case 151:
			$api->pub_subGetCatalogsByGroup_Sub($_GET['tokengroup'], $_GET['access_token']);
			break;

		case 152:
			//var_dump($api);
			$api->pub_subGetFilesByCatalog($_GET['tokencatalog'], $_GET['access_token']);
			break;
			#published
		case 154:
			$api->pub_subGetFilesByUser($_GET['tokenuser'], $_GET['access_token']);
			break;
		case 45:
			$api->getPublicCatalogs($_GET['tokenuser']);
			break;
		case 1530:
			$api->getChildCatalogs($_GET['tokencatalog'], $_GET['access_token']);
			break;
		case 1531:
			$api->getCatalogInfo($_GET['tokencatalog'], $_GET['access_token']);
			break;
		case 1000:
			$api->getPuzzleCatalogResults($_GET['tokenuser'], $_GET['access_token'], $_GET['puzzle'], $_GET['father']);
			break;
		default:
			$api->notFound();
			break;
	}
} else {
	$api->notFound();
}
