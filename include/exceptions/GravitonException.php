<?php

/**
 * Custom exception base class. Adds some things I want all of my exceptions
 * to do.
 */
class GravitonException extends Exception
{
   
   public function __construct($message = null, $code = 0, Exception $previous = null)
   {
      parent::__construct($message = null, $code = 0, Exception $previous = null);
      $this->logger = new GravitonLogger();
   }
   
   /**
    * log()
    *
    * Logs error messages to a log file.
    */
   public function log()
   {
      
   }
}
?>
