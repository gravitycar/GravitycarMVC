<?php
namespace Gravitycar\lib\TagFactory;
/**
 *
 */
class TemplateFactory
{
    public $tf = null;
    
    
    /**
     * __construct()
     *
     * Instantiates this class.
     *
     * @param Graviton $graviton - the module you want to manufacture templates for.
     */
    public function __construct(\Gravitycar\lib\abstracts\Graviton $graviton)
    {
        $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
        $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
        $this->errMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
        $this->module = $graviton;
        $this->tf = new \Gravitycar\lib\TagFactory\TagFactory($this->module);
    }
    
    
    public function twoColumnDetail(Graviton $graviton)
    {
        return $this->twoColumnLayout($graviton, false);
    }
        
    
    /**
     * twoColumnForm()
     *
     * Returns a form tag with a nested table, which in turn lays out the form input
     * fields and includes a div with the standard form buttons (save, cancel, reset).
     * The form fields are based on the prodefs of the passed in Graviton. Tag Factory
     * knows how to render each field in propdefs based on each field's type attribute.
     *
     * @param Graviton $module - the module you want to manufacture templates for.
     * @return Tag - a Tag object with a name of 'form'.
     */
    public function twoColumnForm(\Gravitycar\lib\abstracts\Graviton $graviton)
    {
        return $this->twoColumnLayout($graviton, true);
    }
    
    
    public function twoColumnLayout($graviton, $editView = false)
    {
        $formAttributes = array(
                                'method' => 'POST', 
                                'action' => "index.php",
                                'name'   => "Edit{$this->module->moduleName}",
                                'id'     => "Edit{$this->module->moduleName}",
                                );
        $form = $this->tf->form($formAttributes);
        $form->addChildren($this->tf->getTag('input', array('type' =>'hidden', 'name' => 'module', 'value' => $this->module->moduleName)));
        $form->addChildren($this->tf->getTag('input', array('type' =>'hidden', 'name' => 'action', 'value' => 'save')));
        $tableAttributes = array('id' => "Edit{$this->module->moduleName}" . '_table', 'class' => 'formTable');
        $table = $this->tf->table($tableAttributes);
        
        foreach ($this->module->propdefs as $propName => $propdef) {
            
            if ($propdef['fieldtype'] == 'hidden') {
                $field = $this->tf->getInputField($propdef);
                $form->addChildren($field);
            } else {
                $tdLabel = $this->tf->td(array('class' => 'formLabel'), $propdef['label'] . ':');
                if ($editView) {
                    $field = $this->tf->getInputField($propdef);
                    $errorDiv = $this->tf->div(array('class' => 'fieldErrorMessage', 'id' => $field->getAttribute('id') . 'errorMsg'));
                } else {
                    $field = '{{' . $propName . '}}';
                }
                $tdField = $this->tf->td(array('class' => 'formField'), array($field, $errorDiv));
                $tr = $this->tf->tr(array('vAlign' => 'top'), array($tdLabel, $tdField));
                $table->addChildren($tr);
            } 
        }
        $buttons = $this->getStandardFormButtons();
        $buttonsRow = $this->tf->tr();
        $buttonsRow->addChildren($this->tf->td(array('colSpan' => '2'), $buttons));
        $table->addChildren($buttonsRow);
        $form->addChildren($table);
        return $form;
    }
    
    
    /**
     * getStandardFormButtons()
     *
     * Returns a div that contains the standard buttons for a form: Save, Cancel, and
     * Reset. 
     *
     * @return Tag - a tag of type 'div' with nested buttons.
     */
    public function getStandardFormButtons()
    {
        $div = $this->tf->div(array('class' => 'formButtonContainer'));
        $saveButton = $this->tf->input(array('type' => 'submit', 'value'=>'Save', 'id'=>"{$this->module->moduleName}SaveButton", 'class' => 'formButton'));
        $resetButton = $this->tf->button(array('type' => 'reset', 'value'=>'Clear', 'id'=>"{$this->module->moduleName}ResetButton", 'class' => 'formButton'), 'Reset');
        $cancelButton = $this->tf->input(array('type' => 'button', 'value'=>'Cancel', 'id'=>"{$this->module->moduleName}CancelButton", 'class' => 'formButton'));
        $divSaveButton = $this->tf->div(array('class'=>'buttonContainer', 'id'=>'saveButtonContainer'), $saveButton);
        $divCancelButton = $this->tf->div(array('class'=>'buttonContainer', 'id'=>'cancelButtonContainer'), $cancelButton);
        $divResetButton = $this->tf->div(array('class'=>'buttonContainer', 'id'=>'saveResetContainer'), $resetButton);
        $div->addChildren($divSaveButton, $divResetButton, $divCancelButton);
        return $div;
    }
    
}
?>

