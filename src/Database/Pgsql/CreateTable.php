<?php

namespace Yaoi\Database\Pgsql;


use Yaoi\Database\Definition\Index;
use Yaoi\Sql\SimpleExpression;
use Yaoi\Sql\Symbol;

class CreateTable extends \Yaoi\Sql\CreateTable
{
    protected function appendColumns() {
        $utility = $this->database()->getUtility();

        foreach ($this->table->getColumns(true) as $column) {
            $this->createLines->commaExpr(' ? ' . $utility->getColumnTypeString($column), new Symbol($column->schemaName));
        }
    }

    protected function appendIndexes() {
        foreach ($this->table->indexes as $index) {
            $columns = Symbol::prepareColumns($index->columns);

            if ($index->type === Index::TYPE_KEY) {
                $createIndex = new SimpleExpression(
                    "CREATE INDEX ? ON ? (?)",
                    new Symbol($index->getName()),
                    new Symbol($this->table->schemaName),
                    $columns
                    );
                $createIndex->bindDatabase($this->database());
                $this->add($createIndex);
                //$this->appendExpr(' KEY ? (?),' . PHP_EOL, new Symbol($index->getName()), $columns);
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $this->createLines->commaExpr(' CONSTRAINT ? UNIQUE (?)', new Symbol($index->getName()), $columns);
            }
        }
    }
}