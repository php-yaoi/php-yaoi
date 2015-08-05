<?php

namespace Yaoi\Sql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;

abstract class CreateTable extends Expression
{
    /** @var  Table */
    protected $table;


    private $first = true;
    protected function appendComma() {
        if ($this->first) {
            $this->first = false;
        }
        else {
            $this->appendExpr(',' . PHP_EOL);
        }
    }


    protected function appendColumns() {
        $utility = $this->database->getUtility();

        foreach ($this->table->getColumns(true) as $column) {
            $this->appendComma();
            $this->appendExpr(' ? ' . $utility->getColumnTypeString($column), new Symbol($column->schemaName));

            if ($column->flags & Column::AUTO_ID) {
                $this->appendExpr(' AUTO_INCREMENT');
            }
        }
    }



    protected function appendIndexes() {
        foreach ($this->table->indexes as $index) {
            $columns = array();
            foreach ($index->columns as $column) {
                $columns []= new Symbol($column->schemaName);
            }

            if ($index->type === Index::TYPE_KEY) {
                $this->appendComma();
                $this->appendExpr(' KEY ? (?)', new Symbol($index->getName()), $columns);
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $this->appendComma();
                $this->appendExpr(' UNIQUE KEY ? (?)', new Symbol($index->getName()), $columns);
            }
        }
    }

    protected function appendForeignKeys() {
        foreach ($this->table->foreignKeys as $foreignKey) {
            $this->appendComma();
            $this->appendExpr(' CONSTRAINT ? FOREIGN KEY (?) REFERENCES ? (?)',
                new Symbol($foreignKey->getName()),
                $foreignKey->getChildColumns(),
                new Symbol($foreignKey->getReferencedTable()->schemaName),
                $foreignKey->getParentColumns()
            );
        }
    }

    public function appendPrimaryKey() {
        $this->appendComma();
        $this->appendExpr(' PRIMARY KEY (?)', array_values($this->table->primaryKey));
    }


    public function generate(Table $table) {
        if (!$this->database) {
            throw new Exception('Database is not set, please use `bindDatabase`', Exception::DATABASE_REQUIRED);
        }

        $this->table = $table;

        $this->appendExpr('CREATE TABLE ? (' . PHP_EOL, new Symbol($this->table->schemaName));
        $this->appendColumns();
        $this->appendIndexes();
        $this->appendForeignKeys();
        $this->appendPrimaryKey();

        $this->appendExpr(PHP_EOL . ')');
        return $this;
    }

}