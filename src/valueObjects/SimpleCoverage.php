<?php

namespace ValueObjects;

/**
 * Used in POST process to create or modify the Coverage configuration on GeoServer. 
 * 
 * February of 2017
 *
 * @author andre
 *
 */
class SimpleCoverage {
	private $name,$title,$nativeCoverageName;
	
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
			$coverage=$jsonResponse->coverage;
			$this->name = $coverage->name;
			$this->nativeCoverageName = $coverage->nativeCoverageName;
			$this->title = $coverage->title;
		}
	}

	public function toJSON() {
		$json = '{"coverage":'.
					'{'.
						'"title":"'.$this->title.'",'.
						'"name":"'.$this->name.'",'.
						'"nativeCoverageName":"'.$this->name.'",'.
						'"metadata": {'.
							'"entry": [{'.
								'"@key": "time",'.
								'"dimensionInfo": {'.
									'"enabled": true,'.
									'"presentation": "LIST",'.
									'"units": "ISO8601",'.
									'"defaultValue": ""'.
								'}'.
							'}]'.
						'},'.
					'}'.
				'}';
		return $json;
	}

}