<?php

namespace Yaoi\Sql;

use Yaoi\Database;
use Yaoi\Database\Contract;
use Yaoi\String\Quoter;

class Statement extends ComplexStatement
{

    public function query(Contract $client = null)
    {
        if (null === $client) {
            $query = $this->database->query($this);
        } else {
            $query = $client->query($this);
        }
        if ($this->resultClass) {
            $query->bindResultClass($this->resultClass);
        }
        return $query;
    }


    const CMD_SELECT = 'SELECT';
    const CMD_DELETE = 'DELETE';
    const CMD_INSERT = 'INSERT INTO';
    const CMD_UPDATE = 'UPDATE';
    private $command;

    public function update($table = null)
    {
        // TODO implement
        // UPDATE t1 LEFT JOIN t2 ON t1.e = t2.e SET t1.c = t2.cc WHERE t1.ff = 45
        // UPDATE t1 SET dd = 1 WHERE ddd = 2
        $this->command = self::CMD_UPDATE;
        if (null !== $table) {
            $this->from($table);
        }
        return $this;
    }

    public function insert($table)
    {
        $this->command = self::CMD_INSERT;
        if (null !== $table) {
            $this->from($table);
        }
        return $this;
    }

    public function delete($table = null)
    {
        $this->command = self::CMD_DELETE;
        if (null !== $table) {
            $this->from($table);
        }
        return $this;
    }


    /**
     * @var SimpleExpression[]
     */
    protected $select = array();

    public function select($expression = null, $binds = null)
    {
        $this->command = self::CMD_SELECT;
        if (null !== $expression) {
            $this->select [] = SimpleExpression::createFromFuncArguments(func_get_args());
        }
        return $this;
    }

    protected function buildSelect(Quoter $quoter)
    {
        $columns = '';
        if ($this->select) {
            foreach ($this->select as $column) {
                if (!$column->isEmpty()) {
                    $columns .= $column->build($quoter) . ', ';
                }
            }
            if ($columns) {
                $columns = substr($columns, 0, -2);
            }
        } else {
            $columns = '*';
        }

        if (!$columns) {
            throw new \Yaoi\Sql\Exception('Missing columns in SELECT statement', \Yaoi\Sql\Exception::MISSING_COLUMNS);
        }
        return ' ' . $columns;
    }


    public function build(Quoter $quoter = null)
    {
        if (null === $quoter) {
            $quoter = $this->database->getDriver();
        }

        $q = '';

        if ($this->command === self::CMD_SELECT) {
            $q = self::CMD_SELECT;
            $q .= $this->buildSelect($quoter);
            $q .= $this->buildFrom($quoter);
            $q .= $this->buildJoin($quoter);
            $q .= $this->buildWhere($quoter);
            $q .= $this->buildGroupBy($quoter);
            $q .= $this->buildHaving($quoter);
            $q .= $this->buildOrder($quoter);
            $q .= $this->buildLimit();
            $q .= $this->buildUnion($quoter);
        } elseif ($this->command === self::CMD_UPDATE) {
            $q = self::CMD_UPDATE;
            $q .= $this->buildTable($quoter);
            $q .= $this->buildJoin($quoter);
            $q .= $this->buildSet($quoter);
            $q .= $this->buildWhere($quoter);
            $q .= $this->buildOrder($quoter);
            $q .= $this->buildLimit();
        } elseif ($this->command === self::CMD_DELETE) {
            $q = self::CMD_DELETE;
            $from = $this->buildTable($quoter);
            if ($from) {
                $from = ' FROM' . $from;
            }
            $q .= $from;
            $q .= $this->buildJoin($quoter);
            $q .= $this->buildWhere($quoter);
            $q .= $this->buildOrder($quoter);
            $q .= $this->buildLimit();
        } elseif ($this->command === self::CMD_INSERT) {
            $q = self::CMD_INSERT;
            $q .= $this->buildTable($quoter);
            $q .= $this->buildValues($quoter);
        }

        return $q;
    }


    public function expr($expression, $binds = null)
    {
        $e = SimpleExpression::createFromFuncArguments(func_get_args());
        return $e;
    }

    public function isEmpty()
    {
        if ($this->disabled) {
            return true;
        }
        return false;
    }

    protected $resultClass;

    public function bindResultClass($resultClass = null)
    {
        $this->resultClass = $resultClass;
        return $this;
    }


}