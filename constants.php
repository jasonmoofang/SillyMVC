<?php

class Constants {
  const PAGELIMIT = 30;
  public static $ERRORCODES = array(
      'SQL_ERROR' => 50,
      'REQUIRED_FIELD_MISSING' => 9,
      'UNIQUENESS_VIOLATION' => 1,
      'INVALID_VALUE' => 2,
      'MIGRATION_ERROR' => 3,
      'LOG_ERROR' => 4,
      'CANT_CREATE_UPLOAD_FOLDER' => 9,
      'DUPLICATE_UPLOAD_FILENAME' => 10,
      'UPLOAD_TOO_LARGE' => 11,
      'INVALID_UPLOAD_TYPE' => 12,
      'UPLOAD_UNEXPECTED_ERROR' => 13,
      'INVALID_TASK' => 15,
      'INVALID_TASK_PARAMS' => 16,
      'SMTP_ERROR' => 17,
      'INVALID_FIELD_KEY' => 18,
      'API_INVALID_USER_KEY' => 101,
      'API_AUTHENTICATION_FAILED' => 100
  );
  
}
