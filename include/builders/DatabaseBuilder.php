<?php
/**
 * class DatabaseBuilder
 *
 * The DatabaseBuilder class is responsible for creating and maintaining the
 * database. 
 *
 * It has to be able to detect whether or not the database name specified in
 * the config file exists, and create it if it does not exist.
 *
 * It has to loop through every module defined in the module_action_view_map
 * file, and for each of those module discover if the table for them exists, and
 * then inspect the table and compare it to what's expected to be there based
 * on the module's propdefs. If the table doesn't match the propdefs, the table
 * must be updated with alter table statements. 
 *
 * Every module must also be inspected for relationships. The DBB must check 
 * every relationship in every module by making sure that the relationship
 * table for that relationship exists.
 */

//require_once('../../../gravitycar.config.php');
//require_once('../interfaces/builder_interface.php');
class DatabaseBuilder implements builder
{
    public function __construct($params)
    {
        $this->log = GravitonLogger::singleton();
        $this->cfg = ConfigManager::singleton();
        $this->cfg->setConfigFilePath('/var/gravitycar.config.php');
        $this->cfg->init();
        $this->errMgr = ErrorManager::singleton();
        $this->db = DBManager::singleton();
    }
    
    
    /**
     * getModuleList()
     *
     * Returns a list of all the module names that our application knows about.
     *
     * @return array - an array of module names.
     */
    public function getModuleList()
    {
        $map = new Module_Action_View_Map();
        return $map->listModules();
    }
    
    
    
    /** 
     * generateSQLCreateTable()
     *
     * Creates the SQL for a CREATE TABLE statement for a particular module based on 
     * the passed in Graviton
     *
     * @param Graviton - a Graviton object to geneate the sql for.
     * @return string - the sql string.
     */
    public function generateSQLCreateTable($graviton)
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$graviton->table} (";
        $clauses = array();
        foreach ($graviton->propdefs as $fieldName => $defs) {
            if (!$this->db->propertyIsInDB($defs)) {
                continue;
            }
            
            $type = $this->db->getMySQLType($defs);
            $len = IsSet($defs['len']) ? "({$defs['len']})" : '';
            $primary = IsSet($defs['isPrimary']) ? 'PRIMARY KEY UNIQUE NOT NULL' : '';
            $clauses[] = " $fieldName $type $primary";
        }
        
        return $sql . implode(', ', $clauses) . ')';
    }
    
    
    public function generateSQLCreateDB()
    {
        $databaseName = $this->cfg->get('db.name');
        $sql = "CREATE DATABASE $databaseName";
        return $sql;
    }
    
    
    public function databaseExists()
    {
        return !empty($this->db->databaseName);
    }
    
    
    public function createDB()
    {
        $this->log->debug("Creating database '{$this->cfg->get('db.name')}'");
        $sql = $this->generateSQLCreateDB();
        $createDBOK = $this->db->query($sql);
        if (!$createDBOK) {
            $errorMsg = 'Create database Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error();
            $this->errMgr->error("Could not create database '{$this->cfg->get('db.name')}'.\n$errorMsg");
            
            if (mysqli_connect_errno() == '1007') {
                $this->log->debug("Database '{$this->cfg->get('db.name')}' actually does exist. Trying to re-connect.");
                $this->db->connect();
            }
        } else {
            $this->log->debug("Created new database '{$this->cfg->get('db.name')}'");
            // reset the database connection - it will use the db.name value by default.
            $this->db->connect();
        }
        return $createDBOK;
    }
    
    
    public function getAllTables()
    {
        $tables = array();
        $result = $this->db->query("show tables");
        while ($row = $this->db->fetchByAssoc($result)) {
            $tables = $row['Tables_in_' . $this->cfg->get('db.name')];
        }
        return $tables;
    }
    
    
    
    public function createTable($graviton)
    {
        $sql = $this->generateSQLCreateTable($graviton);
        $this->log->debug("Creating table {$graviton->table}\n$sql");
        $queryOK = $this->db->query($sql);
        return $queryOK;
    }
    
    
    
    public function verifyTable($graviton)
    {
        $tableFields = $this->getTableDescription($graviton->table);
        foreach ($graviton->propdefs as $prop => $defs) {
            $mysqlTypeShouldBe = $this->db->getMySQLType($defs);
            $mysqlTypeIs = $tableFields[$prop];
            if ($mysqlTypeShouldBe != $mysqlTypeIs) {
                $this->alterTable();
            }
        }
    }
    
    
    
    public function alterTable()
    {
    }
    
    
    
    public function getTableDescription($table)
    {
        $fields = array();
        $result = $this->db->query("describe $table");
        while ($row = $this->db->fetchByAssoc($result)) {
            $fields[$row['Field']] = $row;
        }
        return $fields;
    }
    
    
    
    public function run($params) 
    {
        $this->log->debug("Running DatabaseBuilder");
        
        if (!$this->databaseExists()) {
            $dbExists = $this->createDB();
        } else {
            $dbExists = true;
        }
        
        $modules = $this->getModuleList();
        $tables = $this->getAllTables();
        
        foreach ($modules as $module) {
            $this->log->debug("Checking db tables for '$module'.");
            $graviton = new $module();
            
            if (empty($graviton->propdefs)) {
                continue;
            }
            
            if (!in_array($graviton->table, $tables)) {
                $this->createTable($graviton);
            } else {
                $this->verifyTable($graviton);
            }
            
        }
    }
}

?>
