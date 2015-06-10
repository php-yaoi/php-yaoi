<?php

namespace Yaoi\Entity\Database;

use Yaoi\BaseClass;
use Yaoi\Client\Exception;
use Yaoi\Database;
use Yaoi\Database\Contract;
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
     * @return Contract
     * @throws Exception
     */
    public function db()
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
            $this->tableDefinition = $this->db()->getTableDefinition($this->getTableName());
        }
        return $this->tableDefinition;
    }

}