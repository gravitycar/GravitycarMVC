<?php
namespace Gravitycar\lib\builders;

/**
 *
 */
class LayoutDataBuilder implements \Gravitycar\lib\interfaces\builder
{
    public $fieldProperties = array(
        'name',
        'datatype',
        'fieldtype',
        'defaultvalue',
        'required',
        'len',
        'tool-tip',
        'validation',
        'options',
    );
    
    public $propdefs = array();
    
    
    public function __construct()
    {
      $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
      $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
      $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
    }
        
    public function run($params)
    {
        // loop through all modules.
        $path = 'modules';
        $moduleDirHandle = opendir($path);
        
        if (is_bool($moduleDirHandle)) {
            $this->errMgr->error("'$path' could not be searched for Module Classes.");
            return;
        }
        
        
        while (false !== ($moduleDirName = readdir($moduleDirHandle))) {
            if (!$this->dirEntryIsValid($moduleDirName)) {
                continue;
            }
            
            // include the propdefs file for each module.
            $propDefsPath = "{$path}/{$moduleDirName}/propdefs.php";
            if (file_exists($propDefsPath)) {
                include($propDefsPath);
                $this->propdefs = $propdefs;
            }
            
            $layoutDirPath = "{$path}/{$moduleDirName}/layouts";
            if (!file_exists($layoutDirPath)) {
                continue;
            }
            $layoutDirHandle = opendir($layoutDirPath);
            
            if (is_bool($layoutDirHandle)) {
                $this->errMgr->error("'$layoutDirPath' could not be searched for Module layout files.");
                continue;
            }
            
            while (false !== ($layoutFileName = readdir($layoutDirHandle))) {
                if (!$this->dirEntryIsValid($layoutFileName)) {
                    continue;
                }
                $layoutName = pathinfo($layoutFileName, PATHINFO_FILENAME);
                // include each layouts file. It should define a 'fields' key as a child
                // of the key named for the layouts file, i.e. modules/Users/layouts/list.php defines
                // $layouts['Users']['list']['fields'].
                $layoutFilePath = "{$layoutDirPath}/{$layoutFileName}";
                include($layoutFilePath);
                
                // for each field defined, copy these values from propdefs and add them as 
                // an array for the layout defs, i.e.
                // $layouts['Users']['list']['fields']['user_type'] => array('fieldtype' => 'select','required' => true,'label' => 'User Type', etc.)
                // we definitely want fieldtype, defaultvalue, required, label, len, tool-tip, options.
                $layoutDef = $layouts[$moduleDirName][$layoutName];
                //$layouts[$moduleDirName][$layoutName] = $this->combineLayoutWithPropdefs($layoutDef, $moduleDirName, $layoutName);
            }
            $layouts[$moduleDirName]['propdefs'] = $this->propdefs;
        }
        // after all layout files have been included, the $layouts variable
        // should be completely populated.
        // convert the $layouts variable to JSON and write it as a js file.
        $this->writeJavaScriptCacheFile($layouts);
    }
    
    public function combineLayoutWithPropdefs($layoutDefs, $moduleName, $layoutName)
    {
        $layoutDefs['field_defs'] = array();
        foreach ($layoutDefs['fields'] as $fieldName) {
            if (!isset($this->propdefs[$fieldName])) {
                $this->errMgr->error("$moduleName does not define $fieldName in propdefs, but uses that field in $layoutName");
                continue;
            }
            $layoutDefs['field_defs'][$fieldName] = array();
            foreach ($this->fieldProperties as $property) {
                if (!isset($this->propdefs[$fieldName][$property])) {
                    continue;
                }
                $layoutDefs['field_defs'][$fieldName][$property] = $this->propdefs[$fieldName][$property];
            }
        }
        return $layoutDefs;
    }
    
    
    public function writeJavaScriptCacheFile($layoutsAggregate)
    {
        $layoutsAggregateJSON = json_encode($layoutsAggregate, JSON_PRETTY_PRINT);
        $cacheDir = $this->cfg->get('root_dir') . '/' . $this->cfg->get('cache_dir');
        if (!file_exists($cacheDir)) {
            $this->log->debug("$cacheDir does not exist");
            mkdir($cacheDir, 0755, true);
        }
        $cacheFilePath = "{$cacheDir}/layouts.js";
        $fh = fopen($cacheFilePath, 'w+');
        if (!$fh) {
            $this->errMgr->error("$cacheFilePath could not be opened for writing.");
        }
        
        $writeOK = fwrite($fh, "if (typeof gc == 'undefined') {gc = {app: {}};}\ngc.app.layoutdefs = $layoutsAggregateJSON\n");
        if (!$writeOK) {
            $this->errMgr->error("Could not write to $cacheFilePath");
        }
        
        fclose($fh);
    }
    
    
    public function dirEntryIsValid($entry)
    {
        if ($entry == 'docs' || 
            $entry == '.' || 
            $entry == '..' || 
            strpos($entry, '.') === 0) {
        
            return false;
        }
        return true;
    }
}