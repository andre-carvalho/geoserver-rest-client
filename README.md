# js-geoserver-rest-client
Using the PHP with cURL and FTP classes over GeoServer REST API to create a Web service that copy files from remote FTP Server and configure a layer on GeoServer based on set of GeoTiffs and one SLD style document.

## Making server-side with PHP
Using recommended PHP scripts to make the server-side process [according the documentation examples](http://docs.geoserver.org/2.8.x/en/user/rest/examples/php.html).

## Dependencies

- This project is organized using Composer and the used version is [1.3.1](https://getcomposer.org/download/1.3.1/composer.phar)
- Other technique used is the [PSR-4 autoload spec](http://www.php-fig.org/psr/psr-4/).
	- If a new path do registered on composer.json in autoload property, use this command [#php composer.phar dumpautoload -o] to update the composer autoload file.
	
## Installation (tested in Linux - Ubuntu 14.04)

The expected environment to deployment is composed for:
- Apache 2 HTTPD Server
- PHP 5

  -Install curl module on php.
  
  ```
  apt-get install php5-curl
  ```
  
  -Install the php composer on root directory of the project.
  
  ```
  wget https://getcomposer.org/download/1.3.2/composer.phar
  ```
- Tomcat 7
- GeoServer 2.8.2
  - Make a new directory in $GEOSERVER_DATA_DIR/coverages/externalData and change permission to apache user can write in it. Apache user is user of the system used to run the apache server, usually www-data on Ubuntu.


### Installing dependecies from composer.json
 - To install the defined dependencies for project, just run the install command.
 
  ```
  php composer.phar install
  ```
  
  ~~note: We will need a little change on one line of the Curl.php code from php-curl-class after install because the put method don't work for me. I will change it using the correct way on a second moment. On file Curl.php in line 307, where we see *http_build_query($data)* i changed to $this->postFields($data)~~
  
  I made it of the correct form. Extended Curl class.
