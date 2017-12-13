<?php
namespace Gravitycar\lib\relationships;

class ManyToMany extends \Gravitycar\lib\abstracts\Relationship
{
    public $relationshipType = 'many-to-many';
    
    public function add($fromGraviton, $relatedIDs)
    {
        $addOK = false;
        
        $clearOK = $this->clear($fromGraviton);
        
        if (!$clearOK) {
            $this->errMgr->error("ManyToMany->clear() failed! Cannot add.");
            return false;
        }
        
        $joinGravitonClass = $this->getOtherModule($fromGraviton);
        $joinGraviton = new $joinGravitonClass();
        
        if (!is_array($relatedIDs)) {
            $relatedIDs = array($relatedIDs);
        }
        
        foreach ($relatedIDs as $id) {
            $joinGraviton->id = $id;
            $sql = $this->generateSQLAddRelationship($fromGraviton, $joinGraviton);
            
            if (!empty($sql)) {
                $addOK = $this->db->query($sql);
            }
            
            if (!$addOK) {
                break;
            }
        }
        
        return $addOK;
    }
    
    
    public function generateSQLAddRelationship($fromGraviton, $joinGraviton)
    {
        $graviton1 = null;
        $gravitonA = null;
        
        if (get_class($fromGraviton) == $this->module1) {
            $graviton1 = $fromGraviton;
            $module1 = $fromGraviton->moduleName;
        } elseif (get_class($fromGraviton) == $this->moduleA) {
            $gravitonA = $fromGraviton;
            $moduleA = $fromGraviton->moduleName;
        }
        
        if (get_class($joinGraviton) == $this->module1) {
            $graviton1 = $joinGraviton;
            $module1 = $joinGraviton->moduleName;
        } elseif (get_class($joinGraviton) == $this->moduleA) {
            $gravitonA = $joinGraviton;
            $moduleA = $joinGraviton->moduleName;
        }
        
        if (is_null($graviton1) || is_null($gravitonA) || $graviton1->id == $gravitonA->id) {
            $this->errMgr->error("Relationship {$this->relationshipName} could not add a record. Can't figure out who's who.");
            $this->log->debug(get_class($fromGraviton));
            $this->log->debug(get_class($joinGraviton));
            $this->log->debug("\n{$fromGraviton->id}\n{$joinGraviton->id}");
            return '';
        }
        
        $id = $this->db->generateDBID();
        $module1Field = "{$module1}_{$this->key1}";
        $moduleAField = "{$moduleA}_{$this->keyA}";
        $key1 = $this->key1;
        $keyA = $this->keyA;
        $module1Value = $graviton1->$key1;
        $moduleAValue = $gravitonA->$keyA;
        $sql = "INSERT INTO {$this->table} set id = '$id', $module1Field = '$module1Value', $moduleAField = '$moduleAValue', deleted = 0";
        return $sql;
    }
    
    
    public function clear($graviton)
    {
        $deleteOK = $this->db->query($this->generateSQLClear($graviton));
        return $deleteOK;
    }
    
    
    public function generateSQLClear($graviton)
    {
        $keyField = $this->getKeyField($graviton);
        $joinTableKeyField = "{$graviton->moduleName}_{$keyField}";
        $sql = "DELETE from {$this->table} where $joinTableKeyField = '{$graviton->$keyField}'";
        return $sql;
    }
    
    
    public function generateSQLJoinClause($fromModule, $keyValue)
    {
        if ($fromModule == $this->module1) {
            $fromKey = $this->key1;
            $joinModule = $this->moduleA;
            $joinKey = $this->keyA;
        } else {
            $fromKey = $this->keyA;
            $joinModule = $this->module1;
            $joinKey = $this->key1;
        }
        
        $joinTableAlias = "{$this->relationshipName}_JoinTable";
        $this->joinModuleAlias = "{$joinModule}_from_{$this->relationshipName}";
        $sql = "\t{$this->joinType} {$this->table} $joinTableAlias on {$joinTableAlias}.{$fromModule}_{$fromKey} = '$keyValue' and {$joinTableAlias}.deleted = 0\n";
        $sql .= "\t{$this->joinType} $joinModule {$this->joinModuleAlias} on {$joinTableAlias}.{$joinModule}_{$joinKey} = {$this->joinModuleAlias}.{$joinKey}\n";
        return $sql;
    }
    
    
    public function getOptionsForSelect($graviton)
    {
        $options = array();
        $linkedModuleName = $this->getOtherModule($graviton);
        $linkedGraviton = new $linkedModuleName();
        $whichModule = $this->whichModuleIsThis($linkedGraviton);
        $displayFields = "{$whichModule}_displayField";
        if (is_array($this->$displayFields)) {
            $nameField = "CONCAT_WS(' ', " . implode(', ', $this->$displayFields) . ') as name';
        } else {
            $nameField = "{$this->$displayFields} as name";
        }
        
        $selectParams = array('fields' => array('id', $nameField));
        $query = $this->db->generateSQLSelect($linkedGraviton, $selectParams);
        $result = $this->db->query($query);
        while($row = $this->db->fetchByAssoc($result)) {
            $options[$row['id']] = $row['name'];
        }
        return $options;
    }
    
    
    public function loadLinkedIDs($graviton)
    {
        $ids = array();
        $linkedModuleName = $this->getOtherModule($graviton);
        $linkedGraviton = new $linkedModuleName();
        $sql = $this->db->generateSQLSelect($linkedGraviton, array('fields' => array('id')));
        $subselect = "select {$linkedGraviton->moduleName}_id from {$this->table} where {$graviton->moduleName}_id = '{$graviton->id}'";
        $sql .= "where id in ($subselect)";
        
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchByAssoc($result)) {
            $ids[] = $row['id'];
        }
        return $ids;
    }
    
    
    public function loadLinkedRecordsAsKeyValuePairs($graviton)
    {
        $linkedRecords = array();
        $linkedIDs = implode("','", $this->loadLinkedIDs($graviton));
        $linkedModuleName = $this->getOtherModule($graviton);
        $linkedGraviton = new $linkedModuleName();
        $which = $this->whichModuleIsThis($linkedGraviton) . '_displayField';
        $displayField = $this->$which;
        $linkedGraviton = new $linkedModuleName();
        
        $sql = "select id, $displayField from {$linkedGraviton->table} where id in ('$linkedIDs')";
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchByAssoc($result)) {
            $linkedRecords[$row['id']] = $row[$displayField];
        }
        return $linkedRecords;
    }
    
    
    public function loadLinkedGravitons($graviton)
    {
        $linkedModuleName = $this->getOtherModule($graviton);
        $result = $this->db->query($this->generateSQLLoadLinkedGravitons($graviton));
        while ($row = $this->db->fetchByAssoc($result)) {
            $linkedGraviton = new $linkedModuleName();
            $linkedGraviton->populateFromHash($row);
            $this->linkedRecords[] = $linkedGraviton;
        }
    }
    
    
    public function generateSQLLoadLinkedGravitons($graviton)
    {
        $linkedModuleName = $this->getOtherModule($graviton);
        $linkedGraviton = new $linkedModuleName();
        $sql = $this->db->generateSQLSelect($linkedGraviton);
        
        // select all of the linked module's records from the relationship's join table.
        $subselect = "select {$linkedModuleName}_id from {$this->table} where {$graviton->moduleName}_id = '{$graviton->id}'";
        
        $sql .= "where id in ($subselect)";
        return $sql;
    }
}