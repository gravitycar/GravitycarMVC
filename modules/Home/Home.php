<?php
class Home extends Graviton
{
   public $name = "Home";
   
   public function execute()
   {
      print("I'm from execute()");
      $this->log->debug("I'm a debug message", true);
      $this->log->error("I'm an error message", true);
   }
}
?>
