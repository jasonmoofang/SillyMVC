<?php

require_once __DIR__.'/session.php';
require_once __DIR__.'/roles.php';
require_once __DIR__.'/model/users.php';

define("VIEWHELPERINCLUDED", "yes");

$BASE_PATH = explode($_SERVER['DOCUMENT_ROOT'], __DIR__)[1];
if ($BASE_PATH[0] !== '/' && $BASE_PATH != "") { $BASE_PATH = "/".$BASE_PATH; }

function link_url($controller, $view, $query="") {
  global $BASE_PATH;
  $path = $BASE_PATH.'/'.$controller.'/'.$view;
  if ($query) {
    if (is_array($query)) {
      return $path."?".http_build_query($query);
    } else {
      return $path."?".$query;
    }
  } else {
    return $path;
  }
}

function link_url_path($url="") {
  global $BASE_PATH;
  $path = $BASE_PATH.'/'.$url;
  return $path;
}

function upload_link($id, $name) {
  return link_url("attachments", "download", array("id" => $id, "name" => $name));
}

function link_domain_url($url){
    global $BASE_PATH;   
    $HTTP = isset($_SERVER['HTTPS']) ? 'https' : 'http';
    return $HTTP.'://'.$_SERVER['HTTP_HOST'].$BASE_PATH.$url; 
}

function renderTemplate($name, $vars = array()) {
  global $BASE_PATH, $_GET, $_SERVER;
  require __DIR__."/view/".$name.".html.php";
}

function build_new_querystring($vars, $keyvalues) {
  foreach ($keyvalues as $key => $value) {
    $vars[$key] = $value;
  }
  return http_build_query($vars);
}

function format_date($date) {
  return date("d M Y (l)", strtotime($date));
}

function format_plaintext($text) {
  return str_replace("\n", "<br />", htmlentities($text));
}

if(!$MODEL_users){
  global $MODEL_users;
}

function tz_list() {
  $zones_array = array();
  $timestamp = time();
  $dummy_datetime_object = new DateTime();
  foreach(timezone_identifiers_list() as $key => $zone) {
    date_default_timezone_set($zone);
    $zones_array[$key]['zone'] = $zone;
    $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);

    $tz = new DateTimeZone($zone);
    $zones_array[$key]['offset'] = $tz->getOffset($dummy_datetime_object) / 3600;
  }

  return $zones_array;
}

