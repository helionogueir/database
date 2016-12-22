<?php

namespace helionogueir\database\table;

use PDO;
use stdClass;
use helionogueir\shell\Output;
use helionogueir\database\Routine;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\process\mysql\Csv;

/**
 * - Change behavior of column
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Import implements Routine {

  private $info = null;
  private $pdo = null;
  private $output = null;

  public function __construct(Info $info, PDO $pdo, Output $output = null) {
    $this->info = $info;
    $this->pdo = $pdo;
    $this->output = $output;
    return null;
  }

  /**
   * - Import CSV in table
   * @param stdClass $variable Content variables for execute functionality
   * @return null
   */
  public function csv(stdClass $variable) {
    (new Csv())->render($this->pdo, $this->info, $variable, $this->output);
  }

}
