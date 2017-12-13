<?php
namespace Gravitycar\lib\restApi\Exceptions;

class ApiException412 extends \Gravitycar\lib\abstracts\ApiException
{
    public $returnCode = '412';
    public $errorName = 'Validation Failure';
    public $message = 'The following field(s) contained invalid values:';
    public $invalidFieldsValues = array();
    
    public function __construct($path = '', $invalidFieldsValues)
    {
        $this->invalidFieldsValues = $invalidFieldsValues;
        $this->message .= "\n" . $this->invalidFieldValuesToString();
        parent::__construct($this->errorMessage);
    }
    
    
    public function invalidFieldValuesToString()
    {
        $list = array();
        foreach ($this->invalidFieldValues as $field => $invalidValue) {
            $list = "$field is $invalidValue";
        }
        return implode("\n", $list);
    }
}