<?php

require_once __DIR__.'/hasCountries.php';


class Contacts extends DB {

  use HasCountries;

  public function __construct() {
    parent::__construct();
    $this->table_name = "contacts";
    $this->required_fields = array("owner_id", "email", "first_name", "last_name");
    $this->unique_fields = array("email");
  }
  
  public function sampleByOwner($id, $limit = 10) {
    return $this->queryAssoc("SELECT * FROM `".$this->table_name."`
                                WHERE `owner_id` = '%s'
                                LIMIT ".intval($limit), array($id));
  }
  
  public function isOwner($userid, $contactid) {
    return $this->runQuery("SELECT id FROM `".$this->table_name."`
                                WHERE `owner_id` = '%s' AND `id` = '%s' LIMIT 1",
                                array($userid, $contactid))->numRows() > 0;
  }
  
  public function validateCreate($assoc) {
    parent::validateCreate($assoc);
    $this->countryValidateCreate($assoc);
  }

  public function validateUpdate($assoc) {
    parent::validateUpdate($assoc);
    $this->countryValidateUpdate($assoc);
  }
}

$MODEL_contacts = new Contacts();
