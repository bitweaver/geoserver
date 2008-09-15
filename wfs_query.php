<?php
/**
 * Makes a WFS query easier to do.
 *
 * @package  geoserver
 * @version  $Header: /home/cvs/bwpkgs/geoserver/wfs_query.php,v 1.3 2008/09/15 22:34:19 waterdragon Exp $
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

  if( !empty( $args ) ) {
    foreach ($args as $arg => $val) {
      if ($filter && strtolower($arg) == 'bbox') {
	$gBitSmarty->assign('bbox', $val);
      } elseif ($filter && strtolower($arg) == 'filter') {
	$f = preg_replace('/<\/?filter\s*>/i','',$val);
	$gBitSmarty->assign('filter', $f);
      } else {
	$post .= '&'.$arg.'='.$val;
      }
    }
  }

  // Get the filter to post
  if( $filter ) {
    $post .= "&FILTER=".$gBitSmarty->fetch('bitpackage:geoserver/'.$filter);
  }
  
  // create a new cURL resource
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
  $result = curl_exec($ch);

  if( !$result ) {
    geoserver_exception(curl_error($ch));
  }

  curl_close($ch);

  // Trick out any URLs in the result
  $new_url = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
  $result = str_replace($url, $new_url, $result);

  $gBitSmarty->assign('result', $result);
  $gBitSystem->display('bitpackage:geoserver/wfs_result.tpl', '', array( 'format' => 'xml' ));

}


// Make sure the request isn't empty
if( empty( $_REQUEST['request'] ) ) {

  geoserver_exception('No request specified.');

} else {

  // TODO: Parameterize these in admin
  $url = 'http://localhost:8080/geoserver/wfs';
  $namespace = 'geotest';

  if( empty($_REQUEST['args']) ) {
    $args = array();
  } elseif( !is_array($_REQUEST['args']) ) {
    geoserver_exception('Invalid request. Args must be an array!');
  } else {
    $args = $_REQUEST['args'];
  }

  $args = $_GET;
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
