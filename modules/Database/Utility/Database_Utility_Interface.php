<?php

interface Database_Utility_Interface {
    public function setDatabase(Database_Interface $db);

    /**
     * @param $tableName
     * @return Database_Definition_Table
     */
    public function getTableDefinition($tableName);
}