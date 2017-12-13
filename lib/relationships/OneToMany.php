<?php
namespace Gravitycar\lib\relationships;

/**
 * A one-to-many relationship means one record can be associated with any
 * number of other records, but those records will only be associated with
 * the one and no others. For example, a user can be associated with many
 * blog postings, but a blog posting only has one user. 
 *
 * In that example, the user is the One record, and the blog post would
 * be the Many record. So a one-to-many relationship is stored on the "many"
 * record, as a field. I.e. blogposts.user_id. The "many" graviton would include
 * the relationship in its propdefs, but the "one" graviton would not, because
 * the "one" graviton doesn't store that information. However, the "one" graviton
 * should be able to retrieve all of the records from the "many" by adding the
 * relationship name to its layout defs. 
 */
class OneToManay extends \Gravitycar\lib\abstracts\Relationship
{
    public $relationshipType = 'one-to-many';
    
    public function add($oneGraviton, $manyGraviton)
    {
        $addOK = false;
        
    }
    
    
    public function generateSQLAddRelationship($fromGraviton, $joinGraviton)
    {
        
    }
    
    public function clear($graviton)
    {
        
    }
    
    public function generateSQLJoinClause($fromModule, $keyValue)
    {
        if ($fromModule == $this->oneModule) {
            $fromKey = $this->oneKey;
            $joinModule = $this->manyModule;
            $joinKey= $this->manyKey;
        } else {
            $fromKey = $this->manyKey;
            $joinModule = $this->oneModule;
            $joinKey= $this->oneKey;
        }
        
        $this->joinModuleAlias = "{$joinModule}_from_{$this->relationshipName}";
        $sql = "\t{$this->joinType} $joinModule {$this->joinModuleAlias} on {$this->joinModuleAlias}.{$joinKey} = {$fromModule}.{$fromKey}";
        return $sql;
    }
}