<?php
interface Mock_Able {
    public function mockRecordStart(Mock_DataSet $dataSet);
    public function mockRecordStop();
    public function mockPlay(Mock_DataSet $dataSet);
}