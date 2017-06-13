<?php

namespace Yaoi\Database;

use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Database\Definition\ForeignKey;
use Yaoi\Database\Utility\Contract as UtilityContract;
use Yaoi\Sql\Raw;
use Yaoi\Sql\SimpleExpression;
use Yaoi\Sql\Symbol;

abstract class Utility extends BaseClass implements UtilityContract
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @param Database $database
     * @return $this
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
        return $this;
    }


    public function dropTableIfExists($tableName)
    {
        $this->database->query("DROP TABLE IF EXISTS ?", new Symbol($tableName));
    }

    public function dropTable($tableName)
    {
        $this->database->query($this->generateDropTable($tableName));
    }

    public function generateDropTable($tableName)
    {
        return SimpleExpression::create("DROP TABLE ?", new Symbol($tableName))->bindDatabase($this->database);
    }

    /**
     * @param $tableName
     * @return \Yaoi\Sql\AlterTable
     */
    public function generateDropForeignKeys($tableName)
    {
        $before = $this->getTableDefinition($tableName);
        $after = clone $before;
        $after->removeForeignKeys();
        return $this->generateAlterTable($before, $after);
    }


    public function generateForeignKeyExpression(Database\Definition\ForeignKey $foreignKey) {
        return new SimpleExpression(' CONSTRAINT ? FOREIGN KEY (?) REFERENCES ? (?)??',
            new Symbol($foreignKey->getName()),
            Symbol::prepareColumns($foreignKey->getLocalColumns()),
            new Symbol($foreignKey->getReferencedTable()->schemaName),
            Symbol::prepareColumns($foreignKey->getReferenceColumns()),
            new Raw($foreignKey->onUpdate === ForeignKey::NO_ACTION ? '' : ' ON UPDATE ' . $foreignKey->onUpdate),
            new Raw($foreignKey->onDelete === ForeignKey::NO_ACTION ? '' : ' ON DELETE ' . $foreignKey->onDelete)
        );

    }


}
