<?php

namespace Yaoi\Sql;

use Yaoi\BaseClass;
use Yaoi\String\Quoter;

class Batch extends Expression
{
    /** @var Expression[] */
    private $statements = array();
    public function add(Expression $expression) {
        $this->statements []= $expression;
        return $this;
    }

    public function get() {
        $result = array();
        foreach ($this->statements as $statement) {
            if ($statement instanceof Batch) {
                $statement->appendResult($result);
            }
            else {
                $result []= $statement;
            }
        }
        return $result;
    }

    private function appendResult(&$result) {
        foreach ($this->statements as $statement) {
            if ($statement instanceof Batch) {
                $statement->appendResult($result);
            }
            else {
                $result []= $statement;
            }
        }
    }


    public function build(Quoter $quoter = null) {
        $result = '';
        foreach ($this->statements as $expression) {
            $build = $expression->build($quoter);
            if ($build) {
                if ($expression instanceof Batch) {
                    $result .= $build;
                }
                else {
                    $result .= $build . ';' . PHP_EOL;
                }
            }
        }
        return $result;
    }

    public function __toString() {
        return $this->build();
    }
}