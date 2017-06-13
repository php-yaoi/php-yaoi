<?php

namespace Yaoi\Database\Entity;

use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;
use Yaoi\Log;
use Yaoi\Migration\AbstractMigration;
use Yaoi\String\Expression;

class Migration extends AbstractMigration
{

    /** @var Table */
    private $table;

    // @todo extract these statics to some managing object
    private static $applied = array();
    private static $rolledBack = array();
    public static $enableStateCache = true;
    public static $dependenciesRollback = array();
    public static $dependenciesApply = array();


    public static function dropStateCache()
    {
        self::$applied = array();
        self::$rolledBack = array();
    }

    public function __construct(Table $table)
    {
        $this->id = null;
        $this->table = $table;
    }

    /**
     * @return \Yaoi\Sql\AlterTable|\Yaoi\Sql\CreateTable
     */
    private function checkRun($skipForeignKeys = false)
    {
        $database = $this->table->database();
        $utility = $database->getUtility();

        $tableExists = $utility->tableExists($this->table->schemaName);
        if (!$tableExists) {
            $statement = $this->table->getCreateTable();
        } else {
            $statement = $this->table->getAlterTableFrom(
                $utility->getTableDefinition($this->table->schemaName)
            );
        }

        return $statement;
    }


    private function runStatement(\Yaoi\Sql\Expression $statement)
    {
        $query = $statement->build();
        if (!$query) {
            return;
        }
        if ($this->log) {
            $this->log->push(trim($query, ";\n") . ';');
        }
        if (!$this->dryRun) {
            $this->table->database()->query($statement);
        }

    }

    private function applyRequired(\Yaoi\Sql\Expression $statement)
    {
        $requires = (string)$statement;
        if ($this->log) {
            $this->log->push(
                Expression::create('# Apply, table ? (?) ?',
                    $this->table->schemaName,
                    $this->table->entityClassName,
                    $requires ? 'requires migration' : 'is up to date'
                )

            );
        }
        if (!$requires) {
            self::setApplied($this->table->entityClassName);
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function apply()
    {
        if (self::$enableStateCache && isset(self::$applied[$this->table->entityClassName])) {
            if ($this->log) {
                $this->log->push(Expression::create(
                    '# Migration for table ? (?) already applied, skipping',
                    $this->table->schemaName,
                    $this->table->entityClassName
                ));
            }
            return true;
        }

        /** @var Migration[] $dependentMigrations */
        $dependentMigrations = array();
        foreach ($this->table->getForeignKeys() as $foreignKey) {
            $referenceMigration = $foreignKey->getReferencedTable()->migration();
            $referenceMigration->dryRun = $this->dryRun;
            $referenceMigration->log = $this->log;
            $dependentMigrations[$referenceMigration->table->schemaName] = $referenceMigration;
        }


        try {
            $statement = $this->checkRun();

            if ($dependentMigrations) {

                $fkStatement = $statement->extractForeignKeysStatement();

                if (!$this->applyRequired($statement)) {
                    return false;
                }

                $this->runStatement($statement);
                self::setApplied($this->table->entityClassName);
                if ($this->log) {
                    $this->log->push('# Dependent tables found: ' . implode(', ', array_keys($dependentMigrations)));
                }
                foreach ($dependentMigrations as $migration) {
                    $migration->apply();
                }
                $this->runStatement($fkStatement);
            } else {
                if (!$this->applyRequired($statement)) {
                    return false;
                }
                $this->runStatement($statement);
            }

            if ($this->log) {
                $this->log->push('# OK', Log::TYPE_SUCCESS);
            }
        } catch (Exception $exception) {
            if ($this->log) {
                $this->log->push($exception->getMessage(), Log::TYPE_ERROR);
            }
            return false;
        }


        return true;
    }


    private static function setApplied($name)
    {
        self::$applied[$name] = true;
        if (isset(self::$rolledBack[$name])) {
            unset(self::$rolledBack[$name]);
        }
    }

    private static function setRolledBack($name)
    {
        self::$rolledBack[$name] = true;
        if (isset(self::$applied[$name])) {
            unset(self::$applied[$name]);
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function rollback()
    {
        if (self::$enableStateCache && isset(self::$rolledBack[$this->table->entityClassName])) {
            if ($this->log) {
                $this->log->push(Expression::create(
                    '# Migration for table ? (?) already rolled back, skipping',
                    $this->table->schemaName,
                    $this->table->entityClassName
                ));
            }
            return true;
        }


        $utility = $this->table->database()->getUtility();
        $tableExists = $utility->tableExists($this->table->schemaName);

        $requires = $tableExists;
        if ($this->log) {
            $this->log->push(
                Expression::create('# Rollback, table ? (?) ?',
                    $this->table->schemaName,
                    $this->table->entityClassName,
                    $requires ? 'requires deletion' : 'is already non-existent'
                )

            );
        }

        if (!$requires) {
            self::setRolledBack($this->table->entityClassName);
            return false;
        }


        /** @var Migration[] $dependentMigrations */
        $dependentMigrations = array();
        foreach ($this->table->dependentTables as $dependentTable) {
            $referenceMigration = $dependentTable->migration();
            $referenceMigration->dryRun = $this->dryRun;
            $referenceMigration->log = $this->log;
            $dependentMigrations[$referenceMigration->table->schemaName] = $referenceMigration;
        }


        if (!$this->dryRun) {
            try {
                if ($dependentMigrations) {
                    $dropFk = $utility->generateDropForeignKeys($this->table->schemaName);
                    $this->runStatement($dropFk);
                    self::setRolledBack($this->table->entityClassName);
                    if ($this->log) {
                        $this->log->push('# Dependent tables found: ' . implode(', ', array_keys($dependentMigrations)));
                    }
                    foreach ($dependentMigrations as $migration) {
                        $migration->rollback();
                    }
                    $this->runStatement($utility->generateDropTable($this->table->schemaName));
                } else {
                    $this->runStatement($utility->generateDropTable($this->table->schemaName));
                    self::setRolledBack($this->table->entityClassName);
                }

                if ($this->log) {
                    $this->log->push('# OK', Log::TYPE_SUCCESS);
                }
            } catch (Exception $exception) {
                if ($this->log) {
                    $this->log->push($exception->getMessage(), Log::TYPE_ERROR);
                }
                throw $exception;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function hasInternalState()
    {
        return true;
    }


}