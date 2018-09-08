<?php

require_once __DIR__."/model/users.php";
require_once __DIR__."/session.php";

class Roles {

  public static $ROLES_PRIORITY = array("admin", "user");

  public static function userHasPermission($role, $target_user) {
    return array_search($target_user['role'], self::$ROLES_PRIORITY)
                    <= array_search($role, self::$ROLES_PRIORITY);
  }
  
  public static function userHasRole($role, $target_user) {
    return $target_user['role'] === $role;
  }

  public static function hasPermission($role, $userid = FALSE) {
    global $MODEL_users;
    if($userid === FALSE) {
      $userid = Session::loggedInUserId();
    }
    return self::userHasPermission($role, 
        $MODEL_users->runQuery("SELECT role FROM `users` WHERE id='%s'", array($userid))->getRow());
    
  }
  
  public static function isRole($role, $userid = FALSE) {
    global $MODEL_users;
    if($userid === FALSE) {
      $userid = Session::loggedInUserId();
    }
    return self::userHasRole($role, 
        $MODEL_users->runQuery("SELECT role FROM `users` WHERE id='%s'", array($userid))->getRow());
  }

}

