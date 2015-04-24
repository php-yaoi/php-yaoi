<?php

class Sql_Statement extends Sql_ComplexStatement {

    /**
     * @var Database
     */
    protected $database;

    public function bindDatabase(Database_Interface $client = null) {
        $this->database = $client;
        return $this;
    }

    public function query(Database_Interface $client = null) {
        if (null === $client) {
            return $this->database->query($this);
        }
        else {
            return $client->query($this);
        }
    }

    public function __toString() {
        try {
            $res = $this->build($this->database->getDriver());
            return $res;
        }
        catch (Exception $e) {
            return '/* ERROR: ' . $e->getMessage() . ' */';
        }
    }



    const CMD_SELECT = 'SELECT';
    const CMD_DELETE = 'DELETE';
    const CMD_INSERT = 'INSERT';
    const CMD_UPDATE = 'UPDATE';
    private $command;

    protected $tables = array();
    public function update($table = null) {
        // TODO implement
        // UPDATE t1 LEFT JOIN t2 ON t1.e = t2.e SET t1.c = t2.cc WHERE t1.ff = 45
        // UPDATE t1 SET dd = 1 WHERE ddd = 2
        $this->command = self::CMD_UPDATE;
        if (null !== $table) {
            $this->tables []= $table;
        }
        return $this;
    }

    public function insert($table) {
        $this->command = self::CMD_INSERT;
        if (null !== $table) {
            $this->tables []= $table;
        }
        return $this;
    }

    public function delete($table = null) {
        $this->command = self::CMD_DELETE;
        if (null !== $table) {
            $this->tables []= $table;
        }
        return $this;
    }




    /**
     * @var Sql_Expression[]
     */
    protected $select = array();
    public function select($expression = null, $binds = null) {
        $this->command = self::CMD_SELECT;
        if (null !== $expression) {
            $this->select []= Sql_Expression::createFromFuncArguments(func_get_args());
        }
        return $this;
    }

    protected function buildSelect(Database_Quoter $quoter) {
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
        }
        else {
            $columns = '*';
        }

        if (!$columns) {
            throw new Sql_Exception('Missing columns in SELECT statement', Sql_Exception::MISSING_COLUMNS);
        }
        return ' ' . $columns;
    }


    public function build(Database_Quoter $quoter = null) {
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
        }

        elseif ($this->command === self::CMD_UPDATE) {
            $q = self::CMD_UPDATE;
            $q .= $this->buildTable($quoter);
            $q .= $this->buildJoin($quoter);
            $q .= $this->buildSet($quoter);
            $q .= $this->buildWhere($quoter);
            $q .= $this->buildOrder($quoter);
            $q .= $this->buildLimit();
        }

        elseif ($this->command === self::CMD_DELETE) {
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
        }

        elseif ($this->command === self::CMD_INSERT) {
            $q = self::CMD_INSERT;
            $q .= $this->buildTable($quoter);
            $q .= $this->buildValues($quoter);
        }

        return $q;
    }


    private function buildTable(Database_Quoter $quoter) {
        if ($this->tables) {
            $tables = $this->tables;
            foreach ($tables as &$table) {
                if ($table instanceof Sql_Symbol) {
                    $table = $quoter->quote($table);
                }
            }
            return ' ' . implode(', ', $tables);
        }
        else {
            return '';
        }
    }



    public function expr($expression, $binds = null) {
        $e = Sql_Expression::createFromFuncArguments(func_get_args());
        return $e;
    }

    public function isEmpty() {
        if ($this->disabled) {
            return true;
        }
        return false;
    }

}