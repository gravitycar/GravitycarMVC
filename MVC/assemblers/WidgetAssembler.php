<?php
/**
 * Widget Assembler
 *
 * A class for generating the HTML for form inputs (widgets). Takes a propdef
 * hash and a module name, and an optional input count (in case you need to display
 * the same input field multiple times) and then builds the right input field
 * with handlebars tags so that handlebars can do the final population. I do it
 * this way (letting handlebars fill in the data) so that we can use ajax requests
 * to grab the data and re-populate tempates easily.
 */
class WidgetAssembler
{
    /** @var ErrorManager 
    the error manager to record any errors */
    public $errMgr = null;

   /** @var GravitonLogger 
   the loger object to log messages */
   public $log = null;
   
    /** @var ConfigManager
    The configuration manager object */
    public $cfg = null;
    
    
    
    public function __construct()
    {
      $this->log = GravitonLogger::singleton();
      $this->errMgr = ErrorManager::singleton();
      $this->cfg = ConfigManager::singleton();
    }
    
    
    /**
     * getWidget()
     *
     * Generates the HTML for any type of HTML input field. The type of field
     * to generate must be defined in $propdef['fieldtype'], which should map
     * to a method name in this class. Typically, the fieldtype also maps to a 
     * type of html input field (text, textarea, button, radio, checkbox, etc).
     * However, custom types are supported, as long as the method to generate 
     * the necessary mark up is defined.
     *
     * @param string $moduleName - the name of the module the field belongs to. 
     *  Will be prepended to every input field name.
     * @param hash $propdef - the property definitions for this field.
     * @param string $value - the value to pre-populate the field with.
     * @param int $count - an optional number value to append to the field's name.
     * @return string - The HTML for a text field, formatted for handlebars.
     */
    public function getWidget($moduleName, $propdef, $value, $count=false)
    {
        $funcName = 'assemble' . ucwords($propdef['fieldtype']) . 'Field';
        if (method_exists($this, $funcName)) {
            return $this->$funcName($moduleName, $propdef, $value, $count);
        } else {
            $this->log->error("WidgetAssembler does not define a $funcName() method. Asked for by $moduleName->{$propdef['name']} which defines a fieldtype of {$propdef['fieldtype']}");
            return '';
        }
    }
    
    
    /**
     * generateFieldName()
     *
     * Generates the name for an input field. To be used for name and id atributes.
     *
     * @param string $moduleName - the name of the module the field belongs to. 
     *  Will be prepended to every input field name.
     * @param string $fieldName - the property definitions for this field.
     * @param int $count - an optional number value to append to the field's name.
     * @return string - The name/id for an input field, formatted like: <module>_<fieldName>[_<count>]
     */
    public function generateFieldName($moduleName, $fieldName, $count=false)
    {
        $name = "{$moduleName}_{$fieldName}";
        if ($count !== false) {
            $name .= "_$count";
        }
        return $name;
    }
    
    
    public function generateAttributes($moduleName, $propdef, $count=false, $startingAttributes=array())
    {
        $attributes = array(
            'id' => $this->generateFieldName($moduleName, $propdef['name'], $count),
        );
        
        if (IsSet($propdef['tooltip'])) {
            $attributes['alt'] = $propdef['tooltip'];
        }
        
        $attributes = array_merge($attributes, $startingAttributes);
        
        if (IsSet($propdef['custom_html_attributes'])) {
            $attributes = array_merge($attributes, $propdef['custom_html_attributes']);
        }
        
        $attributePairs = array();
        foreach($attributes as $attName => $attValue) {
            if (is_bool($attValue)) {
                $attributePairs[] = "$attName";
            } else {
                $attValue = str_replace('"', '\"', $attValue);
                $attributePairs[] = "$attName=\"$attValue\"";
            }
        }
        
        $attributesString = implode(' ', $attributePairs);
        return $attributesString;
    }
    
    
    /**
     * assembleTextField()
     *
     * Generates the HTML for a text field.
     *
     * @param string $moduleName - the name of the module the field belongs to. 
     *  Will be prepended to every input field name.
     * @param hash $propdef - the property definitions for this field.
     * @param string $value - the value to pre-populate the field with.
     * @param int $count - an optional number value to append to the field's name.
     * @return string - The HTML for a text field, formatted for handlebars.
     */
    public function assembleTextField($moduleName, $propdef, $value, $count=false)
    {
        $attributes = array(
            'type' => 'text',
            'value' => '{{' . $propdef['name'] . '}}',
        );
        
        $attributesString = $this->generateAttributes($moduleName, $propdef, $count, $attributes);
        $html = "<input $attributesString/>";
        
        return $html;
    }
    
    
    public function assemblePasswordField($moduleName, $propdef, $value, $count=false)
    {
        $attributes = array(
            'type' => 'password',
            'value' => $propdef['defaultvalue'],
        );
        
        $attributesString = $this->generateAttributes($moduleName, $propdef, $count, $attributes);
        $html = "<input $attributesString/>";
        return $html;
    }
    
    
    public function assembleHiddenField($moduleName, $propdef, $value, $count=false)
    {
        $attributes = array(
            'type' => 'hidden',
            'value' => '{{' . $propdef['name'] . '}}',
        );
        
        $attributesString = $this->generateAttributes($moduleName, $propdef, $count, $attributes);
        $html = "<input $attributesString/>";
        return $html;
    }
    
    
    public function assembleSelectField($moduleName, $propdef, $value, $count=false)
    {
        $attributes = array();
        if (IsSet($propdef['multiple']) && $propdef['multiple'] == true) {
            $attributes['multiple'] = true;
            $attributes['name'] = $this->generateFieldName($moduleName, $propdef['name'], $count) . '[]';
            $attributes['size'] = IsSet($propdef['size']) ? $propdef['size'] : count($propdef['options']) - 1;
        }
        
        $attributesString = $this->generateAttributes($moduleName, $propdef, $count, $attributes);
        $html = "<select $attributesString>";
        foreach ($propdef['options'] as $optionValue => $optionLabel) {
            $html .= $this->assembleOptionField($optionValue, $optionLabel, ($value == $optionValue));
        }
        $html .= "</select>";
        return $html;
    }
    
    
    public function assembleOptionField($value, $label, $selected)
    {
        $label = str_replace('"', '\"', $value);
        $selected ? $selected = 'selected' : $selected = '';
        $optionTag = "<option $selected value=\"$value\">$label</option>";
        return $optionTag;
    }
}
?>
