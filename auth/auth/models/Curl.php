<?php

class Curl {

	public function get($url){
		$ch = curl_init($url);                              
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");          
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		return $result;
	}

	public function post($url, $data_string){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data_string)));                                                        	
		$result = curl_exec($ch);
		return $result;
	}

	public function put($url, $data_string){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',                 'Content-Length: ' . strlen($data_string))
		);                                                          
		$result = curl_exec($ch);
		return $result;
	}

	public function delete($url, $data_string){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
		);                                                   	
		$result = curl_exec($ch);
		return $result;
	}

}
?>