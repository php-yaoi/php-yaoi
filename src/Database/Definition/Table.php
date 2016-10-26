<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\DependencyRepository;
use Yaoi\Log;
use Yaoi\Sql\CreateTable;
use Yaoi\String\Utils;

class Table extends BaseClass
{
    /** @var Column */
    public $autoIdColumn;

    /** @var Column[] */
    public $primaryKey = array();

    /** @var Index[]  */
    public $indexes = array();

    /** @var Columns  */
    public $columns;


    public $schemaName; // tODO exception on empty schemaName

    public function setSchemaName($schemaName) {
        $this->schemaName = $schemaName;
        return $this;
    }

    public $entityClassName;

    /**
     * Table constructor.
     * @param \stdClass|null $columns @deprecated
     * @param Database\Contract|null $database
     * @param $schemaName
     */
    public function __construct(\stdClass $columns = null, Database\Contract $database = null, $schemaName) {
        $this->schemaName = $schemaName;
        $this->databaseId = DependencyRepository::add($database);
        $this->columns = new Columns($this);
        if ($columns) {
            foreach ((array)$columns as $name => $column) {
                $this->columns->$name = $column;
            }
        }
    }

    /**
     * @param bool $asArray
     * @param bool $bySchemaName
     * @return array|Column[]|Columns
     */
    public function getColumns($asArray = false, $bySchemaName = false) {
        if ($bySchemaName) {
            $columns = array();
            /** @var Column $column */
            foreach ($this->columns->getArray() as $column) {
                $columns [$column->schemaName]= $column;
            }
            return $columns;
        }

        return $asArray ? $this->columns->getArray() : $this->columns;
    }

    /**
     * @param $name
     * @return null|Column
     */
    public function getColumn($name) {
        return isset($this->columns->$name) ? $this->columns->__get($name) : null;
    }

    /**
     * @param Column[]|Column $columns
     * @return $this
     */
    public function setPrimaryKey($columns) {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        $this->primaryKey = array();
        /** @var Column $column */
        $index = new Index($columns);
        $index->setType(Index::TYPE_PRIMARY);
        $this->indexes [Index::TYPE_PRIMARY]= $index;

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

        if ($index->type === Index::TYPE_PRIMARY) {
            $this->setPrimaryKey($index->columns);
            return $this;
        }

        $this->indexes [$index->getName()]= $index;

        return $this;
    }

    public function dropIndex($index)
    {
        if (!$index instanceof Index) {
            $args = func_get_args();
            $type = array_shift($args);
            $columns = $args;

            $index = Index::create($columns)->setType($type);
        }

        if ($index->type === Index::TYPE_PRIMARY) {
            throw new Exception('Can not drop primary key', Exception::NOT_IMPLEMENTED);
        }

        if (isset($this->indexes[$index->getName()])) {
            unset($this->indexes[$index->getName()]);
        }

        return $this;
    }

    private $columnForeignKeys = array();

    /**
     * @param Column $column
     * @return null|ForeignKey
     */
    public function getForeignKeyByColumn(Column $column)
    {
        return $column->foreignKey;
    }

    /**
     * @var array|Table[]
     * @todo make dynamic
     */
    public $dependentTables = array();

    /**
     * @var ForeignKey[]
     */
    private $foreignKeys = array();
    public function addForeignKey(ForeignKey $foreignKey) {
        //$foreignKey->getReferencedTable()->dependentTables [$this->schemaName]= $this;
        $this->foreignKeys [$foreignKey->getName()]= $foreignKey;
        foreach ($foreignKey->getLocalColumns() as $column) {
            $this->columnForeignKeys[$column->propertyName] = $foreignKey;
        }
        return $this;
    }

    /**
     * @return array|ForeignKey[]
     */
    public function getForeignKeys()
    {
        $foreignKeys = $this->foreignKeys;
        foreach ($this->columns->getArray() as $column) {
            if ($column->foreignKey) {
                $foreignKeys[] = $column->foreignKey;
            }
        }
        return $foreignKeys;
    }



    private $databaseId;

    /**
     * @return Database|null
     */
    public function database() {
        return DependencyRepository::get($this->databaseId);
    }


    /**
     * @return CreateTable
     * @throws Database\Exception
     */
    public function getCreateTable() {
        return $this->database()->getUtility()->generateCreateTableOnDefinition($this);
    }


    public function getAlterTableFrom(Table $before) {
        return $this->database()->getUtility()->generateAlterTable($before, $this);
    }


    public function migration() {
        foreach ($this->getForeignKeys() as $foreignKey) {
            $foreignKey->getReferencedTable()->dependentTables[$this->schemaName] = $this;
        }
        return new Database\Entity\Migration($this);
    }


    public $disableForeignKeys = false;
    public function disableDatabaseForeignKeys($disable = true) {
        $this->disableForeignKeys = $disable;
        return $this;
    }

    public $alias;
}