<?php
/**
 * Module_Action_View_Map class
 * This class stores our map of which modules are valid, and what actions they
 * support. Modules that aren't listed in the map should never be loaded by the
 * controller and actions that aren't in the map for a given module should never
 * be started by the controller.
 */
class Module_Action_View_Map
{
   public $map = array();
   
   public function __construct()
   {
      $this->addModuleToMap("Home", "display", 'HomePage');
      $this->addModuleToMap("Test", "detail", '');
   }
   
   /**
    * addModuleToMap()
    *
    * Adds a module to our module_action_view map.
    *
    * @param string $module - the module we want to find in the mapping.
    * @param string $action - the action (save, edit, view, etc.) for the module
    *    to perform.
    * @return void.
    */
   private function addModuleToMap($module, $action, $view)
   {
      $this->map[$module][$action] = $view;
   }
   
   /**
    * validate()
    *
    * Validates that the passed in module is allowed. Which modules are allowed
    * is specified in the constructor to this class.
    *
    * @param string $module - the module we want to find in the mapping.
    * @param string $action - the action (save, edit, view, etc.) for the module
    *    to perform.
    * @return bool - true if the module supports the action, false otherwise.
    */
   public function validate($module)
   {
      return IsSet($this->map[$module]);
   }
}
?>
