<?php
/**
 *
 */
class TemplateFactory
{
    public $tf = null;
    
    
    public function __construct(Graviton $module)
    {
        $this->cfg = ConfigManager::singleton();
        $this->log = GravitonLogger::singleton();
        $this->errMgr = ErrorManager::singleton();
        $this->module = $module;
        $this->tf = new TagFactory();
    }
    
    
    public function twoColumnDetail(Graviton $graviton)
    {
        return $this->twoColumnLayout($graviton, false);
    }
    
    
    public function twoColumnForm(Graviton $graviton)
    {
        return $this->twoColumnLayout($graviton, true);
    }
    
    
    public function twoColumnLayout($graviton, $editView = false)
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

