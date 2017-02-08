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

/**
 * - MySQL find data type
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class RegisterReference implements Process {

  private $table = null;
  private $column = null;
  private $idOld = null;
  private $idNew = null;

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
      if ($update = $this->prepareUpdate($pdo, $info, $variables)) {
        if (!$pdo->inTransaction()) {
          $pdo->beginTransaction();
        }
        foreach ($update as $sql) {
          $queries[] = $sql;
          if (!is_null($output)) {
            $output->display($sql, 1, "-");
          }
          $stmt = $pdo->prepare($sql);
          $stmt->execute(Array(
            "idOld" => $this->idOld,
            "idNew" => $this->idNew
          ));
        }
        $sql = "DELETE FROM `{$info->getDbname()}`.`{$this->table}` WHERE `{$this->column}` = :idOld";
        if (!is_null($output)) {
          $output->display($sql, 1, "-");
        }
        $queries[] = $sql;
        $stmt = $pdo->prepare($sql);
        $stmt->execute(Array("idOld" => $this->idOld));
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
  private function prepareUpdate(PDO $pdo, Info $info, stdClass $variables): Array {
    $update = Array();
    if ($this->factoryParameter($variables)) {
      foreach ((new ForeignKey())->get($pdo, $info, $variables) as $query) {
        $update[] = "UPDATE `{$query->schema}`.`{$query->table}` SET `{$query->column}` = :idNew WHERE `{$query->column}` = :idOld";
      }
    }
    //print_r($steps);die;
    return $update;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = true;
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("table", "column", "idOld", "idNew")as $parameter) {
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
