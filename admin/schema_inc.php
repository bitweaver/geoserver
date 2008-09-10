<?php
$tables = array(
  'geoserver_tilelayers_meta' => "
	tilelayer_id I4 NOTNULL,
	datakey X
		CONSTRAINT ', CONSTRAINT `geoserver_perm_name_ref` FOREIGN KEY (`tilelayer_id`) REFERENCES `".BIT_DB_PREFIX."gmap_tilelayers`( `tilelayer_id` )'
  "
);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( GEOSERVER_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( GEOSERVER_PKG_NAME, array(
	'description' => "This package provides hooks between bitweaver and various geoserver services.",
) );
?>
