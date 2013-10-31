<?php

abstract class Database_Abstract_Mock_DriverCapture extends Database_Driver implements Mock_Able {
    /**
     * @var Database_Driver
     */
    protected $driver;

    /**
     * @param Database_Driver $driver
     */
    public function setOriginalDriver(Database_Driver $driver) {
        $this->driver = $driver;
    }

    public function query($statement)
    {
        $queryMock = $this->mock->branch($statement, self::QUERY);
        $queryMock->temp(self::RESULT, $this->driver->query($statement));
        return $queryMock;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function lastInsertId($queryMock)
    {
        $res = $this->driver->lastInsertId($queryMock->temp(self::RESULT));
        $queryMock->add(self::LAST_INSERT_ID, $res);
        return $res;
    }

    public function escape($value)
    {
        $res = $this->driver->escape($value);
        $this->mock->add($value, $res, self::ESCAPE);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rewind($queryMock)
    {
        $res =  $this->driver->rewind($queryMock->temp(self::RESULT));
        $queryMock->add(self::REWIND, $res);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        $row = $this->driver->fetchAssoc($queryMock->temp(self::RESULT));
        $queryMock->add(null, $row, self::ASSOC_ROWS);
        return $row;
    }


    /**
     * @var Mock_DataSetCapture
     */
    protected $mock;
    public function mock(Mock_DataSet $dataSet = null)
    {
        if ($dataSet instanceof Mock_DataSetCapture) {
            $this->mock = $dataSet;
        }
        else {
            throw new Mock_Exception(Mock_Exception::CAPTURE_REQUIRED, 'Capture data set required');
        }
    }

}