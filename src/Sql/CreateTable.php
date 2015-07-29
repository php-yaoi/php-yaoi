<?php

namespace Yaoi\Sql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;

class CreateTable extends Expression
{
    /** @var  Table */
    protected $table;

    protected function appendColumns() {
        $utility = $this->database->getUtility();

        foreach ($this->table->getColumns(true) as $column) {
            $this->appendExpr(' ? ' . $utility->getColumnTypeString($column), new Symbol($column->schemaName));

            if ($column->flags & Column::AUTO_ID) {
                $this->appendExpr(' AUTO_INCREMENT');
            }

            $this->appendExpr(',' . PHP_EOL);
        }
    }

    protected function appendIndexes() {
        foreach ($this->table->indexes as $index) {
            $columns = array();
            foreach ($index->columns as $column) {
                $columns []= new Symbol($column->schemaName);
            }

            if ($index->type === Index::TYPE_KEY) {
                $this->appendExpr(' KEY ? (?),' . PHP_EOL, new Symbol($index->getName()), $columns);
            }
            elseif ($index->type === Index::TYPE_UNIQUE) {
                $this->appendExpr(' UNIQUE KEY ? (?),' . PHP_EOL, new Symbol($index->getName()), $columns);
            }
        }
    }

    protected function appendForeignKeys() {
        foreach ($this->table->foreignKeys as $foreignKey) {
            $this->appendExpr(' CONSTRAINT ? FOREIGN KEY (?) REFERENCES ? (?),' . PHP_EOL,
                new Symbol($foreignKey->getName()),
                $foreignKey->getChildColumns(),
                new Symbol($foreignKey->getReferencedTable()->schemaName),
                $foreignKey->getParentColumns()
            );
        }
    }

    public function appendPrimaryKey() {
        $this->appendExpr(' PRIMARY KEY (?)' . PHP_EOL, array_values($this->table->primaryKey));
    }


    public function setTable(Table $table) {
        $this->table = $table;

        $this->appendExpr('CREATE TABLE ? (' . PHP_EOL, new Symbol($this->table->schemaName));
        $this->appendColumns();
        $this->appendIndexes();
        $this->appendForeignKeys();
        $this->appendPrimaryKey();

        $this->appendExpr(')' . PHP_EOL);
        return $this;
    }

}