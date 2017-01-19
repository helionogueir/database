<?php

namespace helionogueir\database\command\table;

use PDO;
use stdClass;
use helionogueir\shell\output\Trace;
use helionogueir\database\Routine;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\process\mysql\create\Table;

/**
 * - Change behavior of table
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Create implements Routine {

  private $info = null;
  private $pdo = null;
  private $output = null;

  public function __construct(Info $info, PDO $pdo, Trace $output = null) {
    $this->info = $info;
    $this->pdo = $pdo;
    $this->output = $output;
    return null;
  }

  /**
   * - Create table and insert rows
   * @param stdClass $variable Content variables for execute functionality
   * @return null
   */
  public function table(stdClass $variable) {
    (new Table())->render($this->pdo, $this->info, $variable, $this->output);
  }

}
