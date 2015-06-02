<?php

class Entity_Database_Definition extends Base_Class {
    public $className;
    public $tableName;
    private $db;
    /**
     * @var Database_Definition_Table
     */
    private $tableDefinition;

    public function bindDatabase(Database $db = null) {
        $this->db = $db;
        $this->tableDefinition = null;
    }

    /**
     * @return Database_Interface
     * @throws Client_Exception
     */
    public function db() {
        if (null === $this->db) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }

    public function getTableName() {
        if (null === $this->tableName) {
            $this->tableName = $this->className;
        }
        return $this->tableName;
    }

    /**
     * @return Database_Definition_Table
     */
    public function getTableDefinition() {
        if (null === $this->tableDefinition) {
            $this->tableDefinition = $this->db()->getTableDefinition($this->getTableName());
        }
        return $this->tableDefinition;
    }

}