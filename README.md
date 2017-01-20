# js-geoserver-rest-client
Using the JavaScript XMLHTTPREQUEST and GeoServer REST API to create a Web component to upload and configuring a layer on GeoServer based on set of GeoTiffs and one SLD style document.

## Making server-side with PHP
Using recommended PHP scripts to make the server-side process [according the documentation examples](http://docs.geoserver.org/2.8.x/en/user/rest/examples/php.html).

## Dependencies

- This project is organized using Composer and the used version is [1.3.1](https://getcomposer.org/download/1.3.1/composer.phar)
- Other technique used is the [PSR-4 autoload spec](http://www.php-fig.org/psr/psr-4/).
	- If a new path do registered on composer.json in autoload property, use this command [#php composer.phar dumpautoload -o] to update the composer autoload file.