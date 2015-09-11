<?php

namespace Yaoi\Sql;

use Yaoi\Database\Definition\ForeignKey;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;

abstract class CreateTable extends Batch
{
    /** @var  Table */
    protected $table;

    protected function appendColumns() {
        $utility = $this->database->getUtility();

        foreach ($this->table->getColumns(true) as $column) {
            $this->createLines->commaExpr(' ? ' . $utility->getColumnTypeString($column), new Symbol($column->schemaName));
        }
    }



    protected function appendIndexes() {
        foreach ($this->table->indexes as $index) {
            $columns = Symbol::prepareColumns($index->columns);

            if ($index->type === Index::TYPE_KEY) {
                $this->createLines->commaExpr(' KEY ? (?)', new Symbol($index->getName()), $columns);
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $this->createLines->commaExpr(' UNIQUE KEY ? (?)', new Symbol($index->getName()), $columns);
            }
        }
    }

    protected function appendForeignKeys() {
        if ($this->table->disableForeignKeys) {
            return;
        }
        foreach ($this->table->foreignKeys as $foreignKey) {
            $this->createLines->commaExpr($this->database->getUtility()->generateForeignKeyExpression($foreignKey));
        }
    }

    public function appendPrimaryKey() {
        $columns = array();
        foreach ($this->table->primaryKey as $column) {
            $columns []= new Symbol($column->schemaName);
        }
        $this->createLines->commaExpr(' PRIMARY KEY (:columns)', array('columns' => $columns));
    }


    /** @var  Expression */
    protected $createLines;

    public function __construct(Table $table) {
        $this->table = $table;
        $this->database = $table->database();
        $this->createLines = new SimpleExpression();
        $this->createLines->setOpComma(',' . PHP_EOL);

        $createExpression = new SimpleExpression('CREATE TABLE ? (' . PHP_EOL, $this->table);
        $this->add($createExpression);

        $this->appendColumns();
        $this->appendIndexes();
        $this->appendForeignKeys();
        $this->appendPrimaryKey();

        if ($this->createLines->isEmpty()) {
            $createExpression->disable();
        }
        else {
            $createExpression->appendExpr($this->createLines);
            $createExpression->appendExpr(PHP_EOL . ')');
        }
    }
}