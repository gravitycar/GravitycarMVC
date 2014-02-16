<?php
/**
 * DBManager
 *
 * Class for all things database for any module:
 * 
 * - Creating tables
 * - Dropping tables
 * - Altering tables.
 * - Selects, Inserts, Updates and Deletes
 *
 * This class should generate the SQL statements above based on the propdefs for any
 * module. A module will pass in its propdefs and various methods of this class are
 * expected to return executable SQL, or execute said SQL.
 */
class DBManager extends Singleton
{
    /** @var string $userName
    The user name to log into the database. */
    private $userName = '';
    
    /** @var string $password 
    The password to use to log into the database */
    private $password = '';
    
    /** @var string $hostName
    The name/IP address of the database host machine */
    private $hostName = '';
    
    /** @var string $databaseName
    The name of the database */
    private $databaseName = '';
    
    /** @var mysqli 
    The database object */
    private $mysqli = null;
    
    public function init()
    {
        parent::init();
        $this->connect();
    }
    
    protected function connect()
    {
        $this->mysqli = mysqli_init();
        $this->userName = $this->cfg->get('db.user');
        $this->password = $this->cfg->get('db.pass');
        $this->hostName = $this->cfg->get('db.host');
        $this->databaseName = $this->cfg->get('db.name');
        
        $connectOK = $this->mysqli->real_connect($this->hostName, 
                                   $this->userName, 
                                   $this->password, 
                                   $this->databaseName);
        
        if (!$connectOK) {
            $errorMsg = 'Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error(); 
            $this->errMgr->error($errorMsg);
            $this->log->error($errorMsg);
            die($errorMsg);
        }
        
    }
    
    
    public function fetchByAssoc($result)
    {
        return $result->fetch_assoc();
    }
    
    
    /**
     * query()
     *
     * Sends an sql query to the database.
     *
     * NOTE - it's NOT this method's job to scrub the user data in the SQL!
     * That is the job of whatever method PASSES the SQL to this method. 
     * Individual pieces of user-submitted data should be scrubbed invidually 
     * DBManager::formatDatum() (which relies partially on mysqli::real_escape_string().
     *
     * @param string $sql - the sql you want to execute against the database.
     */
    public function query($sql)
    {
        return $this->mysqli->query($sql);
    }
    
    
    /**
     * getMySQLType()
     *
     * Looks at the datatype from the propdefs for a particular field and returns the
     * appropriate MySQL data type value for creating/altering tables.
     *
     * @param hash $propdefs - the definitions for a single field (not the whole
     *  propdefs array for the module).
     * @return string - a mysql datatype name.
     */
    private function getMySQLType($propdefs)
    {
        $type = $propdefs['datatype'];
        $mysqlType = '';
        switch ($type) {
            case "bool":
                $mysqlType = 'bool';
            break;
            
            case "float":
            case "numeric":
                $mysqlType = 'double';
            break;
            
            case "datetime":
                $mysqlType = 'datetime';
            break;
                
            case "date":
                $mysqlType = 'date';
            break;
            
            case "integer":
            case "int":
                $mysqlType = 'int';
            break;
            
            case "string":
                $maxLength = IsSet($propdefs['len']) ? IsSet($propdefs['len']) : 255;
                if ($maxLength <= 255) {
                    $mysqlType = 'tinytext';
                } else if ($maxLength <= 65535) {
                    $mysqlType = 'text';
                } else {
                    $mysqlType = 'longtext';
                }
                
                if ($propdefs['isPrimary']) {
                    $mysqlType = "varchar({$propdefs['len']})";
                }
            break;
            
            case "array":
                $mysqlType = 'text';
            break;
            
            case "object":
                $mysqlType = 'blob';
            break;
            
            default:
                $mysqlType = 'text';
            break;
        }
        
        return $mysqlType;
    }
    
    
    /**
     * generateDBID()
     *
     * Creates a (probably) unique id for a table row. 
     *
     * @param int $idLength - how long the ID can be (typically 32 characters).
     * @return string - a hex string 32 characters long.
     */
    public function generateDBID($idLength = 16)
    {
        $bytes = openssl_random_pseudo_bytes($idLength);
        $hex = bin2hex($bytes);
        return $hex;
    }
    
    
    /**
     * propertyIsInDB()
     *
     * Returns true if the passed in property definition array indicates that
     * the property's value should be stored in the database, false if not.
     *
     * @param hash $defs - a property definition hash
     * @return bool - true if property is stored in db, false otherwise.
     */
    public function propertyIsInDB($defs)
    {
        if (!IsSet($defs['source'])) {
            return false;
        }
        
        if ($defs['source'] != 'db') {
            return false;
        }
        
        if ($defs['datatype'] == 'relationship') {
            return false;
        }
        
        return true;
    }
    
    
    
    /** 
     * generateSQLCreateTable()
     *
     * Creates the SQL for a CREATE TABLE statement for a particular module based on 
     * the passed in 
     *
     * @param Graviton - a Graviton object to geneate the sql for.
     * @return string - the sql string.
     */
    public function generateSQLCreateTable($graviton)
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$graviton->table} (";
        $clauses = array();
        foreach ($graviton->propdefs as $fieldName => $defs) {
            if (!$this->propertyIsInDB($defs)) {
                continue;
            }
            
            $type = $this->getMySQLType($defs);
            $len = IsSet($defs['len']) ? "({$defs['len']})" : '';
            $primary = IsSet($defs['isPrimary']) ? 'PRIMARY KEY UNIQUE NOT NULL' : '';
            $clauses[] = " $fieldName $type $primary";
        }
        
        return $sql . implode(', ', $clauses) . ')';
    }
    
    
    public function formatDatum($defs, $datum)
    {
        print("<pre>" . var_export($datum, true) . "</pre>");
        if (is_scalar($datum)) {
            $cleanDatum = $this->mysqli->real_escape_string($datum);
        } else {
            $this->log->debug(var_export($datum, true));
        }
        
        if (is_array($datum)) {
            $tempDefs = array('datatype' => 'string');
            foreach ($datum as $value) {
                $cleanDatum[] = $this->formatDatum($tempDefs, $value);
            }
        }
        
        switch ($defs['datatype'])
        {
            case "bool":
            case "float":
            case "numeric":
            case "int":
                return $cleanDatum;
            break;
            
            case "array":
                return "(" . implode(", ", $cleanDatum) . ")";
            break;
            
            default:
                return "'" . str_replace("'", "\'", $cleanDatum) . "'";
            break;
        }
    }
    
    
    public function generateSQLInsert($graviton, $data)
    {
        $sql = "INSERT INTO {$graviton->table} ";
        $clauses = array();
        foreach ($graviton->propdefs as $fieldName => $defs) {
            if (!$this->propertyIsInDB($defs)) {
                continue;
            }
            
            if ($defs['required'] && empty($data[$fieldName])) {
                if (!empty($defs['defaultvalue'])) {
                    $data[$fieldName] = $defs['defaultvalue'];
                } else {
                    $this->errMgr->error("Cannot insert {$graviton->name} - $fieldName is empty and has no default!");
                    return '';
                }
            }
            
            $clauses[$fieldName] = $this->formatDatum($defs, $data[$fieldName]);
        }
        
        $sql .= '(' . implode(', ', array_keys($clauses)) . ')';
        $sql .= ' VALUES (' . implode(', ', $clauses) . ')';
        return $sql;
    }
    
    
    public function generateSQLUpdate($graviton, $data)
    {
        $sql = "UPDATE {$graviton->table} SET ";
        $clauses = array();
        foreach ($data as $propName => $propValue) {
            $defs = $graviton->getPropDef($propName);
            if (!$this->propertyIsInDB($defs)) {
                continue;
            }
            
            if ($defs['required'] && empty($data[$propName])) {
                if (!empty($defs['defaultvalue'])) {
                    $data[$propName] = $defs['defaultvalue'];
                } else {
                    $this->errMgr->error("Cannot update {$graviton->name} - $propName is empty and has no default!");
                    return '';
                }
            }
            
            $cleanPropValue = $this->formatDatum($defs, $propValue);
            $clauses[] = "$propName = $cleanPropValue";
        }
        
        $searchParams = $this->generateSearchParam($graviton, 'id');
        $whereClause = $this->generateWhereClause($graviton, $searchParams);
        $sql .= implode(', ', $clauses) . " WHERE $whereClause";
        return $sql;
    }
    
    
    public function generateSQLDelete($graviton)
    {
        $sql = "DELETE FROM {$graviton->table} ";
        
        $searchParams = $this->generateSearchParam($graviton, 'id');
        $whereClause = $this->generateWhereClause($graviton, $searchParams);
        
        $sql .= " WHERE $whereClause";
        return $sql;
    }
    
    
    public function generateSearchParam($graviton, $searchColumns, $searchTerms=array(), $exactMatch=true)
    {
        $params = array();
        if (!is_array($searchColumns)) {
            $searchColumns = array($searchColumns);
        }
        
        foreach ($searchColumns as $column) {
            $params[$column] = array();
            $defs = $graviton->getPropDef($column);
            if (empty($defs)) {
                $this->log->error("$column is not a valid property of a {$graviton->name} - skipping as a search param.");
                continue;
            }
            
            if (!IsSet($defs['source']) || $defs['source'] != 'db') {
                $this->log->error("$column is not a database value for a {$graviton->name} - skipping as a search param.");
                continue;
            }
            
            if (!is_array($searchTerms) || !IsSet($searchTerms[$column])) {
                
                $searchTerms[$column] = $graviton->$column;
            }
            
            if (!IsSet($searchTerms[$column])) {
                $this->log->error("No search term for '{$graviton->name}.$column' provided - skipping as a search param.");
                continue;
            }
            
            $params[$column]['value'] = $searchTerms[$column];
            
            if ($defs['datatype'] == 'array') {
                $params[$column]['operator'] = 'in';
            } else if ($exactMatch !== true) {
                $params[$column]['operator'] = 'like';
            }
        }
        
        return $params;
    }
    
    
    /**
     * generateWhereClause()
     *
     * Creates a where clause (without the word "where" - you supply that) based 
     * on pairs of column names mapped to hashes of search term information. Here
     * are  a couple of examples:
     *
     * searchParams['id'] => array('value' => '12345678', 'operator' => '=')
     * searchParams['id'] => array('value' => array('12345678', 'ABCDEFGH'), 'operator' => 'in')
     *
     * The resulting where clause should be suitable for updates, deletes and selects.
     * This method is supposed to be for relatively simple statements - where clauses with
     * deeply nested logic are probably best created manually rather than with 
     * this method.
     *
     * @param Graviton $graviton - a graviton object.
     * @param hash $searchParams - which columns/values you're search for, and which operator
     *  to use.
     * @param string $and_or - Whether to join your various sub-clauses with an AND
     *  or an OR. Default is ' AND '.
     * @return string - an SQL where clause.
     */
    public function generateWhereClause($graviton, $searchParams, $and_or = ' AND ')
    {
        /*
        searchParams['id']
        */
        $clause = '';
        $pairs = array();
        foreach ($searchParams as $columnName => $termData) {
            $defs = $graviton->getPropDef($columnName);
            $formattedValue = $this->formatDatum($defs, $termData['value']);
            $operator = empty($termData['operator']) ? '=' : $termData['operator'];
            $columnName = "{$graviton->table}.$columnName";
            $pairs[] = "$columnName $operator $formattedValue";
        }
        $clause = implode($and_or, $pairs);
        return $clause;
    }
}
?>
