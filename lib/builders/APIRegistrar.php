<?php
namespace Gravitycar\lib\builders;
use \Gravitycar\lib\managers as Managers, \Gravitycar\lib\abstracts as Abstracts;
/**
 *
 */

class APIRegistrar implements \Gravitycar\lib\interfaces\builder
{
    public $apiClasses = array();
    public $registry;
    
    public function __construct()
    {
      $this->log = Managers\GravitonLogger::singleton();
      $this->cfg = Managers\ConfigManager::singleton();
      $this->errMgr = Managers\ErrorManager::singleton();
    }
    
    
    public function searchForAPIClasses($path = '')
    {
        if (empty($path)) {
            $path = $this->cfg->get('root_dir');
        }
        
        $dh = opendir($path);
        
        if (is_bool($dh)) {
            $this->errMgr->error("'$path' could not be searched for API Classes.");
            return;
        }
        
        while (false !== ($entry = readdir($dh))) {
            if ($entry == 'docs' || $entry == '.' || $entry == '..' || strpos($entry, '.') === 0) {
                continue;
            }
            
            $entryPath = "{$path}/{$entry}";
            if (is_dir($entryPath)) {
                $this->searchForAPIClasses($entryPath);
                continue;
            }
            
            if (substr($entry, -7) != 'API.php') {
                //$this->log->debug("$entryPath is not a PHP file.");
                continue;
            }
            
            require_once($entryPath);
            $className = pathinfo($entryPath, PATHINFO_FILENAME);
            $qualifiedClassName = str_replace($this->cfg->get('root_dir'), '\Gravitycar', $entryPath);
            $qualifiedClassName = str_replace('/', '\\', $qualifiedClassName);
            $qualifiedClassName = str_replace('.php', '', $qualifiedClassName);
            
            if (!class_exists($qualifiedClassName)) {
                $this->log->debug("$entryPath does not define $qualifiedClassName.");
                continue;
            }
            
            $reflection = new \ReflectionClass($qualifiedClassName);
            if (!$reflection->isInstantiable()) {
                $this->log->debug("$qualifiedClassName is not instantiable.");
                continue;
            }
            
            if (is_subclass_of($qualifiedClassName, "\Gravitycar\lib\abstracts\RestAPI")) {
                $this->log->debug("$entryPath defines $qualifiedClassName which extends RestAPI, looks like an API class!");
                $this->apiClasses[] = $qualifiedClassName;
            }
        }
    }
    
    
    public function addEndpointsToRegistry($endpoints)
    {
        foreach ($endpoints as $endpoint) {
            $requestType = $endpoint['requestType'];
            $module = $endpoint['module'];
            $elementCount = count($endpoint['pathVars']);
            $this->registry[$requestType][$module][$elementCount] = array(
                'pathVars' => $endpoint['pathVars'],
                'class' => $endpoint['class'],
                'method' => $endpoint['method'],
            );
        }
    }
    
    
    public function writeRegistryFile()
    {
        $cacheDir = $this->cfg->get('root_dir') . '/' . $this->cfg->get('cache_dir');
        if (!file_exists($cacheDir)) {
            $this->log->debug("$cacheDir does not exist");
            mkdir($cacheDir, 0755, true);
        }
        $registryFilePath = "{$cacheDir}/apiRoutes.php";
        $fh = fopen($registryFilePath, 'w+');
        if (!$fh) {
            $this->log->debug("$registryFilePath could not be opened for writing.");
        }
        $registryString = var_export($this->registry, true);
        $writeOK = fwrite($fh, "<?php\n\$apiRoutes = $registryString;");
        if (!$writeOK) {
            $this->log->debug("Could not write to to $registryFilePath");
        }
        fclose($fh);
    }
    
    
    /**
     * run()
     *
     * Searches the codebase for any classes that extend the RestAPI abstract
     * class. Then it will call their registerEndpoints() method, which will
     * return an array of all the endpoints that class knows how to handle. All
     * of those endpoints will be collected in one large array, and that array
     * will then be output to a php file in cache. Then any REST API calls that
     * come into this application will use that cache file to figure out which
     * class and method should respond to the request.
     */
    public function run($params = array())
    {
        $this->searchForAPIClasses();
        foreach ($this->apiClasses as $className) {
            $apiClass = new $className();
            $endpoints = $apiClass->registerEndpoints();
            $this->addEndpointsToRegistry($endpoints);
        }
        $this->writeRegistryFile();
    }
}