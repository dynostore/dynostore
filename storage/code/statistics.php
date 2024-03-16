<?php
/*
* Statistics
* Author: Pablo Morales Ferreira
* Company: Cinvestav-Tamaulipas
*/
require_once 'functions.php';
define('FOLDER', 'c/');
define('FOLDER1', 'abekeys/');

$response = array();
$response['count'] = 'false';
$response['deleted'] = 'false';

if(isset($_GET['count'])){
	if ($_GET['count'] == 'true') {
		$size = 0;
		$count = 0;
		foreach (glob(FOLDER. "{*}", GLOB_BRACE) as $file) {
			$size += filesize($file);
			$count++;
    	}
		$response['size'] = $size;
		$response['count'] = $count;
	} 
}

if(isset($_GET['delete'])){
	if ($_GET['delete'] == 'true') {
		$dir = FOLDER; 
		$handle = opendir($dir); 
		$count = 0;
		$size = 0;
		while ($file = readdir($handle)) {
			if (is_file($dir . $file)) { 
				$size += filesize($dir.$file);
				unlink($dir.$file); 
				$count++;
			}
		}

		$dir1 = FOLDER1; 
		$handle1 = opendir($dir1); 
		while ($file1 = readdir($handle1)) {
			if (is_file($dir1 . $file1)) {
				echo $dir1.$file1;
				unlink($dir1.$file1);
			}
		}

		$response['size'] = $size;
		$response['deleted'] = $count;
	} 
}

echoRespnse($response);
?>