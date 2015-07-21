<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

class Sqlite extends Utility
{
    public function getTableDefinition($tableName)
    {
        $res = $this->database->query("PRAGMA table_info($tableName)");

        /** @var Column[] $primaryKey */
        $primaryKey = array();
        $columns = new \stdClass();
        while ($r = $res->fetchRow()) {
            $field = $r['name'];

            $column = new Column(Column::AUTO_TYPE);
            $column->setFlag(Column::NOT_NULL, (bool)$r['notnull']);
            $column->setDefault($r['dflt_value']);

            if ($r['pk']) {
                $primaryKey []= $column;
            }

            $columns->$field = $column;
        }
        if (count($primaryKey) === 1) {
            $primaryKey[0]->setFlag(Column::AUTO_ID);
        }

        $definition = new Table($columns);


        return $definition;
    }


    public function generateCreateTableOnDefinition(Table $table)
    {
        throw new \Exception('Not implemented');
        // TODO: Implement generateCreateTableOnDefinition() method.
    }

    public function getColumnTypeString(Column $column)
    {
        throw new \Exception('Not implemented');
        // TODO: Implement getColumnTypeString() method.
    }



}