<?php
namespace Gravitycar\lib\managers;

/**
 * RelationshipManager
 *
 * The job of the relationship manager is very simple: 
 * 1) take the name of a relationship in a getRelationship() method.
 * 2) require_once() the propdef file for that relationship in the relationships/ directory.
 * 3) cache the propdefs in case we need them again.
 * 4) instantiate the correct type of relationship based on the propdefs.
 * 5) return the instantated relationship object.
 */
class RelationshipManager extends \Gravitycar\lib\abstracts\Singleton
{
    /** @var - an array of relationship defs that have already been retrieved. **/
    public $cache = array();
    
    public function __construct()
    {
      $this->log = GravitonLogger::singleton();
      $this->errMgr = ErrorManager::singleton();
      $this->pdm = PropdefManager::singleton();
      $this->db = DBManager::singleton();
      $this->cfg = ConfigManager::singleton();
    }
    
    public function getRelationship($relationshipName)
    {
        if (isset($this->cache[$relationshipName])) {
            $propdefs = $this->cache[$relationshipName];
        } else {
            $propdefs = $this->pdm->getRelationshipPropDefs($relationshipName);
            $this->cache[$relationshipName] = $propdefs;
        }
        
        $className = "\\Gravitycar\\lib\\relationships\\{$propdefs['type']}";
        return new $className($relationshipName);
    }
    
}