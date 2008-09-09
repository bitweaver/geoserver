<?php
if( !empty( $_REQUEST['geoserver_preferences'] )) {
	$gBitSystem->storeConfig( 'geoserver_data_path', $_REQUEST['geoserver_data_path'], GEOSERVER_PKG_NAME );
}
?>
