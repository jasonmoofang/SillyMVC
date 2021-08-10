  <?php

require_once __DIR__.'/controller.php';
require_once __DIR__.'/../roles.php';

class ContactsController extends Controller {
  protected $insert_fields;

  public function __construct() {
    parent::__construct();
    $this->controller_name = "contacts";
    $this->layout = "dashboard";
    $this->insert_fields = array("owner_id", "email", "first_name", "last_name", "country_id");
  }
  
  protected function isLoginOnly($action) {
    return true; // everything is login only
  }

  public function listing() {
    $list = $this->getModel('contacts')->addCountryToListing(
                          $this->getModel('contacts')->sampleByOwner($this->getCurrentUser()));
    $vars = array("contact_list" => $list);
    $this->renderView('listing', $vars);
  }
  
  public function create() {
    $params = $this->getPostParams();
    $vars = array('countryOptions' => $this->getModel('contacts')->getCountries());
    if ($params) {
      try {
        $params['owner_id'] = $this->getCurrentUser();
        $this->getModel('contacts')->create($this->filterQueryKeys($params, $this->insert_fields));
        $this->setFlashMessage('success', "Successfully created!");
        $this->redirect('contacts/listing');
        return $this->listing($vars);
      } catch (Exception $e) {
        $vars['flash']['error'] = $e->getMessage();
        $vars['form_item'] = $params;
      }
    }
    $this->renderView('create', $vars);
  }
  
  public function update() {
    $params = $this->getPostParams();
    $id = $this->getGetParams()['id'];
    if ($params['id']) {
      $id = $params['id'];
    }
    $vars = array('countryOptions' => $this->getModel('contacts')->getCountries());
    if ($this->canEditContact($this->getCurrentUser(), $id)) {
      if ($params) {
        try {
          $update_object = $this->filterQueryKeys($params, $this->insert_fields);
          $update_object['id'] = $id;
          $this->getModel('contacts')->update($update_object);
          $this->setFlashMessage('success', "Successfully updated!");
        } catch (Exception $e) {
          $vars['flash']['error'] = $e->getMessage();
          $vars['form_item'] = $params;
        }
      }
      $vars['form_item'] = $this->getModel('contacts')->addCountry($this->getModel('contacts')->getOne($id));
      $this->renderView('update', $vars);
    } else {
      $this->setFlashMessage("warning", "You don't have permission");
      $this->redirect("contacts/listing");
    }
  }
  
  public function destroy() {
    $params = $this->getRequestParams();
    if (isset($params['id'])) {
      if ($this->canDeleteContact($this->getCurrentUser(), $params['id'])) {
        $this->getModel('contacts')->destroy($params['id']);
        $this->setFlashMessage('info', "Removed");
      } else {
        $this->setFlashMessage('warning', "You don't have permission");
      }
    }
    $this->redirect('contacts/listing');
  }

  public function defaultAction() {
    return $this->listing();
  }
  
  protected function canEditContact($userid, $contactid) {
    return $this->getModel('contacts')->isOwner($userid, $contactid);
  }

  protected function canDeleteContact($userid, $contactid) {
    return Roles::hasPermission("admin");
  }
}

$contactsController = new ContactsController();
if (isset($ROUTE_action)) {
  $contactsController->invokeAction($ROUTE_action);
}
