<?php
include('nucleus.php');
$_REQUEST['api_path'] = trim($_REQUEST['api_path'], "/");
$api_path_vars = explode('/', $_REQUEST['api_path']);
$method = $_SERVER['REQUEST_METHOD'];

$factory = new \Gravitycar\lib\restApi\RestAPIFactory($_SERVER['REQUEST_METHOD'], $_REQUEST['api_path']);
$api = $factory->getRestAPIObject();
$method = $factory->method;

header('Content-Type: text/json');
try {
    echo $api->$method();
} catch (Exception $e) {
    header("HTTP/1.1 {$e->returnCode} {$e->errorName}");
    echo $e->toJSON();
}