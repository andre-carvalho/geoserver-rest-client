<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DAO\Geoserver;
use ValueObjects\CoverageStores;
use ValueObjects\CoverageStore;
use ValueObjects\SimpleWorkspace;
use ValueObjects\SimpleCoverage;
use ValueObjects\SimpleStyle;
use ValueObjects\LayerDefaultStyle;

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
$nameDataDirectory = "uploadData";

$gs = new Geoserver("admin", "geoserver", $url, $workspace);

// Get all Coverage Stores
$cs=$gs->getCoverageStores();
if(is_object($cs)) {
	print_data($cs->toJSON());
}

// Create a new Coverage Store
$cs = new CoverageStore();
$cs->fileLocation = "file:coverages/".$nameDataDirectory;
$cs->name = $nameDataDirectory;
$cs->description = "My CoverageStore via REST";
$cs->type = "ImageMosaic";
$cs->enabled = true;
$cs->workspace = new SimpleWorkspace();
$cs->workspace->name = "ESENSING";

// Using Object CoverageStore to registry a new Store on GeoServer
if($gs->addCoverageStore($cs)===true) {
	echo "The CoverageStore ".$cs->name." was created";
}else {
	echo "Failure to create the coverageStore ".$cs->name;
}

$c = new SimpleCoverage();
$c->name = $nameDataDirectory;
$c->nativeCoverageName = $nameDataDirectory;
$c->title = "My first coverage via REST";

// Using Object Coverage to registry a new Coverage on GeoServer
if($gs->addCoverage($cs, $c)===true) {
	echo "The Coverage ".$c->name." was created";
}else {
	echo "Failure to create the coverage ".$c->name;
}

$style = new SimpleStyle();
$style->name=$nameDataDirectory;
$style->fileName=$nameDataDirectory.".sld";

// Using Object SimpleStyle to registry a new Style on GeoServer
if($gs->addStyle($style)===true) {
	echo "The SLD Style ".$style->name." was created";
}else {
	echo "Failure to create the style ".$style->name;
}

// Get the Layer implicity created when the Coverage is created.
$layerName = $nameDataDirectory;
$layer=$gs->getLayer($layerName);
if($layer!==false) {
	echo "The Layer ".$layer->name." exists.";
	// Change the Default Style to Layer.
	$defaultStyle = new LayerDefaultStyle();
	$defaultStyle->name=$nameDataDirectory;
	if($gs->applyStyleToLayer($layer, $defaultStyle)===true) {
		echo "The Layer default style was changed.";
	}else {
		echo "Failure to change Layer default style.";
	}
	
}else {
	echo "The Layer ".$layerName." no exists.";
}

exit();

// Remove the Coverage Store recursively
if($gs->getCoverageStore($cs->name)!==false && $gs->delCoverageStore($cs)) {
	echo "CoverageStore was removed";
}else {
	echo "Failure on remove the coverageStore";
}
