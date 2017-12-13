<?php
function psr4GravitonLoader($className)
{
    $prefix = 'Gravitycar';
    // if the class name doesn't use our prefix, do nothing.
    $prefixLen = strlen($prefix);
    if (strncmp($prefix, $className, $prefixLen) !== 0) {
        return false;
    }
    
    $relativeClassName = substr($className, $prefixLen);
    $classPath = getcwd() . str_replace('\\', '/', $relativeClassName) . '.php';
    if (file_exists($classPath)) {
        include($classPath);
    }
}


// file map functionality has been removed in favor of psr4GravitonLoader().
function gravitonLoader($qualifiedClassName)
{
    return false;
    static $map;
    static $loadedClasses = array();
    if (empty($map)) {
        $map = loadFileMap();
    }
    
    $className = array_pop(explode('\\', $qualifiedClassName));
    
    if (in_array($className, $loadedClasses)) {
        return true;
    }

    if (IsSet($map[$className])) {
        include($map[$className]); 
        $loadedClasses[] = $className;
        return true;
    }
}


// file map functionality has been removed in favor of psr4GravitonLoader().
function loadFileMap()
{
    return false;
    $file_map_path = Gravitycar\lib\builders\FileMapBuilder::getFileMapPath();
    if (file_exists($file_map_path)) {
        require_once($file_map_path);
    } else {
        buildFileMap();
        require_once($file_map_path);
    }
    return $map;
}

// file map functionality has been removed in favor of psr4GravitonLoader().
function buildFileMap()
{
    return false;
    $base = getcwd();
    require_once("$base/lib/builders/FileMapBuilder.php");
    $fmb = new Gravitycar\lib\builders\FileMapBuilder();
    $fmb->run();
}

//spl_autoload_register('gravitonLoader');
spl_autoload_register('psr4GravitonLoader');
?>
