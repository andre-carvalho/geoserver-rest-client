<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DAO\Geoserver;
use ValueObjects\CoverageStores;

function print_data($data) {
	if(is_object($data)) $data = print_r($data, true);
	echo "\n";
	echo "#########################################################################";
	echo "\n";
	echo $data;
	echo "\n";
	echo "#########################################################################";
	echo "\n\n";
}

$url = "http://localhost:8080/geoserver/";
$workspace = "ESENSING";

$gs = new Geoserver("admin", "geoserver", $url, $workspace);

// Get all Coverage Stores
$cs=$gs->getCoverageStores();
print_data($cs->toJSON());

// Get one Coverage
$cs=$gs->getCoverageStore("reproject");
print_data($cs->toJSON());

// Change File Location for Coverage
$cs->fileLocation = "file:coverages/uploadData";
$cs->name = "novo_via_rest";
$cs->description = "Meu teste via rest api";

// Using this Object CoverageStore to registry a new Store on GeoServer
$gs->addCoverageStore($cs); 