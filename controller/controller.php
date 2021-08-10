<?php

require_once __DIR__.'/../session.php';
require_once __DIR__.'/../controllerHelper.php';
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../constants.php';

class Controller {
  protected $controller_name;
  protected $layout;
  protected $login_only_actions;
  protected $loaded_models = array();
  
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
  
  protected function getPageNumber($page = 'page') {
    global $_REQUEST;
    return $_REQUEST[$page] ? $_REQUEST[$page] : 1;
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
    if (!defined("VIEWHELPERINCLUDED") || VIEWHELPERINCLUDED !== "yes") {
      require __DIR__.'/../viewHelper.php';
    }
    if ($layout) {
      require_once __DIR__.'/../view/layout/'.$layout.'.html.php';
    } else {
      require_once $VIEW_path;
    }
  }
  
  protected function renderApiResponse($arr) {
    header('Content-Type: application/json');
    echo json_encode($arr);
  }
  
  protected function authenticateApiCall() {
    $params = $this->getRequestParams();
    if ($params['api_secret'] !== API_SECRET) {
      throw new Exception("Authentication Failed", Constants::$ERRORCODES['API_AUTHENTICATION_FAILED']);
    }
  }
  
  protected function getCurrentUser() {
    return Session::loggedInUserId();
  }
  
  protected function isLoggedIn() {
    if (Session::isLoggedIn()) {
      return true;
    } else {
      $cookie = Session::getRememberCookie();
      if ($cookie !== false &&
          $this->getModel('users')->checkRememberMe($cookie['user_id'], $cookie['token']) &&
          $this->getModel('users')->existsWhere(array("id" => $cookie['user_id']))) {
        Session::logUserIn($cookie['user_id']);
        return true;
      }
      Session::clearAllSession();
      return false;
    }
  }

  public function defaultAction() {
    require_once __DIR__.'/../404.php';
    return FALSE;
  }

  protected function redirect($path) {
    global $BASE_PATH;
    $base_path = $BASE_PATH;
    if (!$base_path || $base_path == "/") { 
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
  
  /**
   * id:          identifier for the entity the uploads are associated with. usually "<controller>/<id>"
   * namemap:     assoc array with upload field name as keys and existing filename as value (blank if N/A)
   * clearfirst:  if true, will delete all specified existing files in namemap before processing
   *
   * When called in a method handling a form POST with file uploads, will process file fields of names
   * given as keys in namemap, saving the uploaded files in a folder unique to 'id', and returning an
   * updated namemap with the new filenames, which can be saved to models that implement HasUploads.
   * If clearfirst is set to false, will treat the provided namemap as map of existing uploads, and will
   * keep existing files if no new uploads are found, or overwrite them if they are. If clearfirst is set 
   * to true all existing files specified in the namemap argument will be cleared first. To process fields
   * with no existing files, simply set the field as key with value "" in namemap.
   *
   * Note of course this only handles the files and folders and does not update models.
   */
  protected function processFileUploads($id, $namemap, $clearfirst = true) {
    $target_dir = $this->getUploadPath($id, "");
    if (!file_exists($target_dir)) {
      if (!mkdir($target_dir, 0777, true)) {
        throw new Exception("Failed to create upload folder ".$target_dir, Constants::$ERRORCODES['CANT_CREATE_UPLOAD_FOLDER']);
      }
    }
    if ($clearfirst) { // empty folder first
      foreach ($namemap as $name => $val) {
        if ($val) {
          $target_file = $target_dir . $val;
          if (file_exists($target_file)) {
            unlink($target_file);
          }
        }
      }
    }
    foreach ($namemap as $name => $val) {
      if (!$_FILES[$name]["name"]) {
        if (!$clearfirst && $val) {
          $resmap[$name] = $val; // keep the value
        }
        continue; // wasn't uploaded
      }
      $target_file = $target_dir . basename($_FILES[$name]["name"]);
      $ext = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

      // TODO: check allowed file types

      // Check if file already exists
      if (file_exists($target_file)) {
        throw new Exception("Duplicate file during upload", Constants::$ERRORCODES['DUPLICATE_UPLOAD_FILENAME']);
      }

      // Check file size
      if ($_FILES["fileToUpload"]["size"] > 50000000) {
        throw new Exception("Upload file size too large", Constants::$ERRORCODES['UPLOAD_TOO_LARGE']);
      }
      
      // Allow certain file formats
      /*$allowed_exts = array("jpg", "png", "jpeg", "gif", "pdf", "doc", "docx", "odf");
      if(!in_array($ext, $allowed_exts)) {
        throw new Exception("File format not allowed for upload", Constants::$ERRORCODES['INVALID_UPLOAD_TYPE']);
      }*/
      
      // delete the old file if its there
      if ($val && file_exists($target_dir.$val)) {
        unlink($target_dir.$val);
      }
      
      // move the new file in
      if (!move_uploaded_file($_FILES[$name]["tmp_name"], $target_file)) {
        throw new Exception("Unexpected error with file upload", Constants::$ERRORCODES['UPLOAD_UNEXPECTED_ERROR']);
      }
      $resmap[$name] = basename($_FILES[$name]["name"]);
    }
    return $resmap;
  }
  
  protected function clearUploads($id, $deletefolder = true) {
    $target_dir = $this->getUploadPath($id, "");
    if (file_exists($target_dir)) {
      array_map('unlink', glob($target_dir."*"));
    }
    if ($deletefolder) {
      rmdir($target_dir);
    }
  }
  
  protected function clearUpload($id, $name) {
    $target_file = $this->getUploadPath($id, $name);
    if (file_exists($target_file)) {
      unlink($target_file);
    }
  }
  
  protected function getUploadPath($id, $name) {
    global $BASE_PATH;
    global $_SERVER;
    $base_path = $BASE_PATH;
    if (!$base_path) { 
      $base_path = "/";
    } else {
      $base_path .= "/";
    }
    $base_path = $_SERVER['DOCUMENT_ROOT'].$base_path;
    return $base_path."uploads/".$id."/".$name;
  }
  
  protected function getModel($name) {
    if (!$this->loaded_models[$name]) {
      $this->loaded_models[$name] = Controller::loadModel($name);
    }
    return $this->loaded_models[$name];
  }
  
  public static function loadModel($name) {
    global ${'MODEL_'.$name};
    require_once __DIR__.'/../model/'.$name.'.php';
    return ${'MODEL_'.$name};
  }

  public function invokeAction($action) {
    if (!method_exists($this, $action)) {
      require_once __DIR__.'/../404.php';
      return FALSE;
    }
    if ($this->isLoginOnly($action) && !$this->isLoggedIn()) {
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
  
  public function getTask($taskname, $args) {
    if ($taskname && strlen($taskname) > 0 && ctype_alpha(substr($taskname, 0, 1)) && strpos($taskname, "..") === FALSE
        && file_exists(__DIR__.'/../tasks/'.$taskname.'.php')) {
      // okay we have a task
      require(__DIR__.'/../tasks/'.$taskname.'.php');
      // now check params
      $sanitized_params = array();
      foreach($task_params as $param) {
        if (empty($args[$param])) {
          throw new Exception("Invalid task params!",
                                          Constants::$ERRORCODES['INVALID_TASK_PARAMS']);
        }
        $sanitized_params[$param] = $args[$param];
      }
      // we're good!
      return array("name" => $taskname, "params" => $sanitized_params, "execute" => $task_execute);
    } else {
      throw new Exception("Invalid task", Constants::$ERRORCODES['INVALID_TASK']);
    }
  }
  
  // call getTask to get a taskobj
  public function executeTask($taskobj) {
    // execute task
    $db = new DB();
    $msg = $taskobj["execute"]($taskobj["params"], $db);
    return $msg;
  }
} 
