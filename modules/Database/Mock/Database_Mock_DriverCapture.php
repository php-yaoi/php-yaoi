<?php

/**
 * Class Database_Mock_DriverCapture
 * TODO proxy quote
 * TODO proxy quoteSymbol
 */
class Database_Mock_DriverCapture extends Database_Driver implements Mock_Able {
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

    /**
     * @var Mock_DataSetCapture
     */
    private $lastQuery;

    public function query($statement)
    {
        $queryMock = $this->mock->branch(self::QUERY, $statement);
        $queryMock->temp(self::RESULT, $this->driver->query($statement));
        $this->lastQuery = $queryMock;
        return $queryMock;
    }

    /**
     * @return mixed
     */
    public function lastInsertId()
    {
        $queryMock = $this->lastQuery;
        $res = $this->driver->lastInsertId($queryMock->temp(self::RESULT));
        $queryMock->add($res, self::LAST_INSERT_ID);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rowsAffected($queryMock)
    {
        $res = $this->driver->rowsAffected($queryMock->temp(self::RESULT));
        $queryMock->add($res, self::ROWS_AFFECTED);
        return $res;
    }


    public function escape($value)
    {
        $res = $this->driver->escape($value);
        $this->mock->branch(self::ESCAPE)->add($res, $value);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rewind($queryMock)
    {
        $res = $this->driver->rewind($queryMock->temp(self::RESULT));
        $queryMock->branch(self::REWIND)->add($res);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        $row = $this->driver->fetchAssoc($queryMock->temp(self::RESULT));
        $queryMock->branch(self::ASSOC_ROWS)->add($row);
        return $row;
    }


    /**
     * @var Mock_DataSetCapture
     */
    protected $mock;
    public function mock(Mock $dataSet = null)
    {
        if ($dataSet->mode === Mock::MODE_CAPTURE) {
            $this->mock = $dataSet;
        }
        else {
            throw new Mock_Exception(Mock_Exception::CAPTURE_REQUIRED, 'Capture data set required');
        }
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     */
    public function queryErrorMessage($queryMock)
    {
        $err = $this->driver->queryErrorMessage($queryMock->temp(self::RESULT));
        $queryMock->add($err, self::ERROR_MESSAGE);
        return $err;
    }

    public function disconnect()
    {
    }

}