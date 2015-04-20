<?php

class Migration_Client extends Client {
    public static $conf = array();
    /**
     * @var Migration_Dsn
     */
    protected $dsn;


    public function isApplied($migrationId) {
        return (bool)$this->getStorage()->get($migrationId);
    }

    /**
     * @var Storage
     */
    private $storage;
    protected function getStorage() {
        if (null === $this->storage) {
            $this->storage = Storage::getInstance($this->dsn->storage);
        }
        return $this->storage;
    }

    public function applyProvider(Migration_Required $provider) {
        return $this->applyMigration($provider->getMigration());
    }

    public function rollbackProvider(Migration_Required $provider) {
        return $this->rollbackMigration($provider->getMigration());
    }

    public function skipProvider(Migration_Required $provider) {
        return $this;
    }


    public function applyMigration(Migration $migration) {
        if ($migration instanceof Migration_IsApplied) {
            $isApplied = $migration->isApplied($this);
        }
        else {
            $isApplied = $this->isApplied($migration->getId());
        }
        if (!$isApplied) {
            $migration->apply();
            $this->getStorage()->set($migration->getId(), 1);
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
        if ($migration instanceof Migration_IsApplied) {
            $isApplied = $migration->isApplied($this);
        }
        else {
            $isApplied = $this->isApplied($migration->getId());
        }
        if ($isApplied) {
            $migration->rollback();
            $this->getStorage()->set($migration->getId(), null);
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