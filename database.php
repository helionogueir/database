<?php

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . "autoload.php";

use helionogueir\shell\parameter\Getter;
use helionogueir\database\command\Execute;

/**
 * - Execute command by configuration file
 * @return null
 */
(new Execute())->byJsonFile((new Getter())->variable(Array("--configuration", "-c")));
