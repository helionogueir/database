<?php

namespace helionogueir\database\routine\database\process\mysql;

use PDO;
use stdClass;
use Exception;
use helionogueir\shell\Output;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\Process;

/**
 * - MySQL find foreign key
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class FindForeignKey implements Process {

  private $table = null;
  private $column = null;

  public function render(PDO $pdo, Info $info, stdClass $variables, Output $output): bool {
    $executed = false;
    if ($queries = $this->get($pdo, $info, $variables, $output)) {
      $executed = true;
    }
    return $executed;
  }

  public function get(PDO $pdo, Info $info, stdClass $variables, Output $output = null): Array {
    $queries = Array();
    try {
      if (!is_null($output)) {
        $output->display(Lang::get("database:trace:start", "helionogueir/database", Array("classname" => __CLASS__)));
      }
      if ($this->factoryParameter($variables)) {
        $select = "SELECT
                    CONSTRAINT_NAME AS `foreignKey`,
                    TABLE_SCHEMA AS `schema`,
                    TABLE_NAME AS `table`,
                    COLUMN_NAME AS `column`,
                    REFERENCED_TABLE_SCHEMA AS `schemaReferenced`,
                    REFERENCED_TABLE_NAME AS `tableReferenced`,
                    REFERENCED_COLUMN_NAME AS `columnReferenced`
                  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                  WHERE TABLE_SCHEMA = :dbname
                  AND REFERENCED_TABLE_NAME = :table
                  AND REFERENCED_COLUMN_NAME = :column";
        $stmt = $pdo->prepare($select);
        $stmt->execute(Array(
          "dbname" => $info->getDbname(),
          "table" => $this->table,
          "column" => $this->column
        ));
        foreach ($stmt->fetchAll() as $row) {
          if (!empty($row->foreignKey) && !empty($row->schema) && !empty($row->table) && !empty($row->column) && !empty($row->schemaReferenced) && !empty($row->tableReferenced) && !empty($row->columnReferenced)) {
            $queries[] = $row;
            if (!is_null($output)) {
              $output->display("{$row->foreignKey} / {$row->schema}.{$row->table}", 1, "-");
            }
          }
        }
      }
      if (!is_null($output)) {
        $output->display(Lang::get("database:trace:finish", "helionogueir/database", Array("classname" => __CLASS__)));
      }
    } catch (Exception $ex) {
      $queries = Array();
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
