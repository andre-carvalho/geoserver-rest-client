<?php
namespace DAO;

use EdwardStock\Curl;
use Log\Log;
use ValueObjects\CoverageStores;
use ValueObjects\CoverageStore;
use ValueObjects\SimpleCoverage;
use ValueObjects\SimpleStyle;
use ValueObjects\LayerDefaultStyle;
use ValueObjects\SimpleLayer;

class Geoserver {
	
	protected $auth = array();
	protected $curl = null;
	protected $logger = null;
	protected $logFile = "/tmp/";
	
	function __construct($user, $pass, $geoserver_url, $workspace_name) {
		$this->logger = new Log($this->logFile);
		
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
	
	private function writeErrorLog($msg="") {
		if(!empty($msg)) {
			$this->logger->log_error($msg);
		}
		if ($this->curl->error) {
			$this->logger->log_error("ErrorCode:".$this->curl->errorCode);
			$this->logger->log_error("ErrorMsg:".$this->curl->errorMessage);
		}
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
			$this->writeErrorLog();
			return false;
		}
		
		if($this->curl->responseHeaders['Status-Line']=="HTTP/1.1 200 OK" && $this->curl->responseHeaders['Content-Type']=="application/json") {
			$jsonResponse = $this->curl->response;
			if(isset($jsonResponse->coverageStores) && isset($jsonResponse->coverageStores->coverageStore)) {
				$coverageStores = new CoverageStores($this->curl->response);
			}else {
				$this->writeErrorLog("No coverages on GeoServer.");
				return false;
			}
		}else {
			$this->writeErrorLog("Failure of response test on getCoverageStores.");
			return false;
		}
		
		return $coverageStores;
	}
	
	/**
	 * Get one coverage store from GeoServer via REST API.
	 * The tipical url is: http://localhost:8080/geoserver/rest/workspaces/ESENSING/coveragestores/reproject.xml
	 * @param $coverageStoreName, The name of the valid Coverage Store
	 * @return object or false: Return ValueObjects\CoverageStore or false otherwise.
	 */
	public function getCoverageStore($coverageStoreName) {
		$coverageStore = null;
	
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores/".$coverageStoreName.".json";
	
		$this->curl->get($URL);
	
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
	
		if($this->curl->responseHeaders['Status-Line']=="HTTP/1.1 200 OK" && $this->curl->responseHeaders['Content-Type']=="application/json") {
			$coverageStore = new CoverageStore($this->curl->response);
		}else {
			$this->writeErrorLog("Failure of response test on getCoverageStore.");
			return false;
		}
	
		return $coverageStore;
	}
	
	public function addCoverageStore($coverageStore) {
		
		if($this->getCoverageStore($coverageStore->name)!==false) {
			$this->logger->log_warn("Coverage store exists!");
			return false;
		}

		$json = $coverageStore->toJSON();
		
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores.json";
		
		$this->curl->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen($json)));

		$this->curl->post($URL, $json);
		
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
		
		return true;
		
	}
	
	public function delCoverageStore($coverageStore) {
		if($this->getCoverageStore($coverageStore->name)===false) {
			$this->logger->log_warn("Coverage store no exists!");
			return false;
		}

		$json = $coverageStore->toJSON();
		
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores/".$coverageStore->name."?recurse=true";

		$this->curl->delete($URL);
		
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
		
		return true;
	}
	
	public function getCoverage($coverageStoreName, $coverageName) {
		$coverage = null;
		
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores/".$coverageStoreName."/coverages/".$coverageName.".json";
		
		$this->curl->get($URL);
		
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
		
		if($this->curl->responseHeaders['Status-Line']=="HTTP/1.1 200 OK" && $this->curl->responseHeaders['Content-Type']=="application/json") {
			$coverage = new SimpleCoverage($this->curl->response);
		}else {
			$this->writeErrorLog("Failure of response test on getCoverage.");
			return false;
		}
		
		return $coverage;
	}
	
	public function addCoverage($coverageStore, $coverage) {
		
		if($this->getCoverageStore($coverageStore->name)===false) {
			$this->logger->log_warn("Coverage store no exists!");
			return false;
		}
		
		if($this->getCoverage($coverageStore->name, $coverage->name)!==false) {
			$this->logger->log_warn("Coverage exists!");
			return false;
		}
		
		$json = $coverage->toJSON();
		
		$request = "rest/workspaces/";
		$URL = $this->auth["geoserver_url"].$request.$this->auth["workspace_name"]."/coveragestores/".$coverageStore->name."/coverages.json";
		
		$this->curl->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen($json)));
		
		$this->curl->post($URL, $json);
		
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
		
		return true;
	}

	public function delCoverage($coverageName) {
		return 'Hello World';
	}
	
	/**
	 * Get the style by name using REST API from GeoServer.
	 * The tipical url is: http://localhost:8080/geoserver/rest/styles/<style_name>.json
	 * @return object or false: Return ValueObjects\SimpleStyle or false otherwise.
	 */
	public function getStyle($styleName) {
		if(!isset($styleName)) {
			$this->writeErrorLog("Parameter styleName is mandatory on getStyle.");
			return false;
		}
		
		$style = null;
	
		$request = "rest";
		$URL = $this->auth["geoserver_url"].$request."/styles/".$styleName.".json";

		$this->curl->get($URL);
	
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
	
		if($this->curl->responseHeaders['Status-Line']=="HTTP/1.1 200 OK" && $this->curl->responseHeaders['Content-Type']=="application/json") {
			$jsonResponse = $this->curl->response;
			if(isset($jsonResponse->style) && isset($jsonResponse->style->name)) {
				$style = new SimpleStyle($jsonResponse->style);
			}else {
				$this->writeErrorLog("No such style ".$styleName." in GeoServer.");
				return false;
			}
		}else {
			$this->writeErrorLog("Failure of response test on getStyle.");
			return false;
		}
	
		return $style;
	}
	
	public function addStyle($style) {
		if($this->getStyle($style->name)!==false) {
			$this->logger->log_warn("Style exists!");
			return false;
		}
		
		$json = $style->toJSON();
		
		$request = "rest";
		$URL = $this->auth["geoserver_url"].$request."/styles";//".$style->name.".json";
		
		$this->curl->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen($json)));
		
		$this->curl->post($URL, $json);
		
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
		
		return true;
	}
	
	public function delStyle($styleName) {
		return 'Hello World';
	}
	
	public function getLayer($layerName) {
		if(!isset($layerName)) {
			$this->writeErrorLog("Parameter layerName is mandatory on getLayer.");
			return false;
		}
	
		$layer = null;
	
		$URL = $this->auth["geoserver_url"]."rest/layers/".$layerName.".json";
	
		$this->curl->get($URL);
	
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
	
		if($this->curl->responseHeaders['Status-Line']=="HTTP/1.1 200 OK" && $this->curl->responseHeaders['Content-Type']=="application/json") {
			$jsonResponse = $this->curl->response;
			if(isset($jsonResponse->layer) && isset($jsonResponse->layer->name)) {
				$layer = new SimpleLayer($jsonResponse->layer);
			}else {
				$this->writeErrorLog("No such layer ".$styleName." in GeoServer.");
				return false;
			}
		}else {
			$this->writeErrorLog("Failure of response test on getLayer.");
			return false;
		}
	
		return $layer;
	}
	
	public function applyStyleToLayer($layer, $defaultStyle) {
		if($this->getStyle($defaultStyle->name)===false) {
			$this->logger->log_warn("Style no exists!");
			return false;
		}
		$layer->defaultStyle=$defaultStyle;
		$json = $layer->toJSON();
		
		$URL = $this->auth["geoserver_url"]."rest/layers/".$layer->name;//.".json";
		
		$this->curl->setOption(CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen($json)));
		
		$this->curl->put($URL, $json);
		
		if ($this->curl->error) {
			$this->writeErrorLog();
			return false;
		}
		
		return true;
	}
}