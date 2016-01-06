<?php

namespace Yaoi\Database\Entity;

use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;
use Yaoi\Log;
use Yaoi\Migration\AbstractMigration;
use Yaoi\String\Expression;

class Migration extends AbstractMigration
{

    /** @var Table  */
    private $table;

    private static $applied = array();
    private static $rolledBack = array();

    public function __construct(Table $table) {
        $this->id = null;
        $this->table = $table;

    }

    private function checkRun()
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


    /**
     * @return bool
     */
    public function apply()
    {
        if (isset(self::$applied[$this->table->entityClassName])) {
            if ($this->log) {
                $this->log->push(Expression::create(
                    'Migration for table ? (?) already applied, skipping',
                    $this->table->schemaName,
                    $this->table->entityClassName
                ));
            }
            return true;
        }

        $database = $this->table->database();
        $statement = $this->checkRun();
        $requires = (string)$statement;
        if ($this->log) {
            $this->log->push(
                Expression::create('Apply, table ? (?) ?',
                    $this->table->schemaName,
                    $this->table->entityClassName,
                    $requires ? 'requires migration' : 'is up to date'
                )

            );
        }

        if (!$requires) {
            self::$applied[$this->table->entityClassName] = true;
            if (isset(self::$rolledBack[$this->table->entityClassName])) {
                unset(self::$rolledBack[$this->table->entityClassName]);
            }
            return false;
        }

        foreach ($this->table->foreignKeys as $foreignKey) {
            $referenceMigration = $foreignKey->getReferencedTable()->migration();
            $referenceMigration->dryRun = $this->dryRun;
            $referenceMigration->log = $this->log;

            if ($this->log) {
                $this->log->push('Dependent migration required');
            }

            $referenceMigration->apply();
        }

        if ($this->log) {
            $this->log->push($statement->build());
        }

        if (!$this->dryRun) {
            try {
                $database->query($statement);
                self::$applied[$this->table->entityClassName] = true;
                if (isset(self::$rolledBack[$this->table->entityClassName])) {
                    unset(self::$rolledBack[$this->table->entityClassName]);
                }
                if ($this->log) {
                    $this->log->push('OK', Log::TYPE_SUCCESS);
                }
            }
            catch (Exception $exception) {
                if ($this->log) {
                    $this->log->push($exception->getMessage(), Log::TYPE_ERROR);
                }
                return false;
            }
        }


        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function rollback()
    {
        if (isset(self::$rolledBack[$this->table->entityClassName])) {
            if ($this->log) {
                $this->log->push(Expression::create(
                    'Migration for table ? (?) already rolled back, skipping',
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
                Expression::create('Rollback, table ? (?) ?',
                    $this->table->schemaName,
                    $this->table->entityClassName,
                    $requires ? 'requires deletion' : 'is already non-existent'
                )

            );
        }

        if (!$requires) {
            self::$rolledBack[$this->table->entityClassName] = true;
            if (isset(self::$applied[$this->table->entityClassName])) {
                unset(self::$applied[$this->table->entityClassName]);
            }
            return false;
        }


        foreach ($this->table->dependentTables as $dependentTable) {
            $referenceMigration = $dependentTable->migration();
            $referenceMigration->dryRun = $this->dryRun;
            $referenceMigration->log = $this->log;

            if ($this->log) {
                $this->log->push('Dependent migration required');
            }

            $referenceMigration->rollback();
        }


        if (!$this->dryRun) {
            try {
                $utility->dropTable($this->table->schemaName);
                self::$rolledBack[$this->table->entityClassName] = true;
                if (isset(self::$applied[$this->table->entityClassName])) {
                    unset(self::$applied[$this->table->entityClassName]);
                }

                if ($this->log) {
                    $this->log->push('OK', Log::TYPE_SUCCESS);
                }
            }
            catch (Exception $exception) {
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