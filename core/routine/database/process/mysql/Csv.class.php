<?php

namespace helionogueir\database\routine\database\process\mysql;

use PDO;
use stdClass;
use Exception;
use SplFileObject;
use helionogueir\shell\Output;
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
  private $pathName = null;

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
        $csv = new SplFileObject($this->pathName, "r");
        $csv->rewind();
        var_dump($csv->current());
        die;
        /* $pdo->beginTransaction();
          if ($steps = $this->prepareSteps($pdo, $info, $variables)) {
          foreach ($steps as $step) {
          if (count($step)) {
          foreach ($step as $sql) {
          $queries[] = $sql;
          $stmt = $pdo->prepare($sql);
          $stmt->execute();
          if (!is_null($output)) {
          $output->display($sql, 1, "-");
          }
          }
          }
          }
          }
          $pdo->commit(); */
      }
      if (!is_null($output)) {
        $output->display(Lang::get("database:trace:finish", "helionogueir/database", Array("classname" => __CLASS__)));
      }
    } catch (Exception $ex) {
      $queries = Array();
      //$pdo->rollBack();
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
    foreach (Array("table", "pathName")as $parameter) {
      if (empty($variables->{$parameter})) {
        $match = false;
        throw new Exception(Lang::get("database:json:paramter:invalid", "helionogueir/database", Array("paramter" => $parameter)));
      } else {
        $this->{$parameter} = $variables->{$parameter};
      }
    }
    if (!is_readable($this->pathName) && !preg_match("/^(.*)(\.csv)$/i", $subject)) {
      throw new Exception(Lang::get("database:paramter:not:readable", "helionogueir/database", Array("paramter" => $this->pathName)));
    }
    return $match;
  }

}
