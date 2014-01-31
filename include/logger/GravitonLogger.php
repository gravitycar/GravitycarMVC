<?php
/**
 * GravitonLogger
 *
 * A class for logging messages for the Gravitycar Web site. Logs any string
 * the gravitycar.log file.
 */
class GravitonLogger
{
   /** @var string - Path to the log file. */
   private $logFilePath = 'gravitycar.log';
   
   /** @var int - max size of log file in bytes. Log file should be rolled after getting this big. */
   private $maxLogFileSize = '100000'; // @ 0.1 MB
   
   /** @var array - a list of log entries. */
   private $logEntries = array();
   
   /** @var string - a string to prepend to every log entry. */
   private $prefix = '';
   
   /** @var array  - a list of logging 'levels' - a method trying to log must user a higher level than the minimum */
   private $levels = array(0 => 'error', 1 => 'debug');
   
   /** @var int - the current logging level - higher values means more logging */
   private $loggingLevel = 1;
   
   
   /**
    * __construct()
    *
    * Instantiates the class. You should avoid calling this and use the singleton
    * instead. Also sets the prefix to use in front of all log entries.
    *
    * @param string $prefix - some string to prepend to all log entries. Will be added to 
    *    other strings.
    */
   private function __construct($prefix = '')
   {
      $prefixes = array();
      if (!empty($prefix)) {
         $prefixes[] = $prefix;
      }
      
      if (IsSet($_SESSION['user_id'])) {
         $prefixes[] = "{$_SESSION['user_id']}";
      }
      
      foreach ($_GET as $key => $value) {
         $prefixes[] = "$key=$value";
      }
      
      $this->prefix = implode('|', $prefixes) . ': ';
   }
   
   
   /**
    * singleton()
    *
    * Returns the one and only instance of the GravitonLogger class.
    *
    * @param string $prefix - some text to prepend to every log entry.
    * @return GravitonLogger - the instance of this class.
    */
   public static function singleton($prefix = '')
   {
      static $instance = null;
      if ($instance === null) {
         $instance = new GravitonLogger($prefix);
      }
      return $instance;
   }
   
   
   /**
    * setLoggingLevel()
    *
    * Sets the minimum logging level value - higher means more logging. Set to
    *
    * @param int $level - A logging functions level value must be equal to or 
    *    lower than this value.
    */
    public function setLoggingLevel($level)
    {
       $this->loggingLevel = $level;
    }
   
    
   /**
    * error()
    *
    * Stores an error message in the logEntries array for later writing.
    *
    * @param string $msg - whatever you want to write to the log file.
    * @param bool $force - open, write to and close the log file for this 
    *    message to make sure its written.
    * @return void
    */
    public function error($msg, $force)
    {
       $this->log($msg, 0, $force);
    }
    
    
   /**
    * debug()
    *
    * Stores a debugging message in the logEntries array for later writing.
    *
    * @param string $msg - whatever you want to write to the log file.
    * @param bool $force - open, write to and close the log file for this 
    *    message to make sure its written.
    * @return void
    */
    public function debug($msg, $force)
    {
       $this->log($msg, 1, $force);
    }
   
    
   /**
    * log()
    *
    * Stores a message in the logEntries array for later writing.
    *
    * @param string $msg - whatever you want to write to the log file.
    * @param int $level - level must be equal to or lower than our logging level 
    *    for the message to be logged.
    * @param bool $forceImmediateWrite - open, write to and close the log file for this 
    *    message to make sure its written.
    * @return void
    */
   protected function log($msg, $level, $forceImmediateWrite = false)
   {
      if ($level > $this->loggingLevel) {
         return;
      }
      
      $msg = "\n" . $this->getTimeStamp() . "[{$this->prefix}] - $msg";
      if ($forceImmediateWrite) {
         $this->writeLogEntry($msg);
      } else {
         $this->logEntries[] = $msg;
      }
   }
   
   
   /**
    * writeOutLogEntries()
    *
    * Writes out all accumulated log entries to the log file in one operation.
    *
    * @return void
    */
   protected function writeOutLogEntries()
   {
      $this->writeLogEntry(implode('', $this->logEntries));
   }
   
   
   /**
    * setLogFilePath()
    *
    * Sets the path to the log file.
    *
    * @param string $path - the path to the log file.
    * @return void.
    */
   public function setLogFilePath($path)
   {
      $this->logFilePath = $path;
   }
   
   
   /**
    * openLogFile()
    *
    * Creates and returns a file handle for the log file. Throws an exception
    * if the file cannot be opened.
    *
    * @throws Exception
    * @return resource - a file handle.
    */
   protected function openLogFile()
   {
      try {
         $fh = fopen($this->logFilePath, "a+");
      } catch (Exception $e) {
         print($e->getMessage());
         return false;
      }
      
      if (!$fh) {
         throw new Exception("GravitonLogger could not open {$this->logFilePath}.");
      }
      
      return $fh;
   }
   
   
   /**
    * writeLogEntry()
    *
    * Opens the log file, then writes a single log entry to the log file, and
    * then closes the log file.
    * If you pass it an array, it calls itself recursively for each element in 
    * the array.
    * 
    * @param mixed $msg - the log entry to write, or an array of strings.
    * @return void
    */
   protected function writeLogEntry($msg)
   {
      try {
         $fh = $this->openLogFile();
      } catch (Exception $e) {
         print($e->getMessage());
      }
      
      if (is_array($msg)) {
         foreach ($msg as $separateMessage) {
            $this->writeLogEntry($msg);
         }
      } else {
         fwrite($fh, $msg . "\n");
      }
      $this->closeLogFile($fh);
   }
   
   
   /**
    * closeLogFile()
    *
    * Closes the log file.
    *
    * @param resource $fh - a file handle.
    * @return void
    */
   protected function closeLogFile($fh)
   {
      fclose($fh);
   }
   
   
   /**
    * getTimeStamp()
    * 
    * Returns a time stamp for the current time.
    *
    * @return string - a time stamp formatted as 'YYYY-mm-dd HH:ii:ss
    */
   protected function getTimeStamp()
   {
      return date("Y-m-d H:i:s");
   }
   
   
   /**
    * rollLogFile()
    *
    * Rolls the log file by renaming it when its size grows too large as defined
    * by the maxLogFileSize property. A new log file will be created in its place.
    *
    * @return void
    */
   protected function rollLogFile()
   {
      if ($this->getLogSize() > $this->maxLogFileSize) {
         $newName = $this->logFilePath . date('Y_m_d_H_i_s');
         rename($this->logFilePath, $newName);
      }
   }
   
   
   /**
    * getLogSize()
    *
    * Returns the size the log file in bytes.
    *
    * @return int - the size of the log file in bytes.
    */
   protected function getLogSize()
   {
      return filesize($this->logFilePath);
   }
}
?>
