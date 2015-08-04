<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Pgsql\TypeString;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Pgsql\CreateTable;

class Pgsql extends Utility
{
    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        $res = $this->database->query("select c.column_name, c.is_nullable, c.data_type, c.column_default, tc.constraint_type
from INFORMATION_SCHEMA.COLUMNS AS c
  LEFT JOIN INFORMATION_SCHEMA.constraint_column_usage AS ccu ON c.column_name = ccu.column_name AND c.table_name = ccu.table_name
  LEFT JOIN INFORMATION_SCHEMA.table_constraints AS tc ON ccu.constraint_name = tc.constraint_name
where c.table_name = '$tableName';
");
        $primaryKey = array();
        $columns = new \stdClass();

        while ($r = $res->fetchRow()) {
            $field = $r['column_name'];
            $type = $r['data_type'];
            $phpType = Column::AUTO_TYPE;

            switch (true) {
                case 'integer' === substr($type, 0, 7):
                case 'smallint' === substr($type, 0, 8):
                case 'bigint' === substr($type, 0, 6):
                    $phpType = Column::INTEGER;
                    break;

                case 'numeric' === substr($type, 0, 7):
                case 'double' === substr($type, 0, 6):
                case 'real' === substr($type, 0, 4):
                    $phpType = Column::FLOAT;
                    break;

                case 'character' === substr($type, 0, 9):
                case 'text' === $type:
                    $phpType = Column::STRING;
                    break;

                case 'time' === $type:
                case 'time ' === substr($type, 0, 5):
                    $phpType = Column::STRING;
                    break;

                case 'timestamp' === substr($type, 0, 9):
                case 'date' === substr($type, 0, 4):
                    $phpType = Column::TIMESTAMP;
                    break;

            }

            $column = new Column($phpType);
            $column->setDefault($r['column_default']);
            if ('nextval' === substr($r['column_default'], 0, 7)) {
                $column->setFlag(Column::AUTO_ID);
            }
            $column->setFlag(Column::NOT_NULL, $r['is_nullable'] === 'NO');
            if ($r['constraint_type'] === 'PRIMARY KEY') {
                $primaryKey []= $column;
            }

            $columns->$field = $column;
        }

        $def = new Table($columns, $this->database, $tableName);
        $def->setPrimaryKey($primaryKey);

        return $def;
    }


    public function generateCreateTableOnDefinition(Table $table) {
        $expression = new CreateTable();
        $expression->bindDatabase($this->database)->generate($table);
        return $expression->batch;
    }

    public function getColumnTypeString(Column $column)
    {
        $typeString = new TypeString($this->database);
        return $typeString->getByColumn($column);
    }


    /**
     * Check/fix database related type misconceptions
     *
     * @param Column[] $columns
     * @return mixed
     */
    public function checkColumns(array $columns)
    {
        foreach ($columns as $column) {
            if ($column->flags & Column::AUTO_ID) {
                $column->flags = Column::AUTO_ID;
            }

            /*
            if ($column->flags & Column::TIMESTAMP) {
                if (!$column->getDefault()) {
                    $column->setDefault(null);
                    $column->setFlag(Column::NOT_NULL);
                }
            }
            */
        }
    }


}