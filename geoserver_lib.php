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

function geoserverGetTilelayer( &$pParamHash ){
	global $gBitSystem;

	$ret = NULL;

	$bindVars = array(); $selectSql = ''; $joinSql = ''; $whereSql = '';

	// if a name is passed in we look up the tilelayer by name
	if( !empty( $pParamHash['tiles_name'] ) ){
		// make sure the name is not too long incase some dumbo uploads a layer with too long a name
		array_push( $bindVars, substr( $pParamHash['tiles_name'], 0, 64 ) );
		$whereSql .= " gtl.`tiles_name` = ?";
	// otherwise we get the layer by id and join to get its meta data also 
	}else{
		array_push( $bindVars, $pParamHash['tilelayer_id'] ); 
		$whereSql .= " gtl.`tilelayer_id` = ? ";
	}

	$selectSql .= ", gtm.theme_id, gtm.datakey ";
	$joinSql .= " LEFT JOIN `".BIT_DB_PREFIX."geoserver_tilelayers_meta` gtm ON( gtm.`tilelayer_id` = gtl.`tilelayer_id` ) "; 

	$query = "SELECT gtl.* $selectSql
			FROM `".BIT_DB_PREFIX."gmaps_tilelayers` gtl
			$joinSql
			WHERE $whereSql";

	$result = $gBitSystem->mDb->query( $query, $bindVars );

	if( $result && $result->numRows() ) {
		$ret = $result->fields;
	}

	return $ret;
}

function geoserverGetTilelayerList( &$pListHash ){
	global $gBitSystem;

	require_once( LIBERTY_PKG_PATH."LibertyContent.php" );

	$ret = array();
	
	$bindVars = array(); $selectSql = ''; $joinSql = ''; $whereSql = '';

	if( empty( $pListHash['sort_mode'] )) {
		// $pListHash['sort_mode'] = array( 'gtl.`tiles_name_asc`' );
		$pListHash['sort_mode'] = array( 'gtt.`theme_title_asc`', 'gtl.`tiles_name_asc`' );
	}
	@LibertyContent::prepGetList( $pListHash );

	$joinSql .= " INNER JOIN `".BIT_DB_PREFIX."geoserver_tilelayers_meta` gtm ON( gtm.`tilelayer_id` = gtl.`tilelayer_id` ) " 
				." LEFT JOIN `".BIT_DB_PREFIX."geoserver_tilelayers_themes` gtt ON( gtt.`theme_id` = gtm.`theme_id` ) ";
	$selectSql .= ", gtm.*, gtt.theme_title";

	if( @BitBase::verifyId( $pListHash['theme_id'] )) {
		$whereSql = " WHERE gtm.`theme_id` = ? ";
		$bindVars[] = $pListHash['theme_id'];
	}

	$sql = "SELECT gtl.* $selectSql
			FROM `".BIT_DB_PREFIX."gmaps_tilelayers` gtl $joinSql
			$whereSql
			ORDER BY ".$gBitSystem->mDb->convertSortmode( $pListHash['sort_mode'] );

	$result = $gBitSystem->mDb->query( $sql, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
	
	while( $aux = $result->fetchRow() ) {
		$ret[$aux['tilelayer_id']] = $aux;
	}

	$pListHash['cant'] = $gBitSystem->mDb->getOne( "SELECT COUNT( gtl.`tilelayer_id` ) FROM `".BIT_DB_PREFIX."gmaps_tilelayers` gtl $joinSql $whereSql", $bindVars );

	@LibertyContent::postGetList( $pListHash );

	return $ret;
}

function geoserverGetTilelayerThemes( &$pParamHash ){
	global $gBitSystem;

	$selectSql = "";
	if( !empty( $pParamHash['require_match'] ) ){
		$selectSql .= " INNER JOIN `".BIT_DB_PREFIX."geoserver_tilelayers_meta` gtm ON( gtm.`theme_id` = gtt.`theme_id` ) ";
	}

	return( $gBitSystem->mDb->getAssoc( "
		SELECT gtt.`theme_id`, gtt.`theme_title`
		FROM `".BIT_DB_PREFIX."geoserver_tilelayers_themes` gtt
		$selectSql
		ORDER BY ".$gBitSystem->mDb->convertSortmode( 'theme_title_asc' )
	));
}

function geoserverVerifyTilelayerMetaData( &$pParamHash ){
	$pParamHash['meta_store'] = array();

	if( isset( $pParamHash['tilelayer_id'] ) ) {
		$pParamHash['meta_store']['tilelayer_id'] = $pParamHash['tilelayer_id'];
	}	
	if( isset( $pParamHash['theme_id'] ) ) {
		$pParamHash['meta_store']['theme_id'] = $pParamHash['theme_id'];
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
		//		'mapdata.js.tpl' => $gBitSmarty->fetch( GEOSERVER_PKG_PATH.'templates/mapdata.js.tpl' ),
		//	'tilelayers_menu_inc.tpl' => $gBitSmarty->fetch( GEOSERVER_PKG_PATH.'templates/tilelayers_menu_inc.tpl' ),
		//	'edit_map_inc.tpl' => $gBitSmarty->fetch( GEOSERVER_PKG_PATH.'templates/edit_map_inc.tpl' ),
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


/* make theme data is safe to store
 */
function geoserverVerifyThemes( &$pParamHash ) {
	global $gBitUser, $gBitSystem;

	$pParamHash['themes_store'] = array();

	//need to break up this string
	$themeMixed = isset($pParamHash['themes']) ? $pParamHash['themes'] : NULL;
	if( !empty( $themeMixed )){
		if (!is_array( $themeMixed ) && !is_numeric( $themeMixed ) ){
			$themeIds = explode( ",", $themeMixed );
		}else if ( is_array( $themeMixed ) ) {
			$themeIds = $themeMixed;
		}else if ( is_numeric( $themeMixed ) ) {
			$themeIds = array( $themeMixed );
		}

		foreach( $themeIds as $value ) {
			$value = trim($value);
			/* Ignore empty themes like a trailing , generate */
			if( !empty($value) ) {
				// We always trim tags
				$value = trim($value);
				if ( !empty($value) ) {
					array_push( $pParamHash['themes_store'], array(
									'theme_title' => $value,
									));
				}
				else {
//					$this->mErrors[$value] = "Invalid theme.";
				}
			}
		}
	}

//	return ( count( $this->mErrors ) == 0 );
	return TRUE;
}

function geoserverStoreThemes( &$pParamHash ){
	global $gBitSystem;

	if( geoserverVerifyThemes( $pParamHash ) ) {

		$themetable = BIT_DB_PREFIX."geoserver_tilelayers_themes";
		
		$query = "SELECT gtt.theme_id
				FROM `".BIT_DB_PREFIX."geoserver_tilelayers_themes` gtt
				WHERE gtt.`theme_title` = ?";

		foreach ( $pParamHash['themes_store'] as $hash) {
			// if it doesnt exist already then store it
			if( !$gBitSystem->mDb->getOne( $query, $hash ) ){
				$gBitSystem->mDb->StartTrans();
				$hash['theme_id'] = $gBitSystem->mDb->GenID( 'geoserver_tl_theme_id_seq' );
				$gBitSystem->mDb->associateInsert( $themetable, $hash );
				$gBitSystem->mDb->CompleteTrans();
			}
		}
	}
}


/********* SERVICE FUNCTIONS *********/
/* 
 * universal service func
 * for both content_display and content_edit
 */
function geoserver_content_gettilelayers( &$pObject ) {
	global $gBitSystem, $gBitSmarty, $gBitUser;
	if ( $gBitSystem->isPackageActive( 'gmap' ) && $gBitSystem->isPackageActive( 'geoserver' ) && $pObject->getContentType() == 'bitgmap' && $pObject->hasViewPermission() ){
		$list = array( 'max_records' => 999999 );
		$tilelayers = geoserverGetTilelayerList( $list );
		$gBitSmarty->assign( 'geoserverTilelayers', $tilelayers );

		if(	$tilelayerPref = $pObject->getPreference( 'geoserver_tilelayer_id' ) ){
			$gBitSmarty->assign( 'tilelayerPref', $tilelayerPref );
			$gBitSmarty->assign( 'tilelayerPrefName', $tilelayers[$tilelayerPref]['tiles_name'] );
		}
	}
}

function geoserver_content_store( &$pObject ) {
	global $gBitSystem, $gBitSmarty, $gBitUser;
	if ( $gBitSystem->isPackageActive( 'gmap' ) && $gBitSystem->isPackageActive( 'geoserver' ) && $pObject->getContentType() == 'bitgmap' && $pObject->hasEditPermission() ){
		$pObject->storePreference( 'geoserver_tilelayer_id', !empty( $_REQUEST['geoserver_tilelayer_id'] ) ? $_REQUEST['geoserver_tilelayer_id'] : NULL );
	}
}

?>
