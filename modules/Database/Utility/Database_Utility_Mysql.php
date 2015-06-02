<?php

class Database_Utility_Mysql extends Database_Utility {
    public function killSleepers($timeout = 30) {
        foreach ($this->db->query("SHOW PROCESSLIST") as $row) {
            if ($row['Time'] > $timeout) {
                $this->db->query("KILL $row[Id]");
            }
        }
        return $this;
    }

    /**
     * @param $tableName
     * @return Database_Definition_Table
     */
    public function getTableDefinition($tableName)
    {
        $res = $this->db->query("DESC ?", new Sql_Symbol($tableName));
        $definition = new Database_Definition_Table();
        while ($row = $res->fetchRow()) {
            $type = $row['Type'];
            $phpType = Database::COLUMN_TYPE_STRING;
            $field = $row['Field'];
            if ('PRI' === $row['Key']) {
                $definition->primaryKey [$field]= $field;
            }
            if ('auto_increment' === $row['Extra']) {
                $definition->autoIncrement = $field;
            }
            $definition->defaults[$field] = $row['Default'];
            $definition->notNull[$field] = $row['Null'] === 'NO';
            switch (true) {
                case 'bigint' === substr($type, 0, 6):
                case 'int' === substr($type, 0, 3):
                case 'mediumint' === substr($type, 0, 9):
                case 'smallint' === substr($type, 0, 8):
                case 'tinyint' === substr($type, 0, 7):
                    $phpType = Database::COLUMN_TYPE_INTEGER;
                    break;

                case 'decimal' === substr($type, 0, 7):
                case 'double' === $type:
                case 'float' === $type:
                    $phpType = Database::COLUMN_TYPE_FLOAT;
                    break;

                case 'date' === $type:
                case 'datetime' === $type:
                case 'timestamp' === $type:
                    $phpType = Database::COLUMN_TYPE_TIMESTAMP;
                    break;
            }

            $definition->columns[$field] = $phpType;
        }
        return $definition;
    }
}