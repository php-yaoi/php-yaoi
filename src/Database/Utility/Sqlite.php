<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

class Sqlite extends Utility
{
    public function getTableDefinition($tableName)
    {
        $definition = new Table();
        $res = $this->database->query("PRAGMA table_info($tableName)");

        while ($r = $res->fetchRow()) {
            $field = $r['name'];
            if ($r['pk']) {
                $definition->primaryKey [$field] = $field;
            }
            $definition->defaults[$field] = $r['dflt_value'];
            $definition->notNull[$field] = (bool)$r['notnull'];
            $definition->columns[$field] = Column::AUTO_TYPE;
        }
        if (count($definition->primaryKey) === 1) {
            $definition->autoIncrement = reset($definition->primaryKey);
        }

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