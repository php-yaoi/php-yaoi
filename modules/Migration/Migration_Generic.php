<?php

class Migration_Generic implements Migration {
    private $id;
    /**
     * @var callable
     */
    private $applyCallable;
    /**
     * @var callable
     */
    private $isAppliedCallable;
    /**
     * @var callable
     */
    private $rollbackCallable;

    public function __construct($id, callable $apply, callable $isApplied, callable $rollback = null) {
        $this->$id = $id;
        $this->applyCallable = $apply;
        $this->isAppliedCallable = $isApplied;
        $this->rollbackCallable = $rollback;
    }

    public function getId()
    {
        return $this->getId();
    }

    public function apply()
    {
        $f = $this->applyCallable;
        $f();
    }

    public function isApplied()
    {
        $f = $this->isAppliedCallable;
        return $f();
    }

    public function rollback()
    {
        $f = $this->rollbackCallable;
        $f();
    }


}