<?php
namespace DAO;

use EdwardStock\Curl;
use ValueObjects\CoverageStores;
use ValueObjects\CoverageStore;

class Geoserver {
	
	protected $auth = array();
	protected $curl = null;
	
	function __construct($user, $pass, $geoserver_url, $workspace_name) {
		$this->auth["user"]=$user;
		$this->auth["pass"]=$pass;
		$this->auth["geoserver_url"]=$geoserver_url;
		$this->auth["workspace_name"]=$workspace_name;
		$this->curl = new Curl\Curl();
		$this->curl->setBasicAuthentication($user, $pass);
	}
	
	function __destruct() {
		$this->curl->close();
	}
	
	/**
	 * List all coverage stores from GeoServer via REST API.
	 * The tipical url is: http://localhost:8080/geoserver/rest/workspaces/ESENSING/coveragestores.xml
	 * @return object or false: Return ValueObjects\CoverageStores or false otherwise.
	 */
	public function getCoverageStores() {
		$coverageStores = null;
		
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores.json";

		$this->curl->get($URL);
		
		if ($this->curl->error) {
			// write this code on error log...
			//$this->curl->errorCode;
			return false;
		}
		
		if($this->curl->responseHeaders['Status-Line']=="HTTP/1.1 200 OK" && $this->curl->responseHeaders['Content-Type']=="application/json") {
			$jsonResponse = $this->curl->response;
			$coverageStores = new CoverageStores($this->curl->response);
		}else {
			return false;
		}
		
		return $coverageStores;
	}
	
	/**
	 * Get one coverage store from GeoServer via REST API.
	 * The tipical url is: http://localhost:8080/geoserver/rest/workspaces/ESENSING/coveragestores/reproject.xml
	 * @return object or false: Return ValueObjects\CoverageStore or false otherwise.
	 */
	public function getCoverageStore($coverageName) {
		$coverageStore = null;
	
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores/".$coverageName.".json";
	
		$this->curl->get($URL);
	
		if ($this->curl->error) {
			// write this code on error log...
			//$this->curl->errorCode;
			return false;
		}
	
		if($this->curl->responseHeaders['Status-Line']=="HTTP/1.1 200 OK" && $this->curl->responseHeaders['Content-Type']=="application/json") {
			$coverageStore = new CoverageStore($this->curl->response);
		}else {
			return false;
		}
	
		return $coverageStore;
	}
	
	public function addCoverageStore($coverageStore) {

		$json = $coverageStore->toXML();
		
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores?configure=all";// ".$coverageStore->name.".xml";
		
		$this->curl->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/xml", "Content-Length: " . strlen($json)));

		$this->curl->post($URL, $json);
		
		if ($this->curl->error) {
			// write this code on error log...
			echo "ErrorCode:".$this->curl->errorCode." ErrorMsg:".$this->curl->errorMessage."\n";
			return false;
		}
		
		return true;
		
	}
	
	public function delCoverageStore() {
		return 'Hello World';
	}
	
	public function addCoverageLayer() {
		return 'Hello World';
	}
	
	public function addStyle() {
		return 'Hello World';
	}
	
	public function applyStyleToLayer() {
		return 'Hello World';
	}
}