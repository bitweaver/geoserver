<?php
if ($gBitUser->isAdmin()) {
	if( !empty( $_REQUEST['geoserver_url'] ) ){
		$gBitSystem->storeConfig('geoserver_url', $_REQUEST['geoserver_url'], GEOSERVER_PKG_NAME);
	}
}

if( !empty( $_REQUEST['geoserver_preferences'] )) {
	$gBitSystem->storeConfig( 'geoserver_data_path', $_REQUEST['geoserver_data_path'], GEOSERVER_PKG_NAME );
}

require_once( GEOSERVER_PKG_PATH.'admin/edit_tilelayer_themes.php' );
?>
