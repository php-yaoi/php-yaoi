<?php

namespace Yaoi\Database\Mysql;

use Yaoi\Database;
use Yaoi\Sql\Symbol;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Definition\Index;

class SchemaReader
{
    const _PRIMARY = 'PRIMARY';

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Database\Utility
     */
    protected $utility;

    public function __construct(Database\Contract $database) {
        $this->database = $database;
        $this->utility = $database->getUtility();
    }

    public function getTableDefinition($tableName) {
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

        $definition = new Table($columns, $this->database, $tableName);

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


    public function getTypeByString($type) {
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

}