<?php

namespace Services;

use DAO\Geoserver;
use ValueObjects\CoverageStores;
use ValueObjects\CoverageStore;
use ValueObjects\SimpleWorkspace;
use ValueObjects\SimpleCoverage;
use ValueObjects\SimpleStyle;
use ValueObjects\LayerDefaultStyle;

/**
 *
 * @abstract Provide up level methods to register data onto GeoServer.
 *          
 *           This class are stateless.
 *          
 * @since February of 2017
 *       
 * @author andre
 *        
 */
class GeoserverService {
	
	/**
	 * Register a set geotiffs from one directory as Layer WMS on GeoServer.
	 * If exists a SLD file in this directory, it is used as default style to created layer.
	 *
	 * @param string $sourceName,
	 *        	The name of input data directory. This name is used to the name for all registered itens on GeoServer such as Store, Coverage, Style and Layer.
	 *        	
	 * @param array $geoserver,
	 *        	geoserver config parameters like this: array("url"=>"<geoserver >",
	 *        	"workspace"=>"<output workspace name>",
	 *        	"user"=>"<geoserver admin user>",
	 *        	"pass"=>"<geoserver password>")
	 * @param string $error,
	 *        	return error message
	 * @return boolean, true on success or false otherwise.
	 */
	public static function addImageMosaicLayer($sourceName, $geoserver, $styleFileDir, &$error) {
		if (empty ( $sourceName )) {
			echo ("Location of input data is missing.");
			return false;
		}
		if (empty ( $geoserver )) {
			echo ("Missing geoserver configuration.");
			return false;
		}
		
		$logDir = "log/geoserver_rest";
		$gs = new Geoserver ( $geoserver ["user"], $geoserver ["pass"], $geoserver ["url"], $geoserver ["workspace"], $logDir );
		
		// Create a new Coverage Store
		$cs = new CoverageStore ();
		$cs->fileLocation = "file:coverages/externalData/" . $sourceName;
		$cs->name = $sourceName;
		$cs->description = $sourceName . " CoverageStore via REST";
		$cs->type = "ImageMosaic";
		$cs->enabled = true;
		$cs->workspace = new SimpleWorkspace ();
		$cs->workspace->name = $geoserver ["workspace"];
		
		// Using Object CoverageStore to registry a new Store on GeoServer
		if ($gs->addCoverageStore ( $cs ) === false) {
			echo ("Failure to create the coverageStore " . $cs->name);
			return false;
		}
		
		$c = new SimpleCoverage ();
		$c->name = $sourceName;
		$c->nativeCoverageName = $sourceName;
		$c->title = "Coverage " . $sourceName . " via REST";
		
		// Using Object Coverage to registry a new Coverage on GeoServer
		if ($gs->addCoverage ( $cs, $c ) === false) {
			echo ("Failure to create the coverage " . $cs->name);
			return false;
		}
		
		$layer = $gs->getLayer ( $sourceName );
		if ($layer === false) {
			echo ("Failure to create the Layer " . $sourceName);
			return false;
		}
		
		$style = new SimpleStyle ();
		$style->name = $sourceName;
		$style->fileName = $sourceName . ".sld";
		
		// Using Object SimpleStyle to registry a new Style on GeoServer
		if ($gs->addStyle ( $style, $styleFileDir ) === false) {
			echo ("Failure to create the style " . $style->name);
			return false;
		}
		
		// Change the Default Style to Layer.
		$defaultStyle = new LayerDefaultStyle ();
		$defaultStyle->name = $sourceName;
		if ($gs->applyStyleToLayer ( $layer, $defaultStyle ) === false) {
			echo ("Failure to apply the style over the layer " . $sourceName);
			return false;
		}
		return true;
	}
}