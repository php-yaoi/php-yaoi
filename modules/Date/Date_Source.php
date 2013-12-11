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


    public function date($format, $timestamp = null) {
        if (null === $timestamp) {
            $result = date($format, $this->now());
        }
        else {
            $result = date($format, $timestamp);
        }
        return $result;
    }


    private $year;
    private $month;
    private $day;
    private $rusMonths = array(
        'января' => '01',
        'февраля' => '02',
        'марта' => '03',
        'апреля' => '04',
        'мая' => '05',
        'июня' => '06',
        'июля' => '07',
        'августа' => '08',
        'сентября' => '09',
        'октября' => '10',
        'ноября' => '11',
        'декабря' => '12',
    );

    public function rusDayMonthToDate($string) {
        if (null === $this->year || null === $this->month) {
            list($this->year, $this->month, $this->day) = explode('/', $this->date('Y/m/d'));
        }

        list($d, $m) = explode(' ', strtr($string, $this->rusMonths));

        if (strpos($m, '.') !== false) {
            if ($d < $this->day) {
                $m = $this->month + 1;
                if ($m > 12) {
                    $m = 1;
                }
            }
            else {
                $m = $this->month;
            }
        }

        if ($d < 10) {
            $d = '0' . $d;
        }

        if ($m < 10) {
            $m = '0' . (1 * $m);
        }


        if ($m < $this->month) {
            return ($this->year + 1) . '-' . $m . '-' . $d;
        }
        else {
            return $this->year . '-' . $m . '-' . $d;
        }

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