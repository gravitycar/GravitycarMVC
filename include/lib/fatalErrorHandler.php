<?php
require_once('include/managers/ErrorManager.php');
register_shutdown_function('fatalErrorShutdownHandler');

function fatalErrorShutdownHandler()
{
  $last_error = error_get_last();
  if ($last_error['type'] === E_ERROR) {
    // fatal error
    $em = ErrorManager::singleton();
    $em->error("{$last_error['message']} occured in {$last_error['file']} on line {$last_error['line']}");
    }
}
?>
