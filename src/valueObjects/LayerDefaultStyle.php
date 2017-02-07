<?php

namespace ValueObjects;

/**
 * Used to apply one Style to one Layer.
 * The default style represents the fragment of the Layer object returned from GeoServer REST API as configuration Layer.
 * 
 * The DefaultStyle fragment.
 * "defaultStyle": {
 * 		"name": "style name",
 * 		"href": "http://localhost:8080/geoserver/rest/styles/<style_name>.json"
 * }
 *
 * February of 2017
 *
 * @author andre
 *
 */
class LayerDefaultStyle {

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
	
	public function toArray() {
		$a = array("defaultStyle" => array("name" => $this->name));
		return $a;
	}
	
	public function toJSON() {
		$json='"defaultStyle":{'.
					'"name":"'.$this->name.'"'.
					( (isset($this->href) && !empty($this->href))?(',"href":"'.$this->href.'"'):('') ).
				'}';
		return $json;
	}

}