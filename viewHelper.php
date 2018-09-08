<?php

require_once __DIR__.'/model/users.php';
require_once __DIR__.'/session.php';

$BASE_PATH = explode($_SERVER['DOCUMENT_ROOT'], __DIR__)[1];

function link_url($controller, $view, $query="") {
  global $BASE_PATH;
  $path = $BASE_PATH.'/'.$controller.'/'.$view;
  if ($query) {
    return $path."?".$query;
  } else {
    return $path;
  }
}

$CURRENT_USER = FALSE;

if (Session::isLoggedIn()) {
  $CURRENT_USER = $this->user_model->getOne(Session::loggedInUserId());
}

function renderTemplate($name, $vars = array()) {
  require __DIR__."/view/".$name.".html.php";
}
