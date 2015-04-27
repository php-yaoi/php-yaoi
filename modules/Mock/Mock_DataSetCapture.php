<?php

/**
 * Class Mock_DataSetCapture
 * @deprecated
 */
class Mock_DataSetCapture extends Mock_DataSetBase {
    /**
     * @param $value
     * @param $key
     */
    public function add($value, $key = null) {
        if (null === $key) {
            $key = $this->sequenceId++;
        }

        if ($this->branchKey) {
            $key = array_merge($this->branchKey, array($key));
        }

        $this->storage->set($key, $value);
    }


    protected $temp = array();

    /**
     * Store or retrieve temporary data relevant to current mock/branch
     *
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