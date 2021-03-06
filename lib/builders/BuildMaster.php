<?php
namespace Gravitycar\lib\builders;
/**
 * BuildMaster
 *
 * The build master provides one point of entry to access the other builders. By 
 * instantiating this class and calling its run() method and passing it the name of a
 * builder you can run that builder. Whatever the builder does is up to that builder's
 * particular implementation.
 *
 */
require_once('lib/abstracts/Singleton.php');
require_once('lib/managers/ConfigManager.php');
require_once('lib/managers/GravitonLogger.php');
require_once('lib/managers/ErrorManager.php');
class BuildMaster
{
    public $runTimeOptions = array();
    /**
     * __construct()
     *
     * Instantiates this class.
     *
     * @param hash $runTimeOptions - a hash of runtime options - can be empty if the builder
     *  doesn't really require any params.
     */
    public function __construct($runTimeOptions = array())
    {
        $this->runTimeOptions = $runTimeOptions;
        $this->cfg = \Gravitycar\lib\managers\ConfigManager::Singleton();
        $this->log = \Gravitycar\lib\managers\GravitonLogger::Singleton();
        $this->errMgr = \Gravitycar\lib\managers\ErrorManager::Singleton();
    }
    
    
    /**
     * instantiate()
     *
     * Instantiates the builder we want to run and returns the instantiation if the
     * class file can be found in the include/builders/ directory.
     *
     * @param string $builderClassName - the name of the builder class, like 
     *  'FileMapBuilder' (without the .php).
     * @return mixed - either an instantiation of the specified class or NULL if the
     *  class file cannot be found.
     */
    public function instantiate($qualifiedBuilderClassName)
    {
        $builderClassName = array_pop(explode('\\', $qualifiedBuilderClassName));
        $path = $this->cfg->get('root_dir') . "/lib/builders/{$builderClassName}.php";
        if (!file_exists($path)) {
            $this->errMgr->error("Class file for class '$builderClassName' not found in '$path'");
            return null;
        }
        
        require_once($path);
        
        if (!class_exists($qualifiedBuilderClassName)) {
            $this->errMgr->error("$qualifiedBuilderClassName is not defined in '$path'");
        }
        
        $builder = new $qualifiedBuilderClassName($this->runTimeOptions);
        return $builder;
    }
    
    
    
    /**
     * runBuilder()
     *
     * Instantiates the passed in builder class and calls its run() method.
     *
     * @param string $builderClassName - the name of the class we want to run.
     * @param hash $runTimeOptions - a hash of options specific to the builder - may
     *  be empty if the builder requires no options.
     * @return mixed - the result of the run() method or false on failure.
     */
    public function runBuilder($builderClassName, $runTimeOptions = false)
    {
        if ($runTimeOptions) {
            $this->runTimeOptions = $runTimeOptions;
        }
        
        $builderClassName = "\Gravitycar\lib\builders\\$builderClassName";
        
        $builder = $this->instantiate($builderClassName);
        if (!is_null($builder) && is_a($builder, $builderClassName)) {
            return $builder->run($this->runTimeOptions);
        } else {
            $this->errMgr->error("Could not run class '$builderClassName' - instantiation failed.");
            return false;
        }
    }
}
?>
