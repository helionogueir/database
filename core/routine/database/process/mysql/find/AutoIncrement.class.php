<?php

namespace helionogueir\database\routine\database\process\mysql\find;

use PDO;
use stdClass;
use Exception;
use helionogueir\shell\output\Trace;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\Process;

/**
 * - MySQL find auto increment
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class AutoIncrement implements Process {

  private $table = null;
  private $column = null;

  public function render(PDO $pdo, Info $info, stdClass $variables, Trace $output): bool {
    $executed = false;
    if ($queries = $this->get($pdo, $info, $variables, $output)) {
      $executed = true;
    }
    return $executed;
  }

  public function get(PDO $pdo, Info $info, stdClass $variables, Trace $output = null): Array {
    $queries = Array();
    if (!$pdo->inTransaction()) {
      $pdo->beginTransaction();
    }
    try {
      if (!is_null($output)) {
        $output->display(Lang::get("database:trace:start", "helionogueir/database", Array("classname" => __CLASS__)));
      }
      if ($this->factoryParameter($variables)) {
        $select = "SELECT COUNT(1) AS `auto_increment`
                   FROM INFORMATION_SCHEMA.COLUMNS 
                   WHERE TABLE_SCHEMA = :dbname
                   AND TABLE_NAME = :table
                   AND COLUMN_NAME = :column
                   AND EXTRA like :extra";
        $stmt = $pdo->prepare($select);
        $stmt->execute(Array(
          "dbname" => $info->getDbname(),
          "table" => $this->table,
          "column" => $this->column,
          "extra" => "%auto_increment%"
        ));
        if ($pdo->inTransaction()) {
          $pdo->commit();
        }
        foreach ($stmt->fetchAll() as $row) {
          if (!empty($row->auto_increment)) {
            $queries[] = $row->auto_increment;
            if (!is_null($output)) {
              $output->display("AUTO_INCREMENT: {$row->auto_increment}", 1, "-");
            }
          }
        }
      }
      if (!is_null($output)) {
        $output->display(Lang::get("database:trace:finish", "helionogueir/database", Array("classname" => __CLASS__)));
      }
    } catch (Exception $ex) {
      $queries = Array();
      if ($pdo->inTransaction()) {
        $pdo->rollBack();
      }
      throw $ex;
    }
    return $queries;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = true;
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("table", "column")as $parameter) {
      if (empty($variables->{$parameter})) {
        $match = false;
        throw new Exception(Lang::get("database:json:paramter:invalid", "helionogueir/database", Array("paramter" => $parameter)));
      } else {
        $this->{$parameter} = $variables->{$parameter};
      }
    }
    return $match;
  }

}
