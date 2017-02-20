<?php

namespace Services;

/**
 *
 * @abstract Provide utils methods.
 *          
 *           This class are stateless.
 *          
 * @since February of 2017
 *       
 * @author andre
 *        
 */
class UtilsService {
	
	/**
	 * Remove directory and all internal directories and files recursively.
	 *
	 * @param string $dir,
	 *        	the directory to be removed.
	 * @return boolean, true in success and false otherwise
	 */
	public static function delTree($dir) {
		$files = array_diff ( scandir ( $dir ), array (
				'.',
				'..' 
		) );
		foreach ( $files as $file ) {
			(is_dir ( "$dir/$file" )) ? delTree ( "$dir/$file" ) : unlink ( "$dir/$file" );
		}
		return rmdir ( $dir );
	}
	
	/**
	 * Create one directory and apply 777 access mode.
	 *
	 * @param string $dir,
	 *        	the directory to be created including relative or absolute path.
	 * @return boolean, true in success and false otherwise
	 */
	public static function mkdir($dir) {
		$old_umask = umask ( 0 );
		if (mkdir ( $dir, 0777, true ) === false) {
			return false;
		}
		umask ( $old_umask );
		return true;
	}
	
	/**
	 * Print the instruction message to use this service.
	 */
	public static function missingParametersMsg($msg) {
		header ( "HTTP/1.0 400 Bad Request" );
		echo "<h2>" . $msg . "</h2>";
		echo "<br>";
		echo "Mandatory parameters to request this service are:";
		echo "<br>";
		echo "sourceName, the source directory name where are data on FTP service.";
		echo "<br>";
		echo "ftp_host, the hostname where the data is shared. Do not include prefix ex.: http:// or ftp://";
		echo "<br>";
		echo "ftp_user, the username to access the FTP service.";
		echo "<br>";
		echo "ftp_pass, the password to access the FTP service.";
		echo "<br>";
		echo "ftp_directory, the full path to directory where are the data in FTP server.";
		echo "<br>";
		echo "<h3>* This service allow only the GET method to requests.</h3>";
		echo "<hr>";		
		echo "<br>";
		echo "<b>Optional parameters:</b>";
		echo "<br>";
		echo "overwrite, Used to overwrite all data if sourceName is already exists. The default behaviour is false.";
		echo "<br>";
		echo "useDefaults, To use the FTP parameters from configurations. The default value is false.";
		echo "<br>";

	}
	/**
	 * Validate input parameters provided by user via GET method.
	 * @param boolean $useDefaults, if true, FTP parameters are ignored and default configuration is used.
	 * @return multitype:boolean unknown mixed
	 */
	public static function evaluateInputParameters($useDefaults=false) {
		$params = array ();
		
		if (! isset ( $_GET ["sourceName"] )) {
			UtilsService::missingParametersMsg ( "Missing source name." );
			exit ();
		} else {
			if (preg_match ( "/^[a-z0-9\-_]+$/i", $_GET ["sourceName"] )) {
				$params ["sourceName"] = $_GET ["sourceName"];
			} else {
				UtilsService::missingParametersMsg ( "Invalid source name. Use numeric and alphanumeric characters and can include underscore and Hyphen-minus." );
				exit ();
			}
		}
		
		if (! isset ( $_GET ["overwrite"] )) {
			$params ["overwrite"] = false;
		} else {
			if ( $_GET ["overwrite"] === "true")
				$params ["overwrite"] = true;
			else
				$params ["overwrite"] = false;
		}
		
		if($useDefaults===true) {
			return $params;
		}
		
		if (! isset ( $_GET ["ftp_host"] )) {
			UtilsService::missingParametersMsg ( "Missing FTP host." );
			exit ();
		} else {
			
			$search=array("http://","https://","ftp://","ftps://");
			$ftp_host=str_replace($search, "", strtolower($_GET ["ftp_host"]));
			
			if (! preg_match ( "/(http|https|ftp|ftps)\:\/\/?/", $ftp_host ) ) {
				$params ["host"] = $ftp_host;
			} else {
				UtilsService::missingParametersMsg ( "Invalid ftp hostname. Do not include prefix ex.: http:// or ftp://" );
				exit ();
			}
		}
		
		if (! isset ( $_GET ["ftp_user"] )) {
			UtilsService::missingParametersMsg ( "Missing FTP user name." );
			exit ();
		} else {
			$params ["user"] = $_GET ["ftp_user"];
		}
		
		if (! isset ( $_GET ["ftp_pass"] )) {
			UtilsService::missingParametersMsg ( "Missing FTP password name." );
			exit ();
		} else {
			$params ["pass"] = $_GET ["ftp_pass"];
		}
		
		if (! isset ( $_GET ["ftp_directory"] )) {
			UtilsService::missingParametersMsg ( "Missing FTP directory." );
			exit ();
		} else {
			$params ["directory"] = $_GET ["ftp_directory"];
		}
		return $params;
	}
}