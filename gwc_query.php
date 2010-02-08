<?php
/**
 * A straight GWC proxy
 *
 * @package  gwc
 * @version  $Header: /home/cvs/bwpkgs/geoserver/gwc_query.php,v 1.2 2010/02/08 21:37:56 wjames Exp $
 * @author   nick <nick@sluggardy.net>
 */

require_once( '../kernel/setup_inc.php' );

// TODO: Move to a library
/**
 *
 * Outputs an exception document
 *
 * @param string $exception The exception message to send
 */
function gwc_exception($exception) {
  global $gBitSmarty, $gBitSystem;
  $gBitSmarty->assign('exception', $exception);
  $gBitSystem->fatalError($exception);
}

/**
 *
 * Makes a wfs request to the specified gwc and outputs the document returned
 *
 * @param string $url The url to send the request to
 * @param string $args Additional parameters to send along in the post (if any)
 */
function gwc_fetch($url, $args = NULL) {
  global $gBitSystem, $gBitSmarty;

  $query = '?';
  $query_url = $url;
  if( !empty( $args ) ) {
    foreach ($args as $arg => $val) {
      if( $arg == 'file' ) {
	$query_url .= $val;
      } else {
	$query .= '&'.$arg.'='.$val;
      }
    }
  }

  if( $query != '?' ) {
    $query_url .= $query;
  }

  //  vd($query_url);
  //  die;
  // create a new cURL resource
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $query_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  $result = curl_exec($ch);

  if( !$result ) {
    gwc_exception(curl_error($ch));
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

$url = $gBitSystem->getConfig('geoserver_url', 'http://localhost:8080/geoserver/').'gwc';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $args = $_POST;
 } else {
  $args = $_GET;
}
gwc_fetch($url, $args);
