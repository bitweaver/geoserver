<?php
/**
 * Makes a WFS query easier to do.
 *
 * @package  geoserver
 * @version  $Header: /home/cvs/bwpkgs/geoserver/wfs_query.php,v 1.6 2008/09/16 18:58:24 waterdragon Exp $
 * @author   spider <nick@sluggardy.net>
 */

require_once( '../bit_setup_inc.php' );

/**
 *
 * Outputs an exception document
 *
 * @param string $exception The exception message to send
 */
function geoserver_exception($exception) {
  global $gBitSmarty, $gBitSystem;
  $gBitSmarty->assign('exception', $exception);
  $gBitSystem->display('bitpackage:geoserver/wfs_exception.tpl', '', array( 'format' => 'xml'  ));
}

/**
 *
 * Makes a wfs request to the specified geoserver and outputs the document returned
 *
 * @param string $url The url to send the request to
 * @param string $request The WFS Request to make
 * @param string $args Additional parameters to send along in the post (if any)
 * @param string $filter The filter to render and send (if any). This will load wfs_$filter.tpl
 * @param string $format The format desired for the result. Defaults to GML2.
 */
function geoserver_fetch($url, $request, $args = NULL, $filter = FALSE, $format = 'GML2') {
  global $gBitSystem, $gBitSmarty;

  $post = 'SERVICE=WFS&VERSION=1.0.0&REQUEST='.$request;

  $post .= '&OUTPUTFORMAT='.$format;

  $query_url = $url;

  if( !empty( $args ) ) {
    foreach ($args as $arg => $val) {
      if ($filter && strtolower($arg) == 'bbox') {
	$gBitSmarty->assign('bbox', $val);
      } elseif ($filter && strtolower($arg) == 'filter') {
	$f = preg_replace('/<\/?filter\s*>/i','',$val);
	$gBitSmarty->assign('filter', $f);
      } elseif (strtolower($arg) == 'wfs_path') {
	$query_url .= $val;
      } else {
	$post .= '&'.$arg.'='.$val;
      }
    }
  }

  // Get the filter to post
  if( $filter ) {
    $post .= "&FILTER=".$gBitSmarty->fetch('bitpackage:geoserver/'.$filter);
  }

  if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $query_url .= '?'.$post;
  }
  
  // create a new cURL resource
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $query_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  }
  $result = curl_exec($ch);

  if( !$result ) {
    geoserver_exception(curl_error($ch));
  }

  $header = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  if( !empty($header) ) {
    header('Content-Type: ' . $header);
  }

  curl_close($ch);

  // Trick out any URLs in the result
  $result = str_replace($gBitSystem->getConfig('geoserver_url', 'http://localhost:8080/geoserver/'), GEOSERVER_PKG_URI, $result);

  echo $result;
}


// Make sure the request isn't empty
if( empty( $_REQUEST['request'] ) ) {

  geoserver_exception('No request specified.');

} else {
  
  $url = $gBitSystem->getConfig('geoserver_url', 'http://localhost:8080/geoserver/').'wfs';
  $namespace = $gBitSystem->getConfig('geoserver_namespace', 'geotest');

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $args = $_POST;
  } else {
    $args = $_GET;
  }

  // Remove the query from the arguments
  unset($args['request']);

  switch( $_REQUEST['request'] ) {
  case 'GetFeature':
    // Validate we have a type name
    if( empty($args['typename'] ) ) {
      geoserver_exception('No type name specified.');

    } elseif( strstr($args['typename'], 'liberty') ) {
      // Validate the namespace
      if( substr($args['typename'], 0, strlen($namespace) + 1) != $namespace.':') {
	geoserver_exception('Permision denied while trying to request type name: ' . $args['typename']);
      } else {
	geoserver_fetch($url, 'GetFeature', $args, 'wfs_liberty_filter.tpl');
      }
    } else {
      geoserver_fetch($url, 'GetFeature', $args);
    }    
    break;
  case 'DescribeFeatureType':
  case 'GetCapabilities':
    geoserver_fetch($url, $_REQUEST['request']);
    break;
  default:
    geoserver_exception('Invalid request specified: ' . $_REQUEST['request']);
  }
}
