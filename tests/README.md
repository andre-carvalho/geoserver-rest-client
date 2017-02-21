# Tests for debugging process on Eclipse

## To run the tests it is needed:
- Provide one FTP service and upload raster data and one file as style SLD to one directory of this service. This directory name should used as reference name to make a new layer on GeoServer;
- Have one local GeoServer instance;
- Create a new directory in GEOSERVER_DATA_DIR/coverages/externalData and change permission to system user used in debug can read and write in it;
