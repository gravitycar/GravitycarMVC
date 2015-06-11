<?php
require_once('../abstracts/Singleton.php');
require_once('../managers/GravitonLogger.php');
require_once('../managers/ErrorManager.php');
require_once('../managers/ConfigManager.php');
require_once('../lib/autoloader.php');
$cfg = ConfigManager::singleton();
$cfg->setConfigFilePath('../../../gravitycar.config.php');
$cfg->init();
var_export($cfg->get('root_dir'));
//chdir($cfg->get('root_dir'));

$params = array();
$builder = new DatabaseBuilder($params);
$builder->run($params);
?>
