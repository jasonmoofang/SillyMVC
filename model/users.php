<?php

require_once __DIR__.'/db.php';


class Users extends DB {
  public function __construct() {
    parent::__construct();
    $this->table_name = "users";
  }
  
  public function tryAuthenticate($params) {
    $res = $this->runQuery("SELECT * FROM `".$this->table_name."`
                            WHERE email='%s' AND password_hash=MD5('%s');",
                              array($params['email'], $params['password']))->getRow();
    return $res;
  }
}

$MODEL_users = new Users();
