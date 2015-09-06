<?php

namespace Yaoi\Migration;
use Closure;
use Yaoi\Log;

class ClosureMigration extends AbstractMigration
{
    /**
     * @var Closure
     */
    public $applyCallable;

    public function apply() {
        $closure = $this->applyCallable;
        $result = $closure($this);
        if (null === $result) {
            $result = true;
        }
        return $result;
    }

    /**
     * @var Closure
     */
    public $rollbackCallable;

    public function __construct($id, Closure $apply, Closure $rollback = null, $hasInternalState = false)
    {
        $this->id = $id;
        $this->applyCallable = $apply;
        $this->rollbackCallable = $rollback;
        $this->hasInternalState = $hasInternalState;
        $this->log = Log::void();
    }

    /**
     * @return bool
     */
    public function rollback()
    {
        if (null !== $this->rollbackCallable) {
            $closure = $this->rollbackCallable;
            $result = $closure($this);
            if (null === $result) {
                $result = true;
            }
            return $result;
        }
        else {
            return false;
        }
    }

    private $hasInternalState;
    /**
     * @return bool
     */
    public function hasInternalState()
    {
        return $this->hasInternalState;
    }


}