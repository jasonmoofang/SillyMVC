<?php

define("MYSQL_HOST", "localhost");
define("MYSQL_PORT", "3306");
define("MYSQL_USER", "username");
define("MYSQL_PASS", "password");
define("MYSQL_DB", "db");
define("MYSQL_PREFIX", "");


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
}

class DB {

  protected $table_name;
  protected static $DB_conn;
  protected $required_fields = array();
  protected $unique_fields = array();
  
  public static function getError() {
    return self::$DB_conn->error;
  }
  
  public function __construct() {
    if (!isset(self::$DB_conn)) {
      self::$DB_conn = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB, MYSQL_PORT);

      if (self::$DB_conn->connect_error) {
        error_log("MySQL error:".$mysql_db->connect_error);
        die("Database error.");
      }
    }
  }
  
  public function queryAssoc($query, $vars = array(), $limit = 10) {
    $result = $this->runQuery($query, $vars);
    $assoc = array();
    $count = 0;
    while (($row = $result->getRow()) && $count < $limit) {
      array_push($assoc, $row);
      $count++;
    }
    return $assoc;
  }

  public function runQuery($query, $vars = array()) {
    $conn = self::$DB_conn;
    $callback = function($var) use ($conn) {
        return $conn->real_escape_string("" . $var);
    };
    $resultsql = vsprintf($query, array_map($callback, $vars));
    return new QueryResult(self::$DB_conn->query($resultsql));
  }
  
  public function sample($limit = 10) {
    return $this->queryAssoc("SELECT * FROM `".$this->table_name."` LIMIT ".intval($limit));
  }
  
  public function getOne($id) {
    return $this->runQuery("SELECT * FROM `".$this->table_name."` WHERE id='%s'", array($id))->getRow();
  }
  
  public function createDirect($assoc) {
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
                              (".implode(",", $sanitized_values).");");
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
    
    $this->createDirect($assoc);
  }
  
  public function update($assoc) {
    $this->validateUpdate($assoc);
    $this->updateDirect($assoc);
  }
  
  public function destroy($id) {
    return $this->runQuery("DELETE FROM `".$this->table_name."` WHERE `id`='%s'", array($id));
  }
  
  public function validateUpdate($assoc) {
    // check required fields - they can be missing, but not empty!
    foreach ($this->required_fields as $required) {
      if (isset($assoc[$required]) && trim($assoc[$required]) === '') {
        throw new Exception("Create: missing required field '".$required."'", 0);
      }
    }
    // check unique
    foreach($this->unique_fields as $unique) {
      if (isset($assoc[$unique])) {
        if ($this->runQuery("SELECT id FROM `".$this->table_name."`
                                WHERE `".$this->escapeFieldname($unique)."`='%s' AND
                                `id` <> '%s' LIMIT 1;",
                                array($assoc[$unique], $assoc['id']))->numRows() > 0) {
          throw new Exception($unique." value '".$assoc[$unique]."' already exists!", 1);
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
          throw new Exception($unique." value '".$assoc[$unique]."' already exists!", 1);
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
