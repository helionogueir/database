<?php

namespace helionogueir\database\routine\database;

use Exception;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;

/**
 * - Database Indformation
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Info {

  private $dsn;
  private $host;
  private $dbname;
  private $user;
  private $password;
  private $port;
  private $charset;

  /**
   * - Storage database information
   * @param string $dsn DSN consists of a name
   * @param string $host Host database
   * @param string $dbname Database name
   * @param string $user User database
   * @param string $password Password database
   * @param string $port Port database
   * @param string $charset Charset database
   * @return null
   */
  public function __construct(string $dsn, string $host, string $dbname, string $user, string $password, string $port, string $charset) {
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("dsn", "host", "dbname", "user", "password", "port", "charset") as $name) {
      if (!empty(${$name})) {
        $this->{$name} = ${$name};
      } else {
        throw new Exception(Lang::get("database:database:info:parameter:invalid", "helionogueir/database", Array("name" => $name, "value" => ${$name})));
      }
    }
    return null;
  }

  public final function getDsn(): string {
    return $this->dsn;
  }

  public final function getHost(): string {
    return $this->host;
  }

  public final function getDbname(): string {
    return $this->dbname;
  }

  public final function getUser(): string {
    return $this->user;
  }

  public final function getPassword(): string {
    return $this->password;
  }

  public final function getPort(): string {
    return $this->port;
  }

  public final function getCharset(): string {
    return $this->charset;
  }

}
