<?php

namespace Yaoi\Sql;

use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\String\Quoter;

abstract class CreateTable extends Batch
{
    /** @var  Table */
    protected $table;

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
        foreach ($this->table->getForeignKeys() as $foreignKey) {
            $this->fkLines->commaExpr($this->database()->getUtility()->generateForeignKeyExpression($foreignKey));
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

    /** @var  Expression */
    protected $fkLines;

    public function __construct(Table $table) {
        $this->table = $table;
        $this->bindDatabase($table->database());
        $this->createLines = new SimpleExpression();
        $this->createLines->setOpComma(',' . PHP_EOL);
        $this->fkLines = new SimpleExpression();
        $this->fkLines->setOpComma(',' . PHP_EOL);

        $createExpression = new SimpleExpression('CREATE TABLE ? (' . PHP_EOL, $this->table);
        $this->add($createExpression);

        $createExpression->appendExpr($this->createLines);
        $createExpression->appendExpr(PHP_EOL . ')');


        $this->appendColumns();
        $this->appendIndexes();
        $this->createLines->commaExpr($this->fkLines);
        $this->appendForeignKeys();
        $this->appendPrimaryKey();

        if ($this->createLines->isEmpty()) {
            $createExpression->disable();
        }
    }

    public function extractForeignKeysStatement()
    {
        $this->fkLines->disable();
        return $this->database()->getUtility()
            ->generateAlterTable(new Table(null, $this->database(), '_any_name'), $this->table)
            ->extractForeignKeysStatement();
    }

    public function isEmpty()
    {
        return false;
    }
}