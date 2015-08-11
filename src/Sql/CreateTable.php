<?php

namespace Yaoi\Sql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;

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
            $columns = array();
            foreach ($index->columns as $column) {
                $columns []= new Symbol($column->schemaName);
            }

            if ($index->type === Index::TYPE_KEY) {
                $this->createLines->commaExpr(' KEY ? (?)', new Symbol($index->getName()), $columns);
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $this->createLines->commaExpr(' UNIQUE KEY ? (?)', new Symbol($index->getName()), $columns);
            }
        }
    }

    protected function appendForeignKeys() {
        foreach ($this->table->foreignKeys as $foreignKey) {
            $this->createLines->commaExpr(' CONSTRAINT ? FOREIGN KEY (?) REFERENCES ? (?)',
                new Symbol($foreignKey->getName()),
                $foreignKey->getChildColumns(),
                new Symbol($foreignKey->getReferencedTable()->schemaName),
                $foreignKey->getParentColumns()
            );
        }
    }

    public function appendPrimaryKey() {
        $this->createLines->commaExpr(' PRIMARY KEY (?)', array_values($this->table->primaryKey));
    }


    /** @var  Expression */
    protected $createLines;

    public function __construct(Table $table) {
        $this->table = $table;
        $this->database = $table->database();
        $this->createLines = new SimpleExpression();
        $this->createLines->setOpComma(',' . PHP_EOL);

        $createExpression = new SimpleExpression('CREATE TABLE ? (' . PHP_EOL, new Symbol($this->table->schemaName));
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