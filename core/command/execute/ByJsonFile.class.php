<?php

namespace helionogueir\database\command\execute;

use stdClass;
use Exception;
use helionogueir\shell\Output;
use helionogueir\database\Routine;
use helionogueir\languagepack\Lang;
use helionogueir\database\routine\Database;
use helionogueir\database\autoload\Environment;
use helionogueir\database\routine\database\Info;

/**
 * - Execute by JSON file
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class ByJsonFile {

  /**
   * - Execute command by configuration file
   * @param stdClass $routine Object with info routine
   * @param helionogueir\shell\Output $output Output class
   * @return null
   */
  public function render(stdClass $routine, Output $output = null) {
    if (count($routine) != 1) {
      Lang::addRoot(Environment::PACKAGE, Environment::PATH);
      throw new Exception(Lang::get("database:byjsonfile:routine:invalid", "helionogueir/database"));
    }
    foreach ($routine as $className => $row) {
      foreach ($row as $methodName => $object) {
        if (!empty($object->database) && !empty($object->variable)) {
          $class = $this->factoryClass($className, $methodName, $object->database, $output);
          $class->{$methodName}($object->variable);
        } else {
          Lang::addRoot(Environment::PACKAGE, Environment::PATH);
          throw new Exception(Lang::get("database:byjsonfile:routine:invalid", "helionogueir/database"));
        }
      }
    }
    return null;
  }

  /**
   * - Check if class and method exist and instanced class
   * @param string $className Class name
   * @param string $methodName Method name
   * @param stdClass $database Varibles PDO connection
   * @param helionogueir\shell\Output $output Output class
   * @return helionogueir\database\Routine Return class constructed
   */
  private function factoryClass(string $className, string $methodName, stdClass $database, Output $output = null): Routine {
    if (!class_exists($className)) {
      Lang::addRoot(Environment::PACKAGE, Environment::PATH);
      throw new Exception(Lang::get("database:byjsonfile:classname:invalid", "helionogueir/database", Array("className" => $className)));
    }
    if (!in_array($methodName, get_class_methods($className))) {
      Lang::addRoot(Environment::PACKAGE, Environment::PATH);
      throw new Exception(Lang::get("database:byjsonfile:methodname:invalid", "helionogueir/database", Array("className" => $className, "methodname" => $methodName)));
    }
    $info = new Info($database->dsn, $database->host, $database->dbname, $database->user, $database->password, $database->port, $database->charset);
    $pdo = (new Database())->mount($info);
    $routine = new $className($info, $pdo, $output);
    return $routine;
  }

}
