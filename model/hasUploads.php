<?php

require_once __DIR__.'/db.php';
require_once __DIR__.'/../constants.php';

// can only be used by DB subclasses
// due to what was probably a design mistake in the beginning :(

// provides methods to maintain an 'uploads' TEXT field by storing maps of uploaded files.
// meant to work in conjunction with controller->processFileUploads. Will store and regurgitate
// namemaps returned by processFileUploads by covertly json-encoding them.
// see below for sample code
trait HasUploads {

  protected $defaultNamemap;

  public function updateUploads($id, $uploads) {
    $encoded = "";
    if (count($uploads) > 0) {
      $encoded = json_encode($uploads);
    }
    $this->runQuery("UPDATE `".$this->table_name."` SET uploads='%s' WHERE id='%s'",
                      array($encoded, $id));
  }
  
  public function removeUpload($id, $upload) {
    $uploads = $this->getUploads($id);
    if ($uploads) {
      unset($uploads[$upload]);
      $this->updateUploads($id, $uploads);
    }
  }
  
  public function getUploads($id) {
    $res = $this->queryArray("uploads", "SELECT uploads FROM `".$this->table_name."` WHERE id='%s' LIMIT 1", array($id))[0];
    return $res ? json_decode($res, true) : false;
  }

  protected function initUploadFields($arr) {
    foreach ($arr as $field) {
      $this->defaultNamemap[$field] = "";
    }
  }
  
  public function defaultUploadNamemap() {
    return $this->defaultNamemap;
  }
}

/** Sample code in controller:
    try {
      $namemap = $model->defaultUploadNamemap(); // $model here implements hasUploads, ofc
      $uploads = $model->getUploads($id); // $id is the id of a row from the model
      if ($uploads) {
        // there are existing uploads, we merge in case there are more fields than uploads
        $uploads = array_merge($namemap, $uploads);
      } else {
        // no uploads yet, use default
        $uploads = $namemap;
      }
      $newuploads = $this->processFileUploads("controllername/".$id, $uploads, false);
      $model->updateUploads($id, $newuploads); // make sure we update the db with the new uploads
    } catch (Exception $e) {
      $this->setFlashMessage('warning', "There was an error with file upload: ".$e->getMessage());
    }
**/
