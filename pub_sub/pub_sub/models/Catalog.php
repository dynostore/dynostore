<?php
require_once "Rest.php";
require_once "db/DbHandler.php";
require_once "Curl.php";


class Catalog extends REST
{

    //------------------ auth service --------------------
    private function auth($access_token)
    {
        $url = URL_AUTH . '/auth/v1/user/?access_token=' . $access_token;
        $curl = new Curl();
        $response = $curl->get($url);
        //$response['msg'] = $url;
        //$this->response($this->json($response), 200);

        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    return $response['data'];
                    break;
                case 404:
                    $msg = array("message" => "Access denied. Invalid access token.");
                    $this->response($this->json($msg), 401);
                    break;
                default:
                    $msg = array("message" => "Something went wrong.");
                    $this->response($this->json($msg), 500);
                    break;
            }
        }
    }

    private function userTokenExist($tokenuser)
    {
        $url = URL_AUTH . '/auth/v1/user/?tokenuser=' . $tokenuser;
        $curl = new Curl();
        $response = $curl->get($url);

        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    return $response['data'];
                    break;
                case 404:
                    $msg['message'] = "Invalid token.";
                    $msg['tokenuser'] = $tokenuser;
                    $this->response($this->json($msg), 401);
                    break;
                default:
                    $msg = array("message" => "Something went wrong.");
                    $this->response($this->json($msg), 500);
                    break;
            }
        }
    }

    private function userByKey($key)
    {
        $url = URL_AUTH . '/auth/v1/users/' . $key;
        $curl = new Curl();
        $response = $curl->get($url);
        //$response['msg'] = $url;
        //$this->response($this->json($response), 200);

        if (isset($response['code'])) {
            switch ($response['code']) {
                case 200:
                    return $response['data'];
                    break;
                case 404:
                    $msg = array("message" => "Access denied.");
                    $this->response($this->json($msg), 401);
                    break;
                default:
                    $msg = array("message" => "Something went wrong.");
                    $this->response($this->json($msg), 500);
                    break;
            }
        }
    }

    private function getUsers($subscribers)
    {
        $array = array();
        foreach ($subscribers as $key => $value) {
            $url = URL_AUTH . '/auth/v1/users/' . $value['user_id'] . '/';
            $curl = new Curl();
            $response = $curl->get($url);
            if ($response['code'] == 200) {
                array_push($array, $response['data']);
            }
        }
        return $array;
    }

    //------------------ notifications --------------------
    public function getNotifications($keyuser)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->getNotifications($keyuser);
        if ($data) {
            $this->response($this->json($data), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    public function allowNotification($key)
    {
        if ($this->getRequestMethod() != "PUT") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->allowNotification($key);
        // valid
        if ($data) {
            $msg['message'] = "Allowed";
            $this->response($this->json($msg), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    public function denyNotification($key)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->denyNotification($key);
        // valid
        if ($data) {
            $msg['message'] = "Denied";
            $this->response($this->json($data), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    //------------------ notifications groups --------------------
    public function getNotificationsGroups($keyuser)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->getNotificationsGroups($keyuser);
        if ($data) {
            $this->response($this->json($data), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    public function allowNotificationGroup($key)
    {
        if ($this->getRequestMethod() != "PUT") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->allowNotificationGroup($key);
        if ($data) {
            $msg['message'] = "Allowed";
            $this->response($this->json($msg), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    public function denyNotificationGroup($key)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->denyNotificationGroup($key);
        // valid
        if ($data) {
            $msg['message'] = "Denied";
            $this->response($this->json($data), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    //------------------ subscribe --------------------
    public function subscribe($keycatalog)
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong5.");
            $this->response($this->json($msg), 404);
        }
        if (isset($this->_request['keyuser'])) {
            $db = new DbHandler();
            $st = $db->validateSubscribe($this->_request['keyuser'], $keycatalog);
            $status = 1;
            switch ($st['status']) {
                case 0:
                    $data = $db->subscribe($this->_request['keyuser'], $keycatalog, $status);
                    if ($data) {
                        $msg = array("message" => "Request send.");
                        $this->response($this->json($msg), 201);
                    } else {
                        $msg = array("message" => "Something went wrong.");
                        $this->response($this->json($msg), 404);
                    }
                    break;
                case 1:
                    $msg = array("message" => "Request already send.");
                    $this->response($this->json($msg), 200);
                    break;
                case 2:
                    $msg = array("message" => "Request already accepted.");
                    $this->response($this->json($msg), 200);
                    break;
                case 3:
                    $msg = array("message" => "Request denied.");
                    $this->response($this->json($msg), 200);
                    break;
                default:
                    $msg = array("message" => "Bad request.");
                    $this->response($this->json($msg), 400);
                    break;
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
    }

    public function subscribeGroup($keygroup)
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong5.");
            $this->response($this->json($msg), 404);
        }
        if (isset($this->_request['keyuser'])) {
            $db = new DbHandler();
            $st = $db->validateSubscribeGroup($this->_request['keyuser'], $keygroup);
            $status = 1;
            switch ($st['status']) {
                case 0:
                    $data = $db->subscribeGroup($this->_request['keyuser'], $keygroup, $status);
                    if ($data) {
                        $msg = array("message" => "Request send.");
                        $this->response($this->json($msg), 201);
                    } else {
                        $msg = array("message" => "Something went wrong.");
                        $this->response($this->json($msg), 404);
                    }
                    break;
                case 1:
                    $msg = array("message" => "Request already send.");
                    $this->response($this->json($msg), 200);
                    break;
                case 2:
                    $msg = array("message" => "Request already accepted.");
                    $this->response($this->json($msg), 200);
                    break;
                case 3:
                    $msg = array("message" => "Request denied.");
                    $this->response($this->json($msg), 200);
                    break;
                default:
                    $msg = array("message" => "Bad request.");
                    $this->response($this->json($msg), 400);
                    break;
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
    }

    //--------------------------------------------------------------------------
    //--------------------------- VISUALIZATION -------------------------------
    //--------------------------------------------------------------------------

    public function viewCatalogInfo($tokencat, $acess_token)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }

        $response = $this->auth($acess_token);
        if (isset($response['data']['tokenuser'])) {
            $db = new DbHandler();
            $existsCat = $db->catalogTokenExist($tokencat);
            if ($existsCat) {
                $catInfo = $db->getCatalogInfo($tokencat);
                $msg['message'] = "Data obtained.";
                $msg['data'] = $catInfo[0];
                $this->response($this->json($msg), 200);
            } else {
                $msg['message'] = "Catalog doesn't exist.";
                $msg['data'] = array();
                $this->response($this->json($msg), 404);
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
    }

    public function visualizationFunction()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $response = $this->auth($_GET['access_token']);

        if (isset($this->_request['show']) && isset($response['data']['tokenuser'])) {
            switch ($this->_request['show']) {
                case 'FILES':
                    switch ($this->_request['by']) {
                        case 'CATALOG':
                            if (isset($this->_request['tokencatalog'])) {
                                $db = new DbHandler();
                                $this->catalogTokenExist($this->_request['tokencatalog']);
                                $data = $db->getFilesByCatalog($this->_request['tokencatalog']);
                                //print_r($data);
                                if ($data) {
                                    $array = array();
                                    $added = array();
                                    //print_r($data);
                                    foreach ($data as $key) {
                                        $strArray = explode('/', $key['token_file']);
                                        $key = end($strArray);
                                        // consulta a metadata

                                        $url = $_ENV['METADATA_HOST'] . '/register/files/' . $key . '/';
                                        //echo $url;
                                        $curl = new Curl();
                                        $response = $curl->get($url);
                                        //print_r($response);
                                        if ($response['code'] == 200) {
                                            if (isset($added[$response['data']["namefile"]])) {
                                                if ($response['data']["created_at"] > $added[$response['data']["namefile"]]["created_at"]) {
                                                    $added[$response['data']["namefile"]] = $response['data'];
                                                }
                                            } else {
                                                $added[$response['data']["namefile"]] = $response['data'];
                                            }

                                            //array_push($array, $response['data']);
                                        }
                                    }
                                    $msg['message'] = "Ok";
                                    $msg['data'] = array_values($added);
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                            } else {
                                $msg['message'] = "Invalid data.";
                                $this->response($this->json($msg), 400);
                            }
                            break;
                        case 'USER':
                            if (isset($this->_request['tokenuser'])) {
                                $files = $this->filesByUser($this->_request['tokenuser']);
                                if ($files) {
                                    $msg['message'] = "Ok";
                                    $msg['data'] = $files;
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                                //tu
                            } else {
                                $msg['message'] = "Invalid data.";
                                $this->response($this->json($msg), 400);
                            }
                            break;
                        //case 'GROUP': break;
                        default:
                            $msg['message'] = "Invalid action.";
                            $this->response($this->json($msg), 400);
                            break;
                    }
                    break;
                case 'SUBCATALOGS':
                    switch ($this->_request['by']) {
                        case 'CATALOG':
                            if (isset($this->_request['tokencatalog'])) {
                                $db = new DbHandler();
                                $this->catalogTokenExist($this->_request['tokencatalog']);
                                $data = $db->getSubCatalogs($this->_request['tokencatalog']);
                                if ($data) {
                                    //foreach ($data as &$k) {
                                    //  unset($k['keycatalog']);
                                    //}
                                    $msg['message'] = "Ok";
                                    $msg['data'] = $data;
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                            }
                    }
                    break;
                case 'CATALOGS':
                    switch ($this->_request['by']) {
                        case 'USER':
                            if (isset($this->_request['view']) && isset($this->_request['tokenuser'])) {
                                //$db = new DbHandler();
                                //echo $this->_request['view'];
                                switch ($this->_request['view']) {
                                    case 'SUB':
                                        $data = $this->catalogsByUser_Sub($this->_request['tokenuser']);
                                        break;
                                    case 'pub':
                                        $data = $this->catalogsByUser_Pub($this->_request['tokenuser']);
                                        break;
                                    case 'results':
                                        $data = $this->catalogsPuzzleResults($this->_request['tokenuser'], $this->_request['puzzle'], $this->_request['father']);
                                        break;
                                    default:
                                        $msg['message'] = "Invalid data.";
                                        $this->response($this->json($msg), 400);
                                        break;
                                }

                                //print_r($data);                                
                                if ($data) {

                                    foreach ($data as &$k) {
                                        unset($k['keycatalog']);
                                        unset($k['token_user']);
                                        //unset($k['status']);
                                    }
                                    $msg['message'] = "Ok";
                                    $msg['data'] = $data;
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                            } else {
                                $msg['message'] = "Invalid data.";
                                $this->response($this->json($msg), 400);
                            }
                            break;
                        case 'GROUP':
                            if (isset($this->_request['tokengroup'])) {
                                $db = new DbHandler();
                                $this->groupTokenExist($this->_request['tokengroup']);
                                $data = $this->catalogsByGroup($this->_request['tokengroup']);
                                //here
                                if ($data) {
                                    //foreach ($data as &$k) {
                                    //  unset($k['keycatalog']);
                                    //}
                                    $msg['message'] = "Ok";
                                    $msg['data'] = $data;
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                            } else {
                                $msg['message'] = "Invalid data.";
                                $this->response($this->json($msg), 400);
                            }
                            break;
                        //case 'GROUP': break;
                        default:
                            $msg['message'] = "Invalid action.";
                            $this->response($this->json($msg), 400);
                            break;
                    }
                    break;
                case 'USERS':
                    switch ($this->_request['by']) {
                        case 'GROUP':
                            if (isset($this->_request['view']) && isset($this->_request['tokengroup'])) {
                                switch ($this->_request['view']) {
                                    case 'SUB':
                                        $data = $this->usersByGroup_Sub($this->_request['tokengroup']);
                                        break;
                                    case 'PUB':
                                        $data = $this->usersByGroup_Pub($this->_request['tokengroup']);
                                        break;
                                    default:
                                        $msg['message'] = "Invalid data.";
                                        $this->response($this->json($msg), 400);
                                        break;
                                }
                                if ($data) {
                                    $msg['message'] = "Ok";
                                    $msg['data'] = $data;
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                            } else {
                                $msg['message'] = "Invalid data.";
                                $msg['request'] = $this->_request;
                                $this->response($this->json($msg), 400);
                            }
                            break;
                        //case 'CATALOG': break;
                        default:
                            $msg['message'] = "Invalid action.";
                            $this->response($this->json($msg), 400);
                            break;
                    }
                    break;
                case 'GROUPS':
                    switch ($this->_request['by']) {
                        case 'CATALOG':
                            if (isset($this->_request['tokencatalog'])) {
                                $db = new DbHandler();
                                //$this->catalogTokenExist($this->_request['tokencatalog']);
                                $data = $db->getGroupsByCatalog($this->_request['tokencatalog']);
                                if ($data) {
                                    $msg['message'] = "Ok";
                                    $msg['data'] = $data;
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                            } else {
                                $msg['message'] = "Invalid data.";
                                $this->response($this->json($msg), 400);
                            }
                            break;
                        case 'USER':
                            if (isset($this->_request['view']) && isset($this->_request['tokenuser'])) {
                                switch ($this->_request['view']) {
                                    case 'SUB':
                                        $data = $this->groupsByUser_Sub($this->_request['tokenuser']);
                                        break;
                                    case 'PUB':
                                        $data = $this->groupsByUser_Pub($this->_request['tokenuser']);
                                        break;
                                    default:
                                        $msg['message'] = "Invalid data.";
                                        $this->response($this->json($msg), 400);
                                        break;
                                }
                                //$data = $this->groupsByUser_Sub($this->_request['tokenuser']);
                                if ($data) {
                                    $msg['message'] = "Ok";
                                    $msg['data'] = $data;
                                    $this->response($this->json($msg), 200);
                                } else {
                                    $msg['message'] = "No data.";
                                    $msg['data'] = array();
                                    $this->response($this->json($msg), 404);
                                }
                            } else {
                                $msg['message'] = "Invalid data.";
                                $this->response($this->json($msg), 400);
                            }
                            break;


                        default:
                            $msg['message'] = "Invalid action.";
                            $this->response($this->json($msg), 400);
                            break;
                    }
                    break;
                default:
                    $msg['message'] = "Invalid action.";
                    $this->response($this->json($msg), 400);
                    break;
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
    }

    public function filesByUser($user)
    {
        $data = $this->catalogsByUser_Sub($user);
        if ($data) {
            $db = new DbHandler();
            foreach ($data as $key) {
                $tnew = $db->getFilesByCatalog($key['tokencatalog']);
                if ($tnew) {
                    $new[] = $tnew;
                }
                // consulta a metadata
                /*$url = $_ENV['METADATA_HOST'] . '/register/files/'.$key['token_file'].'/';
                $curl = new Curl();
                $response = $curl->get($url);
                if ($response['code'] == 200) {
                array_push($array, $response['data']);
                }*/
            }
            return $new;
        } else {
            return false;
        }
    }



    public function catalogsByUser($user)
    {
        $db = new DbHandler();
        $cat = $db->getCatalogsByUser($user);
        //$catown = $db->getCatalogsByUser2($user);
        switch (true) {
            case ($cat && $catown):
                $data = array_merge($cat, $catown);
                //$this->response($this->json($msg), 200);
                return $data;
                break;
            case ($cat && !$catown):
                $msg['message'] = "Ok";
                $msg['data'] = $cat;
                //$this->response($this->json($msg), 200);
                return $cat;
                break;
            case (!$cat && $catown):
                $msg['message'] = "Ok";
                $msg['data'] = $catown;
                //$this->response($this->json($msg), 200);
                return $catown;
                break;
            default:
                $msg = array("message" => "No data.");
                //$this->response($this->json($msg), 404);
                return false;
                break;
        }
    }

    public function catalogsByUser_PubBKP($user)
    {
        $this->userTokenExist($user);
        $db = new DbHandler();
        $cat = $db->getCatalogsByUser_Pub($user);
        if ($cat) {
            return $cat;
        } else {
            $msg['message'] = "No data.";
            $this->response($this->json($msg), 404);
        }
    }

    public function catalogsByUser_Sub($user)
    {
        $this->userTokenExist($user);
        $db = new DbHandler();
        //si el usuario es dueño del grupo o de los catalogos deben aparecer
        $cat = $db->getCatalogsByUser_Sub($user);
        return $cat;
        //groups by user
        /*$tgroups = $db->getGroupsByUser_Sub($user);
        return $tgroups;
        foreach ($tgroups as $key) {
        //users by group
        $tusers = $db->getUsersByGroup_Sub($key['tokengroup']);
        if($tusers){
        foreach($tusers as $us){
        $tempusers[] = $us;
        //eliminar duplicados
        }
        /*foreach($tempusers as $usr){
        //catalogs by user
        $tcats = $db->getCatalogsByUser_Sub($usr['tokenuser']);
        if($tcats){
        foreach($tcats as $ca){
        $tempcats[] = $ca;
        //eliminar duplicados
        }
        }
        }/
        }
        }*/
        //return $tempcats;


        if ($cat) {
            return $cat;
        } else {
            $msg['message'] = "No data.";
            $this->response($this->json($msg), 404);
        }
    }



    public function visualizationCatalogsByUser()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $response = $this->auth($_GET['access_token']);
        if (isset($response['data']['tokenuser']) && isset($this->_request['tokenuser'])) {
            $data = $this->catalogsByUser_Temp($this->_request['tokenuser']);

            if ($data) {
                //foreach ($data as &$k) {
                //  unset($k['keycatalog']);
                //unset($k['token_user']);
                //}
                $msg['message'] = "Ok";
                $msg['data'] = $data;
                $this->response($this->json($msg), 200);
            } else {
                $msg['message'] = "No data.";
                $msg['data'] = array();
                $this->response($this->json($msg), 404);
            }
        }
    }

    public function visualizationCatalogsByUserPub()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $response = $this->auth($_GET['access_token']);
        if (isset($response['data']['tokenuser']) && isset($this->_request['tokenuser'])) {
            $data = $this->catalogsByUser_Temp($this->_request['tokenuser']);
            if ($data) {
                //foreach ($data as &$k) {
                //  unset($k['keycatalog']);
                //unset($k['token_user']);
                //}
                $msg['message'] = "Ok";
                $msg['data'] = $data;
                $this->response($this->json($msg), 200);
            } else {
                $msg['message'] = "No data.";
                $msg['data'] = array();
                $this->response($this->json($msg), 404);
            }
        }
    }

    public function getPublicGroups($user)
    {
        //$this->userTokenExist($user);

        $db = new DbHandler();
        $data = $db->getPublicCatalogs();
        if ($data) {
            //foreach ($data as &$k) {
            //  unset($k['keycatalog']);
            //unset($k['token_user']);
            //}
            $msg['message'] = "Ok";
            $msg['data'] = $data;
            $this->response($this->json($msg), 200);
        } else {
            $msg['message'] = "No data.";
            $msg['data'] = array();
            $this->response($this->json($msg), 404);
        }
    }


    public function catalogsByUser_Temp($user)
    {
        $this->userTokenExist($user);
        $db = new DbHandler();
        //si el usuario es dueño del grupo o de los catalogos deben aparecer
        $cats = $db->getCatalogsByUser_Sub($user);
        //print_r($cats);
        $res = [];
        //groups by user
        if ($cats) {
            $tgroups = $db->getGroupsByUser_Sub($user);
            //print_r($tgroups);
            if ($tgroups) {
                foreach ($tgroups as $key) {
                    //users by group
                    $tusers = $db->getUsersByGroup_Sub($key['tokengroup']);
                    if ($tusers) {
                        foreach ($tusers as $us) {
                            $users[] = $us['tokenuser'];
                        }
                    }
                    unset($tusers);
                }

                //eliminar duplicados
                $u_users = array_unique($users);

                //return $u_users;
                foreach ($u_users as $key => $val) {
                    //echo $val . "<br><br>";
                    //catalogs by user
                    $tcats = $db->getCatalogsByUser_Own($val);
                    if ($tcats) {
                        foreach ($tcats as $ca) {
                            unset($ca['token_user']);
                            //unset($ca['status']);
                            unset($ca['keycatalog']);
                            //unset($ca['father']);
                            $res[$ca["tokencatalog"]] = $ca;
                        }
                    }
                    unset($tcats);
                }
            }
        } else {
            echo "AAAAAAAASDASD";
        }

        #print_r($cats);
        //print_r($res);
        //echo count($res);
        return $cats;
    }

    public function catalogsPuzzleResults($user, $puzzle, $father)
    {
        $this->userTokenExist($user);
        $db = new DbHandler();
        //catalogos del usuario
        $cat = $db->getCatalogsByPuzzle($user, $puzzle, $father);
        //print_r($cat);
        return $cat;
    }

    public function catalogsByUser_Pub($user)
    {
        $this->userTokenExist($user);
        $db = new DbHandler();
        //catalogos del usuario
        $cat = $db->getCatalogsByUser_Own($user);
        //grupos por usuario
        $tgroups = $db->getGroupsByUser_Sub($user);
        //usuarios a lo que se les han publicado el o los grupos (usuarios por grupo)
        /*foreach ($tgroups as $key) {
        //users by group
        $tusers = $db->getUsersByGroup_Sub($key['tokengroup']);
        if ($tusers) {
        if ($cat) {
        foreach ($cat as &$key) {
        unset($key['token_user']);
        unset($key['keycatalog']);
        $key['status'] = 'Pub';
        }
        $msg['message'] = "Ok";
        $msg['data'] = $cat;
        $this->response($this->json($msg), 200);
        } else {
        $msg['message'] = "No data.";
        $msg['data'] = array();
        $this->response($this->json($msg), 404);
        }
        return $cat;
        }
        //unset($tusers);
        }*/
        //print_r($cat);
        return $cat;
    }


    public function catalogsByGroup($group)
    {
        $db = new DbHandler();
        $usrs = $db->getUsersByGroup_Sub($group);
        //return $usrs;
        if ($usrs) {
            foreach ($usrs as $k) {
                $ucats = $db->getCatalogsByUser($k['tokenuser']);
                if ($ucats) {
                    foreach ($ucats as $key) {
                        $cats[] = $key;
                    }
                }
            }
            if ($cats) {
                return $cats;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function groupsByUser_Sub($user)
    {
        $this->userTokenExist($user);
        $db = new DbHandler();
        $gsub = $db->getGroupsByUser_Sub($user);
        if (is_array($gsub)) {
            foreach ($gsub as &$key) {
                unset($key['keygroup']);
                unset($key['token_user']);
            }
        }
        //$gown = $db->getGroupsByUser2($user);
        switch (true) {
            case ($gsub && $gown):
                $data = array_merge($gsub, $gown);
                return $data;
                break;
            case ($gsub && !$gown):
                return $gsub;
                break;
            case (!$gsub && $gown):
                return $gown;
                break;
            default:
                return false;
                break;
        }
    }

    public function groupsByUser_Pub($user)
    {
        $this->userTokenExist($user);
        $db = new DbHandler();
        $gsub = $db->getGroupsByUser_Pub($user);
        if ($gsub) {
            return $gsub;
        } else {
            return false;
        }
    }



    public function usersByGroup_Sub($group)
    {
        $this->groupTokenExist($group);
        $db = new DbHandler();
        $data = $db->getUsersByGroup_Sub($group);
        //$gown = $db->getGroupsByUser2($this->_request['tokenuser']);
        if ($data) {
            foreach ($data as &$key) {
                $url = URL_AUTH . '/auth/v1/user?tokenuser=' . $key['tokenuser'];
                $curl = new Curl();
                $response = $curl->get($url);
                if ($response['code'] == 200) {
                    foreach ($response['data']['data'] as $k => $value) {
                        $key[$k] = $value;
                    }
                }
            }
            unset($key);
            $ret = $data;
        } else {
            $ret = false;
        }
        return $ret;
    }

    public function usersByGroup_Pub($group)
    {
        $this->groupTokenExist($group);
        $db = new DbHandler();
        $data = $db->getUsersByGroup_Pub($group);
        if ($data) {
            foreach ($data as &$key) {
                $url = URL_AUTH . '/auth/v1/user?tokenuser=' . $key['tokenuser'];
                $curl = new Curl();
                $response = $curl->get($url);
                if ($response['code'] == 200) {
                    foreach ($response['data']['data'] as $k => $value) {
                        $key[$k] = $value;
                    }
                }
            }
            unset($key);
            $ret = $data;
        } else {
            $ret = false;
        }
        return $ret;
    }



    //------------------------------------------------
    //------------------ viewFiles --------------------
    //------------------------------------------------
    public function catalogTokenExist($catalog)
    {
        $db = new DbHandler();
        $data = $db->catalogTokenExist($catalog);
        if (!$data) {
            $msg['message'] = "No such catalog or access denied.";
            $this->response($this->json($msg), 400);
        }
    }



    //------------------------------------------------
    //------------------ viewCatalogs --------------------
    //------------------------------------------------
    /*
    public function viewCatalogsFunction() {
    if ($this->getRequestMethod() != "POST"){
    $msg = array("message" => "Something went wrong.");
    $this->response($this->json($msg), 404);
    }
    $response = $this->auth($_GET['access_token']);
    if ( isset($this->_request['option']) && isset($response['data']['tokenuser']) ) {
    switch ( $this->_request['option'] ) {
    case 'BYGROUP':
    if ( isset($this->_request['keygroup']) ) {
    $db = new DbHandler();
    $this->groupKeyExist($this->_request['keygroup']);
    
    $data = $db->getCatalogsByGroup($this->_request['keygroup']);
    if ($data) {
    
    $this->response($this->json($data), 200);     
    } else {
    $msg = array("message" => "No data.");
    $this->response($this->json($msg), 404);
    }
    } else {
    $msg['message'] = "Invalid data.";
    $this->response($this->json($msg), 400);
    }
    break;
    case 'BYUSER':
    if ( isset($this->_request['keyuser']) ) {
    $db = new DbHandler();
    $this->userByKey($this->_request['keyuser']);
    
    $data = $db->getCatalogsByUser($this->_request['keyuser']);
    if ($data) {
    
    $this->response($this->json($data), 200);     
    } else {
    $msg = array("message" => "No data.");
    $this->response($this->json($msg), 404);
    }
    } else {
    $msg['message'] = "Invalid data.";
    $this->response($this->json($msg), 400);
    }
    break;
    
    
    default:
    $msg['message'] = "Invalid action.";
    $this->response($this->json($msg), 400);
    break;
    }
    }else{
    $msg = array("message" => "Something went wrong.");
    $this->response($this->json($msg), 404);
    }
    }*/


    //------------------------------------------------
    //------------------ viewUsers --------------------
    //------------------------------------------------
    /*
    public function viewUsersFunction() {
    if ($this->getRequestMethod() != "POST"){
    $msg = array("message" => "Something went wrong.");
    $this->response($this->json($msg), 404);
    }
    $response = $this->auth($_GET['access_token']);
    if ( isset($this->_request['option']) && isset($response['data']['tokenuser']) ) {
    switch ( $this->_request['option'] ) {
    case 'BYGROUP':
    if ( isset($this->_request['keygroup']) ) {
    $db = new DbHandler();
    $this->groupKeyExist($this->_request['keygroup']);
    
    $data = $db->getUsersByGroup($this->_request['keygroup']);
    if ($data) {
    foreach ($data as &$key) {
    $url = URL_AUTH . '/auth/v1/users/' . $key['keyuser'] . '/';
    $curl = new Curl();
    $response = $curl->get($url);
    if ($response['code'] == 200) {
    foreach ($response['data'] as $k => $value) {
    $key[$k] = $value;
    }
    }
    }
    unset($key);
    $this->response($this->json($data), 200);     
    } else {
    $msg = array("message" => "No data.");
    $this->response($this->json($msg), 404);
    }
    } else {
    $msg['message'] = "Invalid data.";
    $this->response($this->json($msg), 400);
    }
    break;
    case 'BYCATALOG':
    if ( isset($this->_request['keycatalog']) ) {
    $db = new DbHandler();
    $this->catalogKeyExist($this->_request['keycatalog']);
    
    $data = $db->getUsersByCatalog($this->_request['keycatalog']);
    if ($data) {
    foreach ($data as &$key) {
    $url = URL_AUTH . '/auth/v1/users/' . $key['keyuser'] . '/';
    $curl = new Curl();
    $response = $curl->get($url);
    if ($response['code'] == 200) {
    foreach ($response['data'] as $k => $value) {
    $key[$k] = $value;
    }
    }
    }
    unset($key);
    $this->response($this->json($data), 200);     
    } else {
    $msg = array("message" => "No data.");
    $this->response($this->json($msg), 404);
    }
    } else {
    $msg['message'] = "Invalid data.";
    $this->response($this->json($msg), 400);
    }
    break;
    
    
    default:
    $msg['message'] = "Invalid action.";
    $this->response($this->json($msg), 400);
    break;
    }
    }else{
    $msg = array("message" => "Something went wrong.");
    $this->response($this->json($msg), 404);
    }
    }*/

    public function groupKeyExist($keyc)
    {
        $db = new DbHandler();
        $data = $db->groupKeyExist($keyc);
        if (!$data) {
            $msg['message'] = "No such group or access denied.";
            $this->response($this->json($msg), 400);
        }
    }


    //------------------------------------------------
    //------------------ viewGroups --------------------
    //------------------------------------------------
    //DVDASV



    public function catalogKeyExist($keyc)
    {
        $db = new DbHandler();
        $data = $db->catalogKeyExist($keyc);
        if (!$data) {
            $msg['message'] = "No such catalog or access denied.";
            $this->response($this->json($msg), 400);
        }
    }






    //------------------------------------------------
    //------------------ viewPublications --------------------
    //------------------------------------------------
    /*
    public function viewPublicationsFunction() {
    if ($this->getRequestMethod() != "POST"){
    $msg = array("message" => "Something went wrong.");
    $this->response($this->json($msg), 404);
    }
    $response = $this->auth($_GET['access_token']);
    if ( isset($this->_request['option']) && isset($response['data']['tokenuser']) ) {
    switch ( $this->_request['option'] ) {
    case 'BYCATALOG':
    if ( isset($this->_request['keycatalog']) ) {
    $db = new DbHandler();
    $this->catalogKeyExist($this->_request['keycatalog']);
    
    $data = $db->getPublicationsByCatalog($this->_request['keycatalog']);
    if ($data) {
    
    $this->response($this->json($data), 200);     
    } else {
    $msg = array("message" => "No data.");
    $this->response($this->json($msg), 404);
    }
    } else {
    $msg['message'] = "Invalid data.";
    $this->response($this->json($msg), 400);
    }
    break;
    case 'BYUSER':
    if ( isset($this->_request['keyuser']) ) {
    $db = new DbHandler();
    $this->userByKey($this->_request['keyuser']);
    
    $data = $db->getPublicationsByUser($this->_request['keyuser']);
    if ($data) {
    
    $this->response($this->json($data), 200);     
    } else {
    $msg = array("message" => "No data.");
    $this->response($this->json($msg), 404);
    }
    } else {
    $msg['message'] = "Invalid data.";
    $this->response($this->json($msg), 400);
    }
    break;
    
    
    default:
    $msg['message'] = "Invalid action.";
    $this->response($this->json($msg), 400);
    break;
    }
    }else{
    $msg = array("message" => "Something went wrong.");
    $this->response($this->json($msg), 404);
    }
    }*/


    private function display_childrenPub($parent, $level)
    {
        $db = new DbHandler();
        $data = $db->selectChildrenPub($parent);
        $path = array();
        if ($data) {
            foreach ($data as $key) {
                $path[] = $key['keycatalog'];
                $path = array_merge(display_childrenPub($key['keycatalog'], $level + 1), $path);
            }
        }
        return $path;
    }






    //------------------------------------------------
    //------------------ publications --------------------
    //------------------------------------------------

    public function publicationFunction()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $response = $this->auth($_GET['access_token']);
        if (isset($this->_request['publish']) && isset($response['data']['tokenuser'])) {
            switch ($this->_request['publish']) {
                case 'CATALOG':
                    if (isset($this->_request['to'])) {
                        switch ($this->_request['to']) {
                            case 'USER':
                                if (isset($this->_request['tokencatalog']) && isset($this->_request['tokenuser'])) {
                                    $data = $this->pubCatalogToUser($this->_request['tokencatalog'], $this->_request['tokenuser']);
                                    if ($data) {
                                        $msg['message'] = "Catalog Publicated";
                                        $this->response($this->json($msg), 201);
                                    } else {
                                        $msg = array("message" => "Something went wrong.");
                                        $this->response($this->json($msg), 404);
                                    }
                                } else {
                                    $msg['message'] = "Invalid option.";
                                    $this->response($this->json($msg), 400);
                                }
                                break;

                            //res inf
                            case 'GROUP':
                                if (isset($this->_request['tokencatalog']) && isset($this->_request['tokengroup'])) {
                                    $data = $this->pubCatalogToGroup($this->_request['tokencatalog'], $this->_request['tokengroup']);
                                    if ($data) {
                                        $msg['message'] = "Catalog Publicated";
                                        $this->response($this->json($msg), 201);
                                    } else {
                                        $msg = array("message" => "Something went wrong.");
                                        $this->response($this->json($msg), 404);
                                    }
                                } else {
                                    $msg['message'] = "Invalid option.";
                                    $this->response($this->json($msg), 400);
                                }
                                break;

                            default:
                                $msg['message'] = "Invalid option.";
                                $this->response($this->json($msg), 400);
                                break;
                        }
                    } else {
                        $msg['message'] = "Invalid option.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                case 'GROUP':
                    if (isset($this->_request['to'])) {
                        switch ($this->_request['to']) {
                            case 'USER':
                                if (isset($this->_request['tokengroup']) && isset($this->_request['tokenuser'])) {
                                    $data = $this->pubGroupToUser($this->_request['tokengroup'], $this->_request['tokenuser']);
                                    if ($data) {
                                        $msg['message'] = "Group Publicated";
                                        $this->response($this->json($msg), 201);
                                    } else {
                                        $msg = array("message" => "Something went wrong.123");
                                        $this->response($this->json($msg), 404);
                                    }
                                } else {
                                    $msg['message'] = "Invalid option.";
                                    $this->response($this->json($msg), 400);
                                }
                                break;
                            default:
                                $msg['message'] = "Invalid option.";
                                $this->response($this->json($msg), 400);
                                break;
                        }
                    } else {
                        $msg['message'] = "Invalid option.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                /*case 'PUBLISH_CATALOG':
                
                case 'UNPUBLISH_CATALOG':
                if ( isset($this->_request['keycatalog'])  ) {
                $this->publicationExist2($this->_request['keycatalog'],$_GET['access_token']);
                $db = new DbHandler();
                $data = $db->deletePublication($this->_request['keycatalog'],$_GET['access_token']);
                if ($data) {
                $msg['message'] = "Deleted";
                $this->response($this->json($msg), 200);     
                } else {
                $msg = array("message" => "Something went wrong.");
                $this->response($this->json($msg), 404);
                }
                
                } else {
                $msg['message'] = "Invalid data.";
                $this->response($this->json($msg), 400);
                }
                break;
                
                case 'FILE_TO_USER':
                case 'FILE_TO_GROUP':
                case 'CATALOG_TO_GROUP':
                case 'GROUP_TO_GROUP':*/
                default:
                    $msg['message'] = "Invalid action.";
                    $this->response($this->json($this->_request), 400);
                    break;
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
    }

    public function pubCatalogToUser($catalog, $user)
    {
        $db = new DbHandler();
        //$this->userTokenExist($this->_request['tokenuser']);
        //$this->groupTokenExist($this->_request['tokengroup']);
        //$this->pubGroupToUserExist($this->_request['tokengr$this->_request['tokenuser']);
        $subcats = $db->getSubCatalogs($catalog);

        foreach ($subcats as $s) {
            $this->pubCatalogToUser($s["tokencatalog"], $user);
            //$db->newPubCatalogToUser($user, $s["tokencatalog"], 'Published');
        }

        $data = $db->newPubCatalogToUser($user, $catalog, 'Published');
        //if ($data) {
        //  $msg['message'] = "Already published.";
        //$this->response($this->json($msg), 400);
        //}
        return $data;
    }

    public function pubCatalogToGroup($catalog, $group)
    {
        $db = new DbHandler();
        //$this->groupTokenExist($this->_request['tokenuser']);
        //$this->groupTokenExist($this->_request['tokengroup']);
        //$this->pubGroupToUserExist($this->_request['tokengr$this->_request['tokenuser']);
        $data = $db->newPubCatalogToGroup($catalog, $group, 'Published');
        //if ($data) {
        //  $msg['message'] = "Already published.";
        //$this->response($this->json($msg), 400);
        //}
        return $data;
    }

    public function pubGroupToUser($group, $user)
    {
        $db = new DbHandler();
        //$this->userTokenExist($this->_request['tokenuser']);
        //$this->groupTokenExist($this->_request['tokengroup']);
        $this->pubGroupToUserExist($group, $user);
        $status = 'Pub';
        $data = $db->newPubGroupToUser($user, $group, $status);
        //if ($data) {
        //  $msg['message'] = "Already published.";
        //$this->response($this->json($msg), 400);
        //}
        return $data;
    }



    public function publicationExist($keyc, $keyuser)
    {
        $db = new DbHandler();
        $data = $db->publicationExist($keyc, $keyuser);
        if ($data) {
            $msg['message'] = "Already published.";
            $this->response($this->json($msg), 400);
        }
    }

    public function publicationExist2($keyc, $keyuser)
    {
        $db = new DbHandler();
        $data = $db->publicationExist($keyc, $keyuser);
        if (!$data) {
            $msg['message'] = "No such publication or access denied.";
            $this->response($this->json($msg), 400);
        }
    }

    public function groupTokenExist($group)
    {
        $db = new DbHandler();
        $data = $db->groupTokenExist($group);
        if (!$data) {
            $msg['message'] = "No such group or access denied.";
            $this->response($this->json($msg), 400);
        }
    }



    public function pubGroupToUserExist($group, $user)
    {
        $db = new DbHandler();
        $data = $db->pubGroupToUserExist($group, $user);
        if ($data) {
            $msg['message'] = "Already published.";
            $this->response($this->json($msg), 400);
        }
    }



    //------------------------------------------------
    //------------------ subscriptions --------------------
    //------------------------------------------------
    public function subscriptionFunction()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $response = $this->auth($_GET['access_token']);
        if (isset($this->_request['subscribe']) && isset($response['data']['tokenuser'])) {
            switch ($this->_request['subscribe']) {
                case 'CATALOG':
                    if (isset($this->_request['to'])) {
                        switch ($this->_request['to']) {
                            case 'USER':
                                if (isset($this->_request['tokencatalog']) && isset($this->_request['tokenuser'])) {
                                    $data = $this->pubCatalogToUser($this->_request['tokencatalog'], $this->_request['tokenuser']);
                                    if ($data) {
                                        $msg['message'] = "Catalog Publicated";
                                        $this->response($this->json($msg), 201);
                                    } else {
                                        $msg = array("message" => "Something went wrong.");
                                        $this->response($this->json($msg), 404);
                                    }
                                } else {
                                    $msg['message'] = "Invalid option.";
                                    $this->response($this->json($msg), 400);
                                }
                                break;

                            //res inf
                            case 'GROUP':
                                if (isset($this->_request['tokencatalog']) && isset($this->_request['tokengroup'])) {
                                    $data = $this->pubCatalogToGroup($this->_request['tokencatalog'], $this->_request['tokengroup']);
                                    if ($data) {
                                        $msg['message'] = "Catalog Publicated";
                                        $this->response($this->json($msg), 201);
                                    } else {
                                        $msg = array("message" => "Something went wrong.");
                                        $this->response($this->json($msg), 404);
                                    }
                                } else {
                                    $msg['message'] = "Invalid option.";
                                    $this->response($this->json($msg), 400);
                                }
                                break;

                            default:
                                $msg['message'] = "Invalid option.";
                                $this->response($this->json($msg), 400);
                                break;
                        }
                    } else {
                        $msg['message'] = "Invalid option.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                case 'GROUP':
                    if (isset($this->_request['to'])) {
                        switch ($this->_request['to']) {
                            case 'USER':
                                if (isset($this->_request['tokengroup']) && isset($this->_request['tokenuser'])) {
                                    $data = $this->subGroupToUser($this->_request['tokengroup'], $this->_request['tokenuser']);
                                    if ($data) {
                                        $msg['message'] = "Group Subscribed";
                                        $this->response($this->json($msg), 200);
                                    } else {
                                        $msg = array("message" => "Something went wrong.");
                                        $this->response($this->json($msg), 404);
                                    }
                                } else {
                                    $msg['message'] = "Invalid option.";
                                    $this->response($this->json($msg), 400);
                                }
                                break;
                            default:
                                $msg['message'] = "Invalid option.";
                                $this->response($this->json($msg), 400);
                                break;
                        }
                    } else {
                        $msg['message'] = "Invalid option.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                /*case 'PUBLISH_CATALOG':
                
                case 'UNPUBLISH_CATALOG':
                if ( isset($this->_request['keycatalog'])  ) {
                $this->publicationExist2($this->_request['keycatalog'],$_GET['access_token']);
                $db = new DbHandler();
                $data = $db->deletePublication($this->_request['keycatalog'],$_GET['access_token']);
                if ($data) {
                $msg['message'] = "Deleted";
                $this->response($this->json($msg), 200);     
                } else {
                $msg = array("message" => "Something went wrong.");
                $this->response($this->json($msg), 404);
                }
                
                } else {
                $msg['message'] = "Invalid data.";
                $this->response($this->json($msg), 400);
                }
                break;
                
                case 'FILE_TO_USER':
                case 'FILE_TO_GROUP':
                case 'CATALOG_TO_GROUP':
                case 'GROUP_TO_GROUP':*/
                default:
                    $msg['message'] = "Invalid action.";
                    $this->response($this->json($this->_request), 400);
                    break;
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
    }

    public function subGroupToUser($group, $user)
    {
        $db = new DbHandler();
        //$this->userTokenExist($this->_request['tokenuser']);
        //$this->groupTokenExist($this->_request['tokengroup']);
        //$this->pubGroupToUserExist($this->_request['tokengr$this->_request['tokenuser']);
        $status = 'Sub';
        $data = $db->newSubGroupToUser($user, $group, $status);
        //if ($data) {
        //  $msg['message'] = "Already published.";
        //$this->response($this->json($msg), 400);
        //}
        return $data;
    }

    public function subscriptionExist($keyc, $keyuser)
    {
        $db = new DbHandler();
        $data = $db->subscriptionExist($keyc, $keyuser);
        if ($data) {
            $msg['message'] = "Already published.";
            $this->response($this->json($msg), 400);
        }
    }

    public function subscriptionExist2($keyc, $keyuser)
    {
        $db = new DbHandler();
        $data = $db->subscriptionExist($keyc, $keyuser);
        if (!$data) {
            $msg['message'] = "No such subscription or access denied.";
            $this->response($this->json($msg), 400);
        }
    }





    //------------------------------------------------
    //------------------ catalogs --------------------
    //------------------------------------------------

    public function catalogFunction()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.0");
            $this->response($this->json($msg), 404);
        } //$_GET['access_token']

        $response = $this->auth($_GET['access_token']);

        if (isset($this->_request['option']) && isset($response['data']['tokenuser'])) {
            switch ($this->_request['option']) {
                case 'NEW':
                    if (
                        isset($this->_request['catalogname']) && isset($this->_request['dispersemode']) &&
                        isset($this->_request['encryption']) && isset($this->_request['fathers_token'])
                    ) {

                        $catalogname = $this->_request['catalogname'];
                        $catalogname = str_replace(" ", "_", $catalogname);
                        $tokenuser = $response['data']['tokenuser'];
                        $fatherstoken = $this->_request['fathers_token'];
                        $dispersemode = $this->_request['dispersemode'];
                        $encryption = $this->_request['encryption'];
                        $group = $this->_request['group'];
                        $processed = $this->_request['processed'];
                        $db = new DbHandler();
                        $this->validateCatalogExist($catalogname, $tokenuser);
                        //$this->validateGroupExist($groupname,$tokenuser);

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
                                $msg['tokencatalog'] = $tokenC;
                                $this->response($this->json($msg), 201);
                            } else {
                                $msg = array("message" => "Something went wrong.");
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
                                $msg['tokencatalog'] = $tokenC;
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
                    break;
                case 'DELETE':
                    if (isset($this->_request['tokencatalog'])) {
                        $this->tokenCatalogExist($this->_request['tokencatalog'], $response['data']['tokenuser']);
                        $db = new DbHandler();
                        $data = $db->deleteCatalog($this->_request['tokencatalog']);
                        if ($data) {
                            $msg['message'] = "Deleted";
                            $msg['tokencatalog'] = $this->_request['tokencatalog'];
                            $this->response($this->json($msg), 200);
                        } else {
                            $msg = array("message" => "Something went wrong.");
                            $this->response($this->json($msg), 404);
                        }
                    } else {
                        $msg['message'] = "Invalid data.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                case 'MODIFY':
                    if (isset($this->_request['tokencatalog']) && isset($this->_request['catalogname']) && isset($this->_request['dispersemode']) && isset($this->_request['encryption'])) {
                        $this->_request['catalogname'] = str_replace(" ", "_", $this->_request['catalogname']);
                        $this->tokenCatalogExist($this->_request['tokencatalog'], $response['data']['tokenuser']);
                        $db = new DbHandler();
                        $data = $db->modifyCatalog($this->_request['tokencatalog'], $this->_request['catalogname'], $this->_request['dispersemode'], $this->_request['encryption']);
                        if ($data) {
                            $msg['message'] = "Modified";
                            $msg['tokencatalog'] = $this->_request['tokencatalog'];
                            $this->response($this->json($msg), 200);
                        } else {
                            $msg = array("message" => "Something went wrong.");
                            $this->response($this->json($msg), 404);
                        }
                    } else {
                        $msg['message'] = "Invalid data.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                default:
                    $msg['message'] = "Invalid action.";
                    $this->response($this->json($this->_request), 400);
                    break;
            }
        } else {
            $msg['message'] = 'Something went wrong.';
            //$msg['res'] = $response;
            $this->response($this->json($msg), 404);
        }
    }


    public function validateCatalogExist($name, $tokenuser)
    {
        $db = new DbHandler();
        $data = $db->validateCatalogExist($name, $tokenuser);
        if ($data) {
            $msg['message'] = "Cant use this catalog name.";
            $this->response($this->json($msg), 400);
        }
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

    public function tokenCatalogExist($catalog, $user)
    {
        $db = new DbHandler();
        $data = $db->tokenCatalogExist($catalog, $user);
        if (!$data) {
            $msg['message'] = "No such catalog or access denied.";
            $this->response($this->json($msg), 400);
        }
    }


    public function getAllUserCatalogs()
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        //
        if (isset($this->_request['access_token'])) {
            $db = new DbHandler();
            $data = $db->getAllUserCatalogs($this->_request['access_token']);
            if ($data) {
                $res['message'] = "OK";
                $res['data'] = $data;
                $this->response($this->json($res), 200);
            } else {
                $res['message'] = "No data.";
                $res['data'] = array();
                $this->response($this->json($res), 404);
            }
        } else {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 401);
        }
    }

    public function getCatalogsWithAccess()
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        if (isset($this->_request['keyuser'])) {
            $db = new DbHandler();
            $data = $db->getCatalogsWithAccess($this->_request['keyuser']);
            if ($data) {
                $res['message'] = "OK";
                $res['data'] = $data;
                $this->response($this->json($res), 200);
            } else {
                $res['message'] = "No data.";
                $res['data'] = array();
                $this->response($this->json($res), 404);
            }
        } else {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 401);
        }
    }





    public function createCatalog()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        if (isset($this->_request['catalog_name']) && isset($this->_request['dispersemode']) && isset($this->_request['encryption']) && isset($this->_request['father']) && isset($this->_request['keyuser'])) {
            $this->_request['catalog_name'] = str_replace(" ", "_", $this->_request['catalog_name']);
            $db = new DbHandler();
            $data = $db->createCatalog($this->_request);
            switch (true) {
                case ($data == 'COULDNT_CREATE'):
                    $msg['message'] = "Couldn't create.";
                    $this->response($this->json($msg), 400);
                    break;
                case ($data == 'NAME_EXIST'):
                    $msg['message'] = "Catalog name already exist.";
                    $this->response($this->json($msg), 400);
                    break;
                case (is_array($data) == true):
                    if (isset($data['res']) && $data['res'] == 'OK') {
                        $msg['catalog'] = $data['data'];
                        $this->response($this->json($msg), 201);
                    }
                    break;
                default:
                    $msg = array("message" => "Something went wrong.");
                    $this->response($this->json($msg), 400);
                    break;
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 400);
        }
    }

    public function getCatalogsByGroup()
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        if (isset($this->_request['keygroup'])) {
            $db = new DbHandler();
            $data = $db->getCatalogsByGroup($this->_request['keygroup']);
            if ($data) {
                $res['message'] = "OK";
                $res['data'] = $data;
                $this->response($this->json($res), 200);
            } else {
                $res['message'] = "No data.";
                $res['data'] = array();
                $this->response($this->json($res), 404);
            }
        } else {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 401);
        }
    }

    public function get($id)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->getCatalog($id);
        if ($data) {
            $this->response($this->json($data), 200);
        } else {
            $msg = array("message" => "No such catalog: " . $id);
            $this->response($this->json($msg), 404);
        }
    }




    public function edit()
    {
        if ($this->getRequestMethod() != "PUT") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        if (isset($this->_request['name']) && isset($this->_request['type']) && isset($this->_request['dispersemode']) && isset($this->_request['encryption']) && isset($this->_request['catalog_id'])) {
            $response = $this->auth($_GET['access_token']);
            if ($response['code'] == 200) {
                $db = new DbHandler();
                $isowner = $db->isOwnerCatalog($response['data']->user_id);
                $data = $db->editCatalog($this->_request, $response['data']->user_id);
                if ($data && $isowner) {
                    $msg['catalog'] = $this->_request['name'];
                    $this->response($this->json($msg), 200);
                } else {
                    switch (true) {
                        case ($isowner == false):
                            $msg['message'] = "Not Allowed";
                            $this->response($this->json($msg), 400);
                            break;
                        case (is_array($data) == true):
                            $this->response($this->json($data), 400);
                            break;
                        default:
                            $msg['message'] = "Catalog not registered.";
                            $this->response($this->json($msg), 400);
                            break;
                    }
                }
            } else {
                $msg = array("message" => "Access denied. Invalid access token.");
                $this->response($this->json($msg), 401);
            }
        } else {
            print_r($this->request);
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 400);
        }
    }

    public function delete($id)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->deleteCatalog($id);
        if ($data) {
            $msg = array("message" => 'deleted: ' . $id);
            $this->response($this->json($msg), 200);
        } else {
            $msg = array("message" => "No catalog found.");
            $this->response($this->json($msg), 404);
        }
    }

    public function getAvailableCatalogs($keyuser)
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        $db = new DbHandler();
        $data = $db->getAvaliableCatalogs($keyuser);
        if ($data) {
            $res['message'] = "OK";
            $res['data'] = $data;
            $this->response($this->json($res), 200);
        } else {
            $res['message'] = "No data.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
    }

    public function addFileCatalog($id)
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        if (isset($this->_request['keyfile'])) {
            $db = new DbHandler();
            $status = 3;
            $owner = true;
            $data = $db->createCatalogFile($id, $this->_request['keyfile'], $status);
            if ($data) {
                $msg['message'] = "File created successfully.";
                $this->response($this->json($msg), 201);
            } else {
                $msg['message'] = "Failed to register. Please check you data and try again.";
                $this->response($this->json($msg), 400);
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 400);
        }
    }

    public function getCatalogFiles($id)
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        $db = new DbHandler();
        $data = $db->getCatalogFiles($id);
        if ($data) {
            $array = array();
            foreach ($data as $key) {
                // consulta a metadata
                $url = $_ENV['METADATA_HOST'] . '/register/files/' . $key['keyfile'] . '/';
                $curl = new Curl();
                $response = $curl->get($url);
                if ($response['code'] == 200) {
                    array_push($array, $response['data']);
                }
            }
            if ($array) {
                $res['message'] = 'OK';
                $res['data'] = $array;
                $this->response($this->json($res), 200);
            } else {
                $res['message'] = "No files.";
                $res['data'] = array();
                $this->response($this->json($data), 404);
            }
        } else {
            $res['message'] = "No data.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
    }

    public function getCatalogKeyFiles($id)
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        $db = new DbHandler();
        $data = $db->getCatalogFiles($id);
        if ($data) {
            //$array = array();
            //foreach ($data as $key) {
            //    array_push($array,$key['keyfile']);
            //}
            //$msg['message'] = 'OK';
            //$msg['data'] = $data;
            $this->response($this->json($data), 200);
        } else {
            $res['message'] = "No files.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
    }

    public function getUsersByCatalog($id)
    {
        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $response = $this->auth($_GET['access_token']);
        if ($response['code'] == 200) {
            $catalog = new DbHandler();
            $data = $catalog->getUsersByCatalog($id);
            if ($data) {
                $this->response($this->json($this->getUsers($data)), 200);
            } else {
                $msg = array("message" => "No files.");
                $this->response($this->json($msg), 404);
            }
        } else {
            $msg = array("message" => "Access denied. Invalid access token.");
            $this->response($this->json($msg), 401);
        }
    }




    //------------------------------------------------
    //------------------ groups --------------------
    //------------------------------------------------

    public function groupFunction()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $response = $this->auth($_GET['access_token']);
        if (isset($this->_request['option']) && isset($response['data']['tokenuser'])) {
            $tokenuser = $response['data']['tokenuser'];
            switch ($this->_request['option']) {
                case 'NEW':
                    if (isset($this->_request['groupname']) && isset($this->_request['fathers_token'])) {
                        $groupname = $this->_request['groupname'];
                        $groupname = str_replace(" ", "_", $groupname);
                        $isprivate = $this->_request['isprivate'];
                        $fatherstoken = $this->_request['fathers_token'];
                        $db = new DbHandler();
                        $this->validateGroupExist($groupname, $tokenuser);
                        $keygroup = $this->generateToken();
                        $tokenG = $this->generateSHA256Token();
                        if ($fatherstoken == '/') {
                            $data = $db->newGroup($keygroup, $tokenG, $groupname, $tokenuser, $fatherstoken, $isprivate);
                        } else {
                            $father = $this->tokenfatherGExist($fatherstoken);
                            $data = $db->newGroup($keygroup, $tokenG, $groupname, $tokenuser, $father['keygroup'], $isprivate);
                        }
                        if ($data) {
                            $status = 'Owner';
                            $db->insertUsers_Groups($tokenuser, $tokenG, $status);
                            //$msg['message'] = "Created: ".$groupname;                
                            $msg['message'] = "Created";
                            $msg['tokengroup'] = $tokenG;
                            $this->response($this->json($msg), 201);
                        } else {
                            $msg = array("message" => "Something went wrong.");
                            $this->response($this->json($msg), 404);
                        }
                    } else {
                        $msg['message'] = "Invalid data.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                case 'DELETE':
                    if (isset($this->_request['tokengroup'])) {
                        $gpo = $this->tokenGroupExist($this->_request['tokengroup'], $response['data']['tokenuser']);
                        $db = new DbHandler();
                        $del = $db->deleteGroup($this->_request['tokengroup']);
                        //borrar recursivo
                        $child = $this->display_childrenG($gpo['keygroup'], 0);
                        foreach ($child as $k) {
                            $db->deleteGroupByKey($k['keygroup']);
                        }
                        if ($child || $del) {
                            $msg['message'] = "Deleted";
                            $msg['tokengroup'] = $this->_request['tokengroup'];
                            $this->response($this->json($msg), 200);
                        } else {
                            $msg = array("message" => "Something went wrong.");
                            $this->response($this->json($msg), 404);
                        }
                    } else {
                        $msg['message'] = "Invalid data.";
                        $this->response($this->json($msg), 400);
                    }


                    break;
                case 'MODIFY':
                    if (isset($this->_request['tokengroup']) && isset($this->_request['group_name'])) {
                        $this->_request['group_name'] = str_replace(" ", "_", $this->_request['group_name']);
                        $this->tokenGroupExist($this->_request['tokengroup'], $response['data']['tokenuser']);
                        $db = new DbHandler();
                        $data = $db->modifyGroup($this->_request['tokengroup'], $this->_request['group_name']);
                        if ($data) {
                            $msg['message'] = "Modified";
                            $msg['tokengroup'] = $this->_request['tokengroup'];
                            $this->response($this->json($msg), 200);
                        } else {
                            $msg = array("message" => "Something went wrong.");
                            $this->response($this->json($msg), 404);
                        }
                    } else {
                        $msg['message'] = "Invalid data.";
                        $this->response($this->json($msg), 400);
                    }
                    break;
                default:
                    $msg['message'] = "Invalid action.";
                    $this->response($this->json($msg), 400);
                    break;
            }
        } else {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
    }

    public function validateGroupExist($name, $tokenuser)
    {
        $db = new DbHandler();
        $data = $db->validateGroupExist($name, $tokenuser);
        if ($data) {
            $msg['message'] = "Cant use this group name.";
            $this->response($this->json($msg), 400);
        }
    }

    public function tokenfatherGExist($father)
    {
        $db = new DbHandler();
        $data = $db->tokenfatherGExist($father);
        if (!$data) {
            $msg['message'] = "Invalid data.";
            $this->response($this->json($msg), 400);
        } else {
            return $data;
        }
    }

    public function tokenGroupExist($group, $user)
    {
        $db = new DbHandler();
        $data = $db->tokenGroupExist($group, $user);
        if (!$data) {
            $msg['message'] = "No such group or access denied.";
            $this->response($this->json($msg), 400);
        } else {
            return $data;
        }
    }

    private function display_childrenG($parent, $level)
    {
        $db = new DbHandler();
        $data = $db->selectChildrenG($parent);
        $path = array();
        if ($data) {
            foreach ($data as $key) {
                $path[] = $key;
                $path = array_merge($this->display_childrenG($key['keygroup'], $level + 1), $path);
            }
        }
        return $path;
    }






    public function getGroupsWithAccess()
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        $db = new DbHandler();
        $data = $db->getGroupsWithAccess($this->_request['keyuser']);
        if ($data) {
            $res['message'] = "OK";
            $res['data'] = $data;
            $this->response($this->json($res), 200);
        } else {
            $res['message'] = "No data.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
    }


    public function getAvailableGroups($keyuser)
    {
        if ($this->getRequestMethod() != "GET") {
            $res['message'] = "Something went wrong.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
        $db = new DbHandler();
        $data = $db->getAvaliableGroups($keyuser);
        if ($data) {
            $res['message'] = "OK";
            $res['data'] = $data;
            $this->response($this->json($res), 200);
        } else {
            $res['message'] = "No data.";
            $res['data'] = array();
            $this->response($this->json($res), 404);
        }
    }



    public function createGroup()
    {
        if ($this->getRequestMethod() != "POST") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        if (isset($this->_request['namegroup']) && isset($this->_request['keyuser']) && isset($this->_request['father']) && isset($this->_request['ispublic'])) {
            $db = new DbHandler();
            $this->_request['namegroup'] = str_replace(" ", "_", $this->_request['namegroup']);
            if ($db->validateGroup($this->_request['namegroup'], $this->_request['keyuser'])) {
                $keygroup = $this->generateToken();
                $cg = $db->insertIntoGroups($keygroup, $this->_request['namegroup'], $this->_request['father'], $this->_request['ispublic']);
                $rug = $db->relationUsersGroups($this->_request['keyuser'], $keygroup, 2, true, true);

                if ($cg && $rug) {
                    $res['message'] = "Created: " . $keygroup;
                    $this->response($this->json($res), 201);
                } else {
                    $res['message'] = "The group was not created.";
                    $this->response($this->json($res), 400);
                }
            } else {
                $res['message'] = "Group Already Exist";
                $this->response($this->json($res), 400);
            }
        } else {
            $res = array("message" => "Something went wrong.");
            $this->response($this->json($res), 400);
        }
    }

    public function editGroup()
    {
        if ($this->getRequestMethod() != "PUT") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        if (isset($this->_request['name'])) {
            $response = $this->auth($_GET['access_token']);
            if ($response['code'] == 200) {
                $db = new DbHandler();
                $isowner = $db->isOwnerCatalog($response['data']->user_id);
                $data = $db->editCatalog($this->_request, $response['data']->user_id);
                if ($data && $isowner) {
                    $msg['catalog'] = $this->_request['name'];
                    $this->response($this->json($msg), 200);
                } else {
                    switch (true) {
                        case ($isowner == false):
                            $msg['message'] = "Not Allowed";
                            $this->response($this->json($msg), 400);
                            break;
                        case (is_array($data) == true):
                            $this->response($this->json($data), 400);
                            break;
                        default:
                            $msg['message'] = "Catalog not registered.";
                            $this->response($this->json($msg), 400);
                            break;
                    }
                }
            } else {
                $msg = array("message" => "Access denied. Invalid access token.");
                $this->response($this->json($msg), 401);
            }
        } else {
            print_r($this->request);
            $msg = array("message" => "Something went wrong.");
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

    public function notFound()
    {
        $msg = array("Error" => "Not Found.");
        $this->response($this->json($msg), 404);
    }





    //------------------------------------------------
    //------------------ dev only --------------------
    //------------------------------------------------

    public function deleteAllC()
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }

        $db = new DbHandler();
        $data = $db->deleteAllC();
        if ($data) {
            $message = array("deleted" => "All");
            $this->response($this->json($message), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    public function getAllGroups()
    {

        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        //$response = $this->auth($_GET['access_token']);
        //if ($response['code'] == 200) {
        $db = new DbHandler();
        $data = $db->getAllGroups();
        if ($data) {
            $this->response($this->json($data), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
        //} else {
        //   $msg = array("message" => "Access denied. Invalid access token.");
        //   $this->response($this->json($msg), 401);
        //}
    }

    public function getTest()
    {
        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        $db = new DbHandler();
        $data = $db->getTest();
        if ($data) {
            $this->response($this->json($data), 200);
        } else {
            $msg = array("message" => "No data.");
            $this->response($this->json($msg), 404);
        }
    }

    public function deleteGroup($id)
    {
        if ($this->getRequestMethod() != "DELETE") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        //$response = $this->auth($_GET['access_token']);
        //if ($response['code'] == 200) {
        $db = new DbHandler();
        $data = $db->deleteGroup($id);
        if ($data) {
            $msg = array("message" => 'deleted: ' . $id);
            $this->response($this->json($msg), 200);
        } else {
            $msg = array("message" => "No group found.");
            $this->response($this->json($msg), 404);
        }
        //} else {
        //  $msg = array("message" => "Access denied. Invalid access token.");
        //  $this->response($this->json($msg), 401);
        //} 
    }

    public function getAllCatalogs()
    {
        if ($this->getRequestMethod() != "GET") {
            $msg = array("message" => "Something went wrong.");
            $this->response($this->json($msg), 404);
        }
        //$response = $this->auth($_GET['access_token']);
        $db = new DbHandler();
        $data = $db->getAllCatalogs();
        if ($data) {
            //$msg['message'] = 'ok.';        
            //$msg['data'] = $data;        
            $this->response($this->json($data), 200);
        } else {
            $msg['message'] = 'No data.';
            //$msg['data'] = array();
            $this->response($this->json($msg), 404);
        }
    }
}