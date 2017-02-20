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

$url = "http://terrabrasilis.info/esensing/";
$workspace = "E-SENSING";
$nameDataDirectory = "MOD13Q1_pebol_ndvi_evi_nir_h13_v11";

$gs = new Geoserver("admin", "geoserver", $url, $workspace);

/*
$cs=$gs->getCoverageStore($nameDataDirectory);

if($cs!==false) {
	echo "CoverageStore exists!";
	echo "\n";
	print_data($cs->toJSON());
}else {
	echo "Failure on get the coverageStore";
}

exit();
*/
// Get all Coverage Stores
$cs=$gs->getCoverageStores();
if(is_object($cs)) {
	print_data($cs->toJSON());
}

// Create a new Coverage Store
$cs = new CoverageStore();
$cs->fileLocation = "file:coverages/externalData/".$nameDataDirectory;
$cs->name = $nameDataDirectory;
$cs->description = "My CoverageStore via REST";
$cs->type = "ImageMosaic";
$cs->enabled = true;
$cs->workspace = new SimpleWorkspace();
$cs->workspace->name = $workspace;

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
if($gs->addStyle($style, "/var/www/publish/imageMosaic/tmp/MOD13Q1_pebol_ndvi_evi_nir_h13_v11/MOD13Q1_pebol_ndvi_evi_nir_h13_v11.sld")===true) {
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
