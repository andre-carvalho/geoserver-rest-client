<?php

namespace ValueObjects;

/**
 * Used to compose the CoverageStore object.
 * The simple workspace represents the workspace object returned from GeoServer REST API as part of the CoverageStore object requisition.
 * The href parameter can do use to request more detail of the workspace object.
 *
 * January of 2017
 *
 * @author andre
 *
 */
class SimpleWorkspace {

	private $name,$href;
	
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
	
	function __construct($jsonResponse=null) {
		if(isset($jsonResponse)) {
			$this->name = $jsonResponse->name;
			$this->href = $jsonResponse->href;
		}
	}

	public function toJSON() {
		$json='{"name":"'.$this->name.'","href":"'.$this->href.'"}';
		return $json;
	}

}