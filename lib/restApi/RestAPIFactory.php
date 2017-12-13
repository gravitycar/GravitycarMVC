<?php
namespace Gravitycar\lib\restApi;
class RestAPIFactory
{
    protected $routeMap = array();
    public $routeData = array();
    
    public function __construct($requestType, $apiPath)
    {
        $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
        $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
        $this->db = \Gravitycar\lib\managers\DBManager::singleton();
        $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
        $this->routeMap = $this->loadAPIRoutes();
        $this->routeData = $this->lookupRouteMap($requestType, $apiPath);
        $this->className = $this->routeData['class'];
        $this->method = $this->routeData['method'];
        $this->assignRouteVariables($apiPath, $this->routeData['pathVars']);
        $this->assignPostPutData();
    }
    
    
    public function loadAPIRoutes()
    {
        require_once('cache/apiRoutes.php');
        return $apiRoutes;
    }
    
    
    public function getRestAPIObject()
    {
        if (!class_exists($this->routeData['class'])) {
            // throw missing class exception.
        }
        $api = new $this->className();
        return $api;
    }
    
    
    public function assignRouteVariables($route, $routeMapPathVars)
    {
        $routeElements = $this->explodeRoute($route);
        foreach ($routeElements as $index => $element) {
            $varName = $routeMapPathVars[$index];
            $_REQUEST[$varName] = $element;
        }
    }
    
    
    public function assignPostPutData()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST" || $_SERVER['REQUEST_METHOD'] == "PUT") {
            $request_body = file_get_contents('php://input');
            $data = json_decode($request_body, true);
            $_POST = array_merge($_POST, $data);
        }
    }
    
    
    public function lookupRouteMap($requestType, $route)
    {
        $routeElements = $this->explodeRoute($route);
        $routeElementsCount = count($routeElements);
        $module = $routeElements[0];
        
        if (!isset($this->routeMap[$requestType])) {
            // throw invalid request type exception.
        }
        
        // if a module specific map exists, use that. If no module specific map exists,
        // use the generic mapping.
        if (!isset($this->routeMap[$requestType][$module])) {
            $module = 'Generic';
        }
        
        if (!isset($this->routeMap[$requestType][$module][$routeElementsCount])) {
            // throw no matching route exception
        }
        
        return $this->routeMap[$requestType][$module][$routeElementsCount];
    }
    
    
    public function explodeRoute($route)
    {
        $elements = explode('/', $route);
        return $elements;
    }
    
    
}
