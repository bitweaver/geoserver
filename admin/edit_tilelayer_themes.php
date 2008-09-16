<?php
require_once( GEOSERVER_PKG_PATH.'geoserver_lib.php' );

// user must be gmap admin
$gBitSystem->verifyPermission('p_gmap_admin');

// if changes are submitted process them first
if( isset( $_REQUEST["batch_submit"] ) ){
	if( !empty( $_REQUEST['cancel'] )) {
		// user cancelled - just continue on, doing nothing
	}else{
		foreach( $_REQUEST['tilelayers'] as $tilelayer_id => $theme_id ){
			$storeHash = array( 'tilelayer_id' => $tilelayer_id, 'theme_id' => $theme_id );
			// @TODO this is a little buggy
			geoserverStoreTilelayerMetaData( $storeHash );
		}
	}
}elseif( isset( $_REQUEST["themes_submit"] ) ){
	geoserverStoreThemes( $_REQUEST );	
}

// get list of tilelayers
$_REQUEST['max_records'] = $gBitSystem->getConfig( 'max_records' ) * 5;
$tilelayers = geoserverGetTilelayerList( $_REQUEST );
$gBitSmarty->assign( 'geoserverTilelayers', $tilelayers );

// get list of themes
$listHash = array();
$gBitSmarty->assign( 'geoserverTilelayerThemes', geoserverGetTilelayerThemes( $listHash ) );
// $gBitSmarty->assign( 'listInfo', $_REQUEST['listInfo'] );
?>
