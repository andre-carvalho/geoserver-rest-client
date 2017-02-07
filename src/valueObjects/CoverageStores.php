<?php
namespace ValueObjects;

use ValueObjects\SimpleCoverageStore;

/**
 * Used in POST process to create or modify the CoverageStores configuration on GeoServer.
 *
 * The $coverages parameter is the list of coverage stores provided by the GET request from GeoServer REST API.
 *
 * January of 2017
 *
 * @author andre
 *
 */
class CoverageStores {
	// list of SimpleCoverageStore objects
	private $coverages=array();
	
	function __construct($jsonResponse) {
		$coverageStores=$jsonResponse->coverageStores->coverageStore;
		
		foreach ($coverageStores as $jsonCoverageStore) {
			$coverageStore = new SimpleCoverageStore($jsonCoverageStore);
			array_push($this->coverages, $coverageStore);
		}
	}
	
	public function toJSON() {
		$json='{"coverageStores":{"coverageStore":';
		$strCoverages='';
		foreach ($this->coverages as $coverage) {
			$strCoverages.=( (!empty($strCoverages))?(','):('') ).$coverage->toJSON();
		}
		$json.='['.$strCoverages.']';
		$json.='}}';
		
		return $json;
	}
}