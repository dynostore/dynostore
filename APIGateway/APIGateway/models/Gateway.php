<?php

require_once "Rest.php";
require_once "Curl.php";

define('URL_AUTH', $_ENV['AUTH_HOST']);
define('URL_PUB_SUB', $_ENV['PUB_SUB_HOST']);

class Gateway extends REST
{

    public function notFound()
    {
        $msg['message'] = 'Not Found.';
        //$msg['No'] = $no;
        $this->response($this->json($msg), 404);
    }

    //------------------------------------------------
    //------------------ authentication --------------------
    //------------------------------------------------


    public function getUserByTokenuser($id)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/user?tokenuser=' . $id;
        $curl = new Curl();
        $response = $curl->get($url);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getUserByAccesstoken($id)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/user?access_token=' . $id;
        $curl = new Curl();
        $response = $curl->get($url);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    // fun activate account

    public function newUser()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        //$url = URL_AUTH . '/auth/v1/users';
        $url = URL_AUTH . '/auth/v1/users/create';
        $data['option'] = 'NEW';
        $data['username'] = $this->_request['username'];
        $data['email'] =  $this->_request['email'];
        $data['password'] =  $this->_request['password'];
        $data['tokenorg'] =  $this->_request['tokenorg'];
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 201:
                    $this->response($this->json($response), $response['code']);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $msg['resp'] = $response;
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function newUserFromGlobal()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        //$url = URL_AUTH . '/auth/v1/users';
        $url = URL_AUTH . '/auth/v1/users/createfromglobal';
        $data['option'] = 'NEW';
        $data['tokenuser'] = $this->_request['tokenuser'];
        $data['username'] = $this->_request['username'];
        $data['email'] =  $this->_request['email'];
        $data['password'] =  $this->_request['password'];
        $data['tokenorg'] =  $this->_request['tokenorg'];
        $data['keyuser'] = $this->_request['keyuser'];
        $data['passHash'] = $this->_request['passHash'];
        $data['access_token'] = $this->_request['access_token'];
        $data['apikey'] = $this->_request['apikey'];
        $data['code'] = $this->_request['code'];

        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 201:
                    $this->response($this->json($response['data']), $response['code']);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $msg['resp'] = $response;
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    // fun edit user

    public function delUser($tokenuser, $actoken)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/users?access_token=' . $actoken;
        $data['option'] = 'DELETE';
        $data['tokenuser'] =  $tokenuser;
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function activation()
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/users/a/' . $_GET['code'] . '/' . $_GET['tokenuser'];
        $curl = new Curl();
        $response = $curl->get($url);
        //         print_r($response);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    /*if(isset($response['data']['redirectTo'])){
                        $url = $response['data']['redirectTo'];
                        unset($response['data']['redirectTo']);
                        //$this->redirect($url);
                        $//url = $_ENV['GATEWAY_GLOBAL'] . '/auth/v1/users/a/'.$_GET['code'].'/'.$_GET['tokenuser'];
                        $curl = new Curl();
                        //print_r($url);
                        $response = $curl->get($url);
                    }*/
                    $this->response($this->json($response['data']), 200);
                    $this->response($this->json($response), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.12';
                        $msg['res'] = $response;
                        $msg['url'] = $url;
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function redirect($url, $statusCode = 303)
    {
        header('Location: ' . $url, true, $statusCode);
        die();
    }

    public function login()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/users/login';
        $data['user'] = $this->_request['user'];
        $data['password'] =  $this->_request['password'];
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getUsersByOrg($org, $id)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/users?access_token=' . $id;
        $data['option'] = 'VIEW';
        $data['by'] = 'ORG';
        $data['tokenorg'] = $org;
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            //$msg['response'] = $response;
            $this->response($this->json($msg), 500);
        }
    }




    //------------------ hierarchy --------------------



    public function newOrg()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/hierarchy';


        if (isset($this->_request['acronym']) && isset($this->_request['fullname'])) {
            $data['option'] = 'NEW';
            $data['acronym'] = $this->_request['acronym'];
            $data['fullname'] =  $this->_request['fullname'];
            $data['fathers_token'] =  $this->_request['fathers_token'];
            $curl = new Curl();
            $response = $curl->post($url, $data);
            if (isset($response['code'])) {
                switch ($response['code']) {
                    case 201:
                        $this->response($this->json($response['data']), 200);
                        break;
                    default:
                        if (isset($response['data']['message'])) {
                            $this->response($this->json($response['data']), $response['code']);
                        } else {
                            $msg['message'] = 'Something went wrong.';
                            $this->response($this->json($msg), 500);
                        }
                        break;
                }
            } else {
                $msg['message'] = 'Something went wrong.';
                $this->response($this->json($msg), 500);
            }
        } else {
            $msg['message'] = 'No data.';
            $this->response($this->json($msg), 500);
        }
    }

    public function checkIfExistsOrg()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/hierarchy';

        if (isset($this->_request['acronym']) && isset($this->_request['fullname'])) {
            $data['option'] = 'CHECK';
            $data['acronym'] = $this->_request['acronym'];
            $data['fullname'] =  $this->_request['fullname'];
            $data['fathers_token'] =  $this->_request['fathers_token'];
            $curl = new Curl();
            $response = $curl->post($url, $data);
            if (isset($response['code'])) {
                switch ($response['code']) {
                    case 201:
                        $this->response($this->json($response['data']), 200);
                        break;
                    default:
                        if (isset($response['data']['message'])) {
                            $this->response($this->json($response['data']), $response['code']);
                        } else {
                            $msg['message'] = 'Something went wrong.';
                            $this->response($this->json($msg), 500);
                        }
                        break;
                }
            } else {
                $msg['message'] = 'Something went wrong.';
                $this->response($this->json($msg), 500);
            }
        } else {
            $msg['message'] = 'No data.';
            $this->response($this->json($msg), 500);
        }
    }

    // fun edit org

    public function delHierarchy($tokenh, $actoken)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/hierarchy?access_token=' . $actoken;
        $data['option'] = 'DELETE';
        $data['tokenhierarchy'] =  $tokenh;
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getHierarchyDown($tokenh, $id)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/hierarchy?access_token=' . $id;
        $curl = new Curl();
        $data['option'] = 'VIEW';
        $data['direction'] = 'DOWN';
        $data['tokenhierarchy'] = $tokenh;
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getHierarchyUp($tokenh, $id)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/hierarchy?access_token=' . $id;
        $curl = new Curl();
        $data['option'] = 'VIEW';
        $data['direction'] = 'UP';
        $data['tokenhierarchy'] = $tokenh;
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }



    //------------------------------------------------
    //------- publication / subscription -------------
    //------------------------------------------------

    //------- catalogs ----------------------------
    public function newCatalog($actoken)
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/catalogs?access_token=' . $actoken;
        $data['option'] = 'NEW';
        $data['catalogname'] = $this->_request['catalogname'];
        $data['dispersemode'] =  $this->_request['dispersemode'];
        $data['encryption'] =  $this->_request['encryption'] ? $this->_request['encryption'] : 0 ;
        $data['fathers_token'] =  $this->_request['fathers_token'];
        $data['processed'] =  $this->_request['processed'] ? $this->_request['processed'] : 0 ;
        $data['group'] = $this->_request['group'];
        $curl = new Curl();
        $response = $curl->post($url, $data);
        
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 201:
                    $this->response($this->json($response['data']), 201);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrongx.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrongx.';
            $this->response($this->json($msg), 500);
        }
    }

    //aqui va edit catalog

    public function delCatalog($tokencatalog, $actoken)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/catalogs?access_token=' . $actoken;
        $data['option'] = 'DELETE';
        $data['tokencatalog'] =  $tokencatalog;
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    //------- groups ----------------------------
    public function newGroup($actoken)
    {
        
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        
        $url = URL_PUB_SUB . '/subscription/v1/groups?access_token=' . $actoken;
        $data['option'] = 'NEW';
        $data['groupname'] = $this->_request['groupname'];
        $data['fathers_token'] =  $this->_request['fathers_token'];
        $data["isprivate"] = $this->_request["isprivate"];
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 201:
                    $this->response($this->json($response['data']), 201);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrongx.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrongx.';
            $this->response($this->json($msg), 500);
        }
    }

    //aqui va edit catalog

    public function delGroup($tokengroup, $actoken)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/groups?access_token=' . $actoken;
        $data['option'] = 'DELETE';
        $data['tokengroup'] =  $tokengroup;
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getCatalogInfo($tokencatalog, $actoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . "/subscription/v1/view/catalog/$tokencatalog/?access_token=" . $actoken;

        $curl = new Curl();
        $response = $curl->get($url);
        //print_r($response);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }






    //------- publications ----------------------------
    //--------------------------------------------------

    //------- groups -----------------------------------
    public function publishGroupToUser($actoken)
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/publications?access_token=' . $actoken;
        $data['publish'] = 'GROUP';
        $data['to'] = 'USER';
        $data['tokengroup'] = $this->_request['tokengroup'];
        $data['tokenuser'] = $this->_request['tokenuser'];
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 201:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function publishCatalogToUser($actoken)
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/publications?access_token=' . $actoken;
        $data['publish'] = 'CATALOG';
        $data['to'] = 'USER';
        $data['tokencatalog'] = $this->_request['tokencatalog'];
        $data['tokenuser'] = $this->_request['tokenuser'];
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $msg['message'] = "Catalog published";
                    $this->response($this->json($msg), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrongx.' . $response['code'];
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    //------- SUBSCRIPTIONS ----------------------------
    //--------------------------------------------------


    //------- groups -----------------------------------
    public function subscribeGroupToUser($actoken)
    {
        if ($this->getRequestMethod() != "POST") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/subscriptions?access_token=' . $actoken;
        $data['subscribe'] = 'GROUP';
        $data['to'] = 'USER';
        $data['tokengroup'] = $this->_request['tokengroup'];
        $data['tokenuser'] = $this->_request['tokenuser'];
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        //$msg['response'] = $response['data'];
                        $msg['response'] = $response;
                        $this->response($this->json($msg), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }




    //------- visualization ----------------------------
    //--------------------------------------------------

    //------- groups -----------------------------------
    public function pub_subGetGroupsByUser_Sub($tokenuser, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        $data['show'] = 'GROUPS';
        $data['by'] = 'USER';
        $data['tokenuser'] = $tokenuser;
        $data['view'] = 'SUB';
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function pub_subGetGroupsByUser_Pub($tokenuser, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        $data['show'] = 'GROUPS';
        $data['by'] = 'USER';
        $data['tokenuser'] = $tokenuser;
        $data['view'] = 'PUB';
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    //------- users -----------------------------------
    public function pub_subGetUsersByGroup_Sub($tokeng, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        //HERE
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        $data['show'] = 'USERS';
        $data['by'] = 'GROUP';
        $data['tokengroup'] = $tokeng;
        $data['view'] = 'SUB';
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function pub_subGetUsersByGroup_Pub($tokeng, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        //HERE
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        $data['show'] = 'USERS';
        $data['by'] = 'GROUP';
        $data['tokengroup'] = $tokeng;
        $data['view'] = 'PUB';
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }


    //------- catalogs ---------------------------------
    public function pub_subGetCatalogsByUser($tokenuser, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        $data['show'] = 'CATALOGS';
        $data['by'] = 'USER';
        $data['tokenuser'] = $tokenuser;
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getPuzzleCatalogResults($tokenuser,$atoken, $puzzle, $father){
        if ($this->getRequestMethod() != "GET"){
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        
        
        $url = "http://" . URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        
        $data['show'] = 'CATALOGS';
        $data['by'] = 'USER';
        $data['view'] = 'results';
        $data['puzzle'] = $puzzle;
        $data['tokenuser'] = $tokenuser;
        $data['father'] = $father;

        

        $curl = new Curl();
        $response = $curl->post($url,$data);

        //print_r($response);

        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    }else{
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        }else{
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function addFileToCatalog($resource)
    {
        $url = 'http://' . URL_PUB_SUB . '/subscription/v1/catalogs/' . $resource . '/files/upload';
        $curl = new Curl();
        $data = array('keyfile' => $this->_request['keyfile']);
        //print_r($data);
        $response = $curl->post($url, $data);

        if (isset($response['code'])) {
            //$msg['res'] = $response;
            //$this->response($this->json($msg), 200);
            switch ($response['code']) {
                case 200:
                    //$response['data']
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $msg['response'] = $response;
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getCatalog($tokenCat, $tokenuser)
    {
        $url = 'http://' . URL_PUB_SUB . '/subscription/v1/catalogs/' . $tokenCat . '?access_token=' . $tokenuser;
        $curl = new Curl();
        $response = $curl->get($url, $data);

        if (isset($response['code'])) {
            //$msg['res'] = $response;
            //$this->response($this->json($msg), 200);
            switch ($response['code']) {
                case 200:
                    //$response['data']
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $msg['response'] = $response;
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getPublicCatalogs($tokenuser)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/publiccatalogs?keyuser=' . $tokenuser;
        //echo $url;
        $curl = new Curl();
        $response = $curl->get($url, $data);
        if (isset($response['code'])) {
            //$msg['res'] = $response;
            //$this->response($this->json($msg), 200);
            switch ($response['code']) {
                case 200:
                    //$response['data']
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $msg['response'] = $response;
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }



    //-*/-*/-*/-*/
    public function pub_subGetCatalogsByUser_Sub($tokenuser, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/view/catalogsbyuser?access_token=' . $atoken;
        $data['tokenuser'] = $tokenuser;
        $curl = new Curl();
        $response = $curl->post($url, $data);
	//print_r($response);
        if (isset($response['code'])) {
            //$msg['res'] = $response;
            //$this->response($this->json($msg), 200);
            switch ($response['code']) {
                case 200:
                    $msg['url'] = $url;
                    $msg['data'] = $data;
                    //$response['data']
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.FHY';
                        $msg['response'] = $response;
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function pub_subGetCatalogsByUser_Pub($tokenuser, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }

        $url = "http://" . URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        //echo $url;
        $data['show'] = 'CATALOGS';
        $data['by'] = 'USER';
        $data['view'] = 'pub';
        $data['tokenuser'] = $tokenuser;
        $curl = new Curl();
	//print_r(json_encode($data));
        $response = $curl->post($url, $data);
        //print_r($response);
	if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function pub_subGetCatalogsByGroup_Sub($tokengroup, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        $data['show'] = 'CATALOGS';
        $data['by'] = 'GROUP';
        $data['tokengroup'] = $tokengroup;
        $curl = new Curl();
        //echo URL_PUB_SUB;
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $msg['response'] = $response;
                        $msg['data'] = $data;
                        $this->response($this->json($msg), 407);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function getChildCatalogs($tokenc, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;

        $data['show'] = 'SUBCATALOGS';
        $data['by'] = 'CATALOG';
        $data['tokencatalog'] = $tokenc;
        //echo json_encode($data);
        $curl = new Curl();
        $response = $curl->post($url, $data);
        //print_r($response);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['data'])) {
                        $this->response($this->json($response['data']), 200);
                        break;
                    } else if (isset($response['data']['message'])) {
                        //$response['data']
                        $msg = $response;
                        $msg = $url;
                        $this->response($this->json($msg), $response['code']);
                        //$response['code']
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }


    //------- files ---------------------------------
    public function pub_subGetFilesByCatalog($tokenc, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
	//echo $url;
        $data['show'] = 'FILES';
        $data['by'] = 'CATALOG';
        $data['tokencatalog'] = $tokenc;
        //echo json_encode($data);
        $curl = new Curl();
        $response = $curl->post($url, $data);
        //print_r($response);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['data'])) {
                        $this->response($this->json($response['data']), 200);
                        break;
                    } else if (isset($response['data']['message'])) {
                        //$response['data']
                        $msg = $response;
                        $msg = $url;
                        $this->response($this->json($msg), $response['code']);
                        //$response['code']
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function pub_subGetFilesByUser($token, $atoken)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/visualization?access_token=' . $atoken;
        $data['show'] = 'FILES';
        $data['by'] = 'USER';
        $data['tokenuser'] = $token;
        $curl = new Curl();
        $response = $curl->post($url, $data);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $msg['response'] = $response;
                    //$msg['data'] = $data;
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        //$response['data']

                        $this->response($this->json($response['data']), $response['code']);
                        //$response['code']
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }


















    //------- test for dev ---------------------------
    public function authGetAllUsers($at)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/users/all?access_token=' . $at;
        $curl = new Curl();
        $response = $curl->get($url);
        //$response['msg'] = $url;
        //$this->response($this->json($response), 200);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            //$msg['response'] = $response;
            $this->response($this->json($msg), 500);
        }
    }

    public function authGetAllHierarchy()
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_AUTH . '/auth/v1/hierarchy/all';
        $curl = new Curl();
        $response = $curl->get($url);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 500);
        }
    }

    public function pub_subGetAllCatalogs()
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/catalogs/all';
        $curl = new Curl();
        $response = $curl->get($url);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $msg['response'] = $response;
            $this->response($this->json($msg), 500);
        }
    }

    public function pub_subGetAllGroups()
    {
        if ($this->getRequestMethod() != "GET") {
            $msg['message'] = 'Something went wrong.';
            $this->response($this->json($msg), 406);
        }
        $url = URL_PUB_SUB . '/subscription/v1/groups/all';
        $curl = new Curl();
        $response = $curl->get($url);
        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    $this->response($this->json($response['data']), 200);
                    break;
                default:
                    if (isset($response['data']['message'])) {
                        $this->response($this->json($response['data']), $response['code']);
                    } else {
                        $msg['message'] = 'Something went wrong.';
                        $this->response($this->json($msg), 500);
                    }
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            $msg['response'] = $response;
            $this->response($this->json($msg), 500);
        }
    }
}
