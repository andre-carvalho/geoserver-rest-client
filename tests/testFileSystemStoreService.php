<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Services\FTPTransferService;
use Services\FileSystemStoreService;
use Services\GeoserverService;

$sourceName = "MOD13Q1_pebol_ndvi_evi_nir_h13_v11";
$sourceDir = "/tmp/" . $sourceName;
$geoserverDataDir = "/var/lib/tomcat7/webapps/geoserver/data";

$remoteServer = array (
		'host' => 'geometadata.dpi.inpe.br',
		'user' => 'esensing',
		'pass' => 'esensing',
		'directory' => '/home/esensing/'.$sourceName
);

FTPTransferService::copyFilesFromRemote ( $remoteServer, $sourceDir );

$styleFileDir = FileSystemStoreService::prepareDataToPublish ( $sourceDir, $geoserverDataDir );

$geoserver = array (
		"url" => "http://localhost:8080/geoserver/",
		"workspace" => "ESENSING",
		"user" => "admin",
		"pass" => "geoserver" 
);

GeoserverService::addImageMosaicLayer ( $sourceName, $geoserver, $styleFileDir );