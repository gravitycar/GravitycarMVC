<?php
/**
 * class FileMapBuilder
 *
 * The file map builder's job is to build a associative array of all classes
 * in the application and write them out to a file, which will be the 'map'
 * that the autoloader function will use. This means that instead of the 
 * autoloader having to check to see if a class file exists in one of several
 * locations, every class known to the application will already be in the map.
 * The autoloader will then take the name of the class it's looking for, and use
 * that name to look up the path to the class. So the map looks like this:
 *
 * $map = array('Users' => 'modules/Users/Users.php')
 *
 * Since the file map builder operates independently of the main application, 
 * it's not run through index.php. It's run via build scripts or possibly an
 * admin-only update page. Since it's purpose is to set up the autoloader
 * feature, it cannot rely on the autoloader and therefore includes its
 * required libraries explicitly.
 */
require_once('../../../gravitycar.config.php');
require_once('../abstracts/Singleton.php');
require_once('../managers/ConfigManager.php');
require_once('../managers/ErrorManager.php');
require_once('../managers/GravitonLogger.php');
require_once('../interfaces/builder_interface.php');
class FileMapBuilder implements builder
{
    
    public $map = array();
    public $mapPath = '';
    
    /**
     * __construct()
     */
    public function __construct()
    {
      $this->log = GravitonLogger::singleton();
      $this->cfg = ConfigManager::singleton();
      $this->cfg->setConfigFilePath('/var/gravitycar.config.php');
      $this->cfg->init();
      $this->log->error('called loadConfig() again');
      $this->errMgr = ErrorManager::singleton();
      $this->mapPath = $this->cfg->get('root_dir') . '/include/lib/file_map.php';
    }
    
    
    /**
     * searchForClassFiles()
     *
     * Recursively searches the installed directory tree for PHP files that
     * define one or more classes, and then adds that class to the file map.
     *
     * @param string $path - the path to search.
     */
    public function searchForClassFiles($path = '')
    {
        if (empty($path)) {
            $path = $this->cfg->get('root_dir');
        }
        $dh = opendir($path);
        
        if (is_bool($dh)) {
            $this->errMgr->error("'$path' could not be searched!");
            return;
        }
        
        while (false !== ($entry = readdir($dh))) {
            if ($entry == '.' || $entry == '..' || strpos($entry, '.') === 0) {
                continue;
            }
            
            $entryPath = "{$path}/{$entry}";
            if (is_dir($entryPath)) {
                $this->searchForClassFiles($entryPath);
                continue;
            }
            
            if (substr($entry, -4) == '.php') {
                $this->mapClassDeclarations($entryPath);
            }
        }
    }
    
    
    /**
     * mapClassDeclarations()
     *
     * Reads a given file into an array of lines, and looks for the string
     * 'class ' at the beginning of each line. If it finds that string, it will
     * extract the name of the class and add it to the map.
     *
     * This function will search the entire file, so files that define multiple
     * classes are OK.
     *
     * @param string $filePath - the path to the file you want to search for 
     * class definitions.
     */
    public function mapClassDeclarations($filePath)
    {
        $this->log->debug("searching $filePath for class definitions.");
        $lines = $this->getFileAsArray($filePath);
        foreach ($lines as $line) {
            if (strpos($line, 'class ') === 0 || strpos($line, 'interface ') === 0 || strpos($line, 'abstract class ') === 0) {
                $cleanLine = trim(str_replace(array('abstract ', 'class ', 'interface '), '', $line)) . ' ';
                $firstSpace = strpos($cleanLine, ' ');
                $className = substr($cleanLine, 0, $firstSpace);
                if (!IsSet($this->map[$className])) {
                    $this->map[$className] = $filePath;
                    $this->log->debug("mapped '$className' to $filePath");
                } else {
                    $this->errMgr->error("Duplicate class definition: class '$className' was found in '$filePath' but previously mapped in '{$this->map[$className]}'");
                }
            }
        }
    }
    
    
    /**
     * getFileAsArray()
     *
     * Simply a wrapper for the built-in file() method.
     *
     * @param string $filePath - the path to the file you want to return as an array.
     * @return array - an array of lines in $filePath.
     */
    public function getFileAsArray($filePath)
    {
        return file($filePath);
    }
    
    
    /**
     * writeFileMap()
     *
     * Takes the contents of the map property of this class and writes them out
     * to a valid PHP file.
     *
     * @return mixed - number of bytes written, or false on failure.
     */
    public function writeFileMap()
    {
        $contents = "<?php\n\$map = " . var_export($this->map, true) . ";\n?>";
        $bytesWritten = file_put_contents($this->mapPath, $contents);
        return $bytesWritten;
    }
    
    
    /**
     * run()
     *
     * Searches for files that contain class declarations and then writes a map
     * of all found classes and their files.
     *
     * @param array $params - additional data to be passed in. Free form, can
     *  be any hash.
     * @return bool - true if run is OK, false on error.
     */
    public function run($params)
    {
        $this->searchForClassFiles();
        
        if (empty($this->map)) {
            $this->errMgr->error("Mapping failed - found 0 classes to map.");
            return false;
        }
        
        $mapWritingResults = $this->writeFileMap();
        if ($mapWritingResults === false) {
            $this->errMgr->error("Could not write file map to {$this->mapPath}!");
            return false;
        }
        
        return ($mapWritingResults > 20);
    }
}

$builder = new FileMapBuilder();
$params = array();
$builder->run($params);
?>
