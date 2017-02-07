<?php
namespace ValueObjects;

/**
 * Used to compose the CoverageStores object.
 * The simple coverage store represents the coverageStore object returned from GeoServer REST API as part of the CoverageStores object requisition.
 * The href parameter can do use to request more detail of the coverageStore object.
 * 
 * January of 2017 
 * 
 * @author andre
 *
 */
class SimpleCoverageStore {

	private $name,$href;
	
	function __construct($jsonCoverageStore) {
		$this->name = $jsonCoverageStore->name;
		$this->href = $jsonCoverageStore->href;
	}
	
	public function __get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}

	public function __set($property, $value) {
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
		
		return $this;
	}
	
	public function toJSON() {
		$json='{"name":"'.$this->name.'","href":"'.$this->href.'"}';
		return $json;
	}

}