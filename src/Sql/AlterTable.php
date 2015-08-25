<?php

namespace Yaoi\Sql;


use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;

class AlterTable extends Batch
{
    /** @var  Table */
    protected $before;
    /** @var  Table */
    protected $after;

    /** @var  Expression */
    protected $alterLines;

    public function __construct(Table $before, Table $after) {
        $this->before = $before;
        $this->after = $after;

        $this->database = $before->database();
        $alterExpression = new SimpleExpression('ALTER TABLE ?' . PHP_EOL, new Symbol($this->after->schemaName));
        $this->alterLines = new SimpleExpression();
        $this->alterLines->setOpComma(',' . PHP_EOL);
        $this->add($alterExpression);

        $this->processColumns();
        $this->processIndexes();

        if ($this->alterLines->isEmpty()) {
            $alterExpression->disable();
        }
        else {
            $alterExpression->appendExpr($this->alterLines);
        }
    }

    protected function processColumns() {
        $beforeColumns = $this->before->getColumns(true, true);
        foreach ($this->after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $afterColumn->getTypeString();

            if (!isset($beforeColumns[$columnName])) {
                $this->alterLines->commaExpr('ADD COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
            }
            else {
                $beforeColumn = $beforeColumns[$columnName];
                if ($beforeColumn->getTypeString() !== $afterTypeString) {
                    //var_dump('MODIFY:' . $beforeColumn->schemaName, $beforeColumn->getTypeString(), $afterTypeString);
                    $this->alterLines->commaExpr('MODIFY COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
                }
                unset($beforeColumns[$columnName]);
            }
        }
        foreach ($beforeColumns as $columnName => $beforeColumn) {
            $this->alterLines->commaExpr('DROP COLUMN ?', new Symbol($beforeColumn->schemaName));
        }
    }

    protected function processIndexes() {
        $beforeIndexes = $this->before->indexes;
        foreach ($this->after->indexes as $indexId => $index) {
            if (!isset($beforeIndexes[$indexId])) {
                $this->alterLines->commaExpr('ADD '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX ? (?)', new Symbol($index->getName()), $index->columns);
            }
            else {
                unset($beforeIndexes[$indexId]);
            }
        }
        if ($beforeIndexes) {
            foreach ($this->after->foreignKeys as $foreignKey) {
                if (isset($beforeIndexes[$foreignKey->getName()])) {
                    unset($beforeIndexes[$foreignKey->getName()]);
                }
            }
            foreach ($beforeIndexes as $indexId => $index) {
                $this->alterLines->commaExpr('DROP INDEX ?', new Symbol($index->getName()));
            }
        }
    }

}