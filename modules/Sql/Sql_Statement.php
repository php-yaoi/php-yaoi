<?php

class Sql_Statement extends Sql_ComplexStatement {

    /**
     * @var Database
     */
    protected $database;
    public function bindDatabase(Database $client = null) {
        $this->database = $client;
        return $this;
    }

    public function query() {
        return $this->database->query($this);
    }

    public function __toString() {
        return $this->build($this->database);
    }



    const CMD_SELECT = 'SELECT';
    const CMD_DELETE = 'DELETE';
    const CMD_INSERT = 'INSERT';
    const CMD_UPDATE = 'UPDATE';
    private $command;

    protected $tables = array();
    public function update($table) {
        // TODO implement
        // UPDATE t1 LEFT JOIN t2 ON t1.e = t2.e SET t1.c = t2.cc WHERE t1.ff = 45
        // UPDATE t1 SET dd = 1 WHERE ddd = 2
        $this->command = self::CMD_UPDATE;
        $this->tables[]= $table;
        return $this;
    }

    public function insert($table) {
        // TODO insert is totally different, no join, no where
        $this->command = self::CMD_INSERT;
        $this->tables []= $table;
        return $this;
    }

    public function delete($table = null) {
        // TODO implement
        $this->command = self::CMD_DELETE;
        $this->tables []= $table;
        return $this;
    }




    /**
     * @var Sql_Expression[]
     */
    protected $select = array();
    public function select($expression, $binds = null) {
        $this->command = self::CMD_SELECT;
        $this->select []= Sql_Expression::createFromFuncArguments(func_get_args());
        return $this;
    }

    protected function buildSelect(Database $client) {
        $columns = '';
        if ($this->select) {
            foreach ($this->select as $column) {
                if (!$column->isEmpty()) {
                    $columns .= $column->build($client) . ', ';
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



    public function build(Database $client) {
        if (null === $client) {
            $client = $this->database;
        }

        if ($this->command === self::CMD_SELECT) {
            $q = self::CMD_SELECT;
            $q .= $this->buildSelect($client);
            $q .= $this->buildFrom($client);
            $q .= $this->buildJoin($client);
            $q .= $this->buildWhere($client);
            $q .= $this->buildGroupBy($client);
            $q .= $this->buildHaving($client);
            $q .= $this->buildOrder($client);
            $q .= $this->buildLimit();
            return $q;
        }

        elseif ($this->command === self::CMD_UPDATE) {
            $q = self::CMD_UPDATE;
            $q .= $this->buildTable();
            $q .= $this->buildJoin($client);
            $q .= $this->buildSet($client);
            $q .= $this->buildWhere($client);
            $q .= $this->buildOrder($client);
            $q .= $this->buildLimit();

            return $q;
        }

        elseif ($this->command === self::CMD_DELETE) {
            $q = self::CMD_DELETE;
            $q .= $this->buildTable();
            $q .= $this->buildFrom($client);
            $q .= $this->buildJoin($client);
            $q .= $this->buildWhere($client);
            $q .= $this->buildOrder($client);
            $q .= $this->buildLimit();

            return $q;
        }

        return '';
    }


    private function buildTable() {
        if ($this->tables) {
            return ' ' . implode(', ', $this->tables);
        }
        else {
            return '';
        }
    }




}