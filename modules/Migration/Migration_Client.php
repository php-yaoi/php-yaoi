<?php

class Migration_Client extends Client {
    public static $conf = array();
    /**
     * @var Migration_Dsn
     */
    protected $dsn;


    public function isApplied($migrationId) {
        $storage = Storage::getInstance($this->dsn->storage);
        if ($storage->get($migrationId)) {
            return true;
        }
        else {
            return false;
        }
    }

    public function applyMigration(Migration $migration) {
        if (!$this->isApplied($migration->getId())) {
            $migration->apply();
            Storage::getInstance($this->dsn->storage)->set($migration->getId(), 1);
        }
        return $this;
    }

    public function apply($id, callable $apply, callable $rollback = null) {
        $m = $rollback
            ? new Migration_GenericRollback($id, $apply, $rollback)
            : new Migration_Generic($id, $apply);
        return $this->applyMigration($m);
    }

    public function rollback($id, callable $apply, callable $rollback = null) {
        $m = $rollback
            ? new Migration_GenericRollback($id, $apply, $rollback)
            : new Migration_Generic($id, $apply);
        return $this->rollbackMigration($m);
    }

    public function rollbackMigration(Migration_Rollback $migration) {
        if ($this->isApplied($migration->getId())) {
            $migration->rollback();
        }
        return $this;
    }

    public function skip($id, callable $apply, callable $rollback = null) {
        return $this;
    }

    public function skipMigration(Migration_Rollback $migration) {
        return $this;
    }

}