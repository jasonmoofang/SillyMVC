<?php

require_once __DIR__.'/controller.php';

class PagesController extends Controller {

  public function __construct() {
    parent::__construct();
    $this->controller_name = "pages";
    $this->layout = "default";
    $this->login_only_actions = array("dashboard", "logout");
  }

  public function home() {
    $this->renderView('home');
  }

  public function dashboard() {
    $user = $this->getModel('users')->getOne($this->getCurrentUser());
    $this->renderWithLayout('dashboard', 'dashboard');
  }

  public function login() {
    if ($this->isLoggedIn()) {
      $this->redirect("dashboard");
    }
    $params = $this->getPostParams();
    $vars = array();
    if ($params) {
      if ($user = $this->getModel('users')->tryAuthenticate($params)) {
        Session::logUserIn($user['id']);
        $this->setFlashMessage('success', "Welcome back!");
        $this->redirect("dashboard");
      } else {
        $vars['flash']['error'] = "Failed to authenticate";
      }
    }
    $this->renderView('login', $vars);
  }

  public function logout() {
    Session::logout();
    $this->redirect(""); // home page
  }

  public function defaultAction() {
    return $this->home();
  }
}

$pagesController = new PagesController();
if (isset($ROUTE_action)) {
  $pagesController->invokeAction($ROUTE_action);
}
