<?php
namespace Gravitycar\lib\abstracts;
use \Gravitycar\modules as Modules;
/**
 * RestAPI
 *
 * This is the base class for all rest api classes. Any valid endpoint must 
 * register itself through a class that extends this abstract.
 *
 * RULES ABOUT API ENDPOINT PATHS.
 * - starts with a module name
 */
 
abstract class RestAPI implements \Gravitycar\lib\interfaces\api_interface
{
    public $apiHandlers = array();
    
    public function __construct()
    {
        $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
        $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
        $this->db = \Gravitycar\lib\managers\DBManager::singleton();
        $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
    }
    
    public function registerEndpoints()
    {
    }
    
    
    public function getGraviton()
    {
        $moduleName = "\\Gravitycar\\modules\\{$_REQUEST['module']}\\{$_REQUEST['module']}";
        
        // class_exists() will not recognize aliases.
        if (!class_exists($moduleName)) {
            throw new \Gravitycar\lib\exceptions\InvalidModule("The Module '{$moduleName}' is not defined.");
        }
        
        try {
            $graviton = new $moduleName();
        } catch (Exception $e) {
            throw new \Gravitycar\lib\exceptions\InvalidModule("The Module '{$_REQUESET['module']}' could not be instantiated.");
        }
        
        return $graviton;
    }
    
    
    public function getOneRecord()
    {
        try {
            $graviton = $this->getGraviton();
            $graviton->detail();
        } catch (EmptyDBQueryResult $e) {
            throw new \Gravitycar\lib\restApi\Exceptions\ApiException404($e->getMessage(), $e);
        } catch (InvalidModule $e) {
            throw new \Gravitycar\lib\restApi\Exceptions\ApiException404($e->getMessage(), $e);
        } catch (RecordIDNotSpecified $e) {
            throw new \Gravitycar\lib\restApi\Exceptions\ApiException404($e->getMessage(), $e);
        } catch (Exception $e) {
            throw $e;
        }
        return $graviton->toJSON();
    }
    
    
    public function getList()
    {
        $records = array();
        $graviton = $this->getGraviton();
        $params = array();
        $sql = $this->db->generateSQLSelect($graviton, $params);
        $result = $this->db->query($sql);
        while ($row = $this->db->fetchByAssoc($result)) {
            $graviton->populateFromHash($row);
            $records[] = $graviton->toHash();
        }
        return json_encode($records);
    }
    
    
    public function getSearchResults()
    {
    }
    
    
    public function deleteOneRecord($recordParams)
    {
    }
    
    
    public function saveRecord()
    {
        try {
            $graviton = $this->getGraviton();
            $graviton->detail();
            $graviton->save();
        } catch (EmptyDBQueryResult $e) {
            throw new \Gravitycar\lib\restApi\Exceptions\ApiException404($e->getMessage(), $e);
        } catch (InvalidModule $e) {
            throw new \Gravitycar\lib\restApi\Exceptions\ApiException404($e->getMessage(), $e);
        } catch (RecordIDNotSpecified $e) {
            throw new \Gravitycar\lib\restApi\Exceptions\ApiException404($e->getMessage(), $e);
        } catch (Exception $e) {
            throw $e;
        }
        return $graviton->toJSON();
    }
    
    
    public function updateRecord()
    {
    }
}