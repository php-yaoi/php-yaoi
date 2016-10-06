<?php

namespace Yaoi\Database\Definition;

use Yaoi\String\Utils;

class Columns
{
    /**
     * @var Column[]
     */
    private $_arrayOfColumnData = array();
    /**
     * @var Table
     */
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function __set($name, $column)
    {
        if (is_int($column)) {
            $column = new Column($column);
            //$this->_arrayOfColumnData[$name] = $column;
        }

        // another column reference
        if (!empty($column->table)
            && $column->table->schemaName != $this->table->schemaName) {
            $refColumn = $column;
            $column = clone $column;

            $column->propertyName = $name;
            $column->schemaName = Utils::fromCamelCase($name);
            $column->table = $this->table;

            //$this->_arrayOfColumnData[$name] = $column;
            $foreignKey = new ForeignKey(array($column), array($refColumn));
            $column->foreignKey = $foreignKey;
            $this->table->addForeignKey($foreignKey);
            $column->setFlag(Column::AUTO_ID, false);
        } else {
            $column->propertyName = $name;
            $column->schemaName = Utils::fromCamelCase($name);
            $column->table = $this->table;
        }


        if ($column->flags & Column::AUTO_ID) {
            $this->table->autoIdColumn = $column;
            if (!$this->table->primaryKey) {
                $this->table->setPrimaryKey($column);
            }
        }

        if ($column->isUnique) {
            $index = new Index($column);
            $index->setType(Index::TYPE_UNIQUE);
            $this->table->addIndex($index);
        } elseif ($column->isIndexed) {
            $index = new Index($column);
            $index->setType(Index::TYPE_KEY);
            $this->table->addIndex($index);
        }

        $this->table->database()->getUtility()->checkColumn($column);
        $this->_arrayOfColumnData[$name] = $column;

    }

    public function __get($name)
    {
        if (!isset($this->_arrayOfColumnData[$name])) {
            var_dump(array_keys($this->_arrayOfColumnData));
            throw new Exception('Unknown column ' . $name);
        }
        return $this->_arrayOfColumnData[$name];
    }

    public function __isset($name)
    {
        return isset($this->_arrayOfColumnData[$name]);
    }

    public function __unset($name)
    {
        if (!isset($this->_arrayOfColumnData[$name])) {
            return;
        }
        $column = $this->_arrayOfColumnData[$name];
        if ($column->foreignKey !== null) {
            $this->table->removeForeignKey($column->foreignKey);
        }
        unset($this->_arrayOfColumnData[$name]);
    }

    /**
     * @return Column[]
     */
    public function getArray()
    {
        return $this->_arrayOfColumnData;
    }
}