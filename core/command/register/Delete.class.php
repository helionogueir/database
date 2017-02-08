<?php

namespace helionogueir\database\command\register;

use PDO;
use stdClass;
use helionogueir\shell\output\Trace;
use helionogueir\database\Routine;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\process\mysql\delete\WithoutReference;

/**
 * - Delete behavior of column
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Delete implements Routine {

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
   * - Chancge foreign key
   * @param stdClass $variable Content variables for execute functionality
   * @return null
   */
  public function withoutReference(stdClass $variable) {
    (new WithoutReference())->render($this->pdo, $this->info, $variable, $this->output);
  }

}
