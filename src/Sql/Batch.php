<?php

namespace Yaoi\Sql;

use Yaoi\BaseClass;

class Batch extends BaseClass
{
    /** @var Expression[] */
    private $expressions = array();
    public function add(Expression $expression) {
        $this->expressions []= $expression;
        return $this;
    }

    public function get() {
        return $this->expressions;
    }

    public function __toString() {
        $result = '';
        foreach ($this->expressions as $expression) {
            $result .= $expression->build() . ';' . PHP_EOL;
        }
        return $result;
    }
}