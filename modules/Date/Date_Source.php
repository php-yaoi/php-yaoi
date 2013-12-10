<?php

class Date_Source implements Mock_Able {

    public function now() {
        if ($this->mock) {
            if ($this->mock instanceof Mock_DataSetPlay) {
                return $this->mock->get();
            }
            elseif ($this->mock instanceof Mock_DataSetCapture) {
                $now = time();
                $this->mock->add(null, $now);
                return $now;
            }
        }
        return time();
    }

    public function strToTime($string) {
        if ($this->mock) {
            if ($this->mock instanceof Mock_DataSetPlay) {
                return $this->mock->get(array($string, null));
            }
            elseif ($this->mock instanceof Mock_DataSetCapture) {
                $ut = strtotime($string);
                $this->mock->add(array($string, null), $ut);
                return $ut;
            }
        }

        $ut = strtotime($string);

        return $ut;
    }


    /**
     * @var Mock_DataSet
     */
    protected $mock;
    public function mock(Mock_DataSet $dataSet = null)
    {
        $this->mock = $dataSet;
        return $this;
    }
}