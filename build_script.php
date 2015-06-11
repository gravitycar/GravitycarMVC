<?php
require_once('include/lib/autoloader.php');

$params = array();
$master = new BuildMaster($params);
$master->runBuilder('DatabaseBuilder');
?>
