<?php
namespace Gravitycar\lib\managers;
/**
 * PropdefManager class
 *
 * This class is to centalize the handling of all property definition functions.
 * Things like finding the propdef file and storing the property definitions are done
 * here. The module is responsible for instantiating the PropdefManager and copying
 * its data into the module's own properties.
 */
class PropdefManager extends \Gravitycar\lib\abstracts\Singleton
{
    /** @var hash - All previously loaded propdefs, indexed by module name */
    private $propdefMap = array();
        
    
    /**
     * loadPropDefs()
     *
     * Loads up the propdefs file for a given object (module or relationship), stores the
     * propdefs for that object in the propdefsMap hash, and returns the propdefs array
     * defined in the propdefs file.
     *
     * @param string $objectName - the name of the object you want to load property
     *  definitions for.
     * @param string $objectType - the type of object you want to load definitions for.
     * @return array - an array of prop defs for a given module. Empty array if no
     *  propdefs file is found.
     */
    public function loadPropDefs($objectName, $objectType)
    {
        if (IsSet($this->propdefMap[$objectName])) {
            return $this->propdefMap[$objectName];
        }
        
        if ($objectType == 'module') {
            $propdefsFilePath = $this->getModulePropDefPath($objectName);
        } elseif ($objectType == 'relationship') {
            $propdefsFilePath = $this->getRelationshipPropDefPath($objectName);
        }
        
        if (file_exists($propdefsFilePath)) {
            require($propdefsFilePath);
        } else {
            $this->errMgr->error("$propdefsFilePath does not exist for module $objectName.");
        }
        
        if (IsSet($propdefs)) {
            $this->propdefMap[$objectName] = $propdefs;
            unset($propdefs);
        } else {
            $this->errMgr->error("No propdefs for {$objectName}");
            $this->propdefMap[$objectName] = array();
        }
        
        return $this->propdefMap[$objectName];
    }
    
    
    public function getModulePropDefPath($moduleName)
    {
        $moduleName = array_pop(explode('\\', $moduleName));
        return "modules/$moduleName/propdefs.php";
    }
    
    
    public function getRelationshipPropDefPath($relationshipName)
    {
        return "relationships/{$relationshipName}.php";
    }
    
    
    public function getModulePropDefs($moduleName)
    {
        return $this->loadPropDefs($moduleName, 'module');
    }
    
    
    public function getRelationshipPropDefs($relationshipName)
    {
        return $this->loadPropDefs($relationshipName, 'relationship');
    }
    
   
   /**
    * getPropDef()
    *
    * Returns the hash of property definitions for a given property of a given module.
    *
    * @param string $moduleName the name of the module you want to get a property 
    *  definition for.
    * @param string $propName - the name of the property you want definitions for.
    * @return hash - the hash of name/value pairs for the named property. Empty array
    *  if not found.
    */
    public function getPropDef($moduleName, $propName)
    {
        $propdefs = array();
        if (IsSet($this->propdefMap[$moduleName][$propName])) {
            $propdefs = $this->propdefMap[$moduleName][$propName];
        }
        return $propdefs;
    }
    
    
    /**
     * setPropDefAttribute()
     * 
     * Sets the value of an attribute in propdefs to the passed in value.
     * 
     * @param string $moduleName - name of the module you want to set a definition for.
     * @param string $propName - name of the property you want to set an attribute's
     * 	value for.
     * @param string $attributeName - the attribute you want to set a value for.
     * @param mixed $value - the value to set the attribute to.
     */
    public function setPropDefAttribute($moduleName, $propName, $attributeName, $value)
    {
    	if (IsSet($this->propdefMap[$moduleName])) {
    		$this->propdefMap[$moduleName][$propName][$attributeName] = $value;
    	}
    }
    
    
   /**
    * searchPropDefs()
    *
    * Search the property definitions for any property that has an attribute that is
    * set to a particular value. Returns an array of property names which can be used
    * as keys in the propdefs array.
    *
    * @param string $moduleName the name of the module you want to get a property 
    *  definition for.
    * @param string $attributeName - a propdef attribute name, i.e. fieldtype.
    * @param mixed $attributeValue - a value for the attribute you are searching for. 
    * @return array - an array of strings, where each string is the name of a propdef
    *    where its $attribute is set to $value. Empty array if there's no match.
    */
    public function searchPropDefs($moduleName, $attributeName, $attributeValue)
    {
      $matches = array();
      foreach ($this->propdefMap[$moduleName] as $prop => $defs) {
         if (IsSet($this->propdefs[$prop])) {
            if ($this->propdefs[$prop] === $value) {
               $matches[] = $prop;
            }
         }
      }
      return $matches;
    }
    
    
    
   /**
    * getPrimaryProperty()
    *
    * Search the property definitions for any property that has an attribute that is
    * set to a particular value. Returns an array of property names which can be used
    * as keys in the propdefs array.
    *
    * @param string $moduleName the name of the module you want to get a property 
    *  definition for.
    * @return array - the propdef for the property that has isPrimary set to true for
    *  the given module. Empty array if there's no match.
    */
   public function getPrimaryProperty($moduleName)
   {
       $primaryPropertyList = $this->searchPropDefs($moduleName, 'isPrimary', true);
       return $this->getPropDef($moduleName, $primaryPropertyList[0]);
   }
   
   
   public function toJSON($moduleName)
   {
       return json_encode($this->propdefMap[$moduleName]);
   }
}
?>
