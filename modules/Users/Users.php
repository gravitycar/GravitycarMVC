<?php
class Users extends Graviton
{
    public $name = "Users";
    public $table = "Users";
    
    public function execute()
    {
        $data = array(
          'id' => $this->db->generateDBID(),
          'first_name' => 'Mike;',
          'last_name' => 'Andersen',
          'email_address' => 'mike@gravitycar.com',
          'user_name' => 'mike@gravitycar.com',
          'password_hash' => crypt('bageldog'),
          'phone_number' => '408-264-4044',
        );
        $this->populateFromHash($data);
        /*
        print($this->db->generateSQLCreateTable($this));
        print("<br/>");
        
        print($this->db->generateSQLInsert($this, $data));
        print("<br/>");
        $this->id = 'abadmotherfuckingid';
        $updateData = array('user_name' => 'Bad Motherfucker', 'email_address' => 'badmotherfucker@gravitycar.com');
        print($this->db->generateSQLUpdate($this, $updateData));
        print("<br/>");
        
        print($this->db->generateSQLDelete($this));
        print("<br/>");
        */
    }
}
?>
