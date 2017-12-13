<?php
namespace Gravitycar\lib\abstracts;
/**
 * Relationship class.
 *
 * I'm going to support two types of relationships.
 * - one to many
 * - many to many
 *
 * one to one relationships are just one-to-many relationships where many is just one.
 *
 * What are the things are relationship needs to do?
 *
 * 1) It needs to connect one module to another module.
 * 2) It needs to know how to create join tables to represent many-to-many
 *  relationships.
 *
 * How will relationships work? 
 *                             
 * An important idea here is that relationships should be reversible. That is,
 * for the Users_Movies relationship, If the Users graviton links to a movie, this
 * class will figure out that the Users graviton's ID gets stored in Users_Movies.Users_id
 * and that the Movie graviton's id goes in Movies_id. If the saving graviton is
 * a Movies graviton, this class will still behave in exactly the same way.
 *
 * modules will define a field in their propdefs with a type of "relationship".
 * That propdef will define things like the field name, the foriegn module
 * and foreign key, and the relationship type.
 *
 * Relationships are defined outside of a module's propdefs. A module will refer
 * to the definition of the relationship. So for example:
 *
 * In the relationships/ folder we could have:
 * Users_Movies.php
 * which would include this code:
 * $relationships['Users_Movies'] = array(
 *  'name' => 'Users_Movies',
 *  'type' => 'many-to-many',
 *  'table' => 'Users_Movies',
 *  'module1' => 'Users',
 *  'key1' => 'id',
 *  'moduleA' => 'Movies',
 *  'keyA' => 'id'
 * );
 *
 * Or for a one-to-one relationship (which has no join table):
 *  'name' => 'Users_Movies',
 *  'type' => 'one-to-one',
 *  'module1' => 'Users'
 *  'key1' => 'movie_id', 
 *  'moduleA' => 'Movies',
 *  'keyA' => 'id'
 */
 
abstract class Relationship
{
    /** @var string - the type of relationship - 1-to-1 or many-to-many. **/
    public $relationshipType;
    
    /** @var string - the name of the relationship **/
    public $relationshipName;
    
    /** @var string - the type of join to use **/
    public $joinType = "LEFT JOIN";
    
    /** @var array - the modules that are linked to the current graviton **/
    public $linkedRecords = array();
    
    public function __construct($relationshipName)
    {
        $this->relationshipName = $relationshipName;
        $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
        $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
        $this->pdm = \Gravitycar\lib\managers\PropdefManager::singleton();
        $this->db = \Gravitycar\lib\managers\DBManager::singleton();
        $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
        $this->loadPropDefs();
    }
    
    
    public function loadPropDefs()
    {
        $propdefs = $this->pdm->getRelationshipPropDefs($this->relationshipName);
        foreach ($propdefs as $prop => $value) {
            $this->$prop = $value;
        }
    }
    
    
    public function getOtherModule($module)
    {
        if (is_a($module, "Gravitycar\lib\abstracts\Graviton")) {
            $module = get_class($module);
        }
        
        if ($module == $this->module1) {
            return $this->moduleA;
        }
        
        if ($module == $this->moduleA) {
            return $this->module1;
        }
        
        $this->errMgr->error("$module is not part of the relationship {$this->relationshipName}");
        return '';
    }
    
    
    public function whichModuleIsThis($moduleName)
    {
        if (is_a($moduleName, "Gravitycar\lib\abstracts\Graviton")) {
            $moduleName = get_class($moduleName);
        } else {
            $moduleName = "\\Gravitycar\\modules\\" . $moduleName . "\\" . $moduleName;
        }
        
        if ($this->module1 == $moduleName) {
            return 'module1';
        }
        
        if ($this->moduleA == $moduleName) {
            return 'moduleA';
        }
        
        $this->errMgr->error("{$this->relationshipName} does not relate this type of module: '$moduleName'");
        return '';
    }
    
    
    public function getKeyField($moduleName)
    {
        if (is_a($moduleName, "Gravitycar\lib\abstracts\Graviton")) {
            $moduleName = get_class($moduleName);
        }
        
        if ($this->module1 == $moduleName) {
            return $this->key1;
        }
        
        if ($this->moduleA == $moduleName) {
            return $this->keyA;
        }
        
        $this->errMgr->error("Cannot get the key field: {$this->relationshipName} does not relate this type of module: '$moduleName'");
        return '';
    }
}
