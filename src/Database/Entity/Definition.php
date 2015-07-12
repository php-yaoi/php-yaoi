<?php

namespace Yaoi\Database\Entity;

use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Database\Definition\Table;

class Definition extends BaseClass
{
    public $className;
    public $tableName;
    private $db;
    /**
     * @var Table
     */
    private $tableDefinition;

    public function bindDatabase(Database $db = null)
    {
        $this->db = $db;
        $this->tableDefinition = null;
    }

    /**
     * @return \Yaoi\Database\Contract;
     * @throws \Yaoi\Service\Exception
     */
    public function database()
    {
        if (null === $this->db) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }

    public function getTableName()
    {
        if (null === $this->tableName) {
            $this->tableName = $this->className;
        }
        return $this->tableName;
    }

    /**
     * @return Table
     */
    public function getTableDefinition()
    {
        if (null === $this->tableDefinition) {
            $this->tableDefinition = $this->database()->getTableDefinition($this->getTableName());
        }
        return $this->tableDefinition;
    }

}