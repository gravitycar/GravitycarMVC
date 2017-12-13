<?php
namespace Gravitycar\modules\Users;
class Users extends \Gravitycar\lib\abstracts\Graviton
{
    public $moduleName = "Users";
    public $table = "Users";
    
    
    public function getDetailFields()
    {
        $fieldNames = array();
        foreach ($this->propdefs as $fieldName => $defs) {
            if ($fieldName == 'password_hash') {
                continue;
            }
            
            if ($defs['datatype'] == 'relationship') {
                continue;
            }
            
            $fieldNames[] = "{$this->table}.{$fieldName}";
        }
        return $fieldNames;
    }
    
    
    public function getListFields()
    {
        return $this->getDetailFields();
    }
    
    
    public function save()
    {
        // only new users have to pass in a password. If this is an update, make
        // password not required.
        if (!empty($_POST["id"])) {
            $this->setPropDefAttribute('password_hash', 'required', false);
        }
            
        // if no password is sent in via POST, don't update it.
        if (empty($_POST["password_hash"])) {
            $this->setPropDefAttribute('password_hash', 'source', 'non-db');
        }
        
        parent::save();
    }
}
?>
