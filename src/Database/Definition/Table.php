<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;
use Yaoi\Database\Exception;

class Table extends BaseClass
{
    public $autoIncrement;
    public $primaryKey = array();
    /** @var Index[]  */
    public $indexes = array();
    /** @var Column[]  */
    public $columns = array();
    public $defaults = array();
    public $notNull = array();

    public $_tableName;

    public function setTableName($name) {
        $this->_tableName = $name;
        return $this;
    }


    public function __construct($columns = null) {
        if (!$columns) {
            return;
        }
        else {
            $this->setColumns($columns);
        }
    }


    public function setColumns($columns) {
        if (is_object($columns)) {
            $this->columns = (array)$columns;
        }
        elseif (is_array($columns)) {
            $this->columns = $columns;
        }
        else {
            throw new Exception('Object of stdClass or Column[] required as argument', Exception::INVALID_ARGUMENT);
        }

        foreach ($this->columns as $name => $column) {
            $column->name = $name;
            $column->table = $this;
            $this->$name = $column;
            if ($column->constraint) {
                $this->addConstraint($column, $column->constraint);
            }
        }

        return $this;
    }

    public function setPrimaryKey($columns) {
        if (is_array($columns)) {
            $this->primaryKey = $columns;
        }
        else {
            $this->primaryKey = func_get_args();
        }
        return $this;
    }


    public function addIndex($index) {
        if ($index instanceof Index) {
            $this->indexes []= $index;
        }
        else {
            $args = func_get_args();
            $type = array_shift($args);
            $columns = $args;

            $this->indexes []= Index::create($columns)->setType($type);
        }
        return $this;
    }

    public $constraints = array();
    public function addConstraint(Column $foreignKeyColumn, Column $referenceColumn) {
        $this->constraints []= array($foreignKeyColumn, $referenceColumn);
        return $this;
    }

}