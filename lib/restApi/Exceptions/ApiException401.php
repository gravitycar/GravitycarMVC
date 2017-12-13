<?php
namespace Gravitycar\lib\restApi\Exceptions;

class ApiException401 extends \Gravitycar\lib\abstracts\ApiException
{
    public $returnCode = '401';
    public $errorName = 'Unauthorized';
    public $message = "You are not authorized to access the requested resource:";
    
    public function __construct($message = '')
    {
        parent::__construct($this->message, $this->returnCode);
    }
}