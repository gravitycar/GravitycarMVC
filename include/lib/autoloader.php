<?php
require_once('file_map.php');
function gravitonLoader($className)
{
    global $map;
    //$log = GravitonLogger::singleton();
    static $loadedClasses = array();
    
    if (in_array($className, $loadedClasses)) {
        return true;
    }

    if (IsSet($map[$className])) {
        require_once($map[$className]); 
        $loadedClasses[] = $className;
        return true;
    } 
    
    //Controller::reportError("gravitonLoader() could not load $className.");
}

spl_autoload_register('gravitonLoader');
?>
