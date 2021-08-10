<?php

// db settings
define("MYSQL_HOST", "localhost");
define("MYSQL_PORT", "3306");
define("MYSQL_USER", "user");
define("MYSQL_PASS", "password");
define("MYSQL_DB", "db");
define("MYSQL_PREFIX", "");
//define("MYSQL_SSL_KEY", "BaltimoreCyberTrustRoot.crt.pem");

// migration settings
define("MIGRATION_ENABLED", true);
define("AUTO_MIGRATE", true);

// logging settings
define("LOG_FILE", __DIR__."/app.log");
define("LOG_LEVEL", 10);

// API settings
define("API_SECRET", "api_secret");

// Mail settings
define("MAIL_FROM_ADDRESS", "noreply@yuenhoe.com");
define("USE_SMTP", TRUE);
define("SMTP_DEBUG_MESSAGES", TRUE);
define("SMTP_HOST", "smtp.gmail.com");
define("SMTP_USERNAME", "yuenhoe86@gmail.com");
define("SMTP_PASSWORD", "password");

// Tasks settings
define("TASKS_ENABLED", true);
define("TASKS_PASSWORD", 'task_password');

// increase session timeout, requires a custom session save path
/***
$lifespan = 86400;//24 hours
ini_set('session.gc_maxlifetime', $lifespan);
session_set_cookie_params($lifespan);
ini_set('save_path', "/var/www/html/session");

ini_set('session.gc_probability', 1);
ini_set('session.gc_divisoi', 1000);
***/
