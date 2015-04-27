<?php

class Mock extends Mock_DataSetBase {
    const MODE_COMBINED = 0;
    const MODE_PLAY = 1;
    const MODE_CAPTURE = 2;

    protected $mode = self::MODE_COMBINED;

    public function setMode($mode) {
        $this->mode = $mode;
        return $this;
    }

    public function getMode() {
        return $this->mode;
    }

    /**
     * @param null $key
     * @param callable $addOnMiss
     * @return mixed
     * @throws Mock_Exception
     */
    public function get($key = null, Closure $addOnMiss = null) {
        if ($this->mode === self::MODE_CAPTURE) {
            throw new Mock_Exception('Reading disabled in capture mode', Mock_Exception::PLAY_REQUIRED);
        }

        if (null === $key) {
            $key = $this->sequenceId++;
        }

        $fullKey = $key;

        if ($this->branchKey) {
            $fullKey = array_merge($this->branchKey, array($key));
        }

        $result = $this->storage->get($fullKey);
        if ((null === $result) && !$this->storage->keyExists($fullKey)) {
            if (null === $addOnMiss) {
                throw new Mock_Exception('Record not found: ' . print_r($fullKey, 1), Mock_Exception::KEY_NOT_FOUND);
            }
            else {
                $result = $addOnMiss();
                $this->add($result, $key);
            }

        }
        return $result;
    }

    /**
     * @param $value
     * @param null $key
     * @throws Mock_Exception
     */
    public function add($value, $key = null) {
        if ($this->mode === self::MODE_PLAY) {
            throw new Mock_Exception('Writing disabled in play mode', Mock_Exception::CAPTURE_REQUIRED);
        }

        if (null === $key) {
            $key = $this->sequenceId++;
        }

        if ($this->branchKey) {
            $key = array_merge($this->branchKey, array($key));
        }

        $this->storage->set($key, $value);
        return $this;
    }


    protected $temp = array();

    /**
     * Store or retrieve temporary data (or non-storable resources) relevant to current mock/branch
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