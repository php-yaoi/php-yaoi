<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;

class Index extends BaseClass
{
    const TYPE_KEY = 'key';
    const TYPE_UNIQUE = 'unique';

    /** @var Column[]  */
    public $columns = array();
    public $type = self::TYPE_KEY;

    public function __construct($columns) {
        if (is_array($columns)) {
            $this->columns = $columns;
        }
        else {
            $this->columns = func_get_args();
        }
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }
}