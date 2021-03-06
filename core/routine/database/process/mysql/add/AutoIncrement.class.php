<?php

namespace helionogueir\database\routine\database\process\mysql\add;

use PDO;
use stdClass;
use Exception;
use helionogueir\shell\output\Trace;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\Process;
use helionogueir\database\routine\database\process\mysql\find\DataType;
use helionogueir\database\routine\database\process\mysql\find\ForeignKey;

/**
 * - MySQL add AUTO_INCREMENT in column
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
      if ($dataType = $this->findDataType($pdo, $info, $variables)) {
        $dataType = strtoupper($dataType);
        $steps = Array(
          "removeForeignKey" => Array(),
          "changeReferenceTable" => Array("ALTER TABLE `{$info->getDbname()}`.`{$this->table}` MODIFY COLUMN `{$this->column}` {$dataType} AUTO_INCREMENT;"),
          "addForeignKey" => Array()
        );
        foreach ((new ForeignKey())->get($pdo, $info, $variables) as $query) {
          $steps["removeForeignKey"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` DROP FOREIGN KEY `{$query->foreignKey}`;";
          $steps["addForeignKey"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` ADD CONSTRAINT `{$query->foreignKey}` FOREIGN KEY (`{$query->column}`) REFERENCES `{$query->schemaReferenced}`.`{$query->tableReferenced}` (`{$query->columnReferenced}`) ON UPDATE NO ACTION ON DELETE NO ACTION;";
        }
      }
    }
    return $steps;
  }

  /**
   * - Render change data type
   * @param PDO $pdo MySQL PDO
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @param stdClass $variables Content variables for execute functionality
   * @return Array Queries steps
   */
  private function findDataType(PDO $pdo, Info $info, stdClass $variables): string {
    $dataType = "";
    if ($values = (new DataType())->get($pdo, $info, $variables)) {
      $dataType = end($values);
    }
    return $dataType;
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
