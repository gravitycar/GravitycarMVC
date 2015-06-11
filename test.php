<?php
require_once('include/lib/autoloader.php');
$db = DBManager::Singleton();
$db->init(true);
$result = $db->query("show databases");
$row = $db->fetchByAssoc($result);
var_export($row);
?>
