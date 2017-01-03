<?php

namespace helionogueir\database\command;

use stdClass;
use Exception;
use SplFileObject;
use helionogueir\shell\output\Text;
use helionogueir\shell\output\Trace;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\command\execute\ByJsonFile;

/**
 * - Change behavior of column
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Execute {

  private $output = null;

  /**
   * - Construct execute and define if output mode
   * @param helionogueir\shell\output\Trace $output Print class
   * @return null
   */
  public function __construct(Trace $output = null) {
    $this->output = $output;
    return null;
  }

  /**
   * - Execute command by configuration file
   * @param stdClass $argument Arguments of execution
   * @return null
   */
  public function run(stdClass $argument) {
    $valid = false;
    // Execute
    if (!empty($argument->execute)) {
      $valid = true;
      $this->byJsonFile($argument->execute);
    }
    // Help
    if (!empty($argument->help) || !$valid) {
      $this->helpToUse();
    }
    return null;
  }

  /**
   * - Help to use
   * @return null
   */
  private function helpToUse() {
    Lang::addRoot(Environment::PACKAGE, Environment::PATH);
    $output = new Text();
    $output->display();
    $output->display(Lang::get("database:execute:help:text", "helionogueir/database"), 0, "-");
    $output->display(Lang::get("database:execute:execute:text", "helionogueir/database"), 0, "-");
    $output->display();
    return null;
  }

  /**
   * - Execute command by configuration file
   * @param string $pathName Path name of JSON file
   * @return null
   */
  private function byJsonFile(string $pathName) {
    $trouble = true;
    if (file_exists($pathName)) {
      $routines = json_decode(file_get_contents((new SplFileObject($pathName, "r"))->getPathname()));
      if (is_array($routines)) {
        $trouble = false;
        $execute = new ByJsonFile();
        foreach ($routines as $routine) {
          $execute->render($routine, $this->output);
        }
      }
    }
    if ($trouble) {
      Lang::addRoot(Environment::PACKAGE, Environment::PATH);
      throw new Exception(Lang::get("database:execute:pathname:invalid", "helionogueir/database", Array("pathName" => $pathName)));
    }
    return null;
  }

}
