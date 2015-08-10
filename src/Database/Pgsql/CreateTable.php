<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 8/1/15
 * Time: 17:09
 */

namespace Yaoi\Database\Pgsql;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Sql\Batch;
use Yaoi\Sql\SimpleExpression;
use Yaoi\Sql\Symbol;

class CreateTable extends \Yaoi\Sql\CreateTable
{
    protected function appendColumns() {
        $utility = $this->database->getUtility();

        foreach ($this->table->getColumns(true) as $column) {
            $this->appendComma();
            if ($column->flags & Column::AUTO_ID) {
                $this->appendExpr(' ? SERIAL', new Symbol($column->schemaName));
            }
            else {
                $this->appendExpr(' ? ' . $utility->getColumnTypeString($column), new Symbol($column->schemaName));
            }
        }
    }

    protected function appendIndexes() {
        $this->batch = new Batch();
        $this->batch->add($this);

        foreach ($this->table->indexes as $index) {
            $columns = array();
            foreach ($index->columns as $column) {
                $columns []= new Symbol($column->schemaName);
            }

            if ($index->type === Index::TYPE_KEY) {
                $createIndex = new SimpleExpression(
                    "CREATE INDEX ? ON ? (?)",
                    new Symbol($index->getName()),
                    new Symbol($this->table->schemaName),
                    $columns
                    );
                $createIndex->bindDatabase($this->database);
                $this->batch->add($createIndex);
                //$this->appendExpr(' KEY ? (?),' . PHP_EOL, new Symbol($index->getName()), $columns);
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $this->appendComma();
                $this->appendExpr(' CONSTRAINT ? UNIQUE (?)', new Symbol($index->getName()), $columns);
            }
        }
    }

    /** @var  Batch */
    public $batch;


}