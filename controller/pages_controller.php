<?php

require_once __DIR__.'/controller.php';
require_once __DIR__.'/../model/users.php';

class PagesController extends Controller {
  protected $user_model;

  public function __construct($userm) {
    parent::__construct();
    $this->user_model = $userm;
    $this->controller_name = "pages";
    $this->layout = "default";
    $this->login_only_actions = array("dashboard", "logout");
  }

  public function home() {
    $this->renderView('home');
  }

  public function dashboard() {
    $user = $this->user_model->getOne($this->getCurrentUser());
    $this->renderWithLayout('dashboard', 'dashboard');
  }

  public function login() {
    if ($this->isLoggedIn()) {
      $this->redirect("dashboard");
    }
    $params = $this->getPostParams();
    $vars = array();
    if ($params) {
      if ($user = $this->user_model->tryAuthenticate($params)) {
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

$pagesController = new PagesController($MODEL_users);
if (isset($ROUTE_action)) {
  $pagesController->invokeAction($ROUTE_action);
}
