<?php
namespace Gravitycar\lib\exceptions;
class RecordIDNotSpecified extends \Gravitycar\lib\exceptions\GravitonException
{
    public $errorName = "No Record ID.";
}


class EmptyDBQueryResult extends \Gravitycar\lib\exceptions\GravitonException
{
    public $errorName =  "The DB Query returned no results.";
}


class InvalidModule extends \Gravitycar\lib\exceptions\GravitonException
{
    public $errorName = "Invalid Module.";
}