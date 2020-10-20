<?php

require_once __DIR__.'/../config.php';
require_once __DIR__.'/../constants.php';

class QueryResult {
  protected $result;
  protected $insert_id;
  public function __construct($res, $in_id = 0) {
    $this->result = $res;
    $this->insert_id = $in_id;
  }
  public function getRow() {
    if (!$this->result) { return FALSE; }
    $res = $this->result->fetch_assoc();
    return $res;
  }
  public function getInsertId() {
    return $this->insert_id;
  }
  public function numRows() {
    return $this->result->num_rows;
  }
  public function setInsertId($id) {
    $this->insert_id = $id;
  }
}

class Page {
  protected $items;
  protected $current_page;
  protected $total_pages;
  public function __construct($list, $curpage, $totpage) {
    $this->items = $list;
    $this->current_page = $curpage;
    $this->total_pages = $totpage;
  }
  public function getItemList() {
    return $this->items;
  }
  public function getCurrentPage() {
    return $this->current_page;
  }
  public function getTotalPages() {
    return $this->total_pages;
  }
}

class DB {

  protected $table_name;
  protected static $DB_conn;
  protected $required_fields = array();
  protected $unique_fields = array();
  protected $create_autofields = array();
  
  public static function getError() {
    return self::$DB_conn->error;
  }
  
  public function __construct() {
    if (!isset(self::$DB_conn)) {
      if (defined("MYSQL_SSL_KEY")) {
        self::$DB_conn = mysqli_init();
        self::$DB_conn->ssl_set(NULL, NULL, MYSQL_SSL_KEY, NULL, NULL) ;
        self::$DB_conn->real_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, MYSQL_PORT, MYSQLI_CLIENT_SSL);
      } else {
        self::$DB_conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, MYSQL_PORT);
      }

      if (self::$DB_conn->connect_error) {
        error_log("MySQL error:".self::$DB_conn->connect_error);
        die("Database error.");
      }
    }
  }
  
  public function queryAssoc($query, $vars = array()) {
    $result = $this->runQuery($query, $vars);
    $assoc = array();
    while (($row = $result->getRow())) {
      array_push($assoc, $row);
    }
    return $assoc;
  }

  public function queryArray($key, $query, $vars = array()) {
    $result = $this->runQuery($query, $vars);
    $arr = array();
    while (($row = $result->getRow())) {
      array_push($arr, $row[$key]);
    }
    return $arr;
  }
  
  public function runQuery($query, $vars = array(), $is_insert = FALSE) {
    $conn = self::$DB_conn;
    $callback = function($var) use ($conn) {
        return $conn->real_escape_string("" . $var);
    };
    $resultsql = vsprintf($query, array_map($callback, $vars));
    $res = self::$DB_conn->query($resultsql);
    if ($res === FALSE) {
      throw new Exception(self::$DB_conn->error, Constants::$ERRORCODES['SQL_ERROR']);
    }
    return $is_insert ? new QueryResult($res, self::$DB_conn->insert_id) : new QueryResult($res);
  }
  
  public function sample($limit = Constants::PAGELIMIT) {
    return $this->queryAssoc("SELECT * FROM `".$this->table_name."` LIMIT ".intval($limit));
  }
  
  public function samplePage($page = 1, $limit = Constants::PAGELIMIT) {
    $offset = ($page - 1) * intval($limit);
    $query = "SELECT %s FROM
                `".$this->table_name."`";
    $count = $this->runQuery($query, array("count(`".$this->table_name."`.id) as count"))->getRow()['count'];
    $items = $this->queryAssoc($query." LIMIT ".$offset.", ".intval($limit),
                                                  array("`".$this->table_name."`.*"));
    return new Page($items, $page, ceil($count/$limit));
  }
  
  public function getOne($id) {
    return $this->runQuery("SELECT * FROM `".$this->table_name."` WHERE id='%s'", array($id))->getRow();
  }
  
  public function getWhere($params, $limit = Constants::PAGELIMIT) {
    $whereclause = array();
    $param_array = array();
    foreach ($params as $key => $value) {
      array_push($whereclause, " `".$this->escapeFieldname($key)."` = '%s' ");
      array_push($param_array, $value);
    }
    return $this->queryAssoc("SELECT * FROM `".$this->table_name."` WHERE ".
                                implode("AND", $whereclause)." LIMIT ".intval($limit), $param_array);
  }
  
  public function existsWhere($params) {
    $whereclause = array();
    $param_array = array();
    foreach ($params as $key => $value) {
      array_push($whereclause, " `".$this->escapeFieldname($key)."` = '%s' ");
      array_push($param_array, $value);
    }
    return $this->runQuery("SELECT id FROM `".$this->table_name."` WHERE ".
                              implode("AND", $whereclause)." LIMIT 1", $param_array)->getRow() > 0;
  }
  
  public function rowCount() {
    $res = $this->runQuery("SELECT count(id) as count FROM `".$this->table_name)->getRow();
    return $res['count'];
  }
  
  public function createDirect($assoc) {
    foreach ($this->create_autofields as $key => $value) {
      if (!isset($assoc[$key])) {
        $assoc[$key] = $value;
      }
    }
    $fields = array_keys($assoc);
    $sanitize = function ($val) {
      return "`".$this->escapeFieldname($val)."`";
    };
    $sanitized_fields = array_map($sanitize, $fields);
    $sanitized_values = array();
    $conn = self::$DB_conn;
    $sanitize_value = function($val) use ($conn) {
      if ($val === "NOW()") {
        return $val;
      } else {
        return "'".$conn->real_escape_string($val)."'";
      }
    };
    foreach ($fields as $field) {
      array_push($sanitized_values, $sanitize_value($assoc[$field]));
    }
    return $this->runQuery("INSERT INTO `".$this->table_name."`
                              (".implode(",", $sanitized_fields).") VALUES
                              (".implode(",", $sanitized_values).");", array(), TRUE);
  }
  
  public function updateDirect($assoc) {
    $update_statements = array();
    $conn = self::$DB_conn;
    $id = $assoc['id'];
    unset($assoc['id']);
    $sanitize_value = function($val) use ($conn) {
      if ($val === "NOW()") {
        return $val;
      } else {
        return "'".$conn->real_escape_string($val)."'";
      }
    };
    foreach ($assoc as $key => $val) {
      array_push($update_statements, "`".$this->escapeFieldname($key)."` = ".
                                          $sanitize_value($val));
    }
    return $this->runQuery("UPDATE `".$this->table_name."` SET
                              ".implode(",", $update_statements)." WHERE id='%s';", array($id));
  }
  
  public function create($assoc) {
    $this->validateCreate($assoc); // throws exception on validation failure
    
    return $this->createDirect($assoc)->getInsertId();
  }
  
  public function update($assoc) {
    $this->validateUpdate($assoc);
    return $this->updateDirect($assoc);
  }
  
  public function destroy($id) {
    return $this->runQuery("DELETE FROM `".$this->table_name."` WHERE `id`='%s'", array($id));
  }
  
  public function validateUpdate($assoc) {
    // check required fields - they can be missing, but not empty!
    foreach ($this->required_fields as $required) {
      if (isset($assoc[$required]) && trim($assoc[$required]) === '') {
        throw new Exception("Create: missing required field '".$required."'",
                                          Constants::$ERRORCODES['REQUIRED_FIELD_MISSING']);
      }
    }
    // check unique
    foreach($this->unique_fields as $unique) {
      if (isset($assoc[$unique])) {
        if ($this->runQuery("SELECT id FROM `".$this->table_name."`
                                WHERE `".$this->escapeFieldname($unique)."`='%s' AND
                                `id` <> '%s' LIMIT 1;",
                                array($assoc[$unique], $assoc['id']))->numRows() > 0) {
          throw new Exception($unique." value '".$assoc[$unique]."' already exists!",
                                          Constants::$ERRORCODES['UNIQUENESS_VIOLATION']);
        }
      }
    }
  }  
  
  public function validateCreate($assoc) {
    $fields = array_keys($assoc);
    // check required fields
    foreach ($this->required_fields as $required) {
      if (!in_array($required, $fields) || trim($assoc[$required]) === '') {
        throw new Exception("Create: missing required field '".$required."'", 0);
      }
    }
    // check unique constraint
    foreach ($this->unique_fields as $unique) {
      if (in_array($unique, $fields)) {
        if ($this->runQuery("SELECT id FROM `".$this->table_name."`
                                WHERE `".$this->escapeFieldname($unique)."`='%s' LIMIT 1;",
                                array($assoc[$unique]))->numRows() > 0) {
          throw new Exception($unique." value '".$assoc[$unique]."' already exists!",
                                          Constants::$ERRORCODES['UNIQUENESS_VIOLATION']);
        }
      }
    }
  }
  
  public function createWithTimestamp($assoc, $createdField = "created") {
    $assoc[$createdField] = "NOW()";
    $this->create($assoc);
  }
  
  public function escapeFieldname($name) {
    return str_replace("`", "``", $name);
  }

}
