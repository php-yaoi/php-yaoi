<?php
interface Mock_Able {
    public function mockRecord(Mock_DataSet $dataSet);
    public function mockStop();
    public function mockPlay(Mock_DataSet $dataSet);
}