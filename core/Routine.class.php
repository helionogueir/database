<?php

namespace helionogueir\database;

use PDO;
use helionogueir\database\routine\database\Info;

/**
 * - Routine pattern
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
interface Routine {

  /**
   * - Construct routine of database
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @param PDO $pdo PDO Database connect
   * @return null
   */
  public function __construct(Info $info, PDO $pdo);
}
