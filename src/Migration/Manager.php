<?php

namespace Yaoi\Migration;

use Yaoi\Log;
use Yaoi\Storage;
use Yaoi\Service;
use Yaoi\Migration;

class Manager extends Service
{
    /**
     * @var Settings
     */
    protected $settings;


    public function isApplied($migrationId)
    {
        return (bool)$this->getStorage()->get($migrationId);
    }

    /**
     * @var Storage
     */
    private $storage;

    protected function getStorage()
    {
        if (null === $this->storage) {
            $this->storage = Storage::getInstance($this->settings->storage);
        }
        return $this->storage;
    }

    public function perform(Migration $migration, $action = Migration::APPLY)
    {
        if ($action === Migration::SKIP) {
            return $this;
        }

        if ($migration->isAppliedCallable !== null) {
            $f = $migration->isAppliedCallable;
            $isApplied = $f($migration);
        } else {
            $isApplied = $this->isApplied($migration->id);
        }

        if (Migration::ROLLBACK === $action) {
            if ($isApplied && null !== $migration->rollbackCallable) {
                $f = $migration->rollbackCallable;
                $f($migration);
                if (null !== $migration->id) {
                    $this->getStorage()->delete($migration->id);
                }
            }
        } elseif (Migration::APPLY === $action) {
            if (!$isApplied) {
                $f = $migration->applyCallable;
                $f($migration);
                if (null !== $migration->id) {
                    $this->getStorage()->set($migration->id, 1);
                }
            }
        }

        return $this;
    }


    private $jobs = array();

    /**
     * @param Migration[]|Migration $migrations
     * @param string $action
     * @return $this
     */
    public function add($migrations, $action = Migration::APPLY) {
        if ($migrations instanceof Migration) {
            $migrations = array($migrations);
        }

        foreach ($migrations as $migration) {
            $this->jobs []= array($migration, $action);
        }
        return $this;
    }

    /** @var  Log */
    private $log;
    public function setLog(Log $log = null) {
        $this->log = $log;
        return $this;
    }

    public function run($dryRun = false)
    {
        foreach ($this->jobs as $job) {
            /** @var Migration $migration */
            $migration = $job[0];
            $action = $job[1];
            if ($this->log) {
                $migration->log = $this->log;
            }
            $migration->dryRun = $dryRun;
            $this->perform($migration, $action);
        }
        return $this;
    }

    protected static function getSettingsClassName()
    {
        return Settings::className();
    }

}