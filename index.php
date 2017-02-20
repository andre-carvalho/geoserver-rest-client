<?php
require_once __DIR__ . '/vendor/autoload.php';

use Services\FTPTransferService;
use Services\FileSystemStoreService;
use Services\GeoserverService;
use Services\UtilsService;
use Configuration\ServiceConfiguration;

/**
 * To run in test mode i use this URLs:
 * http://localhost/publish/imageMosaic/index.php?sourceName=MOD13Q1_pebol_ndvi_evi_nir_h13_v11&ftp_host=geometadata.dpi.inpe.br&ftp_user=esensing&ftp_pass=esensing&ftp_directory=/home/esensing/MOD13Q1_pebol_ndvi_evi_nir_h13_v11
 * http://localhost/publish/imageMosaic/index.php?sourceName=MOD13Q1_pebol_ndvi_evi_nir_h13_v11&useDefaults=true&overwrite=true
 *
 * Optional parameters:
 * overwrite=true because the default behaviour is false.
 * useDefaults=true use the FTP parameters from configurations. See Configuration\ServiceConfiguration class
 */

$params = null;
if ($_GET ["useDefaults"] === "true") {
	$ftpParams = ServiceConfiguration::ftp ();
	$params = UtilsService::evaluateInputParameters ( true );
	$params = array_merge ( $ftpParams, $params );
} else {
	$params = UtilsService::evaluateInputParameters ();
}

// local configuration
$sourceDir = "tmp/" . $params ["sourceName"];
$geoserverDataDir = "/var/lib/tomcat7/webapps/geoserver/data";

$error = "";

if (FTPTransferService::copyFilesFromRemote ( $params, $sourceDir, $params ["overwrite"], $error ) !== true) {
	header ( 'HTTP/1.1 500 Internal Server Error' );
	echo "Failure on copy files from remote server.<br>";
	echo $error;
} else {
	$styleFileDir = FileSystemStoreService::prepareDataToPublish ( $sourceDir, $geoserverDataDir, $params ["overwrite"], $error );
	
	if ($styleFileDir === false) {
		header ( 'HTTP/1.1 500 Internal Server Error' );
		echo "Failure on copy files to GeoServer data dir.<br>";
		echo $error;
	} else {
		
		$geoserver = ServiceConfiguration::geoserver ();
		
		if (GeoserverService::addImageMosaicLayer ( $params ["sourceName"], $geoserver, $styleFileDir, $error )) {
			header ( 'HTTP/1.1 200 OK' );
		} else {
			header ( 'HTTP/1.1 500 Internal Server Error' );
			echo $error;
		}
	}
}