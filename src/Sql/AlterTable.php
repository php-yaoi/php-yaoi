<?php

namespace Yaoi\Sql;


use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;

class AlterTable extends Expression
{
    /** @var  Table */
    protected $before;
    /** @var  Table */
    protected $after;

    protected $lines = array();

    public function generate(Table $before, Table $after)
    {
        if (!$this->database) {
            throw new Exception('Database is not set, please use `bindDatabase`', Exception::DATABASE_REQUIRED);
        }

        $this->before = $before;
        $this->after = $after;
        $this->lines = array();

        $this->processColumns();
        $this->processIndexes();

        if ($this->lines) {
            $this->appendExpr('ALTER TABLE ?' . PHP_EOL, new Symbol($this->after->schemaName));
            $this->appendExpr(implode(',' . PHP_EOL, $this->lines));
        }

        return $this;
    }

    protected function processColumns() {
        $beforeColumns = $this->before->getColumns(true, true);
        foreach ($this->after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $afterColumn->getTypeString();

            if (!isset($beforeColumns[$columnName])) {
                $this->lines []= $this->database->expr('ADD COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
            }
            else {
                $beforeColumn = $beforeColumns[$columnName];
                if ($beforeColumn->getTypeString() !== $afterTypeString) {
                    //var_dump('MODIFY:' . $beforeColumn->schemaName, $beforeColumn->getTypeString(), $afterTypeString);
                    $this->lines []= $this->database->expr('MODIFY COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
                }
                unset($beforeColumns[$columnName]);
            }
        }
        foreach ($beforeColumns as $columnName => $beforeColumn) {
            $this->lines []= $this->database->expr('DROP COLUMN ?', new Symbol($beforeColumn->schemaName));
        }
    }

    protected function processIndexes() {
        $beforeIndexes = $this->before->indexes;
        foreach ($this->after->indexes as $indexId => $index) {
            if (!isset($beforeIndexes[$indexId])) {
                $this->lines []= $this->database->expr('ADD '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX ? (?)', new Symbol($index->getName()), $index->columns);
            }
            else {
                unset($beforeIndexes[$indexId]);
            }
        }
        foreach ($beforeIndexes as $indexId => $index) {
            $this->lines []= $this->database->expr('DROP INDEX ?', new Symbol($index->getName()));
        }
    }

}