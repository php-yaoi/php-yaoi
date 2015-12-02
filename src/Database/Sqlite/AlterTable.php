<?php

namespace Yaoi\Database\Sqlite;


use Yaoi\Database\Definition\Table;
use Yaoi\Sql\Batch;
use Yaoi\Sql\Symbol;

class AlterTable extends \Yaoi\Sql\AlterTable
{
    protected function processColumns() {
        $this->alterLines->disable();

        $intersect = array();
        //$changed = false;

        $changed = (string)$this->before->getCreateTable() !== (string)$this->after->getCreateTable();


        $beforeColumns = $this->before->getColumns(true, true);
        foreach ($this->after->getColumns(true, true) as $columnName => $afterColumn) {
            $afterTypeString = $afterColumn->getTypeString();

            if (!isset($beforeColumns[$columnName])) {
                $changed = true;
                $this->alterLines->commaExpr('ADD COLUMN ? ' . $afterTypeString, new Symbol($afterColumn->schemaName));
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

            $this->add($this->database()->expr(
                "ALTER TABLE ? RENAME TO _temp_table",
                new Symbol($this->before->schemaName)
            ));

            $this->add($this->after->getCreateTable());
            $this->add($this->database()->expr(
                "INSERT INTO ? (?) SELECT ? FROM _temp_table",
                new Symbol($this->after->schemaName), $intersect, $intersect)
            );
            $this->add($this->database()->expr("DROP TABLE _temp_table"));
        }

    }


    protected function processIndexes() {

    }
}