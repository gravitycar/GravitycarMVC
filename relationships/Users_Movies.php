<?php
$propdefs = array(
    'name' => 'Users_Movies',
    'type' => 'ManyToMany',
    'table' => 'Users_Movies',
    'module1' => 'Gravitycar\\modules\\Users\\Users',
    'module1_displayField' => array('first_name', 'last_name'),
    'key1' => 'id',
    'moduleA' => 'Gravitycar\\modules\\Movies\\Movies',
    'moduleA_displayField' => 'title',
    'keyA' => 'id',
);


$oneToMany = array(
    'name' => 'Users_Blogs',
    'type' => 'OneToMany',
    'oneModule' => '\\Gravitycar\\modules\\Users\\Users',
    'one_displayField' => array('first_name', 'last_name'),
    'oneKey' => 'id',
    'manyModule' => '\\Gravitycar\\modules\\Blogs\\Blogs',
    'many_displayField' => 'name',
    'manyKey' => 'user_id',
);