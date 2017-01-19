<?php

namespace helionogueir\database\routine\database\process\mysql\create;

use PDO;
use stdClass;
use Exception;
use SplFileObject;
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
class Table implements Process {

  private $table = null;
  private $pathName = null;

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
        $csv = new SplFileObject($this->pathName, "r");
        $csv->rewind();
        $fields = str_getcsv($csv->current(), ";", "\"");
        $insert = "INSERT INTO `{$info->getDbname()}`.`{$this->table}` (`" . implode("`, `", $fields) . "`) VALUES (:" . implode(", :", $fields) . ")";
        $stmt = $pdo->prepare($insert);
        while (!$csv->eof()) {
          $values = $this->formatValues($fields, $csv->fgetcsv(";", "\""), $csv->key());
          $queries[] = json_encode($values);
          $stmt->execute($values);
          if (!is_null($output)) {
            $output->display(json_encode($values), 1, "-");
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
   * - Create table with first line of CSV
   * @param Array $header First line of CSV with header
   * @return bool Return true case match variable
   */
  private function createTable(Array $header): bool {
    $isCreated = false;
    $create = null;
    try {
      
    } catch (Exception $ex) {
      throw $ex;
    }
    return $isCreated;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = true;
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("table", "pathName")as $parameter) {
      if (empty($variables->{$parameter})) {
        $match = false;
        throw new Exception(Lang::get("database:json:paramter:invalid", "helionogueir/database", Array("paramter" => $parameter)));
      } else {
        $this->{$parameter} = $variables->{$parameter};
      }
    }
    if (!is_readable($this->pathName) && !preg_match("/^(.*)(\.csv)$/i", $this->pathName)) {
      throw new Exception(Lang::get("database:paramter:not:readable", "helionogueir/database", Array("paramter" => $this->pathName)));
    }
    return $match;
  }

}
