<?php
require_once('include/lib/autoloader.php');
$cfg = ConfigManager::singleton();
$cfg->setConfigFilePath('../../gravitycar.config.php');
$cfg->init();
//chdir($cfg->get('root_dir'));
?>
