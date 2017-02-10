<?php

namespace Services;

/**
 *
 * @abstract Provide up level methods to move files to data directory into GeoServer.
 *          
 *           This class are stateless.
 *          
 * @since February of 2017
 *       
 * @author andre
 *        
 */
class FileSystemStoreService {
	
	/**
	 * To copy input data to geoserver data dir and create property files used to configure a imageMosaic layer.
	 *
	 * @param string $sourceDir,
	 *        	the complete path to directory where are input data.
	 * @param string $geoserverDataDir,
	 *        	the complete path of the geoserver data directory.
	 * @param boolean $overwrite,
	 *        	if true the local data is overwritten.
	 * @param string $error,
	 *        	return error message       	
	 * @return string, The path and file name of the style file to use in upload process via GeoServer REST API.
	 */
	public static function prepareDataToPublish($sourceDir, $geoserverDataDir, $overwrite = false, &$error) {
		if (! is_dir ( $sourceDir )) {
			die ( 'Failed to load input directory.' );
		}
		$inputResources = explode ( DIRECTORY_SEPARATOR, $sourceDir );
		$resourceName = $inputResources [count ( $inputResources ) - 1];
		// create new directory into geoserver data dir to store the geotiffs
		$outputDataDir = $geoserverDataDir . "/coverages/externalData/" . $resourceName;
		if (! is_dir ( $outputDataDir )) {
			if (UtilsService::mkdir ( $outputDataDir ) === false) {
				$error.="Failed to create output directory on GeoServer.";
				return false;
			}
		} elseif (! $overwrite) {
			// if exists, Don't remove old data.
			$error.="Output directory exists. Resend request including the parameter overwrite=true";
			return false;
		} else {
			// if exists, remove old data.
			UtilsService::delTree($outputDataDir);
			
			if (UtilsService::mkdir ( $outputDataDir ) === false) {
				$error.="Failed to create output directory on GeoServer.";
				return false;
			}
		}
		
		$styleFile = "";
		
		// move geotiffs to output data directory into geoserver data dir
		if ($handle = opendir ( $sourceDir )) {
			while ( false !== ($entry = readdir ( $handle )) ) {
				if ($entry != "." && $entry != "..") {
					// echo $entry."\n";
					if (mime_content_type ( $sourceDir . DIRECTORY_SEPARATOR . $entry ) === "application/xml") { // style file, sld
						$styleFile = $sourceDir . DIRECTORY_SEPARATOR . $resourceName . ".sld";
						if (! rename ( $sourceDir . DIRECTORY_SEPARATOR . $entry, $styleFile )) {
							$error.="Failure when copy file:" . $entry;
							return false;
						}
					} elseif (mime_content_type ( $sourceDir . DIRECTORY_SEPARATOR . $entry ) === "image/tiff") { // geotiff image file
						if (! copy ( $sourceDir . DIRECTORY_SEPARATOR . $entry, $outputDataDir . DIRECTORY_SEPARATOR . $entry )) {
							$error.="Failure when copy file:" . $entry;
							return false;
						}
					}
				}
			}
			closedir ( $handle );
		}
		
		FileSystemStoreService::generateImageMosaicPropertyFiles ( $outputDataDir );
		
		return $styleFile;
	}
	
	/**
	 * Generates the configuration files, indexer.properties and timeregex.properties to GeoServer work with temporal layer.
	 *
	 * @param string $outputDir,
	 *        	The directory to write files.
	 */
	private static function generateImageMosaicPropertyFiles($outputDir) {
		$indexer = fopen ( $outputDir . DIRECTORY_SEPARATOR . "indexer.properties", "w" ) or die ( "Unable to open indexer.properties file!" );
		$txt = "TimeAttribute=time\n";
		fwrite ( $indexer, $txt );
		$txt = "Schema= the_geom:Polygon,location:String,time:java.util.Date\n";
		fwrite ( $indexer, $txt );
		$txt = "PropertyCollectors=TimestampFileNameExtractorSPI[timeregex](time)";
		fwrite ( $indexer, $txt );
		fclose ( $indexer );
		
		$timeregex = fopen ( $outputDir . DIRECTORY_SEPARATOR . "timeregex.properties", "w" ) or die ( "Unable to open timeregex.properties file!" );
		$txt = "regex=[0-9]{4}";
		fwrite ( $timeregex, $txt );
		fclose ( $timeregex );
	}
}