<?php

class Mock_DataSetCapture implements Mock_DataSet{
    /**
     * @var Storage_Client
     */
    protected $storage;

    public function __construct(Storage_Client $data) {
        $this->storage = $data;
    }


    public function add($key, $value, $section = null) {
        $this->storage->set(array($section, $key), $value);
    }

    /**
     * @param $key
     * @param null $section
     * @return static
     */
    public function branch($key, $section = null) {
        $mock = new static($this->storage);
        $this->add($key, $mock, $section);
        return $mock;
    }


    protected $temp = array();

    /**
     * @param $key
     * @param null $value
     * @return mixed|null
     */
    public function temp($key, $value = null) {
        if (null === $value) {
            if (isset($this->temp[$key])) {
                return $this->temp[$key];
            }
            else {
                return null;
            }
        }
        else {
            $this->temp[$key] = $value;
            return $value;
        }
    }
}