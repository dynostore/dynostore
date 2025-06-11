<?php

/*
    CONFIGURACIONES DEL PROYECTO
*/
$config = array(
    "db" => array(
        "db1" => array(
            "dbname" => "aem",
            "username" => "aem_searcher",
            "password" => 'K$df&bbsd98auja7f8ytycdd59',
            "host" => "localhost",
            "port" => "5432"
        )
    ),
    "urls" => array(
        "baseUrl" => $_SERVER["DOCUMENT_ROOT"] . "" 
    ),
    "paths" => array(
        "resources" => $_SERVER["DOCUMENT_ROOT"] . "/resources"
    )
);

define("PROJECT_HOME", "./");


#Microservices
define('AUTH_HOST', 'auth');
define('METADATA', 'metadata');
define('APIGATEWAY_HOST', 'localhost:8095');
/*
    Creating constants for heavily used paths makes things a lot easier.
    ex. require_once(LIBRARY_PATH . "Paginator.php")
*/
defined("PROJECT_ROOT")
    or define("PROJECT_ROOT",  '');
defined("LIBRARY")
    or define("LIBRARY", realpath(dirname(__FILE__) . '/library'));
defined("CLASES")
    or define("CLASES", realpath(dirname(__FILE__) . '/clases'));
defined("ARCHIVOS")
    or define("ARCHIVOS", realpath(dirname(__FILE__) . '/src'));
defined("SESIONES")
    or define("SESIONES", realpath(dirname(__FILE__) . '/clases/class.Sessions.php'));
defined("TEMPLATES")
    or define("TEMPLATES", realpath(dirname(__FILE__) . '/templates'));
defined("VISTAS")
    or define("VISTAS", realpath(dirname(__FILE__) . '/../views'));
defined("CONTROLADORES")
    or define("CONTROLADORES", realpath(dirname(__FILE__) . '/controllers'));
defined("MODELOS")
    or define("MODELOS", realpath(dirname(__FILE__) . '/../models'));
    defined("PAGES")
    or define("PAGES", realpath(dirname(__FILE__) . '/../pages'));

/*
    Error reporting.
*/
ini_set("error_reporting", "true");
error_reporting(E_ALL|E_STRCT);
?>