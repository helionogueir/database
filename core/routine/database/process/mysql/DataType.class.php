<?php

namespace helionogueir\database\routine\database\process\mysql;

use PDO;
use stdClass;
use Exception;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\Process;
use helionogueir\database\routine\database\process\mysql\FindForeignKey;

/**
 * - MySQL data type functionality
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class DataType implements Process {

  private $table = null;
  private $column = null;
  private $type = null;

  public function render(PDO $pdo, Info $info, stdClass $variables): bool {
    $executed = true;
    try {
      if ($queries = $this->get($pdo, $info, $variables)) {
        $executed = true;
      }
    } catch (Exception $ex) {
      $executed = false;
      throw $ex;
    }
    return $executed;
  }

  public function get(PDO $pdo, Info $info, stdClass $variables): Array {
    $queries = Array();
    try {
      $pdo->beginTransaction();
      if ($steps = $this->prepareSteps($pdo, $info, $variables)) {
        foreach ($steps as $step) {
          if (count($step)) {
            foreach ($step as $sql) {
              $queries[] = $sql;
              $stmt = $pdo->prepare($sql);
              $stmt->execute();
            }
          }
        }
      }
      $pdo->commit();
    } catch (Exception $ex) {
      $queries = Array();
      $pdo->rollBack();
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
        "changeReferenceTable" => Array("ALTER TABLE `{$info->getDbname()}`.`{$this->table}` MODIFY COLUMN `id_concessionaria` {$this->type};"),
        "addForeignKey" => Array()
      );
      foreach ((new FindForeignKey())->get($pdo, $info, $variables) as $query) {
        $steps["removeForeignKey"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` DROP FOREIGN KEY `{$query->foreignKey}`;";
        $steps["changeDataType"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` MODIFY COLUMN `id_concessionaria` {$this->type};";
        $steps["addForeignKey"][] = "ALTER TABLE `{$query->schema}`.`{$query->table}` ADD CONSTRAINT `{$query->foreignKey}` FOREIGN KEY (`{$query->column}`) REFERENCES `{$query->schemaReferenced}`.`{$query->tableReferenced}` (`{$query->columnReferenced}`) ON UPDATE NO ACTION ON DELETE NO ACTION;";
      }
    }
    return $steps;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = false;
    foreach ($variables as $name => $value) {
      if (preg_match("/(table|column|type)/i", $name)) {
        $this->{$name} = $value;
        $match = true;
      }
    }
    return $match;
  }

}
