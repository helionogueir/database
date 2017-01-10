<?php

namespace helionogueir\database\field;

use PDO;
use stdClass;
use helionogueir\shell\output\Trace;
use helionogueir\database\Routine;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\process\mysql\add\AutoIncrement;

/**
 * - Add behavior of column
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Add implements Routine {

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
   * - Chancge data type
   * @param stdClass $variable Content variables for execute functionality
   * @return null
   */
  public function autoIncrement(stdClass $variable) {
    (new AutoIncrement())->render($this->pdo, $this->info, $variable, $this->output);
  }

}
