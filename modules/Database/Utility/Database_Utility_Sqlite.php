<?php

class Database_Utility_Sqlite extends Database_Utility {
    public function getTableDefinition($tableName)
    {
        $definition = new Database_Definition_Table();
        $res = $this->db->query("PRAGMA table_info($tableName)");

        while ($r = $res->fetchRow()) {
            $field = $r['name'];
            if ($r['pk']) {
                $definition->primaryKey [$field]= $field;
            }
            $definition->defaults[$field] = $r['dflt_value'];
            $definition->notNull[$field] = (bool)$r['notnull'];
            $definition->columns[$field] = Database_Definition_Column::AUTO_TYPE;
        }
        if (count($definition->primaryKey) === 1) {
            $definition->autoIncrement = reset($definition->primaryKey);
        }

        return $definition;
    }

}