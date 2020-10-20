<?php
require_once __DIR__.'/../emails/emailHelper.php';

$task_params = array("to_email");

$task_execute = function ($args, $db) {
  EmailHelper::sendEmail("testEmail",array(),array($args["to_email"]));
  // report
  return "Attempted to send an email to ".$args["to_email"];
};
