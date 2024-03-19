<?php

require_once "models/Catalog.php";

$api = new Catalog();

// print_r($_GET);
if (isset($_GET['type'])) {
	$type = $_GET['type'];
	switch ($type) {
		case 1:
			$api->manageCatalog();
			break;
		case 2:
			$api->insertFileInCatalog($_GET['catalog'], $_GET['keyobject']);
			break;
		default:
			$api->notFound();
			break;
	}
}else{
	$api->notFound();	
}
