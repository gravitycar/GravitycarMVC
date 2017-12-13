<?php
namespace Gravitycar\lib\TagFactory;
/**
 *
 */
class TagFactory
{
    public $closedTags = array('input', 'img', 'br', 'hr');
    public $moduleName = '';
    public $registeredIDs = array();
    
    
    public function __construct($module)
    {
        $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
        $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
        $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
        $this->relMgr = \Gravitycar\lib\managers\RelationshipManager::singleton();
        $this->module = $module;
    }
    
    
    public function __call($name, $arguments)
    {
        $arg0 = IsSet($arguments[0]) ? $arguments[0] : array();
        $arg1 = IsSet($arguments[1]) ? $arguments[1] : array();
        
        if (method_exists($this, $name)) {
            return $this->$name($arg0, $arg1);
        } else {
            return $this->getTag($name, $arg0, $arg1);
        }
    }
    
    
    public function getTag($tagName, $attributes = array(), $children = array())
    {
        $closed = $this->isClosed($tagName);
        $tag = new Tag($tagName, $attributes, $children, $closed);
        return $tag;
    }
    
    
    public function getInputField($propdef)
    {
        $fieldType = $propdef['fieldtype'];
        return $this->$fieldType($propdef);
    }
    
    
    public function text($propdef)
    {
        $attributes = array();
        $attributes['type'] = 'text';
        $attributes['maxlength'] = $propdef['len'];
        $attributes['value'] = '{{' . $propdef['name'] . '}}';
        $attributes['id'] = $this->generateID($propdef);
        $attributes['name'] = $attributes['id'];
        return $this->getTag('input', $attributes);
    }
    
    
    public function password($propdef)
    {
        $attributes = array();
        $attributes['type'] = 'password';
        $attributes['maxlength'] = $propdef['len'];
        $attributes['value'] = '';
        $attributes['id'] = $this->generateID($propdef);
        $attributes['name'] = $attributes['id'];
        
        return $this->getTag('input', $attributes);
    }
    
    
    public function hidden($propdef)
    {
        $attributes = array();
        $attributes['type'] = 'hidden';
        $attributes['value'] = '{{' . $propdef['name'] . '}}';
        $attributes['id'] = $this->generateID($propdef);
        $attributes['name'] = $attributes['id'];
        
        return $this->getTag('input', $attributes);
    }
    
    
    public function select($propdef)
    {
        $attributes = array('id' => $this->generateID($propdef));
        $attributes['name'] = $attributes['id'];
        if (IsSet($propdef['multiple'])) {
            $attributes['multiple'] = true;
            $attributes['size'] = IsSet($propdef['size']) ? $propdef['size'] : '';
            $attributes['name'] = $attributes['id'] . '[]';
        }
        
        $children = array();
        
        if ($propdef['datatype'] == 'relationship') {
            $rel = $this->relMgr->getRelationship($propdef['relationship']);
            $propdef['options'] = $rel->getOptionsForSelect($this->module);
            if (!isset($propdef['required']) || !$propdef['required']) {
                $empty = array('' => '');
                $propdef['options'] = array_merge($empty, $propdef['options']);
            }
        }
        
        foreach ($propdef['options'] as $value => $label) {
            $optionAttributes = array('value' => $value);
            $children[] = $this->option($optionAttributes, array($label), $propdef['name']);
        }
        
        
        return $this->getTag('select', $attributes, $children);
    }
    
    
    public function option($attributes, $label, $selectName)
    {
        $value = $attributes['value'];
        $optionTag = $this->getTag('option', $attributes, $label);
        $optionTag->setAttribute("{{{optionSelected $selectName '$value'}}}", true);
        return $optionTag;
    }
    
    
    public function isClosed($tagName)
    {
        return in_array($tagName, $this->closedTags);
    }
    
    
    
    public function generateID($propdef)
    {
        $id = "{$this->module->moduleName}_{$propdef['name']}";
        if (!IsSet($this->registeredIDs[$id])) {
            $this->registeredIDs[$id] = true;
            return $id;
        } else {
            $this->errMgr->error("TagFactory cannot use the same ID '$id' more than once.");
            return '';
        }
    }
    
    
}
?>
