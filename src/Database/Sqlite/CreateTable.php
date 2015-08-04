<?php

namespace Yaoi\Database\Sqlite;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Exception;
use Yaoi\Sql\Batch;
use Yaoi\Sql\Expression;
use Yaoi\Sql\Symbol;

class CreateTable extends \Yaoi\Sql\CreateTable
{

    protected function appendColumns() {
        $utility = $this->database->getUtility();

        foreach ($this->table->getColumns(true) as $column) {
            $this->appendComma();
            if ($column->flags & Column::AUTO_ID) {
                $this->appendExpr(' ? INTEGER PRIMARY KEY AUTOINCREMENT', new Symbol($column->schemaName));
                if (array_values($this->table->primaryKey) !== array($column)) {
                    throw new Exception("Auto ID conflicts PRIMARY KEY", Exception::INVALID_SCHEMA);
                }
                else {
                    $this->skipPrimary = true;
                }
            }
            else {
                $this->appendExpr(' ? ' . $utility->getColumnTypeString($column), new Symbol($column->schemaName));
            }
        }
    }

    private $skipPrimary;
    public function appendPrimaryKey() {
        if (!$this->skipPrimary) {
            $this->appendComma();
            $this->appendExpr(' PRIMARY KEY (?)' . PHP_EOL, array_values($this->table->primaryKey));
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
                $createIndex = new Expression(
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
                $createIndex = new Expression(
                    "CREATE UNIQUE INDEX ? ON ? (?)",
                    new Symbol($index->getName()),
                    new Symbol($this->table->schemaName),
                    $columns
                );
                $createIndex->bindDatabase($this->database);
                $this->batch->add($createIndex);
                //$this->appendExpr(' CONSTRAINT ? UNIQUE (?),' . PHP_EOL, new Symbol($index->getName()), $columns);
            }
        }
    }


    /** @var  Batch */
    public $batch;


}