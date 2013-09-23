<?php

abstract class Database_Abstract_Driver implements Database_Server_Generic, Mock_Able {
    /**
     * @var Database_Dsn
     */
    protected $dsn;
    public function __construct(Database_Dsn $dsn) {
        $this->dsn = $dsn;
    }

    public function quote($value) {
        if (null === $value) {
            return 'NULL';
        }
        elseif (is_int($value)) {
            return (string)$value;
        }
        elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        elseif (is_array($value) || $value instanceof Iterator) {
            $result = '';
            foreach ($value as $item) {
                $result .= $this->quote($item) . ', ';
            }
            return substr($result, 0, -2);
        }
        else {
            return "'" . $this->escape($value) . "'";
        }
    }

    /**
     * @var Mock_DataSet
     */
    protected $mockDataSet;
    protected $mockRecord = false;
    public function mockRecord(Mock_DataSet $dataSet)
    {
        if ($this->mockPlay) {
            throw new Mock_Exception('Mock playback already started', Mock_Exception::ALREADY_PLAYING);
        }
        $this->mockDataSet = $dataSet;
        $this->mockRecord = true;
    }

    public function mockStop()
    {
        unset($this->mockDataSet);
        $this->mockRecord = false;
        $this->mockPlay = false;
    }

    protected $mockPlay = false;
    public function mockPlay(Mock_DataSet $dataSet)
    {
        if ($this->mockRecord) {
            throw new Mock_Exception('Mock recording already started', Mock_Exception::ALREADY_RECORDING);
        }
        $this->mockDataSet = $dataSet;
        $this->mockPlay = true;
    }


    public function query($statement)
    {
        if ($this->mockPlay) {
            return $this->mockDataSet->get($statement);
        }
        else {
            //return $this->
        }
    }

    public function lastInsertId()
    {
        // TODO: Implement lastInsertId() method.
    }

    public function escape($value)
    {
        // TODO: Implement escape() method.
    }

    public function rewind($result)
    {
        // TODO: Implement rewind() method.
    }

    public function fetchAssoc($result)
    {
        // TODO: Implement fetchAssoc() method.
    }
}