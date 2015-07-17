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
    /** @var \stdClass  */
    public $columns;
    public $defaults = array();
    public $notNull = array();

    public $name;

    public function setName($name) {
        $this->name = $name;
        return $this;
    }


    public function __construct($columns = null) {
        if (!$columns) {
            $this->columns = new \stdClass();
            return;
        }
        else {
            $this->setColumns($columns);
        }
    }


    public function setColumns($columns) {
        if (is_object($columns)) {
            $this->columns = $columns;
        }
        else {
            throw new Exception('Object of stdClass required as argument', Exception::INVALID_ARGUMENT);
        }

        /**
         * @var string $name
         * @var Column $column
         */
        foreach ((array)$this->columns as $name => $column) {
            if (is_int($column)) {
                $column = new Column($column);
            }

            if ($column->flags & Column::AUTO_ID) {
                if (!$this->primaryKey) {
                    $this->primaryKey = array($column);
                }
            }
            $column->name = $name;
            $column->table = $this;
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