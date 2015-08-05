<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 8/5/15
 * Time: 02:08
 */

namespace Yaoi\Database\Pgsql;

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Definition\Index;

class SchemaReader
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var Database\Utility
     */
    private $utility;

    public function __construct(Database\Contract $database) {
        $this->database = $database;
        $this->utility = $database->getUtility();
    }


    private function readIndexes($tableName) {
        $query = <<<SQL
select
  t.relname as table_name,
  i.relname as index_name,
  a.attname as column_name,
  ix.indisunique as is_unique,
  ix.indisprimary as is_primary
from
  pg_class t,
  pg_class i,
  pg_index ix,
  pg_attribute a
where
  t.oid = ix.indrelid
  and i.oid = ix.indexrelid
  and a.attrelid = t.oid
  and a.attnum = ANY(ix.indkey)
  and t.relkind = 'r'
  and t.relname = ?
order by
  t.relname,
  i.relname,
  ix.indnatts;
SQL;

        $res = $this->database->query($query, $tableName)->fetchAll();

        $indexData = array();
        foreach ($res as $row) {
            if ($row['is_primary']) {
                $row['index_name'] = 'PRIMARY';
            }
            $indexData [$row['index_name']]['columns'][$row['column_name']] = $row['column_name'];
            $indexData [$row['index_name']]['is_unique'] = $row['is_unique'];
            $indexData [$row['index_name']]['is_primary'] = $row['is_unique'];
        }


        return $indexData;
    }


    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        //echo PHP_EOL . 'table: ' . $tableName . PHP_EOL;
        $res = $this->database->query("select c.column_name, c.is_nullable, c.data_type, c.column_default, tc.constraint_type
from INFORMATION_SCHEMA.COLUMNS AS c
  LEFT JOIN INFORMATION_SCHEMA.constraint_column_usage AS ccu ON c.column_name = ccu.column_name AND c.table_name = ccu.table_name
  LEFT JOIN INFORMATION_SCHEMA.table_constraints AS tc ON ccu.constraint_name = tc.constraint_name
where c.table_name = ? ORDER BY c.ordinal_position ASC;
", $tableName);
        $columns = new \stdClass();

        while ($r = $res->fetchRow()) {
            //print_r($r);

            $field = $r['column_name'];
            //echo 'field: ' . $field . PHP_EOL;
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

            $notNull = $r['is_nullable'] === 'NO';
            $column = new Column($phpType);
            if ('nextval' === substr($r['column_default'], 0, 7)) {
                $column->setFlag(Column::AUTO_ID);
                $column->setFlag(Column::INTEGER);
            }
            $column->setFlag(Column::NOT_NULL, $notNull);
            if ($r['column_default'] !== null || !$notNull) {
                $column->setDefault($r['column_default']);
            }

            $columns->$field = $column;
        }

        $def = new Table($columns, $this->database, $tableName);
        $indexData = $this->readIndexes($tableName);

        if (isset($indexData['PRIMARY'])) {
            $primaryKey = array();
            foreach ($indexData['PRIMARY']['columns'] as $columnName) {
                $primaryKey []= $columns->$columnName;
            }
            $def->setPrimaryKey($primaryKey);
            unset($indexData['PRIMARY']);
        }

        foreach ($indexData as $indexName => $indexInfo) {
            $indexColumns = array();
            foreach ($indexInfo['columns'] as $columnName) {
                $indexColumns []= $columns->$columnName;
            }
            $index = new Index($indexColumns);
            $index->setType($indexInfo['is_unique'] ? Index::TYPE_UNIQUE : Index::TYPE_KEY);
            $def->addIndex($index);
        }

        return $def;
    }


}