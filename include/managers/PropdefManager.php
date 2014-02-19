<?php
/**
 * PropdefManager class
 *
 * This class is to centalize the handling of all property definition functions.
 * Things like finding the propdef file and storing the property definitions are done
 * here. The module is responsible for instantiating the PropdefManager and copying
 * its data into the module's own properties.
 */
class PropdefManager extends Singleton
{
    /** @var hash - All previously loaded propdefs, indexed by module name */
    private $propdefMap = array();
        
    
    /**
     * loadPropdefs()
     *
     * Loads up the propdefs file for a given module, stores the propdefs for that
     * module in the propdefsMap hash, and returns the propdefs array defined in the
     * propdefs file.
     *
     * @param string $moduleName - the name of the module you want to load property
     *  definitions for.
     * @return array - an array of prop defs for a given module. Empty array if no
     *  propdefs file is found.
     */
    public function loadPropdefs($moduleName)
    {
        if (IsSet($this->propdefMap[$moduleName])) {
            return $this->propdefMap[$moduleName];
        }
        
        $propdefsFilePath = "modules/$moduleName/propdefs.php";
        if (file_exists($propdefsFilePath)) {
            require($propdefsFilePath);
        } else {
            $this->errMgr->error("$propdefsFilePath does not exist for module $moduleName.");
        }
        
        if (IsSet($propdefs)) {
            $this->propdefMap[$moduleName] = $propdefs;
            unset($propdefs);
        } else {
            $this->errMgr->error("No propdefs for {$this->name}");
            $propdefs =  array();
        }
        
        return $this->propdefMap[$moduleName];
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
