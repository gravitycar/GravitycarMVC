<?php
function gravitonLoader($className)
{
   static $loadedClasses = array();
   
   if (in_array($className, $loadedClasses)) {
      return true;
   }
   
   $locations = array(
      "modules/$className/$className.php",
      "include/abstracts/$className.php",
      "include/interfaces/$className.php",
      "include/exceptions/$className.php",
      "include/managers/$className.php",
      "MVC/$className.php",
   );
   
   foreach ($locations as $modulePath) {
      if (file_exists($modulePath)) {
         $loadedClasses[] = $className;
         require_once($modulePath);
         return true;
      }
   }
   
   Controller::reportError("gravitonLoader() could not load $className.");
}

spl_autoload_register('gravitonLoader');

?>
