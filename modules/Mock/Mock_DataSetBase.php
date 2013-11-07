<?php

class Mock_DataSetBase implements Mock_DataSet {
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
     */
    public function branch($key, $section = null) {
        /**
         * @var Mock_DataSetBase $mock
         */
        $mock = new static($this->storage);
        $mock->branchKey = $this->branchKey;
        if ($section) {
            $mock->branchKey []= $section;
        }
        $mock->branchKey []= $key;
        return $mock;
    }

} 