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
    private $rollbackCallable;

    public function __construct($id, callable $apply, callable $rollback = null) {
        $this->id = $id;
        $this->applyCallable = $apply;
        $this->rollbackCallable = $rollback;
    }

    public function getId()
    {
        return $this->id;
    }

    public function apply()
    {
        $f = $this->applyCallable;
        $f($this);
    }

    public function rollback()
    {
        $f = $this->rollbackCallable;
        $f($this);
    }


}