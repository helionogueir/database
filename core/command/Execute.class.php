<?php

namespace helionogueir\database\command;

use Exception;
use SplFileObject;
use helionogueir\languagepack\Lang;
use helionogueir\database\autoload\Environment;
use helionogueir\database\command\execute\ByJsonFile;

/**
 * - Change behavior of column
 * @author Helio Nogueira <helio.nogueir@gmail.com>
 * @version v1.0.0
 */
class Execute {

  /**
   * - Execute command by configuration file
   * @param string $pathName Path name of JSON file
   * @return null
   */
  public function byJsonFile(string $pathName) {
    $trouble = true;
    if (file_exists($pathName)) {
      $routines = json_decode(file_get_contents((new SplFileObject($pathName, "r"))->getPathname()));
      if (is_array($routines)) {
        $trouble = false;
        $execute = new ByJsonFile();
        foreach ($routines as $routine) {
          $execute->render($routine);
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