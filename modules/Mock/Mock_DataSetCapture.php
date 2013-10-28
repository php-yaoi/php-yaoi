<?php

interface Mock_DataSetCapture extends Mock_DataSet {
    public function add($key, $value, $section = null);

    /**
     * @return static
     */
    public function create();

    public function temp($key, $value = null);
}