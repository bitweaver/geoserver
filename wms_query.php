<?php
/**
 * A straight WMS proxy
 *
 * @package  geoserver
 * @version  $Header: /home/cvs/bwpkgs/geoserver/wms_query.php,v 1.1 2008/09/15 22:45:50 waterdragon Exp $
 * @author   spider <nick@sluggardy.net>
 */

require_once( '../bit_setup_inc.php' );

/**
 *
 * Makes a wfs request to the specified geoserver and outputs the document returned
 *
 * @param string $url The url to send the request to
 * @param string $args Additional parameters to send along in the post (if any)
 */
function geoserver_fetch($url, $args = NULL) {
  global $gBitSystem, $gBitSmarty;

  $post = '';
  if( !empty( $args ) ) {
    foreach ($args as $arg => $val) {
      $post = $arg.'='.$val;
    }
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

// TODO: Parameterize these in admin
$url = 'http://localhost:8080/geoserver/wfs';

$args = $_GET;
geoserver_fetch($url, $args);
