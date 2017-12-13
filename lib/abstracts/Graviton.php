<?php
namespace Gravitycar\lib\abstracts;

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
   public $moduleName = '';
   
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
   
   /** @var relMgr - relationship manager, loads relationship objects */
   public $relMgr = null;
   
   /** @var - array of relationship objects */
   public $relationships = array();
    
   /** @var ConfigManager
   The configuration manager object */
   public $cfg = null;
   
   
   public function __construct()
   {
      $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
      $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
      $this->pdm = \Gravitycar\lib\managers\PropdefManager::singleton();
      $this->db = \Gravitycar\lib\managers\DBManager::singleton();
      $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
      $this->relMgr = \Gravitycar\lib\managers\RelationshipManager::singleton();
      $this->loadPropDefs();
   }
   
   
   /**
    * execute()
    * 
    * Takes an action as its argument, and if that action is defined as a function
    * for this class, it executes that method. If it doesn't, you get an error.
    * 
    * @param string $action - the name of a function of this class you want to call.
    * @return mixed - whatever $action returns, or false if $action isn't defined.
    */
   public function execute($action)
   {
   		$this->errMgr->error("executing $action");
   		if (!method_exists($this, $action)) {
   			$this->errMgr->error("$action is NOT a method.");
   			$errorMsg = "$action is not defined for module '{$this->moduleName}'";
   			$this->errMgr->error($errorMsg);
   			throw new \Exception($errorMsg);
   		}
   		
   		try {
   			$this->errMgr->error("trying $action");
   			$this->$action();
   		} catch (Exception $e) {
   			$this->errMgr->error("$action threw an error!");
   			throw $e;
   		}
   		
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
      $this->propdefs = $this->pdm->getModulePropDefs($className);
      foreach ($this->propdefs as $prop => $defs) {
         if ($defs['datatype'] == 'relationship') {
            $this->$prop = array();
         } else {
            $this->$prop = IsSet($defs['defaultvalue']) ? $defs['defaultvalue'] : null;
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
    * setPropDefAttribute()
    * 
    * Sets a specific attribute of a specific property definition to the passed
    * in value.
    * 
    * @param string $propName - name of the property you want to change an attribute of.
    * @param string $attributeName - name of the attribute you're changing.
    * @param mixed $value - the value you want to set the attribute to.
    */
   public function setPropDefAttribute($propName, $attributeName, $value)
   {
       $this->pdm->setPropDefAttribute($this->moduleName, $propName, $attributeName, $value);
       $this->propdefs[$propName][$attributeName] = $value;
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
            $propValuesHash[$propName] = $this->$propName;
            foreach ($this->$propName as $graviton) {
                /*
                $propValuesHash[$propName] = array();
                if (is_a($graviton, 'Graviton')) {
                  $propValuesHash[$propName][] = $graviton->toHash();
                } else {
                  $className = $this->getClassName();
                  $gravitonClass = get_class($graviton);
                  $msg = "In toHash(), $className->$propName is a relationship type, but is not a Graviton - it is a $gravitonClass";
                  $this->errMgr->error($msg);
                }
                */
            }
         } else {
            $propValuesHash[$propName] = $this->$propName;
         }
      }
      
      return $propValuesHash;
   }
   
   
   /**
    * populateFromHash()
    *
    * Populates the object based on the contents of a hash or associative array.
    * This could be a database resultset row, or a hash assmebled some other way.
    * Will loop through the module's propdefs and search the passed in hash for
    * each property name, and if it finds the name in the hash it assignes this
    * objects property name to the value from the hash.
    *
    * @param array $hash - an associative array of name/value pairs.
    * @return void.
    */
   public function populateFromHash($hash)
   {
       foreach ($this->propdefs as $propName => $propDef) {
           if (IsSet($hash[$propName])) {
               $this->$propName = $hash[$propName];
           }
       }
   }
   
   
   /**
    * create()
    * 
    * For most classes, create() won't do anything - the application should just
    * return an empty form for the user to populate. However, for the sake of 
    * completeness a shell method is defined so the controller has something to
    * call via execute.
    * 
    * @return bool - returns true.
    */
   public function create()
   {
   		return true;
   }
   
   
   public function getAllRelationships()
   {
       $relationships = array();
       foreach ($this->propdefs as $propName => $propDef) {
           if ($propDef['datatype'] == 'relationship') {
               $relationships[$propName] = $propDef;
           }
       }
       return $relationships;
   }
   
   
   public function loadRelationship($relationshipName)
   {
       $this->relationships[$relationshipName] = $this->relMgr->getRelationship($relationshipName);
       return $this->relationships[$relationshipName];
   }
   
   
   public function loadRelationships()
   {
       $relationships = $this->getAllRelationships();
       foreach ($relationships as $fieldName => $propDef) {
           $this->loadRelationship($propDef['relationship']);
       }
   }
   
   
   
   /**
    * detail()
    * 
    * Populates the module with data from the db (or wherever the module gets its
    * data from.
    * 
    * @param string $idOverride - if you want to force an ID, do it here. Otherwise,
    * 	defaults to using whatever's in $_REQUEST.
    * @return mixed - true if successful in querying db for module data, void if exception
    * 	is thrown.
    */
   public function detail($idOverride = '')
   {
   		$id = empty($idOverride) ? IsSet($_REQUEST['id']) ? $_REQUEST['id'] : '' : $idOverride;
   		if (empty($id)) {
   			throw new \Gravitycar\lib\exceptions\RecordIDNotSpecified("No ID specified for '{$this->moduleName	}'");
   		}
   		
   		$this->loadRelationships();
   		
   		$this->id = $id;
   		$searchParams = $this->db->generateSearchParam($this, 'id');
   		$where = $this->db->generateWhereClause($this, $searchParams);
   		
   		$fieldsList = $this->getDetailFields();
   		$joinsList = array();
   		
        $relationshipPropdefs = $this->getAllRelationships();
        foreach ($relationshipPropdefs as $fieldName => $propDef) {
            $rel = $this->loadRelationship($propDef['relationship']);
            switch ($rel->type) {
                case 'ManyToMany':
                $this->$fieldName = $rel->loadLinkedRecordsAsKeyValuePairs($this);
                break;
                default:
                $keyField = $rel->getKeyField($this->moduleName);
                $joinsList[] = $rel->generateSQLJoinClause($this->moduleName, $this->$keyField);
                $fieldsList[] = "{$rel->joinModuleAlias}.*";
                break;
           }
   		}
   		
   		$fields = implode(', ', $fieldsList);
   		$joins = implode("\n", $joinsList);
   		
   		$sql = "select $fields from {$this->table} $joins where $where";
   		$this->errMgr->error($sql);
   		$result = $this->db->query($sql);
   		$data = $this->db->fetchByAssoc($result);
   		
   		
   		if (empty($data)) {
   			throw new EmptyDBQueryResult("There is no {$this->moduleName} record with an id of '$id'");
   		} else {
   			$this->populateFromHash($data);
   		}
   		
   		return true;
   }
   
   
   /**
    * getDetailFields()
    *
    * Returns an array of all field names needed for the detail display.
    *
    * @return array - an array of field names.
    */
   public function getDetailFields()
   {
       return array("{$this->table}.*");
   }
   
   
   /**
    * getListFields()
    *
    * Returns an array of all field names needed for the list view display.
    *
    * @return array - an array of field names.
    */
   public function getListFields()
   {
       return array("{$this->table}.*");
   }
   
   
   /**
    * save()
    *
    * Basic save function. Will call the DB Manager's generateSQLInsert() or the
    * generateSQLUpdate method and pass itself and whatever the save data is. Whether
    * to create or update is based on the presence of a non-empty ID field in the
    * save data. Save data should always be sent in POST.
    *
    * Data validation must be done according to propdef settings. Module-specific 
    * validation may be performed by extending this method in those classes, as can
    * any data transformation operations.
    * 
    * After the save is done, the db will be queried to get the latest state of the 
    * data loaded into the module.
    *
    * @return bool - true if the save is OK, false otherwise.
    */
   public function save()
   {
       $this->errMgr->error(var_export($_REQUEST, true));
       if (empty($_POST)) {
           $this->errMgr->error("Not saving {$this->moduleName}, no post data submitted.");
           return false;
       }

       $data = $_POST;

       if (!$this->validateData($data)) {
            $this->errMgr->error("Not saving {$this->moduleName}, data failed validation.");
            return false;
       }
       
       if (empty($_POST["id"])) {
           // create new record
       	   $data["id"] = $this->db->generateDBID();
           $sql = $this->db->generateSQLInsert($this, $data);
       } else {
           // update existing record
           $sql = $this->db->generateSQLUpdate($this, $data);
       }
       $this->id = $data["id"];
        
       $saveOK = $this->db->query($sql);
       
       if ($saveOK) {
            $this->errMgr->error("Save was OK!");
       } else {
           $this->errMgr->error("Save failed!");
       }
       
       $relationshipPropdefs = $this->getAllRelationships();
       foreach ($relationshipPropdefs as $fieldName => $propDef) {
           $rel = $this->loadRelationship($propDef['relationship']);
           $idsOfRelatedGravitons = array_keys($data[$fieldName]);
           
           switch ($rel->type) {
           case 'ManyToMany':
               $rel->add($this, $idsOfRelatedGravitons);
               break;
           default:
               // for now, do nothing - OneToOne relationships are handled above.
               break;
           }
       }
       
       $this->detail($this->id);
       
       return $saveOK;
   }
   
   
   /**
    * validateData()
    *
    * Loops through all of the propdefs and tests the data submitted for each one to 
    * make sure it meets all the requirement and expectations we have for each property.
    * For every property that has one or more errors, every error is registered for
    * later display to the user.
    *
    * @param hash $data - probably $_POST, the data submitted to update this object.
    * @return bool - true if all fields pass all validation checks, false if they don't.
    */
   public function validateData($data)
   {
       $validationOK = true;
       foreach ($this->propdefs as $propName => $propDef) {
       	   $fieldName = "$propName"; 
           if (!$this->validateRequired($data, $fieldName, $propDef)) {
               $validationOK = false;
           }
       }
       
       return $validationOK;
   }
   
   
   /**
    * validateRequired()
    *
    * Validates this field if the field is required. Required fields cannot be left empty.
    *
    * @param hash $data - probably $_POST, the data submitted to update this object.
    * @param string $fieldName - the name of the field in POST that we're validating.
    * 	These names are typically "moduleName_propDef['name']"
    * @param hash $propDef - the definitions for this property.
    */
   public function validateRequired($data, $fieldName, $propDef)
   {
       $ok = true;
       if (IsSet($propDef['required']) && $propDef['required'] == true) {
           if (empty($data[$fieldName])) {
               $ok = false;
               $this->errMgr->error("Please fill in the '{$propDef['label']}' field");
           }
       }
       return $ok;
   }
}
?>
