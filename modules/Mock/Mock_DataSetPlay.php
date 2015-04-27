<?php

/**
 * Class Mock_DataSetPlay
 * @deprecated
 */
class Mock_DataSetPlay extends Mock_DataSetBase {

    /**
     * @param null $key
     * @return mixed
     * @throws Mock_Exception
     */
    public function get($key = null) {
        if (null === $key) {
            $key = $this->sequenceId++;
        }

        if ($this->branchKey) {
            $key = array_merge($this->branchKey, array($key));
        }

        $result = $this->storage->get($key);
        if ((null === $result) && !$this->storage->keyExists($key)) {
            throw new Mock_Exception('Record not found: ' . print_r($key, 1), Mock_Exception::KEY_NOT_FOUND);
        }
        return $result;
    }
}