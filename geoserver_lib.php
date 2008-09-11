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

function geoserverGetTilelayerByName( &$pParamHash ){
	global $gBitSystem;

	// make sure the name is not too long incase some dumbo uploads a layer with too long a name
	$bindVars = array( substr( $pParamHash['tiles_name'], 0, 64 ) );

	$query = "SELECT gtl.tilelayer_id
			FROM `".BIT_DB_PREFIX."gmaps_tilelayers` gtl
			WHERE gtl.`tiles_name` = ?";

	$ret = $gBitSystem->mDb->getOne( $query, $bindVars );

	return $ret;
}

function geoserverGetTilelayerList( &$pListHash ){
	global $gBitSystem;

	require_once( LIBERTY_PKG_PATH."LibertyContent.php" );

	$bindVars = $ret = array();

	if( empty( $pListHash['sort_mode'] )) {
		$pListHash['sort_mode'] = array( 'gtl.`tiles_name_asc`' );
	}
	@LibertyContent::prepGetList( $pListHash );

	$joinSql = " INNER JOIN `".BIT_DB_PREFIX."geoserver_tilelayers_meta` gtm ON( gtm.`tilelayer_id` = gtl.`tilelayer_id` ) ";
	$selectSql = ", gtm.*";
	$whereSql = "";

	$sql = "SELECT gtl.* $selectSql
			FROM `".BIT_DB_PREFIX."gmaps_tilelayers` gtl $joinSql
			ORDER BY ".$gBitSystem->mDb->convertSortmode( $pListHash['sort_mode'] );

	$result = $gBitSystem->mDb->query( $sql, $bindVars, $pListHash['max_records'], $pListHash['offset'] );

	while( $aux = $result->fetchRow() ) {
		$ret[] = $aux;
	}

	$pListHash['cant'] = $gBitSystem->mDb->getOne( "SELECT COUNT( gtl.`tilelayer_id` ) FROM `".BIT_DB_PREFIX."gmaps_tilelayers` gtl $joinSql $whereSql", $bindVars );

	@LibertyContent::postGetList( $pListHash );

	return $ret;
}

function geoserverVerifyTilelayerMetaData( &$pParamHash ){
	$pParamHash['meta_store'] = array();

	if( isset( $pParamHash['tilelayer_id'] ) ) {
		$pParamHash['meta_store']['tilelayer_id'] = $pParamHash['tilelayer_id'];
	}	
	if( isset( $pParamHash['datakey'] ) ) {
		$pParamHash['meta_store']['datakey'] = $pParamHash['datakey'];
	}	

	return $pParamHash['meta_store'];
}

function geoserverStoreTilelayerMetaData( &$pParamHash ){
	global $gBitSystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pParamHash['tilelayer_id'] ) && geoserverVerifyTilelayerMetaData( $pParamHash ) ) {
		$gBitSystem->mDb->StartTrans();
		
		// If metadata for this tilelayer was stored before delete it then store it new
		$query = "DELETE FROM `".BIT_DB_PREFIX."geoserver_tilelayers_meta` WHERE `tilelayer_id` =?"; 
		$result = $gBitSystem->mDb->query( $query, array( $pParamHash['tilelayer_id'] ) );

		$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."geoserver_tilelayers_meta", $pParamHash['meta_store'] );	

		$gBitSystem->mDb->CompleteTrans();
		$ret = TRUE;		
	}
	return $ret;
}

function geoserverRewriteTilelayerCache() {
	global $gBitSmarty;

	$path = TEMP_PKG_PATH.GEOSERVER_PKG_NAME.'/templates';

	// if the cache folder doesn't exist yet, create it
	if( !is_dir( $path ) ) {
		mkdir_p( $path );
	}

	if( is_dir( $path ) ) {
		$handle = opendir( $path );
		while( false!== ( $cache_file = readdir( $handle ) ) ) {
			if( $cache_file != "." && $cache_file != ".." ) {
				unlink( $path.'/'.$cache_file );
			}
		}

		// get the tilelayers and rewrite the cache
		$list = array( 'max_records' => 999999 );
		$tilelayers = geoserverGetTilelayerList( $list );
		
		$gBitSmarty->assign( 'geoserverTilelayers', $tilelayers );

		$cacheTpls = array( 
			'mapdata.js.tpl' => $gBitSmarty->fetch( GEOSERVER_PKG_PATH.'templates/mapdata.js.tpl' ),
			'tilelayers_menu_inc.tpl' => $gBitSmarty->fetch( GEOSERVER_PKG_PATH.'templates/tilelayers_menu_inc.tpl' ),
			'edit_map_inc.tpl' => $gBitSmarty->fetch( GEOSERVER_PKG_PATH.'templates/edit_map_inc.tpl' ),
		);

		foreach( $cacheTpls as $cacheFile => $cacheString ){
			$h = fopen( $path.'/'.$cacheFile, 'w' );
			if( isset( $h ) ) {
				fwrite( $h, $cacheString );
				fclose( $h );
			} else {
			//	$this->mErrors['write_module_cache'] = tra( "Unable to write to" ).': '.realpath( $cache_file );
			}
		}

	} else {
		// $this->mErrors['chache_rewrite'] = tra( "The cache directory for geoserver doesn't exist." );
	}
	// return( count( $this->mErrors ) == 0 );
}


/********* SERVICE FUNCTIONS *********/
/* DEPRECATED SLATED FOR DELETE
function geoserver_content_display( &$pObject ) {
	global $gBitSystem, $gBitSmarty, $gBitUser;
	if ( $gBitSystem->isPackageActive( 'gmap' ) && $gBitSystem->isPackageActive( 'geoserver' ) ) {
		if( $pObject->getContentType() == 'bitgmap' && $pObject->hasViewPermission() ){
			// @TODO make sure cache tpls exists or write them
			if( is_dir( $path = TEMP_PKG_PATH.GEOSERVER_PKG_NAME.'/templates' ) ) {
				while( false!== ( $cache_file = readdir( $handle ) ) ) {
					if( $cache_file != "." && $cache_file != ".." ) {
						unlink( $path.'/'.$cache_file );
					}
				}
			}
		}
	}
}
*/

?>
