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

        return $this->storage->get($key);
    }
}