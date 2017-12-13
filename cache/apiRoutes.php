<?php
$apiRoutes = array (
  'GET' => 
  array (
    'Generic' => 
    array (
      1 => 
      array (
        'pathVars' => 
        array (
          0 => 'module',
        ),
        'class' => '\\Gravitycar\\lib\\restApi\\GravitonAPI',
        'method' => 'getList',
      ),
      2 => 
      array (
        'pathVars' => 
        array (
          0 => 'module',
          1 => 'id',
        ),
        'class' => '\\Gravitycar\\lib\\restApi\\GravitonAPI',
        'method' => 'getOneRecord',
      ),
    ),
  ),
);