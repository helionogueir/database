<?php

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "autoload.php";

use helionogueir\shell\parameter\Getter;
use helionogueir\database\command\Execute;
use helionogueir\shell\output\Trace;

/**
 * - Execute command by configuration file
 * @return null
 */
(new Execute(new Trace()))->byJsonFile((new Getter())->variable(Array("--configuration", "-c")));
