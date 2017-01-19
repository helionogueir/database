<?php

namespace helionogueir\database\routine;

use PDO;
use Exception;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\routine\database\MySql;
use helionogueir\database\routine\database\Info;

/**
 * - Mount database
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Database {

  /**
   * - Mount database by object
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @return null
   */
  public function mount(Info $info): PDO {
    $trouble = false;
    $pdo = null;
    switch ($info->getDsn()) {
      case "mysql":
        $pdo = (new MySql())->connect($info);
        break;
      default :
        $trouble = true;
        break;
    }
    if ($trouble) {
      Lang::addRoot(Environment::PACKAGE, Environment::PATH);
      throw new Exception(Lang::get("database:database:invalid", "helionogueir/database"));
    }
    return $pdo;
  }

}
