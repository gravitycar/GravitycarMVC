<?php
namespace Gravitycar\lib\restApi\Exceptions;

class ApiException404 extends \Gravitycar\lib\abstracts\ApiException
{
    public $returnCode = '404';
    public $errorName = 'Resource Not Found';
    public $message = "The requested resource does not exist.";
    
    public function __construct($msg = '', $previous)
    {
        parent::__construct($this->message, $previous);
    }
}