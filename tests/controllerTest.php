<?php
chdir("../");
require_once('include/lib/autoloader.php');
class ControllerTest extends PHPUnit_Framework_TestCase
{
   
   public function setUp()
   {
   }
   
   
   public function tearDown()
   {
   }
   
   
   public function testLoadModule()
   {
   }
   
   
   public function testExecuteModule()
   {
   }
   
   
   public function testGetModuleName()
   {
      $moduleName = "TestModule";
      $this->controller = new Controller();
      $this->assertEquals($this->controller->getModuleName($moduleName), $moduleName);
      $this->assertEquals($this->controller->getModuleName(), $this->controller->defaultModuleName);
   }
}
?>
