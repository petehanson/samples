<?php

class SurveyDataGateway extends ES {
	
	const SURVEY_LOCATION_ES = "ES";
	const SURVEY_LOCATION_S3 = "S3";
	const SURVEY_LOCATION_BOTH = "both";
	
	
	/**
	 * Constructor. Calls parent constructor and set attributes
	 *
	 * @return void
	 */
	function __construct() {
		parent::__construct();
		$this->index = "surveys";
		$this->type = "survey";
		$this->document_id = 'surveyid';
		$this->version_id = 'revision';
		$this->last_updated = 'lastupdated';
		
		$this->setIndex();
		$this->setType();
	}
	
	/**
	 * This function stores the survey into ES, or S3, or both,
	 * depending of the s3.survey.location property value
	 *
	 */
	public function storeData($surveyID, $revision, $data) {
		$dest = Config::get("s3.survey.location");
		
		$return = array();
		
		$data = $this->insertLastUpdatedTime($data);
		
		if ($dest == self::SURVEY_LOCATION_ES) {
			
			$return = $this->addDocument($data);
			if ($return['status'] == 'succeed') {
				$return['guid'] = $data[$this->document_id];

				$fileLocation = $this->writeJSONFile ($return['guid'], $data);
			}
			
			
		} else if ($dest == self::SURVEY_LOCATION_S3) {
			$fileLocation = $this->writeJSONFile ( $surveyID, $data );
	
			$s3Result = $this->postJSONToS3($fileLocation,$surveyID,$revision);
			
			$return['status'] = 'succeed';
			$return['guid'] = $data[$this->document_id];
			$return['s3_url'] = $s3Result['ObjectURL'];
			
			//unlink($fileLocation);
		} else if ($dest == self::SURVEY_LOCATION_BOTH) {
			$fileLocation = $this->writeJSONFile ( $surveyID, $data );
	
			$s3Result = $this->postJSONToS3($fileLocation,$surveyID,$revision);
			
			$return = $this->addDocument($data);
			
			$return['guid'] = $data[$this->document_id];
			$return['s3_url'] = $s3Result['ObjectURL'];
		}
		
		return $return;
	}
	
	/**
	 * Get all version for documents with same ID from the specified location
	 *
	 * @param string $surveyID survey id
	 * @return array all versions of survey with id = $surveyID
	 */
	public function getAllDocumentsForSearch($surveyID) {
		$location = Config::get("s3.survey.location");
		$data = null;
			
		if ($location == self::SURVEY_LOCATION_ES) {
			$data = $this->getAllDocumentsForSearchFromES($surveyID);
		} else if ($location == self::SURVEY_LOCATION_S3) {
			$data = $this->getAllDocumentsForSearchFromS3($surveyID);
		} else if ($location == self::SURVEY_LOCATION_BOTH) {
			$data = $this->getAllDocumentsForSearchFromS3($surveyID);
		}
		
		return $data;
	}
	
	/**
	 * Get all version for documents with same ID from ES
	 *
	 * @param string $surveyID survey id
	 * @return array all versions of survey with id = $surveyID
	 */
	private function getAllDocumentsForSearchFromES($surveyID) {
	
		$results = $this->_getDocumentGroup($this->document_id, $surveyID);
	
		$resultArray = array();
	
		foreach ($results as $result) {
			$data = $result->getData();
			$surveyId = $data['surveyid'];
			// this will help filter out if get close matching survey Ids. The ones that don't exactly match, won't get added.
			if ($surveyId != $surveyID) {
				continue;
			}
	
			$data['esid'] = $result->getId();
			//$data['esid'] = $data['surveyid'] . '_' . $data['revision'];
			$resultArray[$data[$this->version_id]] = $data;
		}
		ksort($resultArray);
	
		return array_values($resultArray);
	}
	
	/**
	 * Get all version for documents with same ID from S3
	 *
	 * @param string $surveyID survey id
	 * @return array all versions of survey with id = $surveyID
	 */
	private function getAllDocumentsForSearchFromS3($surveyID) {
		$surveysPath = Config::get("app.webroot") . DIRECTORY_SEPARATOR . 'survey_files' . DIRECTORY_SEPARATOR;
		
		//Creation of the S3 client
		
		$clientConfig = array(
				'version'     => Config::get("s3.sdk.version"),
				'region'      => Config::get("s3.region"),
		);
		
		if (Config::get("s3.access.key.ID") && Config::get("s3.secret.access.key")) {
			$credentials = new Aws\Credentials\Credentials(Config::get("s3.access.key.ID"), Config::get("s3.secret.access.key"));
			$clientConfig['credentials'] = $credentials;
		}
		
		$s3 = new Aws\S3\S3Client($clientConfig);
		
		//Retrieve all the keys from the bucket
		$objects = $s3->getIterator('ListObjects', array(
	       	'Bucket' => Config::get("s3.bucket")
	    ));
			
		$filepath = $surveysPath.$surveyID.'_temp.json';
		$documents = array();
		
	    foreach ($objects as $object) {
	    	$key = $object['Key'];
	    	$start = "survey_files/".$surveyID."/";

	    	if ( (substr($key, 0, strlen($start)) === $start) && (substr($key, strlen($key)-5, 5) == ".json")) {
		        //echo $key . "\n";	    		
				// Save object to a file.
				$result = $s3->getObject(array(
						'Bucket' => Config::get("s3.bucket"),
						'Key'    => $key,
						'SaveAs' => $filepath
				));
				if (file_exists($filepath)) {
					$jsonString = file_get_contents($filepath);
					$data = json_decode($jsonString,true);

					if (isset($data['revision'])) {
						$data['esid'] = $data['surveyid'] . "_" . $data['revision'];
					} else {
						$data['esid'] = $data['surveyid'] . "_1";
					}

					//Remove the temp file and add the data to the array
					unlink($filepath);
					array_push($documents, $data);
				} else {
					Throw new Exception("Error opening the expected file ".$filepath." - Result from S3 : ".var_export($result,true));
				}
	    	}

	    }

		return $documents;
	}
	
	/**
	 * Get latest versions of all different documents from the specified location
	 *
	 *
	 * @return array Surveys
	 */
	public function getAllDocuments() {
		$location = Config::get("s3.survey.location");
		$data = null;
			
		if ($location == self::SURVEY_LOCATION_ES) {
			$data = $this->getAllDocumentsFromES();
		} else if ($location == self::SURVEY_LOCATION_S3) {
			$data = $this->getAllDocumentsFromS3();
		} else if ($location == self::SURVEY_LOCATION_BOTH) {
			$data = $this->getAllDocumentsFromS3();
		}

		return $data;
	}
	
	/**
	 * Get last versions of all different documents from ES
	 *
	 *
	 * @return array Surveys
	 */
	private function getAllDocumentsFromES() {
		$query = $this->getQueryObject();
		$query->addSort(array($this->version_id => array('order' => 'desc')));
		$query->setLimit(10000);
	
		$this->es->index->refresh();
		$searchResults = $this->es->index->search($query);
		$results = $searchResults->getResults();
	
		$resultArray = array();
	
		foreach ($results as $result) {
			$data = $result->getData();
			if (!array_key_exists($data[$this->document_id], $resultArray))
				$resultArray[$data[$this->document_id]] = $data;
		}
		ksort($resultArray);
	
		return array_values($resultArray);
	}
	
	/**
	 * Get latest versions of all different documents from S3
	 *
	 *
	 * @return array Surveys
	 */
	private function getAllDocumentsFromS3() {
		$surveysPath = Config::get("app.webroot") . DIRECTORY_SEPARATOR . 'survey_files' . DIRECTORY_SEPARATOR;
	
		//Creation of the S3 client
	
		$clientConfig = array(
				'version'     => Config::get("s3.sdk.version"),
				'region'      => Config::get("s3.region"),
		);
	
		if (Config::get("s3.access.key.ID") && Config::get("s3.secret.access.key")) {
			$credentials = new Aws\Credentials\Credentials(Config::get("s3.access.key.ID"), Config::get("s3.secret.access.key"));
			$clientConfig['credentials'] = $credentials;
		}
	
		$s3 = new Aws\S3\S3Client($clientConfig);
	
		//Retrieve all the keys from the bucket
		$objects = $s3->getIterator('ListObjects', array(
				'Bucket' => Config::get("s3.bucket")
		));
			
		$filepath = $surveysPath.'survey_temp.json';
		$documents = array();
	
		foreach ($objects as $object) {
			$key = $object['Key'];
			$start = "survey_files/";
	
			if ( (substr($key, 0, strlen($start)) === $start) && (substr($key, strlen($key)-5, 5) == ".json")) {
				//echo $key . "\n";
				// Save object to a file.
				$result = $s3->getObject(array(
						'Bucket' => Config::get("s3.bucket"),
						'Key'    => $key,
						'SaveAs' => $filepath
				));
					
				$jsonString = file_get_contents($filepath);
				$data = json_decode($jsonString,true);
				if (isset($data['surveyid']) && isset($data['revision'])) {
					
					$data['esid'] = $data['surveyid'] . "_" . $data['revision'];
					
					//Add the data to the array
					
					//If a previous revision exists in the array, replace it with the new one
					if (count($documents) > 0 && ($documents[count($documents)-1]['surveyid'] == $data["surveyid"] && $documents[count($documents)-1]['revision'] < $data["revision"])) {
						$documents[count($documents)-1] = $data;
					} else {
						array_push($documents, $data);
					}
					
				}
				
				//Remove the temp file
				unlink($filepath);
			}
	
		}
	
		return $documents;
	}
	
	
	public function getDocumentByESId($esid) {
		$location = Config::get("s3.survey.location");
			
		if ($location == self::SURVEY_LOCATION_ES) {
			return $this->getDocumentByESIdFromES($esid);
		} else if ($location == self::SURVEY_LOCATION_S3) {
			return $this->getDocumentByESIdFromS3($esid);
		} else if ($location == self::SURVEY_LOCATION_BOTH) {
			return $this->getDocumentByESIdFromS3($esid);
		}
	}
	
	public function getDocumentByESIdFromS3($esid) {
		if (strpos($esid, '_') !== FALSE) {
			$values = explode("_",$esid);
			$surveyID = $values[0];
			$revision = $values[1];
			
			return $this->getJSONfromS3($surveyID, $revision);
		} else return array();
	}
	
	/**
	 * Delete all documents with same id from the specified location
	 *
	 * @param string $id survey id
	 * @return void
	 */
	public function deleteDocumentGroup($id) {
		$location = Config::get("s3.survey.location");
			
		if ($location == self::SURVEY_LOCATION_ES) {
			$this->deleteDocumentGroupFromES($id);
		} else if ($location == self::SURVEY_LOCATION_S3) {
			$this->deleteDocumentGroupFromS3($id);
		} else if ($location == self::SURVEY_LOCATION_BOTH) {
			$this->deleteDocumentGroupFromES($id);
			$this->deleteDocumentGroupFromS3($id);
		}

	}
	
	/**
	 * Delete all documents with same id from ES
	 *
	 * @param string $id survey id
	 * @return void
	 */
	private function deleteDocumentGroupFromES($id) {
		// get document ids
		// first, get all versions for document
		$results = $this->_getDocumentGroup($this->document_id, $id);
	
		$idsArray = array();
		foreach ($results as $result) {
			$idsArray[] = $result->getId();
		}
		// delete whole group
		if ($idsArray) {
			$this->es->type->deleteIds($idsArray);
		}
	}
	
	/**
	 * Delete all documents with same id from S3
	 *
	 * @param string $id survey id
	 * @return void
	 */
	private function deleteDocumentGroupFromS3($id) {
		//Creation of the S3 client
		
		$clientConfig = array(
				'version'     => Config::get("s3.sdk.version"),
				'region'      => Config::get("s3.region"),
		);
		
		if (Config::get("s3.access.key.ID") && Config::get("s3.secret.access.key")) {
			$credentials = new Aws\Credentials\Credentials(Config::get("s3.access.key.ID"), Config::get("s3.secret.access.key"));
			$clientConfig['credentials'] = $credentials;
		}
		
		$s3 = new Aws\S3\S3Client($clientConfig);
		
		$s3->deleteMatchingObjects(Config::get("s3.bucket"),"survey_files/".$id."/");
 
	}
	
	
	/**
	 *
	 * @param unknown $file 
	 */
	
	/**
	 * 
	 * @param string $file the complete path and filename of the file to transfer
	 * @param string $surveyID the ID of the survey
	 * @param string $revision the revision number
	 * @return unknown
	 */
	private function postJSONToS3($file, $surveyID, $revision) {
		 
		//Creation of the S3 client
	
		$clientConfig = array(
				'version'     => Config::get("s3.sdk.version"),
				'region'      => Config::get("s3.region"),
		);
	
		if (Config::get("s3.access.key.ID") && Config::get("s3.secret.access.key")) {
			$credentials = new Aws\Credentials\Credentials(Config::get("s3.access.key.ID"), Config::get("s3.secret.access.key"));
			$clientConfig['credentials'] = $credentials;
		}
	
		$s3 = new Aws\S3\S3Client($clientConfig);
	
		$destination = 's3://'.Config::get("s3.bucket")."/survey_files";
		 
		$result = $s3->putObject(array(
				'Bucket'       => Config::get("s3.bucket"),
				'Key'          => "survey_files/".$surveyID."/".$revision."/".$surveyID.'.json',
				'SourceFile'   => $file,
				'ContentType'  => 'text/plain',
				'ACL'          => 'public-read',
				'StorageClass' => 'REDUCED_REDUNDANCY'
		));

		$result = $s3->putObject(array(
			'Bucket'       => Config::get("s3.bucket"),
			'Key'          => "survey_files/".$surveyID.'.json',
			'SourceFile'   => $file,
			'ContentType'  => 'text/plain',
			'ACL'          => 'public-read',
			'StorageClass' => 'REDUCED_REDUNDANCY'
		));

		//echo "Survey uploaded and available on this URL: ".$result['ObjectURL']."\n";
		 
		return $result;
	}
	
	
	/**
	 * Retrieves the data of the specified survey from the JSON file located at S3
	 *
	 * @param string $surveyID
	 * @param string $revision the revision number
	 * 
	 * @return the JSON object with the data from the survey
	 */
	private function getJSONfromS3($surveyID, $revision) {
		$surveysPath = Config::get("app.webroot") . DIRECTORY_SEPARATOR . 'survey_files' . DIRECTORY_SEPARATOR;
		
		$filepath = $surveysPath.$surveyID.'_temp.json';
	
		//Creation of the S3 client
	
		$clientConfig = array(
				'version'     => Config::get("s3.sdk.version"),
				'region'      => Config::get("s3.region"),
		);
	
		if (Config::get("s3.access.key.ID") && Config::get("s3.secret.access.key")) {
			$credentials = new Aws\Credentials\Credentials(Config::get("s3.access.key.ID"), Config::get("s3.secret.access.key"));
			$clientConfig['credentials'] = $credentials;
		}
	
		$s3 = new Aws\S3\S3Client($clientConfig);
	
		// Save object to a file.
		$result = $s3->getObject(array(
				'Bucket' => Config::get("s3.bucket"),
				'Key'    => "survey_files/".$surveyID."/".$revision."/".$surveyID.'.json',
				'SaveAs' => $filepath
		));
		 
		$jsonString = file_get_contents($filepath);
		$data = json_decode($jsonString,true);
		if (isset($data['revision'])) {
			$data['esid'] = $data['surveyid'] . "_" . $data['revision'];
		} else {
			$data['esid'] = $data['surveyid'] . "_1";
		}
		
		unlink($filepath);
		 
		return $data;
	}
	
	/**
	 * Retrieves the data of the specified survey from the JSON local file (current revision)
	 *
	 * @param string $surveyID
	 * @return the JSON object with the data from the survey
	 */
	private function getJSONfromLocalFile($surveyID) {
		$surveysPath = Config::get("app.webroot") . DIRECTORY_SEPARATOR . 'survey_files' . DIRECTORY_SEPARATOR;
		$surveyFile = $surveysPath.$surveyID.'.json';
		$jsonString = file_get_contents($surveyFile);
		$data = json_decode($jsonString,true);
		 
		return $data;
	}
	
	/**
	 * 
	 * 
	 * @param doc, data
	 */
	
	/**
	 * Write out the survey to the /survey_files/ folder
	 * 
	 * @param String $surveyID the survey ID
	 * @param Array $data
	 * 
	 * @return string the path of the saved file
	 */
	private function writeJSONFile($surveyID, $data) {
		$path = Config::get("app.webroot") . DIRECTORY_SEPARATOR . 'survey_files' . DIRECTORY_SEPARATOR . $surveyID . '.json';
		$jsonImporter = new JSONImporter(Config::get("app.webroot"));
		file_put_contents($path,$jsonImporter->formatJSON($data));
	
		return $path;
	}
	
	/**
	 * Inserts the last updated time (UTC) into the survey data to be stored,
	 * right after the revision number
	 * 
	 * @param unknown $data
	 * @return multitype:string unknown
	 */
	private function insertLastUpdatedTime($data) {
		$newData = array();
		
		foreach ($data as $key => $value) {
			$newData[$key] = $value;
			if ($key == "revision") {
				$newData[$this->last_updated] = gmdate("M d Y H:i:s", time());
			}
		}
		
		return $newData;
		
	}
}