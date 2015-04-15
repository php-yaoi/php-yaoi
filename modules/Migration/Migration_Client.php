<?php

class Migration_Client extends Client {
    public static $conf = array();
    /**
     * @var Migration_Dsn
     */
    protected $dsn;

    private $defaultIsApplied;
    public function __construct() {
        $storageDsn = $this->dsn->storage;
        $this->defaultIsApplied = function (Migration $migration) use ($storageDsn) {
            $storage = Storage::getInstance($storageDsn);
            $id = $migration->getId();
            if ($storage->get($id)) {
                return true;
            }
            else {
                return false;
            }
        };
    }

    public function addMigration(Migration $migration) {
        if (!$migration->isApplied()) {
            $migration->apply();
        }
        return $this;
    }

    public function add($id, callable $apply, callable $isApplied = null, callable $rollback = null) {
        if (null === $isApplied) {
            $isApplied = function () {
                return false;
            };
        }

        $m = $rollback
            ? new Migration_GenericRollback($id, $apply, $isApplied, $rollback)
            : new Migration_Generic($id, $apply, $isApplied);
        return $this->addMigration($m);
    }

    public function remove($id, callable $apply, callable $isApplied = null, callable $rollback = null) {
        if (null === $isApplied) {
            $isApplied = function () {
                return false;
            };
        }

        $m = $rollback
            ? new Migration_GenericRollback($id, $apply, $isApplied, $rollback)
            : new Migration_Generic($id, $apply, $isApplied);
        return $this->removeMigration($m);
    }

    public function removeMigration(Migration_Rollback $migration) {
        if (!$migration->isApplied()) {
            $migration->rollback();
        }
        return $this;
    }

    public function skip(Migration $migration) {
        return $this;
    }

}