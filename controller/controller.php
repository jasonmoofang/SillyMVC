<?php

require_once __DIR__.'/../session.php';
require_once __DIR__.'/../controllerHelper.php';

class Controller {
  protected $controller_name;
  protected $layout;
  protected $login_only_actions;
  
  public function __construct() {
  }
  
  protected function getPostParams() {
    global $_POST;
    return $_POST;
  }
  
  protected function getGetParams() {
    global $_GET;
    return $_GET;
  }
  
  protected function getRequestParams() {
    global $_REQUEST;
    return $_REQUEST;
  }
  
  protected function getLayout() {
    return $this->layout;
  }
  
  protected function setFlashMessage($key, $val) {
    Session::pushFlash($key, $val);
  }
  
  protected function retrieveFlashMessages() {
    return Session::popFlash();
  }
  
  protected function readFlashMessages() {
    return Session::readFlash();
  }
  
  protected function renderView($view, $vars = array()) {
    $this->renderWithLayoutExternal($this->getLayout(), $this->controller_name, $view, $vars);
  }

  protected function renderExternalView($controller, $view, $vars = array()) {
    $this->renderWithLayoutExternal($this->getLayout(), $controller, $view, $vars);
  }

  protected function renderWithLayout($layout, $view, $vars = array()) {
    $this->renderWithLayoutExternal($layout, $this->controller_name, $view, $vars);
  }

  protected function renderWithLayoutExternal($layout, $controller, $view, $vars = array()) {
    $flash = $this->retrieveFlashMessages(); // grab flash messages
    if ($flash) {
      if (is_array($vars['flash'])) {
        $vars['flash'] = array_merge($flash, $vars['flash']);
      } else {
        $vars['flash'] = $flash;
      }
    }
    $VIEW_path = __DIR__.'/../view/'.$controller.'/'.$view.'.html.php';
    require __DIR__.'/../viewHelper.php';
    if ($layout) {
      require_once __DIR__.'/../view/layout/'.$layout.'.html.php';
    } else {
      require_once $VIEW_path;
    }
  }

  protected function getCurrentUser() {
    return Session::loggedInUserId();
  }
  
  protected function isLoggedIn() {
    return Session::isLoggedIn();
  }

  public function defaultAction() {
    require_once __DIR__.'/../404.php';
    return FALSE;
  }

  protected function redirect($path) {
    global $BASE_PATH;
    $base_path = $BASE_PATH;
    if (!$base_path) { 
      $base_path = "/";
    } else {
      $base_path .= "/";
    }
    header('Location: '.$base_path.$path);
    die();
  }
  
  protected function filterQueryKeys($assoc, $filter) {
    $keys = array_flip($filter);
    return array_intersect_key($assoc,$keys);
  }
  
  protected function isLoginOnly($action) {
    return is_array($this->login_only_actions) && in_array($action, $this->login_only_actions);
  }

  public function invokeAction($action) {
    if (!method_exists($this, $action)) {
      require_once __DIR__.'/../404.php';
      return FALSE;
    }
    if ($this->isLoginOnly($action) && !Session::isLoggedIn()) {
      require_once __DIR__.'/../403.php';
      return FALSE;
    }
    $reflection = new ReflectionMethod($this, $action);
    if (!$reflection->isPublic()) { // only public methods are actions
      require_once __DIR__.'/../404.php';
      return FALSE;
    }
    return $this->$action();
  }
} 
