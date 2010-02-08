<?php
/**
 * A straight REST proxy
 *
 * @package  rest
 * @version  $Header: /home/cvs/bwpkgs/geoserver/rest_query.php,v 1.4 2010/02/08 21:37:56 wjames Exp $
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
function rest_exception($exception) {
  require_once( '../kernel/setup_inc.php' );
  global $gBitSmarty, $gBitSystem;
  $gBitSmarty->assign('exception', $exception);
  $gBitSystem->fatalError($exception);
}

/**
 *
 * Makes a wfs request to the specified rest and outputs the document returned
 *
 * @param string $url The url to send the request to
 * @param string $args Additional parameters to send along in the post (if any)
 */
function rest_fetch($url, $args = NULL) {
  global $gBitSystem, $gBitSmarty;

  $query = '?';
  $query_url = $url;
  if( !empty( $args ) ) {
    foreach ($args as $arg => $val) {
      if( $arg == 'rest_path' ) {
	$query_url .= $val;
      } else {
	$query .= $arg.'='.$val;
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
    rest_exception(curl_error($ch));
  }

  $header = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
  if( !empty($header) ) {
    header('Content-Type: ' . $header);
  }

  curl_close($ch);

  // Trick out any URLs in the result
  $result = str_replace( 'http://localhost:8080/geoserver/', '/geoserver/', $result);

  echo $result;
}

$url = 'http://localhost:8080/geoserver/rest';

if( !$gBitUser->isAdmin() ) {
  $gBitSystem->fatalError("You must be logged in to use this interface.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $args = $_POST;
 } else {
  $args = $_GET;
}
rest_fetch($url, $args);
