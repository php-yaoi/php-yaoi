<?php
interface Mock_Able {
    /**
     * @param Mock $dataSet
     * @return $this
     */
    public function mock(Mock $dataSet = null);
}