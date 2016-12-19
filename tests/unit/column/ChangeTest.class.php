<?php

use PHPUnit\Framework\TestCase;
use helionogueir\routing\Route;

class RouteTest extends TestCase {

  public function testConstruct() {
    $request = "route";
    $className = "helionogueir\\routing\\server\\Autoload";
    $method = "registerRoot";
    $route = new Route($request, $className, $method);
    $this->assertInstanceOf("helionogueir\\routing\\Route", $route);
  }

  public function tesGetRequest() {
    $request = "route";
    $className = "helionogueir\\routing\\server\\Autoload";
    $method = "registerRoot";
    $route = new Route($request, $className, $method);
    $this->assertEquals($request, $route->getRequest());
  }

  public function testgetClassName() {
    $request = "route";
    $className = "helionogueir\\routing\\server\\Autoload";
    $method = "registerRoot";
    $route = new Route($request, $className, $method);
    $this->assertEquals($className, $route->getClassName());
  }

  public function testgetMethod() {
    $request = "route";
    $className = "helionogueir\\routing\\server\\Autoload";
    $method = "registerRoot";
    $route = new Route($request, $className, $method);
    $this->assertEquals($method, $route->getMethod());
  }

}
