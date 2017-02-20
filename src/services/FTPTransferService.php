<?php

namespace Services;

use Touki\FTP\Connection\Connection;
use Touki\FTP\FTPFactory;

/**
 *
 * @abstract Provide up level methods to connect on FTP server and copy files of the one directory.
 *          
 *           This class are stateless.
 *          
 * @since February of 2017
 *       
 * @author andre
 *        
 */
class FTPTransferService {
	private static function initConnection($remoteServer) {
		$connection = new Connection ( $remoteServer ['host'], $remoteServer ['user'], $remoteServer ['pass'], $port = 21, $timeout = 20, $passive = false );
		
		try {
			$connection->open ();
			return $connection;
		} catch ( \Exception $e ) {
			return false;
		}
	}
	
	/**
	 *
	 * @param array $remoteServer,
	 *        	Metadata to find FTP server and connect in it.
	 * @param string $localDir,
	 *        	The local directory to copy remote files.
	 * @param boolean $overwrite,
	 *        	if true the local data is overwritten.
	 * @param string $error,
	 *        	return error message
	 * @return boolean, true on success or false otherwise.
	 */
	public static function copyFilesFromRemote($remoteServer, $localDir, $overwrite = false, &$error) {
		$conn = FTPTransferService::initConnection ( $remoteServer );
		
		if ($conn === false || ! $conn->isConnected ()) {
			$error.="Failure to connect to remote server.";
			return false;
		}
		
		$factory = new FTPFactory ();
		
		$ftp = $factory->build ( $conn );
		
		$remoteDir = $ftp->findDirectoryByName ( $remoteServer ['directory'].$remoteServer ["sourceName"] );
		
		if (null === $remoteDir) {
			$conn->close ();
			$error.="No directory in this remote server.";
			return false;
		}
		
		$files = $ftp->findFiles ( $remoteDir );
		
		if (null === $files || ! is_array ( $files ) || ! count ( $files )) {
			$conn->close ();
			$error.="No files in this directory.";
			return false;
		}
		
		if (! is_dir ( $localDir )) {
			if (UtilsService::mkdir ( $localDir ) === false) {
				$error.="Failed to create output directory.";
				return false;
			}
		} elseif (! $overwrite) {
			$conn->close ();
			// Directory exists.
			$error.="Output directory exists.";
			return false;
		} else {
			// Directory exists. Remove old data.
			UtilsService::delTree ( $localDir );
			if (UtilsService::mkdir ( $localDir ) === false) {
				$error.="Failed to create output directory.";
				return false;
			}
		}
		
		foreach ( $files as $file ) {
			$fileRealPath = $file->getRealpath ();
			$fileRealName = basename ( $fileRealPath );
			$fileLocal = $localDir . DIRECTORY_SEPARATOR . $fileRealName;
			
			if (is_file ( $fileLocal ) && $overwrite) {
				if (! unlink ( $fileLocal )) {
					$error.="Failure when remove exist local file.";
					return false;
				}
			}
			
			$execDownload = $ftp->download ( $fileLocal, $file, array (
					$ftp::NON_BLOCKING => true 
			) );
			if ($execDownload === false) {
				$error.="Failure on download this file: " . $fileLocal;
				return false;
			}
		}
		
		$conn->close ();
		
		return true;
	}
}