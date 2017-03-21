<?php

namespace helionogueir\database\routine\database\process\mysql\change;

use PDO;
use stdClass;
use Exception;
use helionogueir\shell\output\Trace;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\Process;
use helionogueir\database\routine\database\process\mysql\find\ForeignKey;
use helionogueir\database\routine\database\process\mysql\find\AutoIncrement;

/**
 * - MySQL data type functionality
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class DataType implements Process {

  private $table = null;
  private $column = null;
  private $type = null;

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
      if ($steps = $this->prepareSteps($pdo, $info, $variables)) {
        foreach ($steps as $step) {
          if (count($step)) {
            foreach ($step as $sql) {
              $queries[] = $sql;
              if (!is_null($output)) {
                $output->display($sql, 1, "-");
              }
              $pdo->exec($sql);
            }
          }
        }
      }
      if ($pdo->inTransaction()) {
        $pdo->commit();
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
   * @param PDO $pdo MySQL PDO
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @param stdClass $variables Content variables for execute functionality
   * @return Array Queries steps
   */
  private function prepareSteps(PDO $pdo, Info $info, stdClass $variables): Array {
    $steps = Array();
    if ($this->factoryParameter($variables)) {
      $this->type = strtoupper($this->type);
      $steps = Array(
        "removeForeignKey" => Array(),
        "changeDataType" => Array(),
        "changeReferenceTable" => Array("ALTER TABLE `{$info->getDbname()}`.`{$this->table}` MODIFY COLUMN `{$this->column}` {$this->type}{$this->isAutoIncrement($pdo, $info, $variables)};"),
        "addForeignKey" => Array()
      );
      foreach ((new ForeignKey())->get($pdo, $info, $variables) as $query) {
        $variablesChidren = (object)Array(
          "table" => $query->table,
          "column" => $query->column,
          "type" => $this->type
        );
        if ($stepsChidren = $this->prepareSteps($pdo, $info, $variablesChidren)) {
          foreach ($stepsChidren as $topic => $chidren) {
            foreach ($chidren as $value) {
              $steps[$topic][] = $value;
            }
          }
        }
        $steps["removeForeignKey"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` DROP FOREIGN KEY `{$query->foreignKey}`;";
        $steps["changeDataType"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` MODIFY COLUMN `{$query->column}` {$this->type};";
        $steps["addForeignKey"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` ADD CONSTRAINT `{$query->foreignKey}` FOREIGN KEY (`{$query->column}`) REFERENCES `{$query->schemaReferenced}`.`{$query->tableReferenced}` (`{$query->columnReferenced}`) ON UPDATE NO ACTION ON DELETE NO ACTION;";
      }
    }
    //print_r($steps);die;
    return $steps;
  }

  /**
   * - Check if field is auto increment
   * @param PDO $pdo MySQL PDO
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @param stdClass $variables Content variables for execute functionality
   * @return string Return if field is auto increment
   */
  private function isAutoIncrement(PDO $pdo, Info $info, stdClass $variables): string {
    $autoIncrement = "";
    if ((new AutoIncrement())->get($pdo, $info, $variables)) {
      $autoIncrement = " AUTO_INCREMENT";
    }
    return $autoIncrement;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = true;
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("table", "column", "type")as $parameter) {
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
