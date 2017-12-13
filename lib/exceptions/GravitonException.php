<?php
namespace Gravitycar\lib\exceptions;

/**
 * Custom exception base class. Adds some things I want all of my exceptions
 * to do.
 */
class GravitonException extends \Exception
{
    public $backtrace = array();
    public $log = null;
    public $message = '';
    public $errorName = '';
    public $cfg = null;
   
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        $this->log = \Gravitycar\lib\managers\GravitonLogger::singleton();
        $this->errorMgr = \Gravitycar\lib\managers\ErrorManager::singleton();
        $this->cfg = \Gravitycar\lib\managers\ConfigManager::singleton();
        $this->message = $message;
        $this->previous = $previous;
        parent::__construct($this->message, $code, $previous);
    }
    
    /**
    * log()
    *
    * Logs error messages to a log file.
    */
    public function log()
    {
        $this->log->debug($this->toString());
    }
    
    
    public function getEveryMessage()
    {
        $message = $this->getMessage();
        $previous = $this->getPrevious();
        while ($previous !== null) {
            $message .= ' ' . $previous->getMessage();
            $previous = $previous->getPrevious();
        }
        return $message;
    }

   
    public function toHash()
    {
        $this->getBacktrace();
        $hash['message'] = $this->getEveryMessage();
        $hash['backtrace'] = $this->backtrace;
        return $hash;
    }
    
    
    public function toString()
    {
        return implode("\n", $this->toHash());
    }
    
    
    public function toJSON()
    {
        return json_encode($this->toHash(), JSON_PRETTY_PRINT);
    }
    
    
    public function getBacktrace()
    {
        $this->backtrace[] = get_class($this) . " thrown at {$this->getFile()}:{$this->getLine()}";
        
        $previous = $this->getPrevious();
        while ($previous !== null) {
            if (method_exists($previous, 'getBacktrace')) {
                $this->backtrace = array_merge($this->backtrace, $previous->getBacktrace());
                $previous = $previous->getPrevious();
                if ($previous === null) {
                    $this->backtrace[] = $this->getTraceAsString();
                }
            }
        }
        return $this->backtrace;
    }
}
