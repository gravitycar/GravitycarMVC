<?php
namespace Gravitycar\lib\restApi;

class GravitonAPI extends \Gravitycar\lib\abstracts\RestAPI
{
    public function registerEndpoints()
    {
        return array(
            'list' => array(
                'module' => 'Generic',
                'requestType' => 'GET',
                'pathVars' => array('module'),
                'class' => '\Gravitycar\lib\restApi\GravitonAPI',
                'method' => 'getList',
            ),
            'detail' => array(
                'module' => 'Generic',
                'requestType' => 'GET',
                'pathVars' => array('module', 'id'),
                'class' => '\Gravitycar\lib\restApi\GravitonAPI',
                'method' => 'getOneRecord',
            ),
            'update' => array(
                'module' => 'Generic',
                'requestType' => 'POST',
                'pathVars' => array('module', 'id'),
                'class' => '\Gravitycar\lib\restApi\GravitonAPI',
                'method' => 'saveRecord',
            ),
            /*
            'create' => array(
            ),
            'delete' => array(
            ),
            */
        );
    }
}