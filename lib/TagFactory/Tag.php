<?php
namespace Gravitycar\lib\TagFactory;
/**
 * Tag 
 * A class for reprsenting HTML tags. An instance of a Tag may have other tags
 * as children. Tags know how to render themselves (i.e. output themselves as 
 * html).
 */
class Tag
{
   /** @var ErrorManager 
   the error manager to record any errors */
   public $errMgr = null;
   
    /** @var ConfigManager
    The configuration manager object */
    public $cfg = null;
    
    public $name = null;
    protected $children = null;
    protected $closed = false;
    protected $attributes = array();
    protected $openTagPrefixWhitespace = '';
    protected $openTagSuffixWhitespace = '';
    protected $closeTagPrefixWhitespace = '';
    protected $closeTagSuffixWhitespace = '';
    
    
    public function __construct($name, $attributes, $children, $closed=false)
    {
      $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
      $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
      $this->name = $name;
      $this->setAttributes($attributes);
      $this->setChildren($children);
      $this->closed = $closed;
    }
    
    
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    
    
    public function setAttributes(array $hash)
    {
        if (is_array($hash)) {
            foreach ($hash as $name => $value) {
                $this->setAttribute($name, $value);
            }
        }
    }
    
    
    public function getAttributes()
    {
        return $this->attributes();
    }
    
    
    public function getAttribute($name)
    {
        if (IsSet($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return null;
    }
    
    
    public function getAttributeString()
    {
        $string = '';
        $pairs = array();
        foreach ($this->attributes as $name => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            
            if (is_bool($value) && $value === true) {
                $pairs[] = $name;
                continue;
            }
            
            $value = str_replace('"', '\"', $value);
            $pairs[] = "$name=\"$value\"";
        }
        
        if ($pairs) {
            $string = ' ' . join(' ', $pairs);
        }
        
        return $string;
    }
    
    
    public function addChildren($child)
    {
        $args = func_get_args();
        foreach ($args as $child) {
            if (is_array($child)) {
                foreach ($child as $separateChild) {
                    $this->addChildren($separateChild);
                }
            } else {
                $this->children[] = $child;
            }
        }
    }
    
    
    public function setChildren($children) {
        if (!is_array($children)) {
            $children = array($children);
        }
        $this->children = $children;
    }
    
    
    public function getChildren()
    {
        return $this->children;
    }
    
    
    public function renderTag()
    {
        if (empty($this->name)) {
            return false;
        }
        
        $html = "{$this->openTagPrefixWhitespace}<{$this->name}";
        $html .= $this->getAttributeString();
        
        if ($this->closed === true) {
            $html .= " />{$this->closeTagSuffixWhitespace}";
        } else {
            $html .= ">{$this->openTagSuffixWhitespace}";
            if ($this->children) {
                $html .= $this->getRenderedChildren();
            }
            $html .= "{$this->closeTagPrefixWhitespace}</{$this->name}>{$this->closeTagSuffixWhitespace}";
        }
        
        return $html;
    }
    
    
    public function getRenderedChildren()
    {
        $html = '';
        foreach ($this->children as $child) {
            if (is_a($child, 'Gravitycar\lib\TagFactory\Tag')) {
                $html .= $child->renderTag();
            } else {
                $html .= $child;
            }
        }
        
        return $html;
    }
}
?>
