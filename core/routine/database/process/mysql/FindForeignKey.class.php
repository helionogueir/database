<?php

namespace helionogueir\database\routine\database\process\mysql;

use PDO;
use stdClass;
use Exception;
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
      if ($this->factoryParameter($variables)) {
        $sql = "SELECT
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
        $stmt = $pdo->prepare($sql);
        $stmt->execute(Array(
          "dbname" => $info->getDbname(),
          "table" => $this->table,
          "column" => $this->column
        ));
        foreach ($stmt->fetchAll() as $row) {
          if (!empty($row->foreignKey) && !empty($row->schema) && !empty($row->table) && !empty($row->column) && !empty($row->schemaReferenced) && !empty($row->tableReferenced) && !empty($row->columnReferenced)) {
            $queries[] = $row;
          }
        }
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
    $match = false;
    foreach ($variables as $name => $value) {
      if (preg_match("/(table|column)/i", $name)) {
        $this->{$name} = $value;
        $match = true;
      }
    }
    return $match;
  }

}
