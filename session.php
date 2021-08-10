<?php

require_once __DIR__."/constants.php";

$currentCookieParams = session_get_cookie_params();

// set secure and httponly
/*session_set_cookie_params(
    $currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams["domain"],
    TRUE,
    TRUE
);*/

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
  public static function setRememberCookie($userid, $days=14) {
    $token = bin2hex(random_bytes(32));
    $expire = time() + 3600 * 24 * $days; // Cookie expire time
    $value = $userid.".".$token;
    setcookie('ewl_remember', $value, $expire, '/', $_SERVER['SERVER_NAME'], true);
    return $token;
  }
  public static function getRememberCookie() {
    if ($_COOKIE['ewl_remember']) {
      $tokens = explode(".", $_COOKIE['ewl_remember'], 2);
      print_r($tokens);
      if (count($tokens) !== 2) {
        return false;
      }
      return array('user_id' => $tokens[0], 'token' => $tokens[1]);
    } else {
      return false;
    }
  }
  public static function clearRememberCookie() {
    $past = time() - 3600;
    setcookie('ewl_remember', "", $past, '/', $_SERVER['SERVER_NAME'], true);
  }
  public static function clearAllSession() {
    Session::logout();
    $past = time() - 3600;
    foreach ( $_COOKIE as $key => $value ) {
      setcookie( $key, $value, $past, '/', $_SERVER['SERVER_NAME'], true);
    }
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
  
  public static function setCustomKey($key, $value) {
    $_SESSION[$key] = $value;
  }
  
  public static function getCustomKey($key) {
    return $_SESSION[$key];
  }
  
  public static function clearCustomKey($key) {
    unset($_SESSION[$key]);
  }
}
