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
     * @param $key
     * @param null $section
     * @return static
     * @deprecated
     */
    public function branch($key, $section = null) {
        /**
         * @var Mock_DataSetBase $mock
         */
        $mock = new static($this->storage);
        $branchKey = $this->branchKey;

        $mock->branchKey = $this->branchKey;
        if ($section) {
            $mock->branchKey []= $section;
        }
        $mock->branchKey []= $key;
        $mock->sequenceId = &$this->sequenceId;
        return $mock;
    }

    /**
     * @return static
     */
    public function branch2() {
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