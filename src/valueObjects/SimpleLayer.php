<?php

namespace ValueObjects;

use ValueObjects\LayerDefaultStyle;

/**
 * Used in POST process to modify the Layer configuration on GeoServer. 
 * 
 * February of 2017
 *
 * @author andre
 *
 */
/*
 {
	"layer": {
		"name": "layer_name",
		"type": "layer_type",
		"defaultStyle": {
			"name": "style_name",
			"href": "http://localhost:8080/geoserver/rest/styles/<style_name>.json"
		}
	}
}
 */

class SimpleLayer {
	private $name,$type,$defaultStyle;
	
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
			$layer=$jsonResponse;
			$this->name = $layer->name;
			$this->type = $layer->type;
			$this->defaultStyle = new LayerDefaultStyle($layer->defaultStyle);
		}
	}
	
	public function toArray() {
		$a = array("layer" => $this->defaultStyle->toArray());
		return $a;
	}

	public function toJSON() {
		$json = '{"layer":'.
					'{'.
						/* '"name":"'.$this->name.'",'.
						'"type":"'.$this->type.'",'. */
						$this->defaultStyle->toJSON().
					'}'.
				'}';
		return $json;
	}

}