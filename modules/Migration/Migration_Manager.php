<?php

class Migration_Manager extends Client {
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

    public function perform(Migration $migration, $action = Migration::APPLY) {
        if ($action === Migration::SKIP) {
            return $this;
        }

        if ($migration->isAppliedCallable !== null) {
            $f = $migration->isAppliedCallable;
            $isApplied = $f();
        }
        else {
            $isApplied = $this->isApplied($migration->id);
        }

        if (Migration::ROLLBACK === $action) {
            if ($isApplied && null !== $migration->rollbackCallable) {
                $f = $migration->rollbackCallable;
                $f();
                $this->getStorage()->delete($migration->id);
            }
        }
        elseif (Migration::APPLY === $action) {
            if (!$isApplied) {
                $f = $migration->applyCallable;
                $f();
                $this->getStorage()->set($migration->id, 1);
            }
        }

        return $this;
    }


    public function run() {
        if ($this->dsn->run instanceof Closure) {
            $f = $this->dsn->run;
            $f($this);
        }
        return $this;
    }

}