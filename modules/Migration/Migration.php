<?php

class Migration {
    const APPLY = 'apply';
    const ROLLBACK = 'rollback';
    const SKIP = 'skip';

    public $id;
    /**
     * @var Closure
     */
    public $applyCallable;
    /**
     * @var Closure
     */
    public $rollbackCallable;

    /**
     * @var Closure
     */
    public $isAppliedCallable;

    public function __construct($id, Closure $apply, Closure $rollback = null, Closure $isApplied = null) {
        $this->id = $id;
        $this->applyCallable = $apply;
        $this->rollbackCallable = $rollback;
        $this->isAppliedCallable = $isApplied;
    }

}