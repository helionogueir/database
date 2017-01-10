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

/**
 * - MySQL data type functionality
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class ForeignKey implements Process {

  private $table = null;
  private $column = null;
  private $schemaReplace = null;
  private $tableReplace = null;
  private $columnReplace = null;

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
        foreach ((new \helionogueir\database\routine\database\process\mysql\find\ForeignKey())->get($pdo, $info, $variables) as $query) {
          $this->removeForeignKey($pdo, $query, $output);
          $this->AddForeignKey($pdo, $query, $output);
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
   * - Remove foreign key
   * @param PDO $pdo MySQL PDO
   * @param stdClass $query Query info
   * @param helionogueir\shell\output\Trace $output Print class
   * @return bool Return true case match variable
   */
  private function removeForeignKey(PDO $pdo, stdClass $query, Trace $output = null) {
    $sql = "ALTER TABLE `{$query->schema}`.`{$query->table}` DROP FOREIGN KEY `{$query->foreignKey}`;";
    if (!is_null($output)) {
      $output->display($sql, 1, "-");
    }
    $pdo->exec($sql);
    return null;
  }

  /**
   * - Add foreign key
   * @param PDO $pdo MySQL PDO
   * @param stdClass $query Query info
   * @param helionogueir\shell\output\Trace $output Print class
   * @return bool Return true case match variable
   */
  private function AddForeignKey(PDO $pdo, stdClass $query, Trace $output = null) {
    $sql = "ALTER TABLE `{$query->schema}`.`{$query->table}` ADD CONSTRAINT `{$query->foreignKey}` FOREIGN KEY (`{$query->column}`) REFERENCES `{$this->schemaReplace}`.`{$this->tableReplace}` (`{$this->columnReplace}`) ON UPDATE NO ACTION ON DELETE NO ACTION;";
    if (!is_null($output)) {
      $output->display($sql, 1, "-");
    }
    $pdo->exec($sql);
    return null;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = true;
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("table", "column", "schemaReplace", "tableReplace", "columnReplace")as $parameter) {
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
