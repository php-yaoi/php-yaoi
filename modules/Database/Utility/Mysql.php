<?php

namespace Yaoi\Database\Utility;

use Sql_Symbol;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

class Mysql extends Utility
{
    public function killSleepers($timeout = 30)
    {
        foreach ($this->db->query("SHOW PROCESSLIST") as $row) {
            if ($row['Time'] > $timeout) {
                $this->db->query("KILL $row[Id]");
            }
        }
        return $this;
    }

    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        $res = $this->db->query("DESC ?", new Sql_Symbol($tableName));
        $definition = new Table();
        while ($row = $res->fetchRow()) {
            $type = $row['Type'];
            $phpType = Column::STRING;
            $field = $row['Field'];
            if ('PRI' === $row['Key']) {
                $definition->primaryKey [$field] = $field;
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
                    $phpType = Column::INTEGER;
                    break;

                case 'decimal' === substr($type, 0, 7):
                case 'double' === $type:
                case 'float' === $type:
                    $phpType = Column::FLOAT;
                    break;

                case 'date' === $type:
                case 'datetime' === $type:
                case 'timestamp' === $type:
                    $phpType = Column::TIMESTAMP;
                    break;
            }

            $definition->columns[$field] = $phpType;
        }
        return $definition;
    }
}