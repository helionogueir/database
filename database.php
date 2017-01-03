<?php

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "autoload.php";

use helionogueir\shell\output\Trace;
use helionogueir\shell\parameter\Getter;
use helionogueir\database\command\Execute;
use helionogueir\shell\output\PrintException;

/**
 * - Execute command by configuration file
 * @return null
 */
try {
  $get = new Getter();
  $argument = new stdClass();
  // Help
  if ($get->exist(Array("--help", "-h"))) {
    $argument->help = true;
  }
  // Execute
  if ($execute = $get->variable(Array("--execute", "-e"))) {
    $argument->execute = $execute;
  }
  // Run
  (new Execute(new Trace()))->run($argument);
} catch (Exception $ex) {
  (new PrintException())->display($ex);
}
