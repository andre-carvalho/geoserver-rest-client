<?php

namespace ValueObjects;

/**
 * Used to record one file SLD Style onto GeoServer.
 * The simple style represents the style object returned from GeoServer REST API as configuration style.
 * The $fileName is a name of SLD file including file extension ".sld".
 * It is assumed that the style directory is in ~GEOSERVER_DATA_DIR/data/styles 
 *
 * February of 2017
 *
 * @author andre
 *
 */
class SimpleStyle {

	private $name,$fileName,$format;

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
			$this->fileName = $jsonResponse->filename;
			$this->format = ( (isset($jsonResponse->format))?($jsonResponse->format):("sld") );
		}
	}

	public function toJSON() {
		$json='{"style":{'.
					'"name":"'.$this->name.'",'.
					'"format":"sld",'.
					'"filename":"'.$this->fileName.'",'.
					'"languageVersion":{"version":"1.0.0"}}'.
				'}';
		return $json;
	}

}