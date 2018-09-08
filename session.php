<?php

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
}
