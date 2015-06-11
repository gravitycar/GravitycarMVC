<?php
/**
 * BuildMaster
 *
 * The build master provides one point of entry to access the other builders. By 
 * instantiating this class and calling its run() method and passing it the name of a
 * builder you can run that builder. Whatever the builder does is up to that builder's
 * particular implementation.
 *
 */
//require_once('../include/abstracts/Singleton.php');
//require_once('../include/managers/ConfigManager.php');
//require_once('../include/managers/GravitonLogger.php');
//require_once('../include/managers/ErrorManager.php');
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
    public function __construct($runTimeOptions)
    {
        $this->runTimeOptions = $runTimeOptions;
        $this->cfg = ConfigManager::Singleton();
        $this->log = GravitonLogger::Singleton();
        $this->errMgr = ErrorManager::Singleton();
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
    public function instantiate($builderClassName)
    {
        $path = $this->cfg->get('root_dir') . "/include/builders/{$builderClassName}.php";
        if (!file_exists($path)) {
            $this->errMgr->error("Class file for class '$builderClassName' not found in '$path'");
            return null;
        }
        
        require_once($path);
        
        if (!class_exists($builderClassName)) {
            $this->errMgr->error("$builderClassName is not defined in '$path'");
        }
        
        $builder = new $builderClassName($this->runTimeOptions);
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
