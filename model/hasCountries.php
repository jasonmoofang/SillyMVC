<?php

require_once __DIR__.'/db.php';

// can only be used by DB subclasses
// due to what was probably a design mistake in the beginning :(
trait HasCountries {

  protected $countriesCache = NULL;

  public function updateCountriesCache() {
    $result = $this->runQuery("SELECT * FROM `countries`");
    $assoc = array();
    while (($row = $result->getRow())) {
      array_push($assoc, $row);
    }
    $this->countriesCache = $assoc; // auto-update the cache here
  }
  
  
  public function getCountries() {
    if ($this->countriesCache === NULL) {
      $this->updateCountriesCache();
    }
    return $this->countriesCache;
  }
  
  public function addCountry($item) {
    if (isset($item['country_id'])) {
      $cache = $this->getCountries();
      foreach ($cache as $country) {
        if ($country['id'] == $item['country_id']) {
          $item['country'] = $country['name'];
          break;
        }
      }
    }
    return $item;
  }
  
  public function addCountryToListing($listing) {
    for($i=0; $i<count($listing); $i++) {
      $listing[$i] = $this->addCountry($listing[$i]);
    }
    return $listing;
  }
  
  public function sampleWithCountries($limit = 10) {
    return $this->addCountryToListing($this->sample($limit));
  }
  
  public function countryIdExists($id) {
    return $this->runQuery("SELECT id FROM `countries` WHERE `id`='%s'", array($id))->numRows() > 0;
  }
  
  public function countryValidateCreate($assoc) {
    // also check country Id
    if (isset($assoc['country_id']) && !$this->countryIdExists($assoc['country_id'])) {
      throw new Exception ("Invalid country id specified: ".$assoc['country_id']);
    }
  }

  public function countryValidateUpdate($assoc) {
    // also check country Id
    if (isset($assoc['country_id']) && !$this->countryIdExists($assoc['country_id'])) {
      throw new Exception ("Invalid country id specified: ".$assoc['country_id']);
    }
  }
}
