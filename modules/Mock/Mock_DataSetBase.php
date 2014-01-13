<?php

class Mock_DataSetBase implements Mock_DataSet {
    protected $sequenceId = 0;
    /**
     * @var Storage_Client
     */
    protected $branches;

    /**
     * @var Storage_Client
     */
    protected $storage;

    protected $branchKey = array();

    public function __construct(Storage_Client $storage) {
        $this->storage = $storage;
    }

    /**
     * @return static
     */
    public function branch() {
        $key = func_get_args();
        if (!$key) {
            return $this;
        }

        if (null === $this->branches) {
            $this->branches = new Storage_Var();
        }

        if (!$mock = $this->branches->get($key)) {
            $mock = new static($this->storage);
            $mock->branchKey = array_merge($this->branchKey, $key);
            $this->branches->set($key, $mock);
        }

        return $mock;
    }

} 