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
        $queryMock = $this->mock->branch(Database_Driver_MockProxy::QUERY, $statement);
        $queryMock->temp(Database_Driver_MockProxy::RESULT, $this->driver->query($statement));
        $this->lastQuery = $queryMock;
        return $queryMock;
    }

    /**
     * @return mixed
     */
    public function lastInsertId()
    {
        $queryMock = $this->lastQuery;
        $res = $this->driver->lastInsertId($queryMock->temp(Database_Driver_MockProxy::RESULT));
        $queryMock->add($res, Database_Driver_MockProxy::LAST_INSERT_ID);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rowsAffected($queryMock)
    {
        $res = $this->driver->rowsAffected($queryMock->temp(Database_Driver_MockProxy::RESULT));
        $queryMock->add($res, Database_Driver_MockProxy::ROWS_AFFECTED);
        return $res;
    }


    public function escape($value)
    {
        $res = $this->driver->escape($value);
        $this->mock->branch(Database_Driver_MockProxy::ESCAPE)->add($res, $value);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rewind($queryMock)
    {
        $res = $this->driver->rewind($queryMock->temp(Database_Driver_MockProxy::RESULT));
        $queryMock->branch(Database_Driver_MockProxy::REWIND)->add($res);
        return $res;
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     */
    public function fetchAssoc($queryMock)
    {
        $row = $this->driver->fetchAssoc($queryMock->temp(Database_Driver_MockProxy::RESULT));
        $queryMock->branch(Database_Driver_MockProxy::ASSOC_ROWS)->add($row);
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
        $err = $this->driver->queryErrorMessage($queryMock->temp(Database_Driver_MockProxy::RESULT));
        $queryMock->add($err, Database_Driver_MockProxy::ERROR_MESSAGE);
        return $err;
    }

    public function disconnect()
    {
    }

}