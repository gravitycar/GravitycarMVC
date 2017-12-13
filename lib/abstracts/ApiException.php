<?php
namespace Gravitycar\lib\abstracts;

abstract class ApiException extends \Gravitycar\lib\exceptions\GravitonException
{
    public $returnCode = 0;
    public $errorName = '';
    public $message = '';
    
    public function __construct($message, $previous)
    {
        $this->message = $message;
        
        $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
        parent::__construct($this->message, $this->returnCode, $previous);
    }
    
    
    public function toHash()
    {
        $this->getBacktrace();
        $hash['returnCode'] = $this->returnCode;
        $hash['errorName'] = $this->errorName;
        $hash['message'] = $this->getEveryMessage();
        $hash['backtrace'] = $this->backtrace;
        return $hash;
    }
}