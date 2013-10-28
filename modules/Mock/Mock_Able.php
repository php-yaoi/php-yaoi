<?php
interface Mock_Able {
    const MOCK_DETACHED = 0;
    const MOCK_PLAY = 1;
    const MOCK_CAPTURE = 2;

    public function mockCapture(Mock_DataSet $dataSet);
    public function mockPlay(Mock_DataSet $dataSet);
    public function mockDetach();
}