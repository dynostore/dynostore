<?php

  class Curl {

    public function get($url){
      $curl = curl_init($url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false); 	            
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      // when the response data is in JSON convert them to array
      $response = $this->jsonToArray($response);

      $result = array('code' => $httpCode, 'data' => $response);
      curl_close($curl);
      return $result;
    }

    public function post($url, $data){
      $curl = curl_init();  
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false); 
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));    
      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      // when the response data is in JSON convert them to array
      $response = $this->jsonToArray($response);

      $result = array('code' => $httpCode, 'data' => $response);
      curl_close($curl);
      return $result;
    }

    public function put($url, $data){
      $curl = curl_init();  
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT"); 
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false); 
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));;
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      // when the response data is in JSON convert them to array
      $response = $this->jsonToArray($response);

      $result = array('code' => $httpCode, 'data' => $response);
      curl_close($curl);
      return $result;
    }

    public function delete($url, $data){
      $curl = curl_init();  
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); 
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HEADER, false); 
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
      $response = curl_exec($curl);
      $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      // when the response data is in JSON convert them to array
      $response = $this->jsonToArray($response);

      $result = array('code' => $httpCode, 'data' => $response);
      curl_close($curl);
      return $result;
    }

    public function jsonToArray($data) {
        return json_decode($data, true);
    }

  }

?>