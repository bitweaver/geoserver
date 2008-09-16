<?php
/**
 * A straight WWW proxy
 *
 * @package  www
 * @version  $Header: /home/cvs/bwpkgs/geoserver/www_query.php,v 1.1 2008/09/16 18:58:24 waterdragon Exp $
 * @author   nick <nick@sluggardy.net>
 */

require_once( '../bit_setup_inc.php' );

// TODO: Move to a library
/**
 *
 * Outputs an exception document
 *
 * @param string $exception The exception message to send
 */
function www_exception($exception) {
  global $gBitSmarty, $gBitSystem;
  $gBitSmarty->assign('exception', $exception);
  $gBitSystem->fatalError($exception);
}

/**
 *
 * Makes a wfs request to the specified www and outputs the document returned
 *
 * @param string $url The url to send the request to
 * @param string $args Additional parameters to send along in the post (if any)
 */
function www_fetch($url, $args = NULL) {
  global $gBitSystem, $gBitSmarty;

  $query = '?';
  $query_url = $url;
  if( !empty( $args ) ) {
    foreach ($args as $arg => $val) {
      if( $arg == 'www_path' ) {
	$query_url .= $val;
      } else {
	$query .= '&'.$arg.'='.$val;
      }
    }
  }

  if( $query != '?' ) {
    $query_url .= $query;
  }

  // create a new cURL resource
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $query_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  $result = curl_exec($ch);

  if( !$result ) {
    www_exception(curl_error($ch));
  }

  $header = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  if( !empty($header) ) {
    header('Content-Type: ' . $header);
  }

  curl_close($ch);

  // Trick out any URLs in the result
  $new_url = GEOSERVER_PKG_URI.'www';
  $result = str_replace($url, $new_url, $result);

  echo $result;
}

$url = $gBitSystem->getConfig('geoserver_url', 'http://localhost:8080/geoserver/').'www';

global $gBitUser, $gBitSystem;
if( !$gBitUser->isAdmin() ) {
  $gBitSystem->fatalError("You must be logged in to use this interface.");
} else {
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $args = $_POST;
  } else {
    $args = $_GET;
  }
  www_fetch($url, $args);
}
