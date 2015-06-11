<?php
class Users extends Graviton
{
    public $name = "Users";
    public $table = "Users";
    
    public function save()
    {
        // only new users have to pass in a password. If this is an update, make
        // password not required.
        if (!empty($_POST["{$this->name}_id"])) {
            $this->setPropDefAttribute('password_hash', 'required', false);
            
            // if no password is sent in via POST, don't update it.
            if (empty($_POST["{$this->name}_password_hash"])) {
                $this->setPropDefAttribute('password_hash', 'source', 'non-db');
            }
        }
        
        parent::save();
    }
}
?>
