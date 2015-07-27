<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Database\Exception;
use Yaoi\String\Utils;

class Table extends BaseClass
{
    /** @var Column */
    public $autoIdColumn;

    /** @var Column[] */
    public $primaryKey = array();

    /** @var Index[]  */
    public $indexes = array();

    /** @var \stdClass  */
    public $columns;

    public $schemaName;

    public function setSchemaName($schemaName) {
        $this->schemaName = $schemaName;
        return $this;
    }

    public $className;

    public function __construct($columns = null) {
        if (!$columns) {
            $this->columns = new \stdClass();
        }
        else {
            $this->setColumns($columns);
        }
    }

    /**
     * @param bool|true $asArray
     * @return Column[]|\stdClass
     */
    public function getColumns($asArray = false, $bySchemaName = false) {
        if ($bySchemaName) {

        }
        return $asArray ? (array)$this->columns : $this->columns;
    }

    /**
     * @param $name
     * @return null|Column
     */
    public function getColumn($name) {
        return isset($this->columns->$name) ? $this->columns->$name : null;
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
                $this->columns->$name = $column;
            }

            // another column reference
            if (!empty($column->table)) {
                $refColumn = $column;
                $column = clone $column;
                $this->columns->$name = $column;
                $column->foreignKey = $refColumn;
                $column->setFlag(Column::AUTO_ID, false);
            }

            $column->propertyName = $name;
            $column->schemaName = Utils::fromCamelCase($name);
            $column->table = $this;

            if ($column->flags & Column::AUTO_ID) {
                $this->autoIdColumn = $column;
                if (!$this->primaryKey) {
                    $this->primaryKey = array($column->schemaName => $column);
                }
            }

            if ($column->foreignKey) {
                $this->addConstraint($column, $column->foreignKey);
            }
            if ($column->isUnique) {
                $this->addIndex(Index::TYPE_UNIQUE, $column);
            }
            elseif ($column->isIndexed) {
                $this->addIndex(Index::TYPE_KEY, $column);
            }
        }

        return $this;
    }

    /**
     * @param Column[] $columns
     * @return $this
     */
    public function setPrimaryKey($columns) {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        $this->primaryKey = array();
        /** @var Column $column */
        foreach ($columns as $column) {
            $this->primaryKey [$column->schemaName]= $column;
        }
        return $this;
    }


    public function addIndex($index) {
        if (!$index instanceof Index) {
            $args = func_get_args();
            $type = array_shift($args);
            $columns = $args;

            $index = Index::create($columns)->setType($type);
        }

        $this->indexes [$index->getId()]= $index;

        return $this;
    }

    public $constraints = array();
    public function addConstraint(Column $foreignKeyColumn, Column $referenceColumn) {
        $this->constraints []= array($foreignKeyColumn, $referenceColumn);
        return $this;
    }


    private $database;
    public function bindDatabase(Database\Contract $database) {
        $this->database = $database;
        return $this;
    }

    /**
     * @return \Yaoi\Database\Contract;
     * @throws \Yaoi\Service\Exception
     */
    public function database()
    {
        if (null === $this->database) {
            $this->database = Database::getInstance();
        }
        return $this->database;
    }

}