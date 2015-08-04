<?php

namespace Yaoi\Sql;


use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;

class AlterTable extends Expression
{
    public function generate(Table $before, Table $after)
    {
        if (!$this->database) {
            throw new Exception('Database is not set, please use `bindDatabase`', Exception::DATABASE_REQUIRED);
        }

        $database = $this->database;

        $alter = array();

        $beforeColumns = $before->getColumns(true, true);
        foreach ($after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $afterColumn->getTypeString();

            if (!isset($beforeColumns[$columnName])) {
                $alter []= $database->expr('ADD COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
            }
            else {
                $beforeColumn = $beforeColumns[$columnName];
                if ($beforeColumn->getTypeString() !== $afterTypeString) {
                    $alter []= $database->expr('MODIFY COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
                }
                unset($beforeColumns[$columnName]);
            }
        }
        foreach ($beforeColumns as $columnName => $beforeColumn) {
            $alter []= $database->expr('DROP COLUMN ?', new Symbol($beforeColumn->schemaName));
        }

        $beforeIndexes = $before->indexes;
        foreach ($after->indexes as $indexId => $index) {
            if (!isset($beforeIndexes[$indexId])) {
                $alter []= $database->expr('ADD '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX ? (?)', new Symbol($index->getName()), $index->columns);
            }
            else {
                unset($beforeIndexes[$indexId]);
            }
        }
        foreach ($beforeIndexes as $indexId => $index) {
            $alter []= $database->expr('DROP INDEX ?', new Symbol($index->getName()));
        }

        if ($alter) {
            $this->appendExpr('ALTER TABLE ?' . PHP_EOL, new Symbol($after->schemaName));
            $this->appendExpr(implode(',' . PHP_EOL, $alter));
        }

        return $this;
    }

}