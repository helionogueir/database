<?php

namespace helionogueir\database\routine\database;

use PDO;
use stdClass;
use helionogueir\shell\Output;
use helionogueir\database\routine\database\Info;

/**
 * - Database process pattern
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
interface Process {

  /**
   * - Render change data type
   * @param PDO $pdo MySQL PDO
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @param stdClass $variables Content variables for execute functionality
   * @param helionogueir\shell\Output $output Output class
   * @return bool Case true executed queries
   */
  public function render(PDO $pdo, Info $info, stdClass $variables, Output $output): bool;

  /**
   * - Render change data type
   * @param PDO $pdo MySQL PDO
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @param stdClass $variables Content variables for execute functionality
   * @param helionogueir\shell\Output $output Output class
   * @return Array Results
   */
  public function get(PDO $pdo, Info $info, stdClass $variables, Output $output = null): Array;
}
