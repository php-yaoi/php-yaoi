<?php
namespace Yaoi\Database\Sqlite;


use Yaoi\Database;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Definition\Index;

class SchemaReader extends Database\Mysql\SchemaReader
{
    public function getTableDefinition($tableName)
    {
        $statement = $this->database->select()
            ->select('sql')
            ->from('sqlite_master')
            ->where('type = "table"')
            ->where('`tbl_name` = ?', $tableName)
            ->query()
            ->fetchRow('sql');

        $createTableReader = new CreateTableReader($statement, $this->database);
        $definition = $createTableReader->getDefinition();

        $this->readIndexes($definition);
        return $definition;
    }


    private function readIndexes(Table $table) {

        $res = $this->database->query("PRAGMA INDEX_LIST (?)", $table->schemaName)->fetchAll();
        $res = array_reverse($res);
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