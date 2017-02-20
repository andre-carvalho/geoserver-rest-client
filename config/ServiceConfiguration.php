<?php
// ServiceConfiguration.php
namespace Configuration;

class ServiceConfiguration {
	public static function geoserver() {
		$config = array (
				"url" => "http://localhost:8080/geoserver/",
				"workspace" => "ESENSING",
				"user" => "admin",
				"pass" => "geoserver"
		);
		return $config;
	}
	public static function ftp() {
		$config = array (
				'host' => "geometadata.dpi.inpe.br",
				'user' => "esensing",
				'pass' => "esensing",
				'directory' => "/home/esensing/"
		);
		return $config;
	}
}
