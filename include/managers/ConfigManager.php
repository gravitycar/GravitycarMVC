<?php
/**
 * ConfigManager
 *
 * Class for loading, testing and retrieving config values. The config file
 * should be stored in the directory above the web directory. The name of the
 * config file is gravitycar.config.php.
 */
class ConfigManager extends Singleton
{
    /** @var string - the path to the configuration file. */
    private $configFilePath = '../gravitycar.config.php';
    
    /** @var hash - the configuration data loaded from the config file */
    private $config = null;
    
    
    /**
     * loadConfig();
     *
     * Looks for and loads via require_once() the configuration file, and assigns
     * the value of the variable stored in the configuration file to this object's
     * config property.
     *
     * @return bool - true if the file is loaded and the variable is defined.
     */
    public function loadConfig()
    {
        if (!file_exists($this->configFilePath)) {
            $this->errMgr->error("The configuration file cannot be found!");
            $this->log->error("Could not find the configuration file at {$this->configFilePath}");
            return false;
        }
        
        try {
            require_once($this->configFilePath);
        } catch (Exception $e) {
            $this->errMgr->error("The configuration file could not be included!");
            $this->log->error("require_once('{$this->configFilePath}') failed!");
            return false;
        }
        
        if (!IsSet($gravitycarConfig)) {
            $this->errMgr->error("The configuration variable is not present!");
            $this->log->error("The config file '{$this->configFilePath}' doesn't define the config variable!");
            return false;
        }
        
        $this->config = $gravitycarConfig;
        unset($gravitycarConfig);
        return true;
    }
    
    
    public function init()
    {
        parent::init();
        $this->loadConfig();
    }
    
    
    /**
     * get()
     *
     * Returns the value set for a given config variable. Accepts config "paths"
     * in dot notation. So if you have a nested hash in the config hash, you
     * can access those values by concatenating the keys with dots. Example:
     *
     * $hash = array('db' => array('user'=>'mike', 'pass'=>'sux0r');
     * You would access 'pass' like this:
     
     * $config->get('db.pass');
     *
     * @param string $configPath - which config var you want to access.
     * @param mixed $default - a default value to use if the config isn't set.
     * @return mixed - whatever the config is eet to, or default value.
     */
    public function get($configPath, $default=null)
    {
        $configData = $this->config;
        $configPathParts = explode('.', $configPath);
        foreach ($configPathParts as $path) {
            if (IsSet($configData[$path])) {
                $configData = $configData[$path];
            } else {
                $configData = $default;
                break;
            }
        }
    
        return $configData;
    }
    
    
    /**
     * equals()
     *
     * Tests a given config path for weak-type equality with a given value.
     *
     * @param string $configPath - which config var you want to access.
     * @param mixed $testValue - the value you want to test for equality.
     * @return bool - true if the config matches the test value.
     */
    public function equals($configPath, $testValue)
    {
        return ($this->get($configPath) == $testValue);
    }
}
?>
