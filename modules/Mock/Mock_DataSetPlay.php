<?php

class Mock_DataSetPlay extends Mock_DataSetBase {
    protected $sequenceId = 0;


    public function get($key = null, $section = null) {
        if (null === $key) {
            $key = $this->sequenceId++;
        }

        if (null !== $key) {
            $key = array($section, $key);
        }

        if ($this->branchKey) {
            $key = array_merge($this->branchKey, is_array($key) ? $key : array($key));
        }

        $result = $this->storage->get($key);
        if ((null === $result) && !$this->storage->keyExists($key)) {
            throw new Mock_Exception('Record not found: ' . print_r($key, 1), Mock_Exception::KEY_NOT_FOUND);
        }
        return $result;
    }
}