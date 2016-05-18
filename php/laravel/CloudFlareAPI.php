<?php

class CloudFlareAPI {
	
	protected $endpoint = "https://api.cloudflare.com/client/v4/";
	
	protected $email;
	protected $apiKey;
	
	/**
	 * 
	 * @param string $email 
	 * @param string $apiKey
	 */
	public function __construct($email, $apiKey) {
		$this->email = $email;
		$this->apiKey = $apiKey;
	}
	
	/**
	 * Search the Zone ID for the specified domain name
	 * 
	 * @param string $domainName the domain name for the Zone
	 * @return string the Zone ID or an empty string if the zone doesn't exist
	 */
	public function getZoneID($domainName) {
		$requestData = array("name" => $domainName);
		
		$response = $this->request("zones", $requestData, "GET");

		$zoneID = "";
		if (isset($response->result[0]) && $response->result[0]->id) {
			$zoneID = $response->result[0]->id;
		} 
		return $zoneID;
	}
	
	/**
	 * Purge all the files in the cache for the specified zone 
	 * 
	 * @param string $zoneID the ID of the zone to purge the cache
	 * @return array with the result info of the operation
	 */
	public function purgeAllCache($zoneID) {
		$requestData = array("purge_everything" => true);
		
		$response = $this->request("zones/".$zoneID."/purge_cache", $requestData, "DELETE");
		
		return $response;
	}
	
	/**
	 * Purge the specified files in the cache for the specified zone
	 * 
	 * @param string $zoneID the zone ID
	 * @param array $files the files to be purged
	 * @return array with the result info of the operation
	 */
	public function purgeCacheFiles($zoneID, $files) {
		$requestData = array("files" => $files);
		
		$response = $this->request("zones/".$zoneID."/purge_cache", $requestData, "DELETE");
		
		return $response;
	}
	
	/**
     * This method send requests using GET, POST, PUT, DELETE OR PATCH
     * 
     * @param string      $path             Path of the endpoint
     * @param array|null  $data             Data to be sent along with the request
     * @param string|null $method           Type of method that should be used ('GET', 'POST', 'PUT', 'DELETE', 'PATCH')
     */
    protected function request($path, array $data = null, $method = null)
    {
        if(!isset($this->email) || !isset($this->apiKey)) {
            throw new Exception('Authentication information must be provided');
            return false;
        }
        $data = (is_null($data) ? array() : $data);
        $method = (is_null($method) ? 'GET' : $method);

        //Removes null entries
        $data = array_filter($data, function ($val) {
            return !is_null($val);
        });
        
        $url = $this->endpoint . $path;
        
        $default_curl_options = array(
            CURLOPT_VERBOSE        => false,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        );
        $curl_options = $default_curl_options;
        if(isset($this->curl_options) && is_array($this->curl_options)) {
            $curl_options = array_replace($default_curl_options, $this->curl_options);
        }
        $headers = array("X-Auth-Email: {$this->email}", "X-Auth-Key: {$this->apiKey}");
        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        if($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else if ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            //curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: PUT'));
        } else if ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            $headers[] = "Content-type: application/json";
        } else if ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            $headers[] = "Content-type: application/json";
        } else {
            $url .= '?' . http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        $http_result = curl_exec($ch);
        $error       = curl_error($ch);
        $information = curl_getinfo($ch);
        $http_code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code != 200) {
            return array(
                'error'       => $error,
                'http_code'   => $http_code,
                'method'      => $method,
                'result'      => $http_result,
                'information' => $information
            );
        } else {
            return json_decode($http_result);
        }
    }
}