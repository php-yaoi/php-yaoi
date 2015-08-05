<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 8/5/15
 * Time: 15:33
 */

namespace Yaoi\Database\Sqlite;


use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Definition\Index;

class SchemaReader extends Database\Mysql\SchemaReader
{
    public function getTableDefinition($tableName)
    {
        $res = $this->database->query("PRAGMA table_info($tableName)");

        /** @var Column[] $primaryKey */
        $primaryKey = array();
        $columns = new \stdClass();
        while ($r = $res->fetchRow()) {
            //print_r($r);
            $field = $r['name'];

            //if ('id' === $field) {
            //    print_r($r);
            //}

            $phpType = $this->getTypeByString(strtolower($r['type']));
            //echo $phpType;


            $column = new Column($phpType);
            $notNull = (bool)$r['notnull'];
            $column->setFlag(Column::NOT_NULL, $notNull);

            $default = $r['dflt_value'];
            //var_dump($r);
            if (null === $default) {
                $default = false;
            }
            elseif ("NULL" === $default) {
                $default = null;
            }
            elseif ("'" === $default[0]) {
                $default = substr($default, 1, -1);
            }

            if (null === $default && !$notNull) {
                $default = false;
            }

            $column->setDefault($default);

            if ($r['pk']) {
                $primaryKey []= $column;
            }

            $columns->$field = $column;
        }
        if (count($primaryKey) === 1) {
            $primaryKey[0]->setFlag(Column::AUTO_ID);
        }

        $definition = new Table($columns, $this->database, $tableName);
        $definition->setPrimaryKey($primaryKey);

        $this->readIndexes($definition);

        return $definition;
    }


    private function readIndexes(Table $table) {

        $res = $this->database->query("PRAGMA INDEX_LIST (?)", $table->schemaName);
        foreach ($res as $indexRow) {
            $cols = $this->database->query("PRAGMA INDEX_INFO (?)", $indexRow['name']);
            $columns = array();
            foreach ($cols as $colRow) {
                $columns []= $table->getColumn($colRow['name']);
            }
            $index = new Index($columns);
            $index->setType($indexRow['unique'] ? Index::TYPE_UNIQUE : Index::TYPE_KEY);
            $table->addIndex($index);
        }

    }


}