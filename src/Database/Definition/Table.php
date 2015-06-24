<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;

class Table extends BaseClass
{
    public $autoIncrement;
    public $primaryKey = array();
    public $columns = array();
    public $defaults = array();
    public $notNull = array();


}