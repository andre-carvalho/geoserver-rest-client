<?php

namespace ValueObjects;

use ValueObjects\SimpleWorkspace;

/**
 * Used in POST process to create or modify the CoverageStore configuration on GeoServer. 
 * 
 * The workspace parameter is the SimpleWorkspace object to compose this object.
 *
 * January of 2017
 *
 * @author andre
 *
 */
class CoverageStore {
	private $name,$description,$type,$enabled,$workspace,$_default,$fileLocation,$coveragesResource;
	
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
	
	function __construct($jsonResponse) {
		$coverageStore=$jsonResponse->coverageStore;
		$this->name = $coverageStore->name;
		$this->description = $coverageStore->description;
		$this->type = $coverageStore->type;// "ImageMosaic"
		$this->enabled = $coverageStore->enabled;// true or false
		$this->workspace = new SimpleWorkspace($coverageStore->workspace);// the workspace related to this coverage store
		$this->_default = $coverageStore->_default;
		$this->fileLocation = $coverageStore->url;// path to directory where are stored the raster files 
		$this->coveragesResource = $coverageStore->coverages;// url to request compose coverages used to current coverage
	}
	
	public function toXML() {
		$xml = '<coverageStore>'.
		'<name>'.$this->name.'</name>'.
		'<description>'.$this->description.'</description>'.
		'<type>'.$this->type.'</type>'.
		'<enabled>'.( ($this->enabled)?('true'):('false') ).'</enabled>'.
		'<workspace>'.
		'<name>ESENSING</name>'.
		'<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="alternate" href="http://localhost:8080/geoserver/rest/workspaces/ESENSING.xml" type="application/xml"/>'.
		'</workspace>'.
		'<__default>'.( ($this->_default)?('true'):('false') ).'</__default>'.
		'<url>'.$this->fileLocation.'</url>'.
		'<coverages>'.
		'<atom:link xmlns:atom="http://www.w3.org/2005/Atom" rel="alternate" href="'.$this->coveragesResource.'" type="application/xml"/>'.
		'</coverages>'.
		'</coverageStore>';
		
		return $xml;
	}

	public function toJSON() {
		$json = '{"coverageStore":'.
				'{"name":"'.$this->name.'",'.
				'"description":"'.$this->description.'",'.
				'"type":"'.$this->type.'",'.
				'"enabled":'.( ($this->enabled)?('true'):('false') ).','.
				'"workspace":{'.$this->workspace->toJSON().'},'.
				'"_default":'.( ($this->_default)?('true'):('false') ).','.
				'"url":"'.$this->fileLocation.'",'.
				'"coverages":"'.$this->coveragesResource.'"}}';
		return $json;
	}

}