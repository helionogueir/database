<?php

namespace helionogueir\database\routine\database;

use PDO;
use Exception;
use helionogueir\database\routine\database\Info;

/**
 * - MySQL database
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class MySql {

  /**
   * - Connect in MySQL database
   * @param helionogueir\database\routine\database\Info $info Database information
   * @return PDO Return PDO MySQL connection
   */
  public function connect(Info $info): PDO {
    try {
      $mysql = new PDO("{$info->getDsn()}:host={$info->getHost()};dbname={$info->getDbname()};port={$info->getPort()};charset={$info->getCharset()}", $info->getUser(), $info->getPassword(), array(
        PDO::ATTR_AUTOCOMMIT => true,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_TIMEOUT => 1800,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$info->getCharset()}"
      ));
    } catch (Exception $ex) {
      $mysql = null;
      throw $ex;
    }
    return $mysql;
  }

}
