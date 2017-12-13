<?php
namespace Gravitycar\modules\Movies;

class Movies extends \Gravitycar\lib\abstracts\Graviton
{
    public $moduleName = "Movies";
    public $table = "Movies";
    
    public function __construct()
    {
        parent::__construct();
    }
}