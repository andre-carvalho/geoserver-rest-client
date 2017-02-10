<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Services\FTPTransferService;

$sourceName = "MOD13Q1_pebol_ndvi_evi_nir_h13_v11";
$sourceDir = "/tmp/" . $sourceName;

$remoteServer = array (
		'host' => 'geometadata.dpi.inpe.br',
		'user' => 'esensing',
		'pass' => 'esensing',
		'directory' => '/home/esensing/' . $sourceName 
);

$overwrite = false;

if (FTPTransferService::copyFilesFromRemote ( $remoteServer, $sourceDir, $overwrite ) !== true) {
	echo "Failure on copy files from remote server.";
} else {
	echo "ok";
}