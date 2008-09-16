<?php
require_once( '../../bit_setup_inc.php' );

require_once( '../geoserver_lib.php' );

require_once( GMAP_PKG_PATH.'BitGmap.php' );
$gContent = new BitGmap();

// user must be gmap admin
$gBitSystem->verifyPermission('p_gmap_admin');

// get geoserver data path
// $gsStylesDir = $gBitSystem->getConfig( 'geoserver_data_path' ).'styles/';

// url to graphserver
$tileLayersUrl = 'http://www.tekimaki.com/geoserver/rest/csv/tileLayers';

// get tileLayers XML that has everything we need
$tileLayersXML = geoserverGetXML( $tileLayersUrl );

// for each layer get title, google url, and style url
$layers = $tileLayersXML->getElementsByTagName( 'Layer' );

foreach( $layers as &$layer ){
	$title = $layer->getElementsByTagName( 'Title' )->item(0)->nodeValue;
	$styleUrl = $layer->getElementsByTagName( 'StyleUrl' )->item(0)->nodeValue;
	$tileUrl = $layer->getElementsByTagName( 'GoolgeUrl' )->item(0)->nodeValue;
	// regex off the xyz
	$tileUrl = preg_replace( '/&zoom={Z}&x={X}&y={Y}/', '', $tileUrl );

	// get the styles from which we will make a style key for the tilelayer
	$keyRows = array();

	$styleXML = geoserverGetXML( $styleUrl );

	$rules = $styleXML->getElementsByTagName( 'Rule' );

	foreach( $rules as &$rule ){
		$color = null;
		$range = $rule->getElementsByTagName( 'Title' )->item(0)->nodeValue;
		$cssParams = $rule->getElementsByTagName( 'CssParameter' );
		// lots of checks to make sure we don't trip on xml
		foreach( $cssParams as &$param ){
			if ( $param->hasAttribute( 'name' ) && $param->getAttribute( 'name' ) == 'fill' ){
				$colors = $param->getElementsByTagName( 'Literal' );
				if( $colors->length > 0 ){ 
					$color = $param->getElementsByTagName( 'Literal' )->item(0)->nodeValue;
					break;
				}
			}
		}
		
		$keyRows[] = array( 'color' => $color, 'range' => $range );
	}

	$gBitSmarty->assign( 'keyRows', $keyRows );
	// end get styles 

	// prep tilelayer
	$tilelayerData = array(
		'tiles_name'=>$title,
		'tiles_minzoom'=>0,
		'tiles_maxzoom'=>17,
		'ispng'=>'false',
		'tilesurl'=>$tileUrl,
		'opacity'=>.7,
	);

	// if it already exists we'll update it
	$tilelayerData['tilelayer_id'] = geoserverGetTilelayer( $tilelayerData );

	// store tilelayer		
	if( $tilelayer = $gContent->storeTilelayer( $tilelayerData ) ){
		// store the tilelayer key html
		$gBitSmarty->assign( 'tilelayer', $tilelayer );
		$tilelayer['datakey'] = $gBitSmarty->fetch( GEOSERVER_PKG_PATH.'templates/tilelayer_key.tpl' );
		geoserverStoreTilelayerMetaData( $tilelayer );

		$rslts[] = 'Tile layer "'.$title.'" stored';
	}else{
		$rslts[] = 'Tile layer "'.$title.'" storage FAILED!';
	}

	// store data key html for tile layer
}

// cache various tpls 
geoserverRewriteTilelayerCache();

// report storage results
$centerContent = '';
foreach( $rslts as $rslt ){
	$centerContent .= "<p>".$rslt."</p>";
}
$gBitSmarty->assign( 'centerContent', $centerContent );

$gBitSystem->display( 'bitpackage:geoserver/csv_tilelayers_import.tpl', tra( 'Import Tilelayers' ), array( 'display_mode' => 'admin' ));
?>
