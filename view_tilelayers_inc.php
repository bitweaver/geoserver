<?php
require_once( '../kernel/setup_inc.php' );

// Is package installed and enabled
$gBitSystem->verifyPackage( 'geoserver' );
$gBitSystem->verifyPackage( 'gmap' );

// Now check permissions to access this page
$gBitSystem->verifyPermission( 'p_gmap_view' );

//if a tilelayer_id is passed try to look it up
if( @BitBase::verifyId( $_REQUEST['tilelayer_id'] )) {
	$XMLContent = "";
	$statusCode = 401;

	if( $result = geoserverGetTilelayer( $_REQUEST )) {
		$statusCode = 200;
		$gBitSmarty->assign_by_ref( 'tilelayerInfo', $result );
	}else{
		$XMLContent = "Requested Tilelayer Not Found";
	}

	$gBitSmarty->assign( 'statusCode', $statusCode);
	$gBitSmarty->assign( 'XMLContent', $XMLContent);

	$gBitSystem->display('bitpackage:geoserver/tilelayer_xml.tpl', null, array( 'format' => 'xml', 'display_mode' => 'display' ));
} else {
	$_REQUEST['max_records'] = $gBitSystem->getConfig( 'max_records' );
	$tilelayers = geoserverGetTilelayerList( $_REQUEST );
	$gBitSmarty->assign( 'geoserverTilelayers', $tilelayers );

	$listHash = array( 'require_match' => TRUE );
	$gBitSmarty->assign( 'themes', geoserverGetTilelayerThemes( $listHash ) );
	$gBitSmarty->assign( 'listInfo', $_REQUEST['listInfo'] );

	$gBitSmarty->display( 'bitpackage:geoserver/view_tilelayers_inc.tpl', tra( 'Map' ));
}
?>
