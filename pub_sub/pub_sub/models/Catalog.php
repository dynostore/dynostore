<?php
require_once "Rest.php";
require_once "db/DbHandler.php";
require_once "Curl.php";


class Catalog extends REST
{

    public function manageCatalog(){
        $resMet = $this->getRequestMethod();
        if ($resMet == "PUT") {
            $this->createCatalog();
        } else {
            $this->methodNotAllowed();
        }   
    }

    public function createCatalog(){
        if (
            isset($_GET['catalog']) && isset($this->_request['dispersemode']) &&
            isset($this->_request['encryption']) && isset($this->_request['fathers_token'])
        ) {
            $tokenuser = $_GET['tokenuser'];
            $catalogname = $_GET['catalog'];
            $catalogname = str_replace(" ", "_", $catalogname);
            $fatherstoken = $this->_request['fathers_token'];
            $dispersemode = $this->_request['dispersemode'];
            $encryption = $this->_request['encryption'];
            $processed = $this->_request['processed'];
            $group  = $this->_request['group'];
            $db = new DbHandler();

            #check if catalog exists otherwise create it, this throw a 302 when catalog exists
            $this->validateCatalogExist($catalogname, $tokenuser);

            $keycatalog = $this->generateToken();
            $tokenC = $this->generateSHA256Token();
            //print_r($response);
            if ($fatherstoken == '/') {
                $data = $db->newCatalog(
                    $keycatalog,
                    $tokenC,
                    $catalogname,
                    $tokenuser,
                    $dispersemode,
                    $encryption,
                    $fatherstoken,
                    $group,
                    $processed
                );

                if ($data) {
                    $status = 'Owner';
                    $db->insertUsers_Catalogs($tokenuser, $tokenC, $status);
                    //$msg['message'] = "Created: ".$catalogname;                
                    $msg['message'] = "Created";
                    $msg['data'] = array("tokencatalog" => $tokenC);
                    $this->response($this->json($msg), 201);
                } else {
                    $msg = array("message" => "Cannot insert catalog into user table.");
                    $this->response($this->json($msg), 404);
                }
            } else {
                $father = $this->tokenFatherCExist($fatherstoken);
                $data = $db->newCatalog(
                    $keycatalog,
                    $tokenC,
                    $catalogname,
                    $tokenuser,
                    $dispersemode,
                    $encryption,
                    $fatherstoken,
                    $group,
                    $processed
                );
                if ($data) {
                    $status = "Owner";
                    $db->insertUsers_Catalogs($tokenuser, $tokenC, $status);
                    $msg['message'] = "Created: " . $catalogname;
                    $msg['data'] = array("tokencatalog" => $tokenC);
                    $this->response($this->json($msg), 201);
                } else {
                    $msg = array("message" => "Something went wrong.2");
                    $this->response($this->json($msg), 404);
                }
            }
        } else {
            $msg['message'] = "Invalid data.";
            $this->response($this->json($msg), 400);
        }
    }

    private function generateToken()
    {
        //return hash('sha256',join('',array(time(),rand())));
        return sha1(join('', array(time(), rand())));
    }

    private function generateSHA256Token()
    {
        return hash('sha256', join('', array(time(), rand())), false);
    }

    public function methodNotAllowed()
    {
        $msg = array("Error" => "Method Not Allowed.");
        $this->response($this->json($msg), 405);
    }


    public function notFound()
    {
        $msg = array("Error" => "Not Found.");
        $this->response($this->json($msg), 404);
    }

    public function tokenFatherCExist($father)
    {
        $db = new DbHandler();
        $data = $db->tokenFatherCExist($father);
        if (!$data) {
            $msg['message'] = "Invalid data.";
            $this->response($this->json($msg), 400);
        } else {
            return $data;
        }
    }

    public function validateCatalogExist($name, $tokenuser)
    {
        $db = new DbHandler();
        $data = $db->validateCatalogExist($name, $tokenuser);
        if ($data) {
            $msg['message'] = "Cant use this catalog name.";
            $msg['data'] = $data;
            $this->response($this->json($msg), 302);
        }
    }
}