<?php

require_once __DIR__."/model/db.php";
require_once __DIR__."/config.php";
require_once __DIR__."/constants.php";
require_once __DIR__."/logger.php";

if (MIGRATION_ENABLED && $_REQUEST['secret'] === "ltvkTZddWt3Z2hXYH2XT") {
  $db = new DB();
  // first check if migration_control table exists
  try {
    $db->runQuery('select 1 from `migration_control` LIMIT 1');
  } catch (Exception $e) {
    if ($e->getCode() === Constants::$ERRORCODES['SQL_ERROR']) {
      // well we'll create the migration_control table
      $db->runQuery("CREATE TABLE `migration_control` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=latin1;");
      // that counts as migration 1!
      $db->runQuery("INSERT INTO `migration_control` (`id`) VALUES ('1');");
    }
  }
  // get the next migration to run
  $latest = $db->queryAssoc("SELECT `id` FROM `migration_control` ORDER BY `id` DESC LIMIT 1");
  if (count($latest) !== 1) {
    throw new Exception("Migration table is empty!", Constants::$ERRORCODES['MIGRATION_ERROR']);
  }
  $next_id = $latest[0]['id'] + 1;
  $count = 0;
  // run all available migrations
  while (file_exists(__DIR__.'/dbsource/migrations/migration'.$next_id.'.php')) {
    require __DIR__.'/dbsource/migrations/migration'.$next_id.'.php';
    Logger::log("Running migration ".$next_id, Logger::INFO);
    if (call_user_func('run_migration_'.$next_id, $db)) {
      // record that we did this migration
      $db->runQuery("INSERT INTO `migration_control` (`id`) VALUES ('%s')", array($next_id));
      $next_id ++;
      $count ++;
    } else {
      Logger::log("Migration ".$next_id." failed!", Logger::ERROR);
      throw new Exception("Migration ".$next_id." failed!", Constants::$ERRORCODES['MIGRATION_ERROR']);
    }
  }
  if ($count > 0) {
    Logger::log("Completed ".$count." migrations", Logger::ANNOUNCE);
  }
}
