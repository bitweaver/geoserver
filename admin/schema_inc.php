<?php
$tables = array(
	'geoserver_tilelayers_meta' => "
		tilelayer_id I4 NOTNULL,
		theme_id I4,
		datakey X
			CONSTRAINT ', CONSTRAINT `geoserver_tl_meta_ref` FOREIGN KEY (`tilelayer_id`) REFERENCES `".BIT_DB_PREFIX."gmaps_tilelayers`( `tilelayer_id` )'
	",

	'geoserver_tilelayers_themes' => "
		theme_id I4 PRIMARY,
		theme_title C(64)
	",
);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( GEOSERVER_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( GEOSERVER_PKG_NAME, array(
	'description' => "This package provides hooks between bitweaver and various geoserver services.",
) );

// ### Sequences
$sequences = array (
  'geoserver_tl_theme_id_seq' => array( 'start' => 1 ),
);
$gBitInstaller->registerSchemaSequences( GEOSERVER_PKG_NAME, $sequences );
?>
