<?php

namespace helionogueir\database\column;

use PDO;
use stdClass;
use helionogueir\shell\Output;
use helionogueir\database\Routine;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\process\mysql\ChangeDataType;

/**
 * - Change behavior of column
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Change implements Routine {

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
   * - Chancge data type
   * @param stdClass $variable Content variables for execute functionality
   * @return null
   */
  public function dataType(stdClass $variable) {
    (new ChangeDataType())->render($this->pdo, $this->info, $variable, $this->output);
  }

}
