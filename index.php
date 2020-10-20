<?php

$base_path = explode($_SERVER['DOCUMENT_ROOT'], __DIR__)[1];

// Grabs the URI and breaks it apart in case we have querystring stuff
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2);
if ($base_path) {
  $request_uri = explode($base_path, $request_uri[0])[1];
} else {
  $request_uri = $request_uri[0];
}
$request_uri = explode('/', $request_uri);

$root_controller = "pages";
$root_action = "home";
$dashboard_controller = "pages";
$dashboard_action = "dashboard";

// Do automigrations if enabled
require __DIR__."/config.php";
if (AUTO_MIGRATE) {
  $_REQUEST['secret'] = "ltvkTZddWt3Z2hXYH2XT";
  require __DIR__."/migrate.php";
}

// Route it up!
// Simple mechanism that automatically routes "name1/name2" to name1_controller->name2().
// Query params are in generic GET strings eg "name1/name2?var1=a,var2=b.
// All public methods in controller classes are considered valid actions.
// Helpers should be made non-public
switch ($request_uri[1]) {
  // Home page
  case '':
    $ROUTE_action = $root_action;
    require __DIR__.'/controller/'.$root_controller.'_controller.php';
    break;
  case 'dashboard':
    $ROUTE_action = $dashboard_action;
    require __DIR__.'/controller/'.$dashboard_controller.'_controller.php';
    break;    
  default:
    if (file_exists(__DIR__.'/controller/'.$request_uri[1].'_controller.php')) {
      $ROUTE_action = $request_uri[2];
      if (!$ROUTE_action) { $ROUTE_action = "defaultAction"; }
      require __DIR__.'/controller/'.$request_uri[1].'_controller.php';
    } else {
      require_once __DIR__.'/404.php';
    }
    break;
}
 
