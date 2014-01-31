<?php
/**
 * Graviton Class
 *
 * All modules will extend the Graviton class. This is the class that will define
 * basic functionality for all modules, like loaded property definition files,
 * and producing a JSON representation of itself.
 */
abstract class Graviton
{
   /** @type string - the name of the module */
   public $name = '';
   
   /** @type string - the table name for this module */
   public $table = '';
   
   /** @type array - a list of error messages to display to the user and/or log */
   public $errors = array();
   
   /** @type array - a list of property definitions for the properties of this class */
   public $propdefs = array();
   
   /** @type string - the name of the template file to use for this module */
   public $templateFile = '';
   
   /** @type GravitonLogger - the loger object to log messages */
   public $log = null;
   
   public function __construct()
   {
      $this->log = GravitonLogger::singleton();
   }
   
   
   public function execute()
   {
      print("I'm from Graviton::execute()");
   }
   
   
   /**
    * getClassName()
    *
    * Returns the name of the class this method belongs to. Can be overridden.
    *
    * @param string $className - an override class name.
    * @return string - the name of the class this method belongs to, or the override.
    */
   public function getClassName($className = '')
   {
      if (empty($className)) {
         $className = get_class($this);
      }
      return $className;
   }
   
   
   /**
    * loadPropDefs()
    *
    * Reads the property definition file for this class and stores the array
    * it defines as a property of this class.
    *
    * @return bool - true if the property definition file is found and read 
    *    successfully, false otherwise.
    */
   public function loadPropDefs()
   {
      $className = $this->getClassName();
      $propdefsFilePath = "modules/$className/propdefs.php";
      try {
         require_once($propdefsFilePath);
      } catch (Exception $e) {
         print($e->getMessage());
         print($e->getTraceAsString());
         return false;
      }
      
      if (IsSet($propdefs)) {
         $this->propdefs = $propdefs;
         unset($propdefs);
         return true;
      } else {
         return false;
      }
   }
   
   
   /**
    * getPropDef()
    *
    * Returns the hash of property definitions for a given property.
    *
    * @param string $propName - the name of the property you want definitions for.
    * @return hash - the hash of name/value pairs for the named property.
    */
   public function getPropDef($propName)
   {
      return IsSet($this->propdefs[$propName]) ? $this->propdefs[$propName] : null;
   }
}
?>
