<?php

namespace Yaoi\Sql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\ForeignKey;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;

class AlterTable extends Batch
{
    /** @var  Table */
    protected $before;
    /** @var  Table */
    protected $after;

    /** @var  Expression */
    protected $alterLines;

    /** @var  Expression */
    protected $alterExpression;

    /** @var  Expression */
    protected $addFkExpression;

    public function __construct(Table $before, Table $after)
    {
        $this->before = $before;
        $this->after = $after;
        $this->bindDatabase($before->database());

        $this->alterExpression = new SimpleExpression('ALTER TABLE ?' . PHP_EOL, new Symbol($this->after->schemaName));
        $this->alterLines = new SimpleExpression();
        $this->alterLines->setOpComma(',' . PHP_EOL);
        $this->add($this->alterExpression);
        $this->alterExpression->appendExpr($this->alterLines);

        $this->processColumns();
        $this->processIndexes();

        $this->addFkExpression = new SimpleExpression();
        $this->processForeignKeys();
        $this->alterExpression->appendExpr($this->addFkExpression);


        if ($this->alterLines->isEmpty()) {
            $this->alterExpression->disable();
        }
    }

    protected function processColumns()
    {
        $beforeColumns = $this->before->getColumns(true, true);
        foreach ($this->after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $afterColumn->getTypeString();

            if (!isset($beforeColumns[$columnName])) {
                $this->alterLines->commaExpr('ADD COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
            } else {
                $beforeColumn = $beforeColumns[$columnName];
                //$beforeColumn->setFlag(Column::IS_REFLECTED);
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

    protected function processIndexes()
    {
        /** @var Index[] $beforeIndexes */
        $beforeIndexes = array();
        foreach ($this->before->indexes as $index) {
            $beforeIndexes [$index->getName()] = $index;
        }

        foreach ($this->after->indexes as $index) {
            if (!isset($beforeIndexes[$index->getName()])) {
                $this->alterLines->commaExpr('ADD '
                    . ($index->type === Index::TYPE_UNIQUE ? 'UNIQUE ' : '')
                    . 'INDEX ? (?)', new Symbol($index->getName()), Symbol::prepareColumns($index->columns));
            } else {
                unset($beforeIndexes[$index->getName()]);
            }
        }
        if ($beforeIndexes) {
            foreach ($this->after->getForeignKeys() as $foreignKey) {
                if (isset($beforeIndexes[$foreignKey->getName()])) {
                    unset($beforeIndexes[$foreignKey->getName()]);
                }
            }
            foreach ($beforeIndexes as $index) {
                $this->alterLines->commaExpr('DROP INDEX ?', new Symbol($index->getName()));
            }
        }
    }


    protected function processForeignKeys()
    {
        /** @var ForeignKey[] $beforeForeignKeys */
        $beforeForeignKeys = array();
        if (!$this->before->disableForeignKeys) {
            foreach ($this->before->getForeignKeys() as $foreignKey) {
                $beforeForeignKeys [$foreignKey->getName()] = $foreignKey;
            }
        }
        $afterForeignKeys = $this->after->getForeignKeys();
        if ($this->after->disableForeignKeys) {
            $afterForeignKeys = array();
        }
        foreach ($afterForeignKeys as $foreignKey) {
            if (!isset($beforeForeignKeys[$foreignKey->getName()])) {
                $this->addFkExpression->commaExpr('ADD');
                $this->addFkExpression->appendExpr($this->database()->getUtility()->generateForeignKeyExpression($foreignKey));
            } else {
                unset($beforeForeignKeys[$foreignKey->getName()]);
            }
        }
        foreach ($beforeForeignKeys as $foreignKey) {
            $this->alterLines->commaExpr('DROP FOREIGN KEY ?', new Symbol($foreignKey->getName()));
        }
    }

    public function extractForeignKeysStatement()
    {
        $alterExpression = new SimpleExpression('ALTER TABLE ?' . PHP_EOL, new Symbol($this->after->schemaName));
        $alterExpression->bindDatabase($this->database());
        $alterExpression->appendExpr(clone $this->addFkExpression);
        $this->addFkExpression->disable();

        return $alterExpression;
    }
}