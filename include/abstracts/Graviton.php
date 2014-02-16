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
   /** @var string the name of the module */
   public $name = '';
   
   /** @var string the table name for this module */
   public $table = '';
   
   /** @var ErrorManager the error manager to record any errors */
   public $errMgr = null;
   
   /** @var array a list of property definitions for the properties of this class */
   public $propdefs = array();
   
   /** @var string the name of the template file to use for this module */
   public $templateFile = '';
   
   /** @var GravitonLogger the loger object to log messages */
   public $log = null;
   
   /** @var PropdefManager the Property Definition Manager object, for loading/reading propdefs */
   public $pdm = null;
   
   /** @var db the database manager object */
   public $db = null;
    
    /** @var ConfigManager
    The configuration manager object */
    public $cfg = null;
   
   
   public function __construct()
   {
      $this->log = GravitonLogger::singleton();
      $this->errMgr = ErrorManager::singleton();
      $this->pdm = PropdefManager::singleton();
      $this->db = DBManager::singleton();
      $this->cfg = ConfigManager::singleton();
      $this->loadPropDefs();
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
      $this->propdefs = $this->pdm->loadPropDefs($className);
      foreach ($this->propdefs as $prop => $defs) {
         if ($defs['datatype'] == 'relationship') {
            $this->$prop = array();
         } else {
            $this->$prop = $defs['defaultvalue'];
         }
      }
      return true;
   }
   
   
   /**
    * searchPropDefs()
    *
    * Search the property definitions for any property that has an attribute that is
    * set to a particular value. Returns an array of property names which can be used
    * as keys in the propdefs array.
    *
    * @param string $attribute - a propdef attribute name, i.e. fieldtype.
    * @param mixed $value - a value for the attribute you are searching for. 
    * @return array - an array of strings, where each string is the name of a propdef
    *    where its $attribute is set to $value.
    */
   public function searchPropDefs($attribute, $value, $moduleName = '')
   {
      if (empty($moduleName)) {
         $moduleName = $this->getClassName();
      }
      return $this->pdm->searchPropDefs($moduleName, $attribute, $value);
   }
   
   
   /**
    * getPropDef()
    *
    * Returns the hash of property definitions for a given property.
    *
    * @param string $propName - the name of the property you want definitions for.
    * @param string $moduleName - the name of the module the property belongs to. Defaults
    *  to this module name.
    * @return hash - the hash of name/value pairs for the named property. Empty array
    *  if not found.
    */
   public function getPropDef($propName, $moduleName = '')
   {
      if (empty($moduleName)) {
         $moduleName = $this->getClassName();
      }
      return $this->pdm->getPropDef($moduleName, $propName);
   }
   
   
   /**
    * toJSON()
    *
    * Returns a JSON string that represents this module. It will loop through the
    * propdefs for the module, collect the values of each property, and add them
    * to a temporary object, which is then encoded as JSON. The resulting string
    * is then returned.
    *
    * @return string - a JSON encoded string of an object which represents this 
    *  module.
    */
   public function toJSON()
   {
      $propValuesObject = (object) $this->toHash();
      return json_encode($propValuesObject);
   }
   
   
   /**
    * toHash()
    *
    * Loops through all the properties listed in the propdefs and returns an
    * associative array of propdefname => someValue. Nested or related objects
    * must support a toHash() method to be included in the results.
    *
    * @return hash - an associative array of propname = propvalue based on the
    *  propdefs for this module.
    */
   public function toHash()
   {
      $propValuesHash = array();
      foreach ($this->propdefs as $propName => $propDefs) {
         if ($propDefs['datatype'] == 'relationship') {
            foreach ($this->$propName as $graviton) {
               $propValuesHash[$propName] = array();
               if (is_a($graviton, 'Graviton')) {
                  $propValuesHash[$propName][] = $graviton->toHash();
               } else {
                  $className = $this->getClassName();
                  $gravitonClass = get_class($graviton);
                  $msg = "In toHash(), $className->$propName is a relationship type, but is not a Graviton - it is a $gravitonClass";
                  $this->errMgr->error($msg);
               }
            }
         } else {
            $propValuesHash[$propName] = $this->$propName;
         }
      }
      
      return $propValuesHash;
   }
}
?>
