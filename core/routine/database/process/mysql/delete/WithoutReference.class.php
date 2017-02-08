<?php

namespace helionogueir\database\routine\database\process\mysql\delete;

use PDO;
use stdClass;
use Exception;
use helionogueir\shell\output\Trace;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\routine\database\Info;
use helionogueir\database\routine\database\Process;
use helionogueir\database\routine\database\process\mysql\find\ForeignKey;

/**
 * - MySQL find data type
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class WithoutReference implements Process {

  private $table = null;
  private $column = null;
  private $list = null;

  public function render(PDO $pdo, Info $info, stdClass $variables, Trace $output): bool {
    $executed = false;
    if ($queries = $this->get($pdo, $info, $variables, $output)) {
      $executed = true;
    }
    return $executed;
  }

  public function get(PDO $pdo, Info $info, stdClass $variables, Trace $output = null): Array {
    $queries = Array();
    try {
      if (!is_null($output)) {
        $output->display(Lang::get("database:trace:start", "helionogueir/database", Array("classname" => __CLASS__)));
      }
      if ($this->factoryParameter($variables)) {
        if (!$pdo->inTransaction()) {
          $pdo->beginTransaction();
        }
        $delete = "DELETE FROM `{$info->getDbname()}`.`{$this->table}` WHERE `{$this->column}` = : id";
        $stmt = $pdo->prepare($delete);
        if ($select = $this->selectDependencies($pdo, $info, $variables)) {
          foreach ($this->list as $id) {
            $canDelete = true;
            foreach ($select as $sql) {
              $stmt = $pdo->prepare($sql);
              $stmt->execute(Array("id" => $id));
              foreach ($stmt->fetchAll() as $row) {
                if (!empty($row->total)) {
                  $canDelete = false;
                  break;
                }
              }
            }
            if ($canDelete) {
              $queries[] = $delete;
              if (!is_null($output)) {
                $output->display("{$delete} / {$id}", 1, "-");
              }
              $stmt->execute(Array("id" => $id));
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
  private function selectDependencies(PDO $pdo, Info $info, stdClass $variables): Array {
    $select = Array();
    foreach ((new ForeignKey())->get($pdo, $info, $variables) as $query) {
      $select[] = "SELECT COUNT(`tbl`.`{$query->column}`) AS `total` FROM `{$query->schema}`.`{$query->table}` AS `tbl` WHERE `tbl`.`{$query->column}` = :id";
    }
    return $select;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = true;
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("table", "column", "list")as $parameter) {
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
