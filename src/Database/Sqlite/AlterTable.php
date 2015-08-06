<?php

namespace Yaoi\Database\Sqlite;


use Yaoi\Database\Definition\Table;
use Yaoi\Sql\Batch;
use Yaoi\Sql\Symbol;

class AlterTable extends \Yaoi\Sql\AlterTable
{
    public $batch;

    public function __construct() {
        $this->batch = new Batch();
    }

    protected function processColumns() {
        $intersect = array();
        $changed = false;

        $beforeColumns = $this->before->getColumns(true, true);
        foreach ($this->after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $afterColumn->getTypeString();

            if (!isset($beforeColumns[$columnName])) {
                $changed = true;
                $this->lines []= $this->database->expr('ADD COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
            }
            else {
                $beforeColumn = $beforeColumns[$columnName];
                $intersect []= new Symbol($beforeColumn->schemaName);
                if ($beforeColumn->getTypeString() !== $afterTypeString) {
                    $changed = true;
                }
            }
        }

        if ($changed) {
            /**
             * ALTER TABLE {tableName} RENAME TO TempOldTable;

            Then create the new table with the missing column:

            CREATE TABLE {tableName} (name TEXT, COLNew {type} DEFAULT {defaultValue}, qty INTEGER, rate REAL);

            And populate it with the old data:

            INSERT INTO {tableName} (name, qty, rate) SELECT name, qty, rate FROM TempOldTable;

            Then delete the old table:

            DROP TABLE TempOldTable;

             */

            $this->batch->add($this->database->expr(
                "ALTER TABLE ? RENAME TO _temp_table",
                new Symbol($this->before->schemaName)
            ));

            $this->batch->add($this->after->getCreateTable());
            $this->batch->add($this->database->expr(
                "INSERT INTO ? (?) SELECT ? FROM _temp_table",
                new Symbol($this->after->schemaName), $intersect, $intersect)
            );
            $this->batch->add($this->database->expr("DROP TABLE _temp_table"));
        }

    }


    protected function processIndexes() {

    }
}