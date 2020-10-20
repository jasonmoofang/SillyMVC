<?php

require_once __DIR__."/config.php";
require_once __DIR__."/constants.php";

class Logger {

  const ANNOUNCE = 10;
  const DEBUG = 1;
  const NOTICE = 2;
  const INFO = 3;
  const WARNING = 4;
  const ERROR = 8;
  protected static $file = NULL;

  public static function log($message, $level=self::DEBUG) {
    // we ignore if log level isn't low enough
    if ($level >= LOG_LEVEL) {
      // make sure we have a file to log to
      if (!self::$file) {
        if (!self::$file = fopen(LOG_FILE, 'a+')) {
          throw new Exception(
            sprintf("Could not open file '%s' for writing.", LOG_FILE), Constants::$ERRORCODES['LOG_ERROR']);
        }
      }
      // okay time to log
      $prefix = "[".(new DateTime())->format('d/m/Y H:i:s')."] ";
      switch ($level) {
        case self::DEBUG:
            $prefix .= "(debug) ";
            break;
        case self::NOTICE:
            $prefix .= "(notice) ";
            break;
        case self::WARNING:
            $prefix .= "(warning) ";
            break;
        case self::INFO:
            $prefix .= "(info) ";
            break;
        case self::ERROR:
            $prefix .= "(ERROR) ";
            break;
      }
      return fwrite(self::$file, $prefix.$message."\n");
    }
    return false;
  }
  
}

