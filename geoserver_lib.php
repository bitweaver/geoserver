<?php

/* Utilities for use in importing geoserver tilelayers into the bitweaver database
 * used in geoserver/admin/csv_tilelayers_import.php
 */
function geoserverGetXML( $pPath ){
	// create a new cURL resource
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $pPath);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$xmlFile = curl_exec($ch);
	curl_close($ch);

	$xml = new DOMDocument();
	$xml->loadXML( $xmlFile );
	return $xml;
}

function geoserverGetMapTypeByName( $pParam ){
	global $gBitSystem;

	// make sure the name is not too long incase some dumbo uploads a layer with too long a name
	$bindVars = array( substr( $pParam['name'], 0, 64 ) );

	$query = "SELECT bmt.maptype_id
			FROM `".BIT_DB_PREFIX."gmaps_maptypes` bmt
			WHERE bmt.`name` = ?";

	$ret = $gBitSystem->mDb->getOne( $query, $bindVars );

	return $ret;
}

function geoserverGetTilelayerByName( $pParam ){
	global $gBitSystem;

	// make sure the name is not too long incase some dumbo uploads a layer with too long a name
	$bindVars = array( substr( $pParam['tiles_name'], 0, 64 ) );

	$query = "SELECT gtl.tilelayer_id
			FROM `".BIT_DB_PREFIX."gmaps_tilelayers` gtl
			WHERE gtl.`tiles_name` = ?";

	$ret = $gBitSystem->mDb->getOne( $query, $bindVars );

	return $ret;
}

function rewriteMapTypeCache() {
	// if the cache folder doesn't exist yet, create it
	if( !is_dir( TEMP_PKG_PATH.GEOSERVER_PKG_NAME.'/templates' ) ) {
		mkdir_p( TEMP_PKG_PATH.GEOSERVER_PKG_NAME.'/templates' );
	}

	if( is_dir( $path = TEMP_PKG_PATH.GEOSERVER_PKG_NAME.'/templates' ) ) {
		$handle = opendir( $path );
		while( false!== ( $cache_file = readdir( $handle ) ) ) {
			if( $cache_file != "." && $cache_file != ".." ) {
				unlink( $path.'/'.$cache_file );
			}
		}

		// get the menus and rewrite the cache, one by one
		$maptypes = $pObject->getMapTypes();
		// @TODO write data to cache file
		$gBitSmarty->assign( 'geoserverMaptypes', $maptypes );

	} else {
		$this->mErrors['chache_rewrite'] = tra( "The cache directory for geoserver doesn't exist." );
	}
	return( count( $this->mErrors ) == 0 );
}


/********* SERVICE FUNCTIONS *********/
function geoserver_content_display( &$pObject ) {
	global $gBitSystem, $gBitSmarty, $gBitUser;
	if ( $gBitSystem->isPackageActive( 'gmap' ) && $gBitSystem->isPackageActive( 'geoserver' ) ) {
		if( $pObject->getContentType() == 'bitgmap' && $pObject->hasViewPermission() ){
			// @TODO make sure cache tpl exists or write it
		}
	}
}

?>
