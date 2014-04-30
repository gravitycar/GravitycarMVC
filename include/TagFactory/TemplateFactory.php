<?php
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
     * @param Graviton $module - the module you want to manufacture templates for.
     */
    public function __construct(Graviton $module)
    {
        $this->cfg = ConfigManager::singleton();
        $this->log = GravitonLogger::singleton();
        $this->errMgr = ErrorManager::singleton();
        $this->module = $module;
        $this->tf = new TagFactory();
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
    public function twoColumnForm($graviton)
    {
        $formAttributes = array(
                                'method' => 'POST', 
                                'action' => "index.php?module={$this->module->name}&action=save",
                                'name'   => "Edit{$this->module->name}",
                                'id'     => "Edit{$this->module->name}",
                                );
        $form = $this->tf->form($formAttributes);
        
        $tableAttributes = array('id' => "Edit{$this->module->name}" . '_table', 'class' => 'formTable');
        $table = $this->tf->table($tableAttributes);
        
        foreach ($this->module->propdefs as $propName => $propdef) {
            
            if ($propdef['fieldtype'] == 'hidden') {
                $field = $this->tf->getInputField($propdef);
                $form->addChildren($field);
            } else {
                $tdLabel = $this->tf->td(array('class' => 'formLabel'), $propdef['label'] . ':');
                $field = $this->tf->getInputField($propdef);
                $errorDiv = $this->tf->div(array('class' => 'fieldErrorMessage', 'id' => $field->getAttribute('id') . 'errorMsg'));
                $tdField = $this->tf->td(array('class' => 'formField'), array($field, $errorDiv));
                $tr = $this->tf->tr(array('vAlign' => 'top'), array($tdLabel, $tdField));
                $table->addChildren($tr);
            } 
        }
        
        $buttons = $this->getStandardFormButtons();
        $form->addChildren(array($table, $buttons));
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
        $saveButton = $this->tf->input(array('type' => 'submit', 'value'=>'Save', 'id'=>"{$this->moduleName}SaveButton", 'class' => 'formButton'));
        $resetButton = $this->tf->button(array('type' => 'reset', 'value'=>'Clear', 'id'=>"{$this->moduleName}ResetButton", 'class' => 'formButton'), 'Reset');
        $cancelButton = $this->tf->input(array('type' => 'button', 'value'=>'Cancel', 'id'=>"{$this->moduleName}CancelButton", 'class' => 'formButton'));
        $divSaveButton = $this->tf->div(array('class'=>'buttonContainer', 'id'=>'saveButtonContainer'), $saveButton);
        $divCancelButton = $this->tf->div(array('class'=>'buttonContainer', 'id'=>'cancelButtonContainer'), $cancelButton);
        $divResetButton = $this->tf->div(array('class'=>'buttonContainer', 'id'=>'saveResetContainer'), $resetButton);
        $div->addChildren($divSaveButton, $divResetButton, $divCancelButton);
        return $div;
    }
    
}
?>

