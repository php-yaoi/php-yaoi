<?php
interface Mock_Able {
    /**
     * @param Mock_DataSet $dataSet
     * @return $this
     */
    public function mock(Mock_DataSet $dataSet = null);
}