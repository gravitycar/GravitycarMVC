<?php
/**
 * Error Manager
 *
 * A cental singleton class for recording errors or other conditions that need to be
 * logged and/or reported to the user.
 *
 * The idea here is that the errors accumulated by the error manager will be in one
 * central location so that the view can easily display them.
 */
class ErrorManager extends Singleton
{
    /** @var array the collected errors */
    private $errors = array();
    
    
    /**
     * error()
     *
     * Register an error message. This records the passed in message in an array
     * of error messages and writes the error to the log file.
     *
     * @param string $msg - your error message.
     * @return void
     */
    public function error($msg)
    {
        $errors[] = $msg;
        $this->log->error($msg, true);
    }
    
    
    /**
     * getErrors()
     *
     * Return an array of every registered error.
     *
     * @return array - an array of all the error messages.
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    
    /**
     * toJSON()
     *
     * Returns the array of registered error message as a JSON string.
     *
     * @return string - a json string of error messages.
     */
    public function toJSON()
    {
        return json_encode($this->errors);
    }
}
?>
