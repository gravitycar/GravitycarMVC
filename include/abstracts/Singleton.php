<?php
/**
 * Base class for all singleton classes. Gives a central location for the singleton
 * logic.
 */
abstract class Singleton
{
   /** @var GravitonLogger the loger object to log messages */
    public $log = null;
    
    /** @var ErrorManager for reporting errors */
    public $errMgr = null;
    
    /** @var ConfigManager for getting config vars */
    public $cfg = null;
    
    protected static $instances = array();
    /**
     * __construct()
     *
     * The constructor method. Must be protected for a singleton so that no other 
     * class can instantiate this class.
     *
     * @return void
     */
    protected function __construct()
    {
    }
    
    
    /**
     * singleton()
     *
     * Always returns the same instance of this class. Is static so it can be
     * called without instantiating the class
     *
     * @return Singleton - an instatiation of the calling class.
     */
    public static function singleton()
    {
        $class = get_called_class();
        if (!IsSet(self::$instances[$class])) {
            self::$instances[$class] = new $class();;
            self::$instances[$class]->init();
        }
        return self::$instances[$class];
    }
    
    
    /**
     * init()
     *
     * Basic setup for all singletons - adds the error manager and the
     * logger to all singletons (except the error manager and logger).
     *
     * @return void
     */
    protected function init()
    {
        if (get_called_class() != 'ErrorManager') {
            $this->errMgr = ErrorManager::singleton();
        }
        
        if (get_called_class() != 'GravitonLogger') {
            $this->log = GravitonLogger::singleton();
        }
        
        
        if (get_called_class() != 'ConfigManager') {
            $this->cfg = ConfigManager::singleton();
        }
    }
}
?>
