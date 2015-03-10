<?php

class Date_Source implements Mock_Able {

    const MOCK_NOW = 'now';
    const MOCK_STR_TO_TIME = 'str_to_time';
    const MOCK_MICRO_NOW = 'micro_now';

    public function now() {
        if ($this->mock) {
            if ($this->mock instanceof Mock_DataSetPlay) {
                return $this->mock->branch(static::MOCK_NOW)->get();
            }
            elseif ($this->mock instanceof Mock_DataSetCapture) {
                $now = time();
                $this->mock->branch(static::MOCK_NOW)->add($now);
                return $now;
            }
        }
        return time();
    }

    public function microNow() {
        if ($this->mock) {
            if ($this->mock instanceof Mock_DataSetPlay) {
                return $this->mock->branch(static::MOCK_MICRO_NOW)->get();
            }
            elseif ($this->mock instanceof Mock_DataSetCapture) {
                $now = microtime(1);
                $this->mock->branch(static::MOCK_MICRO_NOW)->add($now);
                return $now;
            }
        }
        return microtime(1);
    }

    public function strToTime($string, $now = null) {
        if ($this->mock) {
            if ($this->mock instanceof Mock_DataSetPlay) {
                return $this->mock->branch(static::MOCK_STR_TO_TIME, $string)->get();
            }
            elseif ($this->mock instanceof Mock_DataSetCapture) {
                $ut = strtotime($string, $now);
                $this->mock->branch(static::MOCK_STR_TO_TIME, $string)->add($ut);
                //$this->mock->add(array(null, $string), $ut, static::MOCK_STR_TO_TIME);
                return $ut;
            }
        }

        $ut = strtotime($string, $now);

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
    private static $rusMonths = array(
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
        if (null === $this->year || null === $this->month || null === $this->day) {
            list($this->year, $this->month, $this->day) = explode('/', $this->date('Y/m/d'));
        }

        list($d, $m) = explode(' ', strtr($string, self::$rusMonths));

        /**
         * parsing 12 д. as 2013-12-12
         */
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

            if (strlen($m) < 2) {
                $m = '0' . $m;
            }
        }


        if (strlen($d) < 2) {
            $d = '0' . $d;
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