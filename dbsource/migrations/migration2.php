<?php

function run_migration_2($db) {
  // create salt field
  $db->runQuery("ALTER TABLE `users` ADD `salt` VARCHAR( 100 ) NOT NULL DEFAULT 'ALSKDJFH' AFTER `password_hash`");
  $db->runQuery("UPDATE `users` SET `password_hash` = SHA2(CONCAT('ALSKDJFH', 'password'), 256)");
  return true;
}

function revert_migration_2($db) {
  $db->runQuery("ALTER TABLE `users` DROP COLUMN `salt`;");
  return true;
}
