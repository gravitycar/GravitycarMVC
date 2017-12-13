<?php
namespace Gravitycar;
require_once('lib/builders/BuildMaster.php');
require_once('lib/builders/FileMapBuilder.php');

$params = array();
$master = new \Gravitycar\lib\builders\BuildMaster($params);

require_once('lib/utils/autoloader.php');
$master->runBuilder('DatabaseBuilder');
$master->runBuilder('APIRegistrar');
$master->runBuilder('LayoutDataBuilder');
