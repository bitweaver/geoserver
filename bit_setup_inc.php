<?php
define( 'LIBERTY_SERVICE_GEOSERVER', 'geoserver' );

$registerHash = array(
	'package_name' => 'geoserver',
	'package_path' => dirname( __FILE__ ).'/',
	'service' => LIBERTY_SERVICE_GEOSERVER,
);
$gBitSystem->registerPackage( $registerHash );

if( $gBitSystem->isPackageActive( 'geoserver' ) ) {
	require_once( GEOSERVER_PKG_PATH.'geoserver_lib.php' );

	$gLibertySystem->registerService( LIBERTY_SERVICE_GEOSERVER, GEOSERVER_PKG_NAME, array(
		'content_display_function' 	=> 'geoserver_content_gettilelayers',
		'content_edit_function' 	=> 'geoserver_content_gettilelayers',
		'content_store_function' 	=> 'geoserver_content_store',
	) );
}
?>
