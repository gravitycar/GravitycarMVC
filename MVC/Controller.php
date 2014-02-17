<?php
require_once('include/lib/autoloader.php');
require_once('include/lib/fatalErrorHandler.php');
require_once('MVC/Module_Action_View_Map.php');
class Controller
{
   public $defaultModuleName = "Home";
   public $errors = array();
   
   public function __construct()
   {
      $this->map = new Module_Action_View_Map();
   }
   
   
   public function run()
   {
      $module = $this->loadModule($this->getModuleName());
      $module->execute();
   }
   
   
   /**
    * getModuleName()
    *
    * Gets the name of the module we're going to work with. Default is "Home".
    * Also checks our map to make sure that the module is valid and allowed. If
    * an invalid module is specified, the default module will be returned.
    *
    * @param string $overrideName - If you want to force a module name, pass it
    *    in as an argument.
    * @return string - the name of the module. Default is "Home".
    */
   public function getModuleName($overrideName = '')
   {
      if (!empty($overrideName)) {
         return $overrideName;
      }
      
      $moduleName = $this->defaultModuleName;
      if (IsSet($_REQUEST['module'])) {
         $moduleName = $_REQUEST['module'];
      }
      
      if (!$this->map->validate($moduleName)) {
         print("$moduleName is not a valid module. Using {$this->defaultModuleName} instead.");
         $moduleName = $this->defaultModuleName;
      }
      
      return $moduleName;
   }
   
   
   /**
    * getAction()
    *
    * Gets the name of the action the module should perform. Actions can be
    * specified in $_REQUEST, but each module should support its own default
    * action in case no action is specified. "actions" are CRUD things, like
    * saving and reading, but more specific actions may be supported by different
    * modules.
    *
    * @param string $overrideAction - If you want to force a particular action,
    *    pass it in as an argument.
    * @return string - the name of the action.
    */
   public function getAction($overrideAction = '')
   {
      if (!empty($overrideAction)) {
         return $overrideAction;
      }
      
      $action = '';
      if (IsSet($_REQUEST['action'])) {
         $action = $_REQUEST['action'];
      }
      
      return $action;
   }
   
   
   /**
    * loadModule()
    *
    * Loads up the module we pass in by instatiating it. All modules should be
    * loadable via our autoloader method  gravitonLoader().
    *
    * @param string $moduleName - the name of the module to load.
    * @return mixed - an instatiation of the module.
    */
   public function loadModule($moduleName)
   {
      try {
         $module = new $moduleName();
      } catch(Exception $e) {
         $errorMsg = "Could not load $moduleName.\n" . $e->getMessage();
         print($errorMsg);
         self::reportError($errorMsg);
         $module = new $this->defaultModuleName();
      }
      return $module;
   }
   
   
   /**
    * reportError()
    *
    * If some process needs to report an error outside of a module, it may do
    * so via this method.
    *
    * @param string $errorMessage - a message describing the problem.
    */
   public static function reportError($errorMessage)
   {
      //self::$errors[] = $errorMessage;
   }
}


?>
