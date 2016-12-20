<?php

namespace helionogueir\database\column;

use PDO;
use stdClass;
use helionogueir\database\Routine;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\process\mysql\DataType;

/**
 * - Change behavior of column
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Change implements Routine {

  private $info = null;
  private $pdo = null;

  public function __construct(Info $info, PDO $pdo) {
    $this->info = $info;
    $this->pdo = $pdo;
    return null;
  }

  /**
   * - Chancge data type
   * @param stdClass $variable Content variables for execute functionality
   * @return null
   */
  public function dataType(stdClass $variable) {
    (new DataType())->render($this->pdo, $this->info, $variable);
  }

}
