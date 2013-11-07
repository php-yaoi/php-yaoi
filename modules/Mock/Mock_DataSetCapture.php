<?php

class Mock_DataSetCapture extends Mock_DataSetBase {
    protected $sequenceId = 0;

    public function add($key, $value, $section = null) {
        if (null === $key) {
            $key = $this->sequenceId++;
        }

        if (null !== $key) {
            $key = array($section, $key);
        }

        if ($this->branchKey) {
            $key = array_merge($this->branchKey, is_array($key) ? $key : array($key));
        }

        $this->storage->set($key, $value);
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