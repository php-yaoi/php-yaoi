<?php

namespace Yaoi\Sql;

use Yaoi\String\Quoter;

class Batch extends Expression
{
    /** @var Expression[] */
    private $statements = array();
    public function add(Expression $expression) {
        $this->statements []= $expression;
        return $this;
    }

    /**
     * @return Expression[]
     */
    public function get() {
        $result = array();
        $this->appendResult($result);
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
        try {
            $result = '';
            if (null === $quoter) {
                $quoter = $this->database()->getDriver();
            }
            $flatStatements = $this->get();
            $builds = array();
            foreach ($flatStatements as $expression) {
                $build = $expression->build($quoter);
                if ($build) {
                    $builds []= $build;
                }
            }

            if (!$builds) {
                return '';
            }
            if (count($builds) > 1) {
                return implode(';' . PHP_EOL, $builds) . ';' . PHP_EOL;
            }
            else {
                return $builds[0];
            }
        }
        catch (\Exception $e) {
            return '/* ERROR: ' . $e->getMessage() . ' */';
        }
    }

    public function __toString() {
        return $this->build();
    }

    public function isEmpty()
    {
        foreach ($this->statements as $statement) {
            if (!$statement->isEmpty()) {
                return false;
            }
        }
        return true;
    }
}