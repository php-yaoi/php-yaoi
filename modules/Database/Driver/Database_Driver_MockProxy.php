<?php

class Database_Driver_MockProxy extends Database_Driver {
    const RESULT = 'result';
    const LAST_INSERT_ID = 'lid';
    const QUERY = 'query';
    const ESCAPE = 'escape';
    const ERROR_MESSAGE = 'error';
    const ASSOC_ROWS = 'assoc_rows';
    const REWIND = 'rewind';
    const ROWS_AFFECTED = 'rows_affected';
    /**
     * @var Database_Driver
     */
    public $driver;

    /**
     * @var Mock_DataSetCapture
     */
    private $lastQuery;

    /**
     * @param $statement
     * @return static
     */
    public function query($statement)
    {
        $queryMock = $this->mock->branch(Database_Driver_MockProxy::QUERY, $statement);
        if ($queryMock->isEmptyBranch) {
            $queryMock->temp(Database_Driver_MockProxy::RESULT, $this->driver->query($statement));
            $this->lastQuery = $queryMock;
            $this->lastInsertId();
            $this->rowsAffected($queryMock);
        }
        else {
            $this->lastQuery = $queryMock;
        }
        return $queryMock;
    }

    /**
     * @return mixed
     */
    public function lastInsertId()
    {
        $queryMock = $this->lastQuery;
        $driver = $this->driver;
        return $queryMock->get(Database_Driver_MockProxy::LAST_INSERT_ID, function () use ($queryMock, $driver) {
            $res = $driver->lastInsertId($queryMock->temp(Database_Driver_MockProxy::RESULT));
            return $res;
        });
    }


    /**
     * @param Mock $queryMock
     * @return mixed
     */
    public function rowsAffected($queryMock)
    {
        $driver = $this->driver;
        return $queryMock->get(Database_Driver_MockProxy::ROWS_AFFECTED, function() use ($queryMock, $driver) {
            return $driver->rowsAffected($queryMock->temp(Database_Driver_MockProxy::RESULT));
        });
    }


    public function escape($value)
    {
        $driver = $this->driver;
        return $this->mock->branch(Database_Driver_MockProxy::ESCAPE)->get($value, function () use ($value, $driver) {
            return $driver->escape($value);
        });
    }

    /**
     * @param Mock_DataSetCapture $queryMock
     * @return mixed
     */
    public function rewind($queryMock)
    {
        $driver = $this->driver;
        return $queryMock->branch(Database_Driver_MockProxy::REWIND)->get(null, function () use ($queryMock, $driver) {
            return $driver->rewind($queryMock->temp(Database_Driver_MockProxy::RESULT));
        });
    }

    /**
     * @param Mock $queryMock
     * @return mixed
     */
    public function fetchAssoc($queryMock)
    {
        $driver = $this->driver;
        return $queryMock->branch(Database_Driver_MockProxy::ASSOC_ROWS)->get(null, function () use ($queryMock, $driver) {
            return $driver->fetchAssoc($queryMock->temp(Database_Driver_MockProxy::RESULT));
        });
    }


    /**
     * @var Mock
     */
    protected $mock;
    public function mock(Mock $dataSet = null)
    {
        if (null === $dataSet) {
            $dataSet = Mock::getNull();
        }

        $this->mock = $dataSet;
    }

    /**
     * @param Mock $queryMock
     * @return mixed
     */
    public function queryErrorMessage($queryMock)
    {
        $driver = $this->driver;
        return $queryMock->get(Database_Driver_MockProxy::ERROR_MESSAGE, function () use ($queryMock, $driver) {
            return $driver->queryErrorMessage($queryMock->temp(Database_Driver_MockProxy::RESULT));
        });
    }

    public function disconnect()
    {
    }
}