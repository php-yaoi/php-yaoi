<?php
namespace Yaoi\Migration;


use Yaoi\Log;

interface Migration
{
    const APPLY = 'apply';
    const ROLLBACK = 'rollback';
    const SKIP = 'skip';

    /**
     * @return bool
     */
    public function apply();

    /**
     * @return bool
     */
    public function rollback();

    /**
     * @return bool
     */
    public function hasInternalState();

    public function getId();

    /**
     * @param Log|null $log
     * @return $this
     */
    public function setLog(Log $log = null);

    /**
     * @param bool $yes
     * @return $this
     */
    public function setDryRun($yes = true);
}