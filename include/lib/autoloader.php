<?php
function gravitonLoader($className)
{
   $locations = array(
      "modules/$className/$className.php",
      "include/abstracts/$className.php",
      "include/interfaces/$className.php",
      "MVC/$className.php",
   );
   
   foreach ($locations as $modulePath) {
      if (file_exists($modulePath)) {
         require_once($modulePath);
         return true;
      }
   }
   
   Controller::reportError("gravitonLoader() could not load $className.");
}

spl_autoload_register('gravitonLoader');

?>
