<?php
/**
 * GravitonView
 *
 * This class assemblies the HTML we send back to the browser.
 */
class GravitonView
{ 
   /** @var GravitonLogger 
   the loger object to log messages */
   public $log = null;
   
   /** @var ErrorManager 
   the error manager to record any errors */
   public $errMgr = null;
   
    /** @var ConfigManager
    The configuration manager object */
    public $cfg = null;
    
    /** @var TagFactory 
    Object for generating markup */
    public $tf = null;
    
    /** @var Graviton
    The module we're using to get data to base our view on. */
    protected $module = null;
    
    /** @var string 
    The path to our header file. */
    protected $headerFilePath = 'MVC/templates/header.php';
    
    /** @var string
    The path to our footer file. */
    protected $footerFilePath = 'MVC/templates/footer.php';
    
    public function __construct(Graviton $module)
    {
        $this->module = $module;
        $this->cfg = ConfigManager::singleton();
        $this->log = GravitonLogger::singleton();
        $this->errMgr = ErrorManager::singleton();
        $this->tf = new TemplateFactory($module);
    }
    
    
    /**
     * render()
     * 
     * Renders whatever this module's view is supposed to be.
     * @param string $action
     */
    public function render($action)
    {
    	if ($action == 'save') {
    		header("Location: index.php?module={$this->module->name}&action=detail&id={$this->module->id}");
    	} else {
    		print($this->generateHTML());
    	}
    }
    
    
    public function generateHTML()
    {
        $html = '';
        
        ob_start();
        // load the header file.
        $this->loadTemplateFile($this->headerFilePath);
        $form = $this->tf->twoColumnForm($this->module);
        
        // get the html produced by the module.
        $script = "
        <script id=\"detail_template\" type=\"text/x-handlebars-template\">";
        $script .= $form->renderTag();
        /*
        foreach ($this->module->propdefs as $propName => $defs) {
            $field = $wa->getWidget($this->module->name, $defs, '');
            $script .= "{$defs['label']}: $field</br>";
        }
        */
        $script .= "</script>";
        
        $script .= "
        <script type=\"text/javascript\" language=\"JavaScript\">
        var module_data = {$this->module->toJSON()};
        var module_source = $('#detail_template').html();
        var module_template = Handlebars.compile(module_source);
        var module_html = module_template(module_data);
        
        document.body.innerHTML = module_html;
        </script>
        ";
        print($script);
        // load the footer file.
        $this->loadTemplateFile($this->footerFilePath);
        
        $html = ob_get_contents();
        ob_end_clean();
        
        return $html;
    }
    
    
    public function loadTemplateFile($filePath)
    {
        if (file_exists($filePath) && is_readable($filePath)) {
            require_once($filePath);
            return true;
        } else {
            $this->errMgr->error("Cannot load file '$filePath' - does not exist or is not readable.");
            return false;
        }
    }
}
?>
