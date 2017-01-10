<?php

namespace helionogueir\database\routine\database\process\mysql\import;

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
class Csv implements Process {

  private $table = null;
  private $primaryKey = null;
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
          if (!$this->checkRegisterWasInserted($pdo, $info, $values)) {
            $queries[] = json_encode($values);
            $stmt->execute($values);
          }
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
   * - Check if register was inserted
   * @param PDO $pdo MySQL PDO
   * @param helionogueir\database\routine\database\Info $info Database info connection
   * @param Array $values Values of to be insert in table
   * @return bool Case exist return true, case not exist return false
   */
  private function checkRegisterWasInserted(PDO $pdo, Info $info, Array $values): bool {
    $exist = false;
    if (isset($values[$this->primaryKey]) && ("DEFAULT" != $values[$this->primaryKey])) {
      $select = "SELECT COUNT(`{$this->primaryKey}`) AS `total` FROM `{$info->getDbname()}`.`{$this->table}` WHERE `{$this->primaryKey}` = :id";
      $stmt = $pdo->prepare($select);
      $stmt->execute(Array("id" => $values[$this->primaryKey]));
      foreach ($stmt->fetchAll() as $row) {
        $exist = (!empty($row->total)) ? true : false;
      }
    }
    return $exist;
  }

  /**
   * - Format values to be insert in table
   * @param Array $fields Names fields of values
   * @param Array $values Values of to be insert in table
   * @param Array int CSV line of file
   * @return Array Return values to be insert in table
   */
  private function formatValues(Array $fields, Array $values, int $line): Array {
    $row = Array();
    if (count($fields) == count($values)) {
      foreach ($values as &$value) {
        $value = (preg_match("/^(null)$/i", $value)) ? null : trim($value);
      }
      $row = array_combine($fields, $values);
    } else {
      Lang::addRoot(Environment::PACKAGE, Environment::PATH);
      throw new Exception(Lang::get("database:csv:values:total:invalid", "helionogueir/database", Array("fields" => count($fields), "values" => count($values), "line" => "{$line} or " . ($line + 1))));
    }
    return $row;
  }

  /**
   * - Render change data type
   * @param stdClass $variables Content variables for execute functionality
   * @return bool Return true case match variable
   */
  private function factoryParameter(stdClass $variables): bool {
    $match = true;
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    foreach (Array("table", "primaryKey", "pathName")as $parameter) {
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
