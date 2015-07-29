<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Definition\Index;
use Yaoi\Database\Exception;
use Yaoi\Sql\Symbol;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

class Mysql extends Utility
{
    public function killSleepers($timeout = 30)
    {
        foreach ($this->database->query("SHOW PROCESSLIST") as $row) {
            if ($row['Time'] > $timeout) {
                $this->database->query("KILL $row[Id]");
            }
        }
        return $this;
    }


    const _PRIMARY = 'PRIMARY';

    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        $tableSymbol = new Symbol($tableName);
        $res = $this->database->query("DESC ?", $tableSymbol);
        $columns = new \stdClass();
        while ($row = $res->fetchRow()) {
            $type = $row['Type'];
            $field = $row['Field'];

            $phpType = $this->getTypeByString($type);

            if ('auto_increment' === $row['Extra']) {
                $phpType += Column::AUTO_ID;
            }

            $column = new Column($phpType);
            $columns->$field = $column;
            $column->schemaName = $field;
            $notNull = $row['Null'] === 'NO';
            if ($row['Default'] !== null || !$notNull) {
                $column->setDefault($row['Default']);
            }
            $column->setFlag(Column::NOT_NULL, $notNull);
        }

        $definition = new Table($columns);
        $definition->setSchemaName($tableName);

        $res = $this->database->query("SHOW INDEX FROM ?", $tableSymbol);
        $indexes = array();
        $uniqueIndex = array();
        foreach ($res as $row) {
            $indexes [$row['Key_name']][$row['Seq_in_index']] = $columns->{$row['Column_name']};
            $uniqueIndex [$row['Key_name']] = !$row['Non_unique'];
        }

        foreach ($indexes as $indexName => $indexData) {
            ksort($indexData);
            $index = new Index(array_values($indexData));
            $index->setName($indexName);
            $index->setType($uniqueIndex[$indexName] ? Index::TYPE_UNIQUE : Index::TYPE_KEY);
            if ($indexName === self::_PRIMARY) {
                $definition->setPrimaryKey($index->columns);
            }
            else {
                $definition->addIndex($index);
            }
        }

        return $definition;
    }


    protected static function quoteSymbol($symbol) {
        return "`$symbol`";
    }


    protected function generateCreateTableColumns(Table $table) {
        $statement = '';

        foreach ($table->getColumns(true) as $column) {
            $statement .= ' ' . self::quoteSymbol($column->schemaName) . ' ' . $this->getColumnTypeString($column);

            if ($column->flags & Column::AUTO_ID) {
                $statement .= ' AUTO_INCREMENT';
            }

            $statement .= ',' . PHP_EOL;
        }
        return $statement;
    }


    public function generateCreateTableOnDefinition(Table $table) {
        $statement = 'CREATE TABLE ' . self::quoteSymbol($table->schemaName) . ' (' . PHP_EOL;
        $statement .= $this->generateCreateTableColumns($table);

        foreach ($table->indexes as $index) {
            $indexString = '';
            foreach ($index->columns as $column) {
                $indexString .=  self::quoteSymbol($column->schemaName) . ', ';
            }
            $indexString = substr($indexString, 0, -2);

            if ($index->type === Index::TYPE_KEY) {
                $statement .= ' KEY ' . self::quoteSymbol($index->getName()) . ' (' . $indexString . '),' . PHP_EOL;
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $statement .= ' UNIQUE KEY ' . self::quoteSymbol($index->getName()) . ' (' . $indexString . '),' . PHP_EOL;
            }
        }

        foreach ($table->foreignKeys as $foreignKey) {
            $statement .= ' CONSTRAINT ' . self::quoteSymbol($foreignKey->getName())
                . ' FOREIGN KEY (' . $this->quoteColumns($foreignKey->getChildColumns()) . ') REFERENCES '
                . self::quoteSymbol($foreignKey->getReferencedTable()->schemaName)
                . ' (' . $this->quoteColumns($foreignKey->getParentColumns()) . '),' . PHP_EOL;
        }

        $statement .= ' PRIMARY KEY (';
        foreach ($table->primaryKey as $column) {
            $statement .= self::quoteSymbol($column->schemaName) . ',';
        }
        $statement = substr($statement, 0, -1);
        $statement .= ')' . PHP_EOL;

        $statement .= ')' . PHP_EOL;

        return $statement;
    }


    private function getTypeByString($type) {
        $phpType = Column::STRING;
        switch (true) {
            case 'bigint' === substr($type, 0, 6):
            case 'int' === substr($type, 0, 3):
            case 'mediumint' === substr($type, 0, 9):
            case 'smallint' === substr($type, 0, 8):
            case 'tinyint' === substr($type, 0, 7):
                $phpType = Column::INTEGER;
                break;

            case 'decimal' === substr($type, 0, 7):
            case 'double' === $type:
            case 'float' === $type:
                $phpType = Column::FLOAT;
                break;

            case 'date' === $type:
            case 'datetime' === $type:
            case 'timestamp' === $type:
                $phpType = Column::TIMESTAMP;
                break;

        }
        return $phpType;
    }

    private function getIntTypeString(Column $column) {
        $intType = 'int';

        // TODO implement SIZE_ definitions
        /*
        switch (true) {
            case $flags & Column::SIZE_1B:
                $intType = 'tinyint';
                break;

            case $flags & Column::SIZE_2B:
                $intType = 'mediumint';
                break;

            case $flags & Column::SIZE_3B:
                $intType = 'mediumint';
                break;


        }
        */
        return $intType;
    }

    private function getFloatTypeString(Column $column) {
        // TODO implement double
        return 'float';
    }

    private function getStringTypeString(Column $column) {
        // TODO implement long strings

        $length = $column->stringLength ? $column->stringLength : 255;
        if ($column->stringFixed) {
            return 'char(' . $length . ')';
        }
        else {
            return 'varchar(' . $length . ')';
        }
    }

    private function getTimestampTypeString(Column $column) {
        return 'timestamp';
    }

    public function getColumnTypeString(Column $column) {
        $flags = $column->flags;
        switch (true) {
            case ($flags & Column::INTEGER):
                $typeString = $this->getIntTypeString($column);
                break;

            case $flags & Column::FLOAT:
                $typeString = $this->getFloatTypeString($column);
                break;

            case $flags & Column::STRING:
                $typeString = $this->getStringTypeString($column);
                break;

            case $flags & Column::TIMESTAMP:
                $typeString = $this->getTimestampTypeString($column);
                break;

            default:
                throw new Exception('Undefined column type for column ' . $column->propertyName, Exception::INVALID_SCHEMA);

        }

        if ($flags & Column::UNSIGNED) {
            $typeString .= ' unsigned';
        }

        if ($flags & Column::NOT_NULL) {
            $typeString .= ' NOT NULL';
        }

        if (false !== ($default = $column->getDefault())) {
            $typeString .= $this->database->expr(" DEFAULT ?", $default);
        }

        return $typeString;
    }

    public function generateAlterTable(Table $before, Table $after)
    {
        $alter = array();

        $beforeColumns = $before->getColumns(true, true);
        foreach ($after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $this->getColumnTypeString($afterColumn);

            if (!isset($beforeColumns[$columnName])) {
                $alter []= 'ADD COLUMN `' . $afterColumn->schemaName . '` ' . $afterTypeString;
            }
            else {
                $beforeColumn = $beforeColumns[$columnName];
                if ($this->getColumnTypeString($beforeColumn) !== $afterTypeString) {
                    $alter []= 'MODIFY COLUMN `' . $afterColumn->schemaName . '` ' . $afterTypeString;
                }
                unset($beforeColumns[$columnName]);
            }
        }
        foreach ($beforeColumns as $columnName => $beforeColumn) {
            $alter []= 'DROP COLUMN `' . $beforeColumn->schemaName . '`';
        }

        $beforeIndexes = $before->indexes;
        foreach ($after->indexes as $indexId => $index) {
            if (!isset($beforeIndexes[$indexId])) {
                $alter []= 'ADD '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX `' . $index->getName() . '` ()';
            }
            else {
                unset($beforeIndexes[$indexId]);
            }
        }
        foreach ($beforeIndexes as $indexId => $index) {
            $alter []= 'DROP INDEX `' . $index->getName() . '`';
        }

        if ($alter) {
            $alterSql = 'ALTER TABLE `' . $after->schemaName . '`' . PHP_EOL . implode(',' . PHP_EOL, $alter);
            return $alterSql;
        }
        else {
            return '';
        }
    }

    /**
     * @param Column[] $columns
     * @return mixed
     */
    public function checkColumns(array $columns)
    {
        foreach ($columns as $column) {
            if ($column->flags & Column::TIMESTAMP) {
                if (!$column->getDefault()) {
                    $column->setDefault('0000-00-00 00:00:00');
                    $column->setFlag(Column::NOT_NULL);
                }
            }
        }
    }

    /**
     * @param Column[] $columns
     */
    private function quoteColumns(array $columns) {
        $result = '';
        foreach ($columns as $column) {
            $result .= '`' . $column->schemaName . '`,';
        }
        if ($result) {
            return substr($result, 0, -1);
        }
    }


}