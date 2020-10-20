<?php

require_once __DIR__."/constants.php";

$currentCookieParams = session_get_cookie_params();

// set secure and httponly
session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams["domain"],
    TRUE,
    TRUE
);

session_start();

class Session {
  public static function isLoggedIn() {
    return isset($_SESSION['current_userid']);
  }
  public static function loggedInUserId() {
    return $_SESSION['current_userid'];
  }
  public static function logUserIn($userid) {
    $_SESSION['current_userid'] = $userid;
  }
  public static function logout() {
    unset($_SESSION['current_userid']);
  }
  public static function pushFlash($key, $val) {
    $_SESSION['flash'][$key] = $val;
  }
  public static function popFlash() {
    $ret = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $ret;
  }
  public static function readFlash() {
    return $_SESSION['flash'];
  }
  public static function createAndSetNonce($id="", $lifetime = Constants::NONCE_LIFETIME) {
    $_SESSION['nonce'.$id] = array('expiry' => time() + $lifetime,
                                   'nonce' => hash('sha512', openssl_random_pseudo_bytes(10)));
    return $_SESSION['nonce'.$id];
  }
  
  public static function readNonce($id="") {
    return $_SESSION['nonce'.$id];
  }
  
  public static function popNonce($id="") {
    $ret = $_SESSION['nonce'.$id];
    unset($_SESSION['nonce'.$id]);
    return $ret;
  }
}
